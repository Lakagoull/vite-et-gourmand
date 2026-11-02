<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Commande;
use App\Models\Menu;
use App\Services\Auth;
use App\Services\Mailer;
use App\Services\Statistiques;

/**
 * Gestion des commandes par les employés (et l'administrateur).
 */
final class GestionCommandeController
{
    public function index(): void
    {
        Auth::exiger('employe');
        $filtres = [
            'statut' => (string) ($_GET['statut'] ?? ''),
            'client' => trim((string) ($_GET['client'] ?? '')),
        ];
        render('gestion/commandes', [
            'titrePage' => 'Commandes',
            'commandes' => Commande::lister($filtres),
            'filtres'   => $filtres,
            'compteurs' => Commande::compterParStatut(),
        ]);
    }

    public function detail(): void
    {
        Auth::exiger('employe');
        $c = $this->commande((int) ($_GET['id'] ?? 0));
        render('gestion/commande-detail', [
            'titrePage' => 'Commande ' . $c['numero_commande'],
            'c'         => $c,
            'etapes'    => Commande::etapesSuivantes($c),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    /** Passage à l'étape suivante du cycle de vie. */
    public function statut(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $c = $this->commande((int) ($_POST['commande_id'] ?? 0));
        $nouveau = (string) ($_POST['statut'] ?? '');
        $pret = $nouveau === 'livre' ? !empty($_POST['pret_materiel']) : null;

        if (!Commande::changerStatut((int) $c['commande_id'], $nouveau, $pret)) {
            flash('danger', 'Ce changement de statut n\'est pas autorisé.');
            redirect('/employe/commande?id=' . $c['commande_id']);
        }
        Statistiques::majStatut((int) $c['commande_id'], $nouveau);
        $this->notifierClient($c, $nouveau);

        flash('success', 'Commande ' . $c['numero_commande'] . ' : statut « ' . Commande::libelleStatut($nouveau) . ' ».');
        redirect('/employe/commande?id=' . $c['commande_id']);
    }

    /** Annulation : le client doit avoir été contacté (mode de contact + motif obligatoires). */
    public function annuler(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $c = $this->commande((int) ($_POST['commande_id'] ?? 0));
        $mode = (string) ($_POST['mode_contact'] ?? '');
        $motif = trim((string) ($_POST['motif'] ?? ''));

        $erreurs = [];
        if (!in_array($mode, ['telephone', 'mail'], true)) {
            $erreurs['mode_contact'] = 'Indiquez comment le client a été contacté (téléphone ou e-mail).';
        }
        if (mb_strlen($motif) < 5 || mb_strlen($motif) > 1000) {
            $erreurs['motif'] = 'Indiquez le motif de l\'annulation (5 caractères minimum).';
        }
        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = ['motif' => $motif];
            redirect('/employe/commande?id=' . $c['commande_id'] . '#annulation');
        }

        if (!Commande::annulerParEmploye((int) $c['commande_id'], $mode, $motif)) {
            flash('danger', 'Cette commande ne peut plus être annulée (livraison en cours ou terminée).');
            redirect('/employe/commande?id=' . $c['commande_id']);
        }
        Statistiques::majStatut((int) $c['commande_id'], 'annulee');
        Mailer::envoyer(
            $c['email'],
            'Annulation de votre commande ' . $c['numero_commande'],
            '<p>Bonjour ' . e($c['prenom']) . ',</p>'
            . '<p>Suite à notre échange, votre commande <strong>' . e($c['numero_commande']) . '</strong> (' . e($c['menu_titre']) . ') a été annulée.</p>'
            . '<p><strong>Motif :</strong> ' . nl2br(e($motif)) . '</p>'
            . '<p>Nous restons à votre disposition pour toute question.</p>'
        );
        flash('success', 'La commande ' . $c['numero_commande'] . ' a été annulée et le client a été prévenu par e-mail.');
        redirect('/employe/commande?id=' . $c['commande_id']);
    }

    public function modifierForm(): void
    {
        Auth::exiger('employe');
        $c = $this->commande((int) ($_GET['id'] ?? 0));
        if (!in_array($c['statut'], Commande::STATUTS_MODIFIABLES, true)) {
            flash('warning', 'Cette commande ne peut plus être modifiée.');
            redirect('/employe/commande?id=' . $c['commande_id']);
        }
        render('gestion/commande-modifier', [
            'titrePage' => 'Modifier la commande ' . $c['numero_commande'],
            'c'         => $c,
            'menu'      => Menu::trouver((int) $c['menu_id']),
            'erreurs'   => $_SESSION['erreurs'] ?? [],
            'valeurs'   => $_SESSION['old'] ?? [
                'adresse_prestation' => $c['adresse_prestation'],
                'ville_prestation'   => $c['ville_prestation'],
                'date_prestation'    => $c['date_prestation'],
                'heure_livraison'    => substr($c['heure_livraison'], 0, 5),
                'nombre_personne'    => $c['nombre_personne'],
            ],
            'scripts'   => ['commande'],
        ]);
        unset($_SESSION['erreurs'], $_SESSION['old']);
    }

    /** Modification : seulement après avoir contacté le client (mode de contact + motif obligatoires). */
    public function modifier(): void
    {
        verifierCsrf();
        Auth::exiger('employe');
        $c = $this->commande((int) ($_POST['commande_id'] ?? 0));
        $id = (int) $c['commande_id'];

        $menu = Menu::trouver((int) $c['menu_id']);
        [$donnees, $tarif, $erreurs] = CommandeController::analyserFormulaire($_POST, $menu);
        // L'employé peut fixer une date proche si le client l'a accepté : seul un jour passé est refusé
        if (isset($erreurs['date_prestation']) && $donnees['date_prestation'] >= date('Y-m-d')
            && \DateTimeImmutable::createFromFormat('!Y-m-d', $donnees['date_prestation'])) {
            unset($erreurs['date_prestation']);
        }
        $mode = (string) ($_POST['mode_contact'] ?? '');
        $motif = trim((string) ($_POST['motif'] ?? ''));
        if (!in_array($mode, ['telephone', 'mail'], true)) {
            $erreurs['mode_contact'] = 'Indiquez comment le client a été contacté avant la modification.';
        }
        if (mb_strlen($motif) < 5) {
            $erreurs['motif'] = 'Indiquez le motif de la modification (5 caractères minimum).';
        }

        if ($erreurs) {
            $_SESSION['erreurs'] = $erreurs;
            $_SESSION['old'] = $_POST;
            redirect('/employe/commande/modifier?id=' . $id);
        }
        if (!Commande::modifierParEmploye($id, $donnees, $tarif)) {
            flash('danger', 'Cette commande ne peut plus être modifiée.');
            redirect('/employe/commande?id=' . $id);
        }

        $maj = Commande::trouver($id);
        Statistiques::enregistrerCommande($maj, $maj['menu_titre']);
        Mailer::envoyer(
            $maj['email'],
            'Modification de votre commande ' . $maj['numero_commande'],
            '<p>Bonjour ' . e($maj['prenom']) . ',</p>'
            . '<p>Suite à notre échange (' . ($mode === 'telephone' ? 'téléphone' : 'e-mail') . '), votre commande <strong>' . e($maj['numero_commande']) . '</strong> a été modifiée.</p>'
            . '<p><strong>Motif :</strong> ' . nl2br(e($motif)) . '</p>'
            . '<p>Prestation le ' . e(date('d/m/Y', strtotime($maj['date_prestation']))) . ' à ' . e(substr($maj['heure_livraison'], 0, 5))
            . ', ' . (int) $maj['nombre_personne'] . ' personnes, ' . e($maj['adresse_prestation']) . ', ' . e($maj['ville_prestation']) . '.</p>'
            . '<p>Nouveau total : <strong>' . prix($maj['prix_total']) . '</strong></p>'
        );
        flash('success', 'Commande modifiée. Le client a reçu un e-mail récapitulatif.');
        redirect('/employe/commande?id=' . $id);
    }

    // -----------------------------------------------------------------

    private function commande(int $id): array
    {
        $c = $id ? Commande::trouver($id) : null;
        if ($c === null) {
            http_response_code(404);
            render('erreur', ['titre' => 'Commande introuvable', 'message' => 'Cette commande n\'existe pas.']);
            exit;
        }
        return $c;
    }

    /** Mails automatiques liés au changement de statut. */
    private function notifierClient(array $c, string $statut): void
    {
        $url = e(env('APP_URL', '')) . '/espace/commande?id=' . (int) $c['commande_id'];
        if ($statut === 'attente_retour_materiel') {
            Mailer::envoyer(
                $c['email'],
                'Restitution du matériel prêté — commande ' . $c['numero_commande'],
                '<p>Bonjour ' . e($c['prenom']) . ',</p>'
                . '<p>Du matériel vous a été prêté lors de la livraison de votre commande <strong>' . e($c['numero_commande']) . '</strong>.</p>'
                . '<p style="border:2px solid #B42318;padding:10px">Merci de le restituer <strong>sous 10 jours ouvrés</strong>. '
                . 'Passé ce délai, des frais de <strong>600 €</strong> vous seront facturés, conformément à nos conditions générales de vente.</p>'
                . '<p>Pour organiser le retour, merci de prendre contact avec nous : 05 56 00 00 00 ou contact@vite-gourmand.fr.</p>'
            );
        } elseif ($statut === 'terminee') {
            Mailer::envoyer(
                $c['email'],
                'Votre avis nous intéresse — commande ' . $c['numero_commande'],
                '<p>Bonjour ' . e($c['prenom']) . ',</p>'
                . '<p>Votre commande <strong>' . e($c['numero_commande']) . '</strong> est terminée. Merci pour votre confiance !</p>'
                . '<p>Connectez-vous à votre compte pour nous donner votre avis (note de 1 à 5 et commentaire) :</p>'
                . '<p><a href="' . $url . '" style="color:#7A1F3D;font-weight:bold">Donner mon avis</a></p>'
            );
        }
    }
}
