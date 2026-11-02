<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Avis;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Utilisateur;
use App\Services\Auth;
use App\Services\Statistiques;

/**
 * Espace utilisateur (client) : commandes, suivi, avis, informations personnelles.
 */
final class EspaceController
{
    /** Liste des commandes du client. */
    public function utilisateur(): void
    {
        $u = Auth::exiger('utilisateur');
        render('espace/commandes', [
            'titrePage' => 'Mon espace',
            'u'         => $u,
            'commandes' => Commande::listerPourUtilisateur($u['id']),
        ]);
    }

    /** Détail d'une commande. */
    public function commande(): void
    {
        $u = Auth::exiger('utilisateur');
        $commande = $this->commandeDuClient($u['id'], (int) ($_GET['id'] ?? 0));
        render('espace/commande-detail', [
            'titrePage' => 'Commande ' . $commande['numero_commande'],
            'c'         => $commande,
            'erreurs'   => $_SESSION['erreurs'] ?? [],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    /** Formulaire de modification (uniquement si la commande est encore en attente). */
    public function modifierForm(): void
    {
        $u = Auth::exiger('utilisateur');
        $commande = $this->commandeDuClient($u['id'], (int) ($_GET['id'] ?? 0));
        if ($commande['statut'] !== 'en_attente') {
            flash('warning', 'Cette commande a déjà été acceptée : elle ne peut plus être modifiée en ligne. Contactez-nous.');
            redirect('/espace/commande?id=' . $commande['commande_id']);
        }
        render('espace/commande-modifier', [
            'titrePage' => 'Modifier la commande ' . $commande['numero_commande'],
            'c'         => $commande,
            'menu'      => Menu::trouver((int) $commande['menu_id']),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
            'valeurs'   => $_SESSION['old'] ?? [
                'adresse_prestation' => $commande['adresse_prestation'],
                'ville_prestation'   => $commande['ville_prestation'],
                'date_prestation'    => $commande['date_prestation'],
                'heure_livraison'    => substr($commande['heure_livraison'], 0, 5),
                'nombre_personne'    => $commande['nombre_personne'],
            ],
            'scripts'   => ['commande'],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    public function modifier(): void
    {
        verifierCsrf();
        $u = Auth::exiger('utilisateur');
        $commande = $this->commandeDuClient($u['id'], (int) ($_POST['commande_id'] ?? 0));
        $id = (int) $commande['commande_id'];

        // Le menu ne peut pas être changé : on utilise toujours celui de la commande
        $menu = Menu::trouver((int) $commande['menu_id']);
        [$donnees, $tarif, $erreurs] = CommandeController::analyserFormulaire($_POST, $menu);
        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = $_POST;
            redirect('/espace/commande/modifier?id=' . $id);
        }

        if (!Commande::modifier($id, $u['id'], $donnees, $tarif)) {
            flash('warning', 'La commande ne peut plus être modifiée (elle vient sans doute d\'être acceptée).');
            redirect('/espace/commande?id=' . $id);
        }
        $maj = Commande::trouver($id);
        Statistiques::enregistrerCommande($maj, $maj['menu_titre']);
        flash('success', 'Votre commande a été modifiée. Nouveau total : ' . prix($maj['prix_total']) . '.');
        redirect('/espace/commande?id=' . $id);
    }

    public function annuler(): void
    {
        verifierCsrf();
        $u = Auth::exiger('utilisateur');
        $id = (int) ($_POST['commande_id'] ?? 0);
        $this->commandeDuClient($u['id'], $id);

        if (Commande::annulerParClient($id, $u['id'])) {
            Statistiques::majStatut($id, 'annulee');
            flash('success', 'Votre commande a été annulée.');
        } else {
            flash('warning', 'Cette commande ne peut plus être annulée en ligne. Merci de nous contacter.');
        }
        redirect('/espace/commande?id=' . $id);
    }

    /** Dépôt d'un avis sur une commande terminée. */
    public function avis(): void
    {
        verifierCsrf();
        $u = Auth::exiger('utilisateur');
        $id = (int) ($_POST['commande_id'] ?? 0);
        $this->commandeDuClient($u['id'], $id);

        $note = filter_var($_POST['note'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        $commentaire = trim((string) ($_POST['commentaire'] ?? ''));
        $erreurs = [];
        if ($note === false || $note === null) {
            $erreurs['note'] = 'Merci de choisir une note entre 1 et 5.';
        }
        if (mb_strlen($commentaire) < 10 || mb_strlen($commentaire) > 1000) {
            $erreurs['commentaire'] = 'Votre commentaire doit contenir entre 10 et 1000 caractères.';
        }
        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = ['commentaire' => $commentaire];
            redirect('/espace/commande?id=' . $id . '#avis');
        }

        if (Avis::creer($id, $u['id'], $note, $commentaire)) {
            flash('success', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
        } else {
            flash('warning', 'Vous ne pouvez pas (ou plus) donner d\'avis sur cette commande.');
        }
        redirect('/espace/commande?id=' . $id);
    }

    // -----------------------------------------------------------------
    // Informations personnelles
    // -----------------------------------------------------------------

    public function profilForm(): void
    {
        $u = Auth::exiger('utilisateur');
        render('espace/profil', [
            'titrePage' => 'Mes informations',
            'client'    => Utilisateur::trouverParId($u['id']),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
            'scripts'   => ['mot-de-passe'],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    public function profil(): void
    {
        verifierCsrf();
        $u = Auth::exiger('utilisateur');
        $client = Utilisateur::trouverParId($u['id']);

        $d = [
            'nom'             => trim((string) ($_POST['nom'] ?? '')),
            'prenom'          => trim((string) ($_POST['prenom'] ?? '')),
            'telephone'       => trim((string) ($_POST['telephone'] ?? '')),
            'email'           => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'adresse_postale' => trim((string) ($_POST['adresse_postale'] ?? '')),
            'ville'           => trim((string) ($_POST['ville'] ?? '')),
        ];
        $erreurs = [];
        if ($d['nom'] === '' || mb_strlen($d['nom']) > 50) {
            $erreurs['nom'] = 'Merci d\'indiquer votre nom.';
        }
        if ($d['prenom'] === '' || mb_strlen($d['prenom']) > 50) {
            $erreurs['prenom'] = 'Merci d\'indiquer votre prénom.';
        }
        if (!preg_match(Utilisateur::REGLE_GSM, $d['telephone'])) {
            $erreurs['telephone'] = 'Merci d\'indiquer un numéro de mobile valide.';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Merci d\'indiquer une adresse e-mail valide.';
        } elseif ($d['email'] !== $client['email'] && Utilisateur::trouverParEmail($d['email'])) {
            $erreurs['email'] = 'Cette adresse e-mail est déjà utilisée par un autre compte.';
        }
        if (mb_strlen($d['adresse_postale']) < 5) {
            $erreurs['adresse_postale'] = 'Merci d\'indiquer votre adresse postale.';
        }
        if ($d['ville'] === '') {
            $erreurs['ville'] = 'Merci d\'indiquer votre ville.';
        }

        // Changement de mot de passe (facultatif) : l'ancien mot de passe est exigé
        $nouveau = (string) ($_POST['password'] ?? '');
        if ($nouveau !== '') {
            if (!password_verify((string) ($_POST['ancien_password'] ?? ''), $client['password'])) {
                $erreurs['ancien_password'] = 'Le mot de passe actuel est incorrect.';
            } elseif (!Utilisateur::motDePasseValide($nouveau)) {
                $erreurs['password'] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
            } elseif ($nouveau !== ($_POST['password_confirmation'] ?? '')) {
                $erreurs['password_confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
            }
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            redirect('/espace/profil');
        }

        Utilisateur::mettreAJourProfil($u['id'], $d);
        if ($nouveau !== '') {
            Utilisateur::changerMotDePasse($u['id'], $nouveau);
        }
        $_SESSION['utilisateur']['prenom'] = $d['prenom'];
        $_SESSION['utilisateur']['nom'] = $d['nom'];
        $_SESSION['utilisateur']['email'] = $d['email'];
        flash('success', 'Vos informations ont été mises à jour.');
        redirect('/espace/profil');
    }

    // -----------------------------------------------------------------

    /** Récupère une commande du client connecté, ou affiche une erreur 404. */
    private function commandeDuClient(int $utilisateurId, int $id): array
    {
        $commande = $id ? Commande::trouverPourUtilisateur($id, $utilisateurId) : null;
        if ($commande === null) {
            http_response_code(404);
            render('erreur', ['titre' => 'Commande introuvable', 'message' => 'Cette commande n\'existe pas ou ne vous appartient pas.']);
            exit;
        }
        return $commande;
    }
}
