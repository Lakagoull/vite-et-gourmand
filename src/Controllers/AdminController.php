<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Menu;
use App\Models\Utilisateur;
use App\Services\Auth;
use App\Services\Mailer;
use App\Services\Statistiques;

/**
 * Espace administrateur : comptes employés et statistiques (MongoDB).
 */
final class AdminController
{
    // -----------------------------------------------------------------
    // Comptes employés
    // -----------------------------------------------------------------

    public function employes(): void
    {
        Auth::exiger('administrateur');
        render('admin/employes', [
            'titrePage' => 'Comptes employés',
            'employes'  => Utilisateur::employes(),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
            'scripts'   => ['mot-de-passe'],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    /**
     * Création d'un compte employé : e-mail (identifiant) + mot de passe.
     * Le rôle "employe" est imposé côté serveur : aucun compte administrateur ne peut être créé ici.
     */
    public function creerEmploye(): void
    {
        verifierCsrf();
        Auth::exiger('administrateur');

        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $motDePasse = (string) ($_POST['password'] ?? '');

        $erreurs = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Indiquez une adresse e-mail valide.';
        } elseif (Utilisateur::trouverParEmail($email)) {
            $erreurs['email'] = 'Un compte existe déjà avec cette adresse e-mail.';
        }
        if (mb_strlen($prenom) > 50 || mb_strlen($nom) > 50) {
            $erreurs['prenom'] = 'Le prénom et le nom sont limités à 50 caractères.';
        }
        if (!Utilisateur::motDePasseValide($motDePasse)) {
            $erreurs['password'] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
        } elseif ($motDePasse !== ($_POST['password_confirmation'] ?? '')) {
            $erreurs['password_confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = ['email' => $email, 'prenom' => $prenom, 'nom' => $nom];
            redirect('/admin/employes');
        }

        Utilisateur::creer([
            'email' => $email, 'password' => $motDePasse,
            'prenom' => $prenom, 'nom' => $nom, 'pays' => 'France',
        ], 'employe');

        // Le mot de passe n'est volontairement PAS envoyé par e-mail
        Mailer::envoyer(
            $email,
            'Votre compte employé Vite & Gourmand',
            '<p>Bonjour' . ($prenom !== '' ? ' ' . e($prenom) : '') . ',</p>'
            . '<p>Un compte employé a été créé pour vous sur l\'application Vite &amp; Gourmand.</p>'
            . '<p>Votre identifiant de connexion est votre adresse e-mail : <strong>' . e($email) . '</strong></p>'
            . '<p>Pour des raisons de sécurité, votre mot de passe ne vous est pas communiqué par e-mail : '
            . '<strong>merci de vous rapprocher de l\'administrateur pour l\'obtenir</strong>.</p>'
            . '<p><a href="' . e(env('APP_URL', '')) . '/connexion" style="color:#7A1F3D">Accéder à la page de connexion</a></p>'
        );

        flash('success', 'Le compte employé ' . $email . ' a été créé. Un e-mail l\'en informe (sans le mot de passe, à lui transmettre de vive voix).');
        redirect('/admin/employes');
    }

    /** Désactivation (départ de l'entreprise) ou réactivation d'un compte employé. */
    public function basculerEmploye(): void
    {
        verifierCsrf();
        Auth::exiger('administrateur');
        $actif = ($_POST['actif'] ?? '') === '1';
        if (Utilisateur::changerActif((int) ($_POST['utilisateur_id'] ?? 0), $actif)) {
            flash('success', $actif ? 'Le compte a été réactivé.' : 'Le compte a été désactivé : l\'employé ne peut plus se connecter.');
        } else {
            flash('danger', 'Action impossible sur ce compte.');
        }
        redirect('/admin/employes');
    }

    // -----------------------------------------------------------------
    // Statistiques (données issues de MongoDB)
    // -----------------------------------------------------------------

    public function statistiques(): void
    {
        Auth::exiger('administrateur');

        $filtres = ['menu_id' => 0, 'du' => '', 'au' => ''];
        $menuId = filter_var($_GET['menu_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($menuId) {
            $filtres['menu_id'] = $menuId;
        }
        foreach (['du', 'au'] as $cle) {
            $valeur = (string) ($_GET[$cle] ?? '');
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $valeur);
            if ($date && $date->format('Y-m-d') === $valeur) {
                $filtres[$cle] = $valeur;
            }
        }
        $erreur = null;
        if ($filtres['du'] && $filtres['au'] && $filtres['du'] > $filtres['au']) {
            $erreur = 'La date de début doit être antérieure à la date de fin.';
            $filtres['au'] = '';
        }

        $menus = Menu::listeGestion();
        try {
            $resultats = Statistiques::parMenu($filtres);
        } catch (\Throwable $e) {
            error_log('Statistiques MongoDB : ' . $e->getMessage());
            $resultats = [];
            $erreur = 'Les statistiques sont momentanément indisponibles (base MongoDB injoignable).';
        }

        // Les menus sans commande apparaissent avec 0 pour pouvoir les comparer
        $parId = array_column($resultats, null, 'menu_id');
        $lignes = [];
        foreach ($menus as $m) {
            $id = (int) $m['menu_id'];
            if ($filtres['menu_id'] && $id !== $filtres['menu_id']) {
                continue;
            }
            $lignes[] = $parId[$id] ?? ['menu_id' => $id, 'titre' => $m['titre'], 'commandes' => 0, 'personnes' => 0, 'ca' => 0.0];
        }
        usort($lignes, fn ($a, $b) => [$b['commandes'], $b['ca']] <=> [$a['commandes'], $a['ca']]);

        render('admin/statistiques', [
            'titrePage' => 'Statistiques',
            'menus'     => $menus,
            'filtres'   => $filtres,
            'lignes'    => $lignes,
            'totalCommandes' => array_sum(array_column($lignes, 'commandes')),
            'totalCa'   => array_sum(array_column($lignes, 'ca')),
            'erreur'    => $erreur,
            'scripts'   => ['graphique'],
        ]);
    }
}
