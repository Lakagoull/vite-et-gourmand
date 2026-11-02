<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Avis;
use App\Models\Commande;
use App\Models\Horaire;
use App\Services\Auth;

/**
 * Espace employé : tableau de bord, horaires, modération des avis.
 */
final class GestionController
{
    public function employe(): void
    {
        $u = Auth::exiger('employe');
        render('gestion/tableau-de-bord', [
            'titrePage'    => $u['role'] === 'administrateur' ? 'Espace administrateur' : 'Espace employé',
            'u'            => $u,
            'compteurs'    => Commande::compterParStatut(),
            'avisAttente'  => Avis::compterEnAttente(),
            'prochaines'   => array_slice(array_filter(
                Commande::lister(),
                fn ($c) => !in_array($c['statut'], ['terminee', 'annulee'], true)
            ), 0, 8),
        ]);
    }

    // -----------------------------------------------------------------
    // Horaires
    // -----------------------------------------------------------------

    public function horairesForm(): void
    {
        Auth::exiger('employe');
        render('gestion/horaires', [
            'titrePage' => 'Horaires',
            'horaires'  => Horaire::tous(),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
        ]);
        unset($_SESSION['erreurs']);
    }

    public function horaires(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $erreurs = [];
        $aEnregistrer = [];

        foreach (Horaire::tous() as $h) {
            $id = (int) $h['horaire_id'];
            if (!empty($_POST['ferme'][$id])) {
                $aEnregistrer[$id] = [null, null];
                continue;
            }
            $o = (string) ($_POST['ouverture'][$id] ?? '');
            $f = (string) ($_POST['fermeture'][$id] ?? '');
            $format = '/^([01]\d|2[0-3]):[0-5]\d$/';
            if (!preg_match($format, $o) || !preg_match($format, $f) || $o >= $f) {
                $erreurs[$id] = $h['jour'] . ' : indiquez une heure d\'ouverture antérieure à l\'heure de fermeture, ou cochez « Fermé ».';
                continue;
            }
            $aEnregistrer[$id] = [$o . ':00', $f . ':00'];
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            redirect('/employe/horaires');
        }
        foreach ($aEnregistrer as $id => [$o, $f]) {
            Horaire::enregistrer($id, $o, $f);
        }
        flash('success', 'Les horaires ont été mis à jour (visibles dans le pied de page du site).');
        redirect('/employe/horaires');
    }

    // -----------------------------------------------------------------
    // Avis
    // -----------------------------------------------------------------

    public function avis(): void
    {
        Auth::exiger('employe');
        $statut = in_array($_GET['statut'] ?? '', ['en_attente', 'valide', 'refuse'], true) ? $_GET['statut'] : 'en_attente';
        render('gestion/avis', [
            'titrePage' => 'Avis clients',
            'avis'      => Avis::lister($statut),
            'statut'    => $statut,
        ]);
    }

    public function modererAvis(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $decision = (string) ($_POST['decision'] ?? '');
        if (Avis::changerStatut((int) ($_POST['avis_id'] ?? 0), $decision)) {
            flash('success', $decision === 'valide' ? 'Avis validé : il est maintenant visible sur la page d\'accueil.' : 'Avis refusé : il ne sera pas publié.');
        }
        redirect('/employe/avis');
    }

    // -----------------------------------------------------------------
    // Administrateur (développé dans l'étape suivante)
    // -----------------------------------------------------------------

    public function admin(): void
    {
        Auth::exiger('administrateur');
        $this->employe();
    }
}
