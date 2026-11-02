<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Utilisateur;
use App\Services\Auth;
use App\Services\Geolocalisation;
use App\Services\Mailer;
use App\Services\Statistiques;
use App\Services\Tarification;

final class CommandeController
{
    /** Heures de livraison possibles. */
    public const HEURE_MIN = '08:00';
    public const HEURE_MAX = '22:00';

    /** Formulaire de commande (le menu est pré-sélectionné si on vient de la fiche menu). */
    public function formulaire(): void
    {
        $u = Auth::exiger('utilisateur');
        $client = Utilisateur::trouverParId($u['id']);
        $menuId = (int) ($_GET['menu'] ?? 0);

        render('commande', [
            'titrePage' => 'Commander un menu',
            'client'    => $client,
            'menus'     => self::menusCommandables(),
            'menuId'    => $menuId,
            'erreurs'   => $_SESSION['erreurs'] ?? [],
            'valeurs'   => $_SESSION['old'] ?? [
                'adresse_prestation' => $client['adresse_postale'] ?? '',
                'ville_prestation'   => $client['ville'] ?? '',
            ],
            'scripts'   => ['commande'],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    /** Validation et enregistrement de la commande. */
    public function valider(): void
    {
        verifierCsrf();
        $u = Auth::exiger('utilisateur');

        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $menu = $menuId ? Menu::trouver($menuId) : null;
        [$donnees, $tarif, $erreurs] = self::analyserFormulaire($_POST, $menu);

        if ($menu && (int) $menu['quantite_restante'] < 1) {
            $erreurs['menu_id'] = 'Ce menu n\'est plus disponible à la commande.';
        }
        if (empty($_POST['conditions_lues'])) {
            $erreurs['conditions_lues'] = 'Merci de confirmer avoir pris connaissance des conditions du menu.';
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = $_POST;
            redirect('/commande?menu=' . $menuId);
        }

        $commande = Commande::creer($u['id'], $menuId, $donnees, $tarif);
        if ($commande === null) {
            flash('danger', 'Désolé, ce menu vient d\'être épuisé. Votre commande n\'a pas été enregistrée.');
            redirect('/menu?id=' . $menuId);
        }

        Statistiques::enregistrerCommande($commande, $commande['menu_titre']);
        self::envoyerConfirmation($commande);

        flash('success', 'Merci ! Votre commande ' . $commande['numero_commande'] . ' est enregistrée. Un e-mail de confirmation vous a été envoyé.');
        redirect('/espace/commande?id=' . $commande['commande_id']);
    }

    /**
     * API : calcul du prix en direct pour le formulaire (JSON).
     * Le prix final est de toute façon recalculé à la validation.
     */
    public function devis(): void
    {
        if (!Auth::utilisateur()) {
            json(['erreur' => 'Non connecté'], 401);
        }
        $menu = Menu::trouver((int) ($_GET['menu_id'] ?? 0));
        if (!$menu) {
            json(['erreur' => 'Menu introuvable'], 404);
        }
        $nb = max((int) ($_GET['nombre_personne'] ?? 0), 0);
        $lieu = Geolocalisation::localiser((string) ($_GET['adresse'] ?? ''), (string) ($_GET['ville'] ?? ''));
        $tarif = Tarification::calculer($menu, $nb, $lieu['bordeaux'] ?? true, $lieu['distance_km'] ?? 0);

        json([
            'adresse_trouvee' => $lieu !== null,
            'adresse'         => $lieu['libelle'] ?? null,
            'minimum'         => (int) $menu['nombre_personne_minimum'],
            'sous_minimum'    => $nb < (int) $menu['nombre_personne_minimum'],
            'tarif'           => $tarif,
            'textes'          => [
                'prix_menu'      => prix($tarif['prix_menu']),
                'remise'         => '− ' . prix($tarif['remise']),
                'prix_livraison' => prix($tarif['prix_livraison']),
                'prix_total'     => prix($tarif['prix_total']),
            ],
        ]);
    }

    // -----------------------------------------------------------------
    // Outils partagés avec la modification d'une commande (espace client)
    // -----------------------------------------------------------------

    /**
     * Vérifie les champs de la prestation et calcule le prix.
     * Retourne [données nettoyées, tarif, erreurs].
     */
    public static function analyserFormulaire(array $source, ?array $menu): array
    {
        $d = [
            'adresse_prestation' => trim((string) ($source['adresse_prestation'] ?? '')),
            'ville_prestation'   => trim((string) ($source['ville_prestation'] ?? '')),
            'date_prestation'    => (string) ($source['date_prestation'] ?? ''),
            'heure_livraison'    => (string) ($source['heure_livraison'] ?? ''),
            'nombre_personne'    => filter_var($source['nombre_personne'] ?? null, FILTER_VALIDATE_INT) ?: 0,
        ];
        $erreurs = [];

        if ($menu === null) {
            $erreurs['menu_id'] = 'Merci de choisir un menu.';
        }

        if (mb_strlen($d['adresse_prestation']) < 5 || mb_strlen($d['adresse_prestation']) > 255) {
            $erreurs['adresse_prestation'] = 'Merci d\'indiquer l\'adresse de la prestation (numéro, rue, code postal).';
        }
        if ($d['ville_prestation'] === '' || mb_strlen($d['ville_prestation']) > 80) {
            $erreurs['ville_prestation'] = 'Merci d\'indiquer la ville de la prestation.';
        }

        $lieu = null;
        if (!isset($erreurs['adresse_prestation']) && !isset($erreurs['ville_prestation'])) {
            $lieu = Geolocalisation::localiser($d['adresse_prestation'], $d['ville_prestation']);
            if ($lieu === null) {
                $erreurs['adresse_prestation'] = 'Adresse introuvable. Vérifiez le numéro, la rue et le code postal.';
            }
        }

        // Date : au format AAAA-MM-JJ et en respectant le délai de commande du menu
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $d['date_prestation']);
        if ($date === false || $date->format('Y-m-d') !== $d['date_prestation']) {
            $erreurs['date_prestation'] = 'Merci d\'indiquer une date de prestation valide.';
        } elseif ($menu) {
            $delai = max(1, (int) $menu['delai_commande_jours']);
            $dateMin = (new \DateTimeImmutable('today'))->modify("+$delai days");
            if ($date < $dateMin) {
                $erreurs['date_prestation'] = 'Ce menu doit être commandé au moins ' . $delai . ' jour' . ($delai > 1 ? 's' : '')
                    . ' à l\'avance : première date possible le ' . $dateMin->format('d/m/Y') . '.';
            } elseif ($date > (new \DateTimeImmutable('today'))->modify('+1 year')) {
                $erreurs['date_prestation'] = 'Les commandes sont possibles jusqu\'à un an à l\'avance.';
            }
        }

        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['heure_livraison'])
            || $d['heure_livraison'] < self::HEURE_MIN || $d['heure_livraison'] > self::HEURE_MAX) {
            $erreurs['heure_livraison'] = 'Merci d\'indiquer une heure de livraison entre ' . self::HEURE_MIN . ' et ' . self::HEURE_MAX . '.';
        }

        if ($menu && ($d['nombre_personne'] < (int) $menu['nombre_personne_minimum'] || $d['nombre_personne'] > 500)) {
            $erreurs['nombre_personne'] = 'Ce menu se commande pour ' . (int) $menu['nombre_personne_minimum'] . ' personnes minimum.';
        }

        $tarif = $menu
            ? Tarification::calculer($menu, (int) $d['nombre_personne'], $lieu['bordeaux'] ?? true, $lieu['distance_km'] ?? 0)
            : [];
        if ($lieu) {
            $d['ville_prestation'] = $lieu['ville']; // ville officielle renvoyée par l'API
        }
        return [$d, $tarif, $erreurs];
    }

