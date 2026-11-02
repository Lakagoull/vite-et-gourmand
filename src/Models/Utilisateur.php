<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Utilisateur
{
    /** Règle du mot de passe : 10 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial. */
    public const REGLE_MOT_DE_PASSE = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,}$/';

    /** Numéro de mobile français : 06 / 07, avec ou sans +33, espaces, points ou tirets. */
    public const REGLE_GSM = '/^(?:(?:\+|00)33\s?|0)[67](?:[\s.-]?\d{2}){4}$/';

    public static function motDePasseValide(string $motDePasse): bool
    {
        return (bool) preg_match(self::REGLE_MOT_DE_PASSE, $motDePasse);
    }

    public static function trouverParEmail(string $email): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT u.*, r.libelle AS role FROM utilisateur u JOIN role r ON r.role_id = u.role_id WHERE u.email = :email'
        );
        $stmt->execute(['email' => mb_strtolower($email)]);
        return $stmt->fetch() ?: null;
    }

    public static function trouverParId(int $id): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT u.*, r.libelle AS role FROM utilisateur u JOIN role r ON r.role_id = u.role_id WHERE u.utilisateur_id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Crée un compte. Le mot de passe est haché avec bcrypt (password_hash). */
    public static function creer(array $donnees, string $role): int
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            'INSERT INTO utilisateur (email, password, nom, prenom, telephone, ville, pays, adresse_postale, role_id)
             VALUES (:email, :password, :nom, :prenom, :telephone, :ville, :pays, :adresse,
                     (SELECT role_id FROM role WHERE libelle = :role))'
        );
        $stmt->execute([
            'email'     => mb_strtolower($donnees['email']),
            'password'  => password_hash($donnees['password'], PASSWORD_DEFAULT),
            'nom'       => $donnees['nom'],
            'prenom'    => $donnees['prenom'],
            'telephone' => $donnees['telephone'] ?? null,
            'ville'     => $donnees['ville'] ?? null,
            'pays'      => $donnees['pays'] ?? 'France',
            'adresse'   => $donnees['adresse_postale'] ?? null,
            'role'      => $role,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function changerMotDePasse(int $id, string $motDePasse): void
    {
        $stmt = Database::get()->prepare('UPDATE utilisateur SET password = :password WHERE utilisateur_id = :id');
        $stmt->execute(['password' => password_hash($motDePasse, PASSWORD_DEFAULT), 'id' => $id]);
    }

    // -----------------------------------------------------------------
    // Réinitialisation du mot de passe
    // -----------------------------------------------------------------

    /** Crée un jeton valable 1 heure. Seul son empreinte SHA-256 est stockée en base. */
    public static function creerJetonReinitialisation(int $id): string
    {
        $jeton = bin2hex(random_bytes(32));
        $pdo = Database::get();
        // Un seul lien actif à la fois : les anciens sont invalidés
        $pdo->prepare('UPDATE reinitialisation_mdp SET utilise = TRUE WHERE utilisateur_id = :id')->execute(['id' => $id]);
        $pdo->prepare(
            'INSERT INTO reinitialisation_mdp (utilisateur_id, token_hash, date_expiration)
             VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        )->execute(['id' => $id, 'hash' => hash('sha256', $jeton)]);
        return $jeton;
    }

    /** Retourne l'id de l'utilisateur si le jeton est valide, non utilisé et non expiré. */
    public static function verifierJeton(string $jeton): ?int
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $jeton)) {
            return null;
        }
        $stmt = Database::get()->prepare(
            'SELECT utilisateur_id FROM reinitialisation_mdp
             WHERE token_hash = :hash AND utilise = FALSE AND date_expiration > NOW()'
        );
        $stmt->execute(['hash' => hash('sha256', $jeton)]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public static function consommerJeton(string $jeton): void
    {
        Database::get()->prepare('UPDATE reinitialisation_mdp SET utilise = TRUE WHERE token_hash = :hash')
            ->execute(['hash' => hash('sha256', $jeton)]);
    }
}
