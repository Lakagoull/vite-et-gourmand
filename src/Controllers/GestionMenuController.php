<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Menu;
use App\Models\Plat;
use App\Services\Auth;
use App\Services\Upload;

/**
 * Gestion des menus et des plats par les employés (et l'administrateur).
 */
final class GestionMenuController
{
    // -----------------------------------------------------------------
    // Menus
    // -----------------------------------------------------------------

    public function menus(): void
    {
        Auth::exiger('employe');
        render('gestion/menus', ['titrePage' => 'Menus', 'menus' => Menu::listeGestion()]);
    }

    public function menuForm(): void
    {
        Auth::exiger('employe');
        $id = (int) ($_GET['id'] ?? 0);
        $menu = $id ? Menu::trouver($id) : null;
        if ($id && !$menu) {
            redirect('/employe/menus');
        }
        render('gestion/menu-form', [
            'titrePage' => $menu ? 'Modifier le menu' : 'Nouveau menu',
            'menu'      => $menu,
            'valeurs'   => $_SESSION['old'] ?? ($menu ?? ['delai_commande_jours' => 3, 'quantite_restante' => 10, 'nombre_personne_minimum' => 2]),
            'platsMenu' => $_SESSION['old']['plats'] ?? ($menu ? Menu::platsIds($id) : []),
            'images'    => $menu ? Menu::imagesAvecId($id) : [],
            'plats'     => Plat::tous(),
            'themes'    => Menu::themes(),
            'regimes'   => Menu::regimes(),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    public function menuEnregistrer(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $id = (int) ($_POST['menu_id'] ?? 0) ?: null;
        if ($id && !Menu::trouver($id)) {
            redirect('/employe/menus');
        }

        $d = [
            'titre'                   => trim((string) ($_POST['titre'] ?? '')),
            'description'             => trim((string) ($_POST['description'] ?? '')),
            'conditions'              => trim((string) ($_POST['conditions'] ?? '')),
            'theme_id'                => (int) ($_POST['theme_id'] ?? 0),
            'regime_id'               => (int) ($_POST['regime_id'] ?? 0),
            'nombre_personne_minimum' => filter_var($_POST['nombre_personne_minimum'] ?? null, FILTER_VALIDATE_INT),
            'prix_par_personne'       => filter_var(str_replace(',', '.', (string) ($_POST['prix_par_personne'] ?? '')), FILTER_VALIDATE_FLOAT),
            'delai_commande_jours'    => filter_var($_POST['delai_commande_jours'] ?? null, FILTER_VALIDATE_INT),
            'quantite_restante'       => filter_var($_POST['quantite_restante'] ?? null, FILTER_VALIDATE_INT),
        ];
        $plats = array_values(array_unique(array_map('intval', (array) ($_POST['plats'] ?? []))));

        $erreurs = [];
        if (mb_strlen($d['titre']) < 3 || mb_strlen($d['titre']) > 100) {
            $erreurs['titre'] = 'Le titre doit contenir entre 3 et 100 caractères.';
        }
        if (mb_strlen($d['description']) < 10) {
            $erreurs['description'] = 'La description doit contenir au moins 10 caractères.';
        }
        if (!in_array($d['theme_id'], array_map('intval', array_column(Menu::themes(), 'theme_id')), true)) {
            $erreurs['theme_id'] = 'Choisissez un thème.';
        }
        if (!in_array($d['regime_id'], array_map('intval', array_column(Menu::regimes(), 'regime_id')), true)) {
            $erreurs['regime_id'] = 'Choisissez un régime.';
        }
        if ($d['nombre_personne_minimum'] === false || $d['nombre_personne_minimum'] < 1 || $d['nombre_personne_minimum'] > 500) {
            $erreurs['nombre_personne_minimum'] = 'Le nombre minimum de personnes doit être compris entre 1 et 500.';
        }
        if ($d['prix_par_personne'] === false || $d['prix_par_personne'] <= 0 || $d['prix_par_personne'] > 10000) {
            $erreurs['prix_par_personne'] = 'Indiquez un prix par personne valide (ex. : 32,50).';
        }
        if ($d['delai_commande_jours'] === false || $d['delai_commande_jours'] < 0 || $d['delai_commande_jours'] > 365) {
            $erreurs['delai_commande_jours'] = 'Le délai doit être compris entre 0 et 365 jours.';
        }
        if ($d['quantite_restante'] === false || $d['quantite_restante'] < 0) {
            $erreurs['quantite_restante'] = 'Le stock doit être un nombre positif ou nul.';
        }
        $idsPlats = array_map('intval', array_column(Plat::tous(), 'plat_id'));
        if (!$plats || array_diff($plats, $idsPlats)) {
            $erreurs['plats'] = 'Sélectionnez au moins un plat dans la liste.';
        }

        // Vérification des images envoyées avant tout enregistrement
        $nouvellesImages = [];
        $fichiers = Upload::normaliser($_FILES['images'] ?? []);
        if (count($fichiers) > 6) {
            $erreurs['images'] = 'Vous pouvez envoyer 6 images au maximum à la fois.';
            $fichiers = [];
        }
        foreach ($fichiers as $fichier) {
            try {
                $nouvellesImages[] = Upload::image($fichier);
            } catch (\RuntimeException $e) {
                $erreurs['images'] = $e->getMessage();
            }
        }
        $alt = trim((string) ($_POST['texte_alternatif'] ?? ''));
        if ($nouvellesImages && mb_strlen($alt) < 5) {
            $erreurs['texte_alternatif'] = 'Décrivez les images envoyées (texte alternatif pour l\'accessibilité).';
        }

        if ($erreurs) {
            array_map([Upload::class, 'supprimer'], $nouvellesImages);
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = $_POST + ['plats' => $plats];
            redirect('/employe/menu' . ($id ? '?id=' . $id : ''));
        }

        $id = Menu::enregistrer($id, $d, $plats);
        foreach ($nouvellesImages as $chemin) {
            Menu::ajouterImage($id, $chemin, $alt);
        }
        flash('success', 'Le menu « ' . $d['titre'] . ' » a été enregistré.');
        redirect('/employe/menu?id=' . $id);
    }

    public function menuSupprimer(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $id = (int) ($_POST['menu_id'] ?? 0);
        $images = Menu::imagesAvecId($id);
        if (Menu::supprimer($id)) {
            foreach ($images as $img) {
                Upload::supprimer($img['chemin']);
            }
            flash('success', 'Le menu a été supprimé.');
        } else {
            flash('warning', 'Ce menu a déjà été commandé : il ne peut pas être supprimé (historique des commandes). Mettez son stock à 0 pour le retirer de la vente.');
        }
        redirect('/employe/menus');
    }

    public function imageSupprimer(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $chemin = Menu::supprimerImage((int) ($_POST['image_id'] ?? 0), $menuId);
        if ($chemin !== null) {
            Upload::supprimer($chemin);
            flash('success', 'L\'image a été supprimée.');
        }
        redirect('/employe/menu?id=' . $menuId . '#images');
    }

    // -----------------------------------------------------------------
    // Plats
    // -----------------------------------------------------------------

    public function plats(): void
    {
        Auth::exiger('employe');
        render('gestion/plats', ['titrePage' => 'Plats', 'plats' => Plat::tous()]);
    }

    public function platForm(): void
    {
        Auth::exiger('employe');
        $id = (int) ($_GET['id'] ?? 0);
        $plat = $id ? Plat::trouver($id) : null;
        if ($id && !$plat) {
            redirect('/employe/plats');
        }
        render('gestion/plat-form', [
            'titrePage'  => $plat ? 'Modifier le plat' : 'Nouveau plat',
            'plat'       => $plat,
            'valeurs'    => $_SESSION['old'] ?? ($plat ?? []),
            'allergenesPlat' => $_SESSION['old']['allergenes'] ?? ($plat['allergenes'] ?? []),
            'allergenes' => Plat::allergenes(),
            'erreurs'    => $_SESSION['erreurs'] ?? [],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    public function platEnregistrer(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $id = (int) ($_POST['plat_id'] ?? 0) ?: null;
        $existant = $id ? Plat::trouver($id) : null;
        if ($id && !$existant) {
            redirect('/employe/plats');
        }

        $d = [
            'titre_plat' => trim((string) ($_POST['titre_plat'] ?? '')),
            'type_plat'  => (string) ($_POST['type_plat'] ?? ''),
            'photo'      => $existant['photo'] ?? null,
        ];
        $allergenes = array_values(array_unique(array_map('intval', (array) ($_POST['allergenes'] ?? []))));

        $erreurs = [];
        if (mb_strlen($d['titre_plat']) < 3 || mb_strlen($d['titre_plat']) > 100) {
            $erreurs['titre_plat'] = 'Le nom du plat doit contenir entre 3 et 100 caractères.';
        }
        if (!isset(Plat::TYPES[$d['type_plat']])) {
            $erreurs['type_plat'] = 'Choisissez le type de plat.';
        }
        if (array_diff($allergenes, array_map('intval', array_column(Plat::allergenes(), 'allergene_id')))) {
            $erreurs['allergenes'] = 'Allergène inconnu.';
        }

        $nouvellePhoto = null;
        if (($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $nouvellePhoto = Upload::image($_FILES['photo']);
            } catch (\RuntimeException $e) {
                $erreurs['photo'] = $e->getMessage();
            }
        }

        if ($erreurs) {
            Upload::supprimer($nouvellePhoto);
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = $_POST + ['allergenes' => $allergenes];
            redirect('/employe/plat' . ($id ? '?id=' . $id : ''));
        }

        if ($nouvellePhoto) {
            Upload::supprimer($d['photo']);
            $d['photo'] = $nouvellePhoto;
        }
        $id = Plat::enregistrer($id, $d, $allergenes);
        flash('success', 'Le plat « ' . $d['titre_plat'] . ' » a été enregistré.');
        redirect('/employe/plats');
    }

    public function platSupprimer(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $plat = Plat::trouver((int) ($_POST['plat_id'] ?? 0));
        if ($plat) {
            Plat::supprimer((int) $plat['plat_id']);
            Upload::supprimer($plat['photo']);
            flash('success', 'Le plat « ' . $plat['titre_plat'] . ' » a été supprimé (et retiré des menus qui le contenaient).');
        }
        redirect('/employe/plats');
    }
}
