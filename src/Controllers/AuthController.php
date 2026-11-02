<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Utilisateur;
use App\Services\Auth;
use App\Services\Mailer;

final class AuthController
{
    // -----------------------------------------------------------------
    // Inscription
    // -----------------------------------------------------------------

    public function inscriptionForm(): void
    {
        if (Auth::utilisateur()) {
            redirect(Auth::accueilEspace());
        }
        $erreurs = $_SESSION['erreurs'] ?? [];
        render('inscription', [
            'titrePage' => 'Créer un compte',
            'erreurs'   => $erreurs,
            'redirect'  => self::redirectionSure($_GET['redirect'] ?? ''),
            'scripts'   => ['mot-de-passe'],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    public function inscrire(): void
    {
        verifierCsrf();

        $d = [
            'nom'             => trim((string) ($_POST['nom'] ?? '')),
            'prenom'          => trim((string) ($_POST['prenom'] ?? '')),
            'telephone'       => trim((string) ($_POST['telephone'] ?? '')),
            'email'           => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'adresse_postale' => trim((string) ($_POST['adresse_postale'] ?? '')),
            'ville'           => trim((string) ($_POST['ville'] ?? '')),
            'password'        => (string) ($_POST['password'] ?? ''),
        ];
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        $redirect = self::redirectionSure($_POST['redirect'] ?? '');

        $erreurs = [];
        if ($d['nom'] === '' || mb_strlen($d['nom']) > 50) {
            $erreurs['nom'] = 'Merci d\'indiquer votre nom (50 caractères maximum).';
        }
        if ($d['prenom'] === '' || mb_strlen($d['prenom']) > 50) {
            $erreurs['prenom'] = 'Merci d\'indiquer votre prénom (50 caractères maximum).';
        }
        if (!preg_match(Utilisateur::REGLE_GSM, $d['telephone'])) {
            $erreurs['telephone'] = 'Merci d\'indiquer un numéro de mobile valide (ex. : 06 12 34 56 78).';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 180) {
            $erreurs['email'] = 'Merci d\'indiquer une adresse e-mail valide.';
        } elseif (Utilisateur::trouverParEmail($d['email'])) {
            $erreurs['email'] = 'Un compte existe déjà avec cette adresse e-mail.';
        }
        if (mb_strlen($d['adresse_postale']) < 5 || mb_strlen($d['adresse_postale']) > 255) {
            $erreurs['adresse_postale'] = 'Merci d\'indiquer votre adresse postale (numéro, rue, code postal).';
        }
        if ($d['ville'] === '' || mb_strlen($d['ville']) > 80) {
            $erreurs['ville'] = 'Merci d\'indiquer votre ville.';
        }
        if (!Utilisateur::motDePasseValide($d['password'])) {
            $erreurs['password'] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
        } elseif ($d['password'] !== $confirmation) {
            $erreurs['password_confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }
        if (empty($_POST['cgu'])) {
            $erreurs['cgu'] = 'Vous devez accepter les conditions générales de vente et la politique de confidentialité.';
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            unset($d['password']);  // on ne réaffiche jamais un mot de passe
            $_SESSION['old'] = $d;
            redirect('/inscription' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
        }

        // Le rôle "utilisateur" est imposé côté serveur : impossible de choisir un autre rôle
        $id = Utilisateur::creer($d, 'utilisateur');

        Mailer::envoyer(
            $d['email'],
            'Bienvenue chez Vite & Gourmand',
            '<p>Bonjour ' . e($d['prenom']) . ',</p>'
            . '<p>Votre compte a bien été créé. Vous pouvez dès maintenant commander nos menus et suivre vos commandes depuis votre espace.</p>'
            . '<p><a href="' . e(env('APP_URL', '')) . '/menus" style="color:#7A1F3D">Découvrir nos menus</a></p>'
            . '<p>À très bientôt,<br>Julie et José</p>'
        );

        Auth::connecter(Utilisateur::trouverParId($id));
        flash('success', 'Bienvenue ' . $d['prenom'] . ' ! Votre compte a été créé. Un e-mail de bienvenue vous a été envoyé.');
        redirect($redirect ?: Auth::accueilEspace('utilisateur'));
    }

    // -----------------------------------------------------------------
    // Connexion / déconnexion
    // -----------------------------------------------------------------

    public function connexionForm(): void
    {
        if (Auth::utilisateur()) {
            redirect(Auth::accueilEspace());
        }
        $erreur = $_SESSION['erreur_connexion'] ?? null;
        render('connexion', [
            'titrePage' => 'Connexion',
            'erreur'    => $erreur,
            'redirect'  => self::redirectionSure($_GET['redirect'] ?? ''),
        ]);
        unset($_SESSION['erreur_connexion'], $_SESSION['old']);
    }

    public function connecter(): void
    {
        verifierCsrf();
        $email = (string) ($_POST['email'] ?? '');
        $redirect = self::redirectionSure($_POST['redirect'] ?? '');

        $resultat = Auth::tenterConnexion($email, (string) ($_POST['password'] ?? ''));
        if (is_string($resultat)) {
            $_SESSION['erreur_connexion'] = $resultat;
            $_SESSION['old'] = ['email' => $email];
            redirect('/connexion' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
        }

        Auth::connecter($resultat);
        flash('success', 'Bonjour ' . $resultat['prenom'] . ', vous êtes connecté(e).');
        redirect($redirect ?: Auth::accueilEspace($resultat['role']));
    }

    public function deconnecter(): void
    {
        verifierCsrf();
        Auth::deconnecter();
        session_start();
        flash('success', 'Vous êtes déconnecté(e). À bientôt !');
        redirect('/');
    }

    // -----------------------------------------------------------------
    // Mot de passe oublié
    // -----------------------------------------------------------------

    public function oubliForm(): void
    {
        render('mot-de-passe-oublie', ['titrePage' => 'Mot de passe oublié']);
    }

    public function envoyerLien(): void
    {
        verifierCsrf();
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));

        // Limitation : une demande par minute et par session
        if (isset($_SESSION['dernier_lien']) && time() - $_SESSION['dernier_lien'] < 60) {
            flash('warning', 'Une demande vient déjà d\'être faite. Merci de patienter une minute.');
            redirect('/mot-de-passe-oublie');
        }
        $_SESSION['dernier_lien'] = time();

        $utilisateur = filter_var($email, FILTER_VALIDATE_EMAIL) ? Utilisateur::trouverParEmail($email) : null;
        if ($utilisateur && $utilisateur['actif']) {
            $jeton = Utilisateur::creerJetonReinitialisation((int) $utilisateur['utilisateur_id']);
            $lien = env('APP_URL', '') . '/reinitialiser-mot-de-passe?token=' . $jeton;
            Mailer::envoyer(
                $utilisateur['email'],
                'Réinitialisation de votre mot de passe',
                '<p>Bonjour ' . e($utilisateur['prenom']) . ',</p>'
                . '<p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour en choisir un nouveau :</p>'
                . '<p><a href="' . e($lien) . '" style="color:#7A1F3D;font-weight:bold">Choisir un nouveau mot de passe</a></p>'
                . '<p>Ce lien est valable <strong>1 heure</strong> et ne peut être utilisé qu\'une seule fois.</p>'
                . '<p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez simplement ce message.</p>'
            );
        }

        // Même message dans tous les cas : on ne révèle pas si l'adresse a un compte
        flash('success', 'Si un compte existe avec cette adresse, un e-mail contenant un lien de réinitialisation vient de vous être envoyé.');
        redirect('/connexion');
    }

    public function reinitialiserForm(): void
    {
        $jeton = (string) ($_GET['token'] ?? '');
        if (Utilisateur::verifierJeton($jeton) === null) {
            flash('danger', 'Ce lien de réinitialisation est invalide ou a expiré. Merci de refaire une demande.');
            redirect('/mot-de-passe-oublie');
        }
        render('reinitialiser-mot-de-passe', [
            'titrePage' => 'Nouveau mot de passe',
            'jeton'     => $jeton,
            'erreurs'   => $_SESSION['erreurs'] ?? [],
            'scripts'   => ['mot-de-passe'],
        ]);
        unset($_SESSION['erreurs']);
    }

    public function reinitialiser(): void
    {
        verifierCsrf();
        $jeton = (string) ($_POST['token'] ?? '');
        $id = Utilisateur::verifierJeton($jeton);
        if ($id === null) {
            flash('danger', 'Ce lien de réinitialisation est invalide ou a expiré. Merci de refaire une demande.');
            redirect('/mot-de-passe-oublie');
        }

        $motDePasse = (string) ($_POST['password'] ?? '');
        $erreurs = [];
        if (!Utilisateur::motDePasseValide($motDePasse)) {
            $erreurs['password'] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
        } elseif ($motDePasse !== ($_POST['password_confirmation'] ?? '')) {
            $erreurs['password_confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }
        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            redirect('/reinitialiser-mot-de-passe?token=' . urlencode($jeton));
        }

        Utilisateur::changerMotDePasse($id, $motDePasse);
        Utilisateur::consommerJeton($jeton);
        flash('success', 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.');
        redirect('/connexion');
    }

    // -----------------------------------------------------------------

    /**
     * N'accepte que les redirections internes au site (protection "open redirect") :
     * le chemin doit commencer par "/" mais pas par "//".
     */
    private static function redirectionSure(mixed $chemin): string
    {
        $chemin = is_string($chemin) ? $chemin : '';
        return (str_starts_with($chemin, '/') && !str_starts_with($chemin, '//') && !str_contains($chemin, '\\'))
            ? $chemin : '';
    }
}
