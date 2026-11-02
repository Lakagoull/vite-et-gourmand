<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use App\Models\Utilisateur;

/**
 * Gestion de la connexion et des droits d'accès (rôles).
 */
final class Auth
{
    /** Nombre d'échecs autorisés avant blocage, et durée de la fenêtre (minutes). */
    private const MAX_TENTATIVES = 5;
    private const FENETRE_MINUTES = 15;

    /**
     * Vérifie les identifiants. Retourne l'utilisateur ou un message d'erreur.
     * Le message est volontairement identique si l'e-mail ou le mot de passe est faux
     * (on ne révèle pas quels comptes existent).
     */
    public static function tenterConnexion(string $email, string $motDePasse): array|string
    {
        $email = mb_strtolower(trim($email));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';

        if (self::estBloque($email, $ip)) {
            return 'Trop de tentatives de connexion. Merci de réessayer dans ' . self::FENETRE_MINUTES . ' minutes.';
        }

        $utilisateur = Utilisateur::trouverParEmail($email);
        // password_verify est exécuté même si le compte n'existe pas (temps de réponse constant)
        $hash = $utilisateur['password'] ?? '$2y$10$mFTUmwTsnMD8j05M/Kv6GumzKZC0PmxuoNpAMXZedcVD0f4U2Ww3C';
        $ok = password_verify($motDePasse, $hash) && $utilisateur !== null;

        if (!$ok) {
            self::enregistrerEchec($email, $ip);
            return 'Adresse e-mail ou mot de passe incorrect.';
        }
        if (!$utilisateur['actif']) {
            return 'Ce compte a été désactivé. Contactez l\'administrateur.';
        }

        // Mise à jour automatique du hachage si l'algorithme par défaut de PHP évolue
        if (password_needs_rehash($utilisateur['password'], PASSWORD_DEFAULT)) {
            Utilisateur::changerMotDePasse((int) $utilisateur['utilisateur_id'], $motDePasse);
        }
        self::effacerEchecs($email);
        return $utilisateur;
    }

    /** Ouvre la session de l'utilisateur. */
    public static function connecter(array $utilisateur): void
    {
        // Nouvel identifiant de session : protection contre la fixation de session
        session_regenerate_id(true);
        $_SESSION['utilisateur'] = [
            'id'     => (int) $utilisateur['utilisateur_id'],
            'prenom' => $utilisateur['prenom'],
            'nom'    => $utilisateur['nom'],
            'email'  => $utilisateur['email'],
            'role'   => $utilisateur['role'],
        ];
    }

    public static function deconnecter(): void
    {
        $_SESSION = [];
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        session_destroy();
    }

    public static function utilisateur(): ?array
    {
        return $_SESSION['utilisateur'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['utilisateur']['role'] ?? null;
    }

    /**
     * Protège une page : redirige vers la connexion si besoin, refuse l'accès si le rôle ne convient pas.
     * L'administrateur a accès à tout ce que peut faire un employé.
     */
    public static function exiger(string ...$roles): array
    {
        $utilisateur = self::utilisateur();
        if ($utilisateur === null) {
            flash('info', 'Merci de vous connecter pour accéder à cette page.');
            redirect('/connexion?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        }

        // Vérifie en base que le compte est toujours actif (ex. employé désactivé entre-temps)
        $enBase = Utilisateur::trouverParId($utilisateur['id']);
        if ($enBase === null || !$enBase['actif']) {
            self::deconnecter();
            session_start();
            flash('danger', 'Votre compte n\'est plus actif.');
            redirect('/connexion');
        }

        if ($roles && !in_array($utilisateur['role'], $roles, true)
            && !($utilisateur['role'] === 'administrateur' && in_array('employe', $roles, true))) {
            http_response_code(403);
            render('erreur', ['titre' => 'Accès refusé', 'message' => 'Vous n\'avez pas les droits pour accéder à cette page.']);
            exit;
        }
        return $utilisateur;
    }

    /** Page d'accueil de l'espace selon le rôle. */
    public static function accueilEspace(?string $role = null): string
    {
        return match ($role ?? self::role()) {
            'administrateur' => '/admin',
            'employe'        => '/employe',
            default          => '/espace',
        };
    }

    // -----------------------------------------------------------------
    // Protection contre la force brute
    // -----------------------------------------------------------------

    private static function estBloque(string $email, string $ip): bool
    {
        $stmt = Database::get()->prepare(
            'SELECT
                SUM(email = :email)   AS par_email,
                SUM(adresse_ip = :ip) AS par_ip
             FROM tentative_connexion
             WHERE date_tentative > DATE_SUB(NOW(), INTERVAL ' . self::FENETRE_MINUTES . ' MINUTE)'
        );
        $stmt->execute(['email' => $email, 'ip' => $ip]);
        $r = $stmt->fetch();
        return (int) $r['par_email'] >= self::MAX_TENTATIVES || (int) $r['par_ip'] >= self::MAX_TENTATIVES * 4;
    }

    private static function enregistrerEchec(string $email, string $ip): void
    {
        Database::get()->prepare('INSERT INTO tentative_connexion (email, adresse_ip) VALUES (:email, :ip)')
            ->execute(['email' => $email, 'ip' => $ip]);
    }

    private static function effacerEchecs(string $email): void
    {
        Database::get()->prepare('DELETE FROM tentative_connexion WHERE email = :email')->execute(['email' => $email]);
    }
}