    public static function menusCommandables(): array
    {
        return Database::get()->query(
            'SELECT menu_id, titre, nombre_personne_minimum, prix_par_personne, delai_commande_jours, quantite_restante, conditions
             FROM menu ORDER BY titre'
        )->fetchAll();
    }

    private static function envoyerConfirmation(array $c): void
    {
        $lignes = '<tr><td>Menu</td><td>' . e($c['menu_titre']) . '</td></tr>'
            . '<tr><td>Date et heure</td><td>' . e(date('d/m/Y', strtotime($c['date_prestation']))) . ' à ' . e(substr($c['heure_livraison'], 0, 5)) . '</td></tr>'
            . '<tr><td>Adresse</td><td>' . e($c['adresse_prestation']) . ', ' . e($c['ville_prestation']) . '</td></tr>'
            . '<tr><td>Personnes</td><td>' . (int) $c['nombre_personne'] . '</td></tr>'
            . '<tr><td>Prix du menu</td><td>' . prix($c['prix_menu']) . '</td></tr>'
            . ((float) $c['remise'] > 0 ? '<tr><td>Réduction 10 %</td><td>− ' . prix($c['remise']) . '</td></tr>' : '')
            . '<tr><td>Livraison</td><td>' . prix($c['prix_livraison']) . '</td></tr>'
            . '<tr><td><strong>Total</strong></td><td><strong>' . prix($c['prix_total']) . '</strong></td></tr>';

        Mailer::envoyer(
            $c['email'],
            'Confirmation de votre commande ' . $c['numero_commande'],
            '<p>Bonjour ' . e($c['prenom']) . ',</p>'
            . '<p>Nous avons bien reçu votre commande <strong>' . e($c['numero_commande']) . '</strong>. Notre équipe va la valider prochainement.</p>'
            . '<table cellpadding="6" style="border-collapse:collapse;width:100%">' . $lignes . '</table>'
            . ($c['conditions'] ? '<p style="border:2px solid #B42318;padding:10px"><strong>Conditions du menu :</strong> ' . e($c['conditions']) . '</p>' : '')
            . '<p>Vous pouvez suivre, modifier ou annuler votre commande (tant qu\'elle n\'est pas acceptée) depuis votre espace client.</p>'
        );
    }
}
