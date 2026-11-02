<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Commande
{
    /** Libellés affichés pour chaque statut. */
    public const STATUTS = [
        'en_attente'              => 'En attente de validation',
        'accepte'                 => 'Acceptée',
        'en_preparation'          => 'En préparation',
        'en_cours_livraison'      => 'En cours de livraison',
        'livre'                   => 'Livrée',
        'attente_retour_materiel' => 'En attente du retour de matériel',
        'terminee'                => 'Terminée',
        'annulee'                 => 'Annulée',
    ];

    /**
     * Étapes autorisées depuis chaque statut (l'employé suit le cycle de vie de la commande).
     * L'annulation est traitée à part (motif et mode de contact obligatoires).
     */
    public const TRANSITIONS = [
        'en_attente'              => ['accepte'],
        'accepte'                 => ['en_preparation'],
        'en_preparation'          => ['en_cours_livraison'],
        'en_cours_livraison'      => ['livre'],
        'livre'                   => ['attente_retour_materiel', 'terminee'],
        'attente_retour_materiel' => ['terminee'],
        'terminee'                => [],
        'annulee'                 => [],
    ];

    /** Statuts pour lesquels l'employé peut encore modifier ou annuler la commande. */
    public const STATUTS_MODIFIABLES = ['en_attente', 'accepte', 'en_preparation'];

    public static function libelleStatut(string $statut): string
    {
        return self::STATUTS[$statut] ?? $statut;
    }

    /** Étapes suivantes possibles (le retour de matériel n'est proposé que si du matériel a été prêté). */
    public static function etapesSuivantes(array $commande): array
    {
        $etapes = self::TRANSITIONS[$commande['statut']] ?? [];
        if ($commande['statut'] === 'livre') {
            $etapes = $commande['pret_materiel'] ? ['attente_retour_materiel'] : ['terminee'];
        }
        return $etapes;
    }

    // -----------------------------------------------------------------
    // Gestion par les employés
    // -----------------------------------------------------------------

    /** Liste filtrée par statut et/ou client (nom, prénom, e-mail ou numéro de commande). */
    public static function lister(array $filtres = []): array
    {
        $sql = 'SELECT c.commande_id, c.numero_commande, c.date_commande, c.date_prestation, c.heure_livraison,
                       c.nombre_personne, c.prix_total, c.statut, c.ville_prestation,
                       m.titre AS menu_titre, u.nom, u.prenom, u.email, u.telephone
                FROM commande c
                JOIN menu m ON m.menu_id = c.menu_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                WHERE 1 = 1';
        $params = [];
        if (!empty($filtres['statut']) && isset(self::STATUTS[$filtres['statut']])) {
            $sql .= ' AND c.statut = :statut';
            $params['statut'] = $filtres['statut'];
        }
        if (!empty($filtres['client'])) {
            $sql .= ' AND (u.nom LIKE :c1 OR u.prenom LIKE :c2 OR u.email LIKE :c3 OR c.numero_commande LIKE :c4
                           OR CONCAT(u.prenom, \' \', u.nom) LIKE :c5)';
            $motif = '%' . addcslashes($filtres['client'], '%_\\') . '%';
            $params += ['c1' => $motif, 'c2' => $motif, 'c3' => $motif, 'c4' => $motif, 'c5' => $motif];
        }
        $sql .= ' ORDER BY FIELD(c.statut, \'en_attente\', \'accepte\', \'en_preparation\', \'en_cours_livraison\', \'livre\',
                                 \'attente_retour_materiel\', \'terminee\', \'annulee\'), c.date_prestation ASC';
        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function compterParStatut(): array
    {
        return Database::get()->query('SELECT statut, COUNT(*) AS n FROM commande GROUP BY statut')
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Passage à l'étape suivante. Vérifie que la transition est autorisée.
     * $pretMateriel : renseigné lors de la livraison (du matériel a-t-il été prêté ?).
     */
    public static function changerStatut(int $id, string $nouveau, ?bool $pretMateriel = null): bool
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT statut, pret_materiel FROM commande WHERE commande_id = :id FOR UPDATE');
            $stmt->execute(['id' => $id]);
            $c = $stmt->fetch();
            if (!$c) {
                $pdo->rollBack();
                return false;
            }
            if (!in_array($nouveau, self::etapesSuivantes($c), true)) {
                $pdo->rollBack();
                return false;
            }

            $sql = 'UPDATE commande SET statut = :s';
            $params = ['s' => $nouveau, 'id' => $id];
            if ($nouveau === 'livre' && $pretMateriel !== null) {
                $sql .= ', pret_materiel = :pret';
                $params['pret'] = $pretMateriel ? 1 : 0;
            }
            if ($nouveau === 'terminee' && $c['statut'] === 'attente_retour_materiel') {
                $sql .= ', restitution_materiel = TRUE';
            }
            $pdo->prepare($sql . ' WHERE commande_id = :id')->execute($params);
            self::ajouterSuivi($id, $nouveau);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Annulation par un employé : le client doit avoir été contacté (téléphone ou mail),
     * le mode de contact et le motif sont enregistrés. Le stock du menu est rendu.
     */
    public static function annulerParEmploye(int $id, string $modeContact, string $motif): bool
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'UPDATE commande SET statut = \'annulee\', motif_annulation = :motif, mode_contact_annulation = :mode
                 WHERE commande_id = :id AND statut IN (\'' . implode("','", self::STATUTS_MODIFIABLES) . '\')'
            );
            $stmt->execute(['motif' => $motif, 'mode' => $modeContact, 'id' => $id]);
            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }
            $pdo->prepare(
                'UPDATE menu SET quantite_restante = quantite_restante + 1
                 WHERE menu_id = (SELECT menu_id FROM commande WHERE commande_id = :id)'
            )->execute(['id' => $id]);
            self::ajouterSuivi($id, 'annulee');
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Modification par un employé (après contact du client), tant que la commande n'est pas partie en livraison. */
    public static function modifierParEmploye(int $id, array $d, array $tarif): bool
    {
        $stmt = Database::get()->prepare(
            'UPDATE commande SET date_prestation = :date, heure_livraison = :heure, adresse_prestation = :adresse,
                ville_prestation = :ville, distance_km = :distance, nombre_personne = :nb,
                prix_menu = :prix_menu, remise = :remise, prix_livraison = :livraison, prix_total = :total
             WHERE commande_id = :id AND statut IN (\'' . implode("','", self::STATUTS_MODIFIABLES) . '\')'
        );
        $stmt->execute([
            'date' => $d['date_prestation'], 'heure' => $d['heure_livraison'],
            'adresse' => $d['adresse_prestation'], 'ville' => $d['ville_prestation'],
            'distance' => $tarif['distance_km'], 'nb' => $d['nombre_personne'],
            'prix_menu' => $tarif['prix_menu'], 'remise' => $tarif['remise'],
            'livraison' => $tarif['prix_livraison'], 'total' => $tarif['prix_total'], 'id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Enregistre une commande dans une transaction :
     * vérification du stock (verrou sur la ligne du menu), insertion, historique, décrément du stock.
     * Retourne la commande créée, ou null si le menu n'est plus disponible.
     */
    public static function creer(int $utilisateurId, int $menuId, array $d, array $tarif): ?array
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT quantite_restante FROM menu WHERE menu_id = :id FOR UPDATE');
            $stmt->execute(['id' => $menuId]);
            if ((int) $stmt->fetchColumn() < 1) {
                $pdo->rollBack();
                return null;
            }

            $pdo->prepare(
                'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison,
                    adresse_prestation, ville_prestation, distance_km, nombre_personne,
                    prix_menu, remise, prix_livraison, prix_total, statut)
                 VALUES (:numero, :utilisateur, :menu, :date, :heure, :adresse, :ville, :distance, :nb,
                    :prix_menu, :remise, :livraison, :total, \'en_attente\')'
            )->execute([
                'numero'      => 'TMP-' . bin2hex(random_bytes(6)),
                'utilisateur' => $utilisateurId,
                'menu'        => $menuId,
                'date'        => $d['date_prestation'],
                'heure'       => $d['heure_livraison'],
                'adresse'     => $d['adresse_prestation'],
                'ville'       => $d['ville_prestation'],
                'distance'    => $tarif['distance_km'],
                'nb'          => $d['nombre_personne'],
                'prix_menu'   => $tarif['prix_menu'],
                'remise'      => $tarif['remise'],
                'livraison'   => $tarif['prix_livraison'],
                'total'       => $tarif['prix_total'],
            ]);
            $id = (int) $pdo->lastInsertId();

            // Numéro lisible : VG-2026-0004
            $numero = sprintf('VG-%s-%04d', date('Y'), $id);
            $pdo->prepare('UPDATE commande SET numero_commande = :n WHERE commande_id = :id')->execute(['n' => $numero, 'id' => $id]);
            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = :id')->execute(['id' => $menuId]);
            self::ajouterSuivi($id, 'en_attente');

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return self::trouver($id);
    }

    /** Modification par le client : uniquement tant que la commande est "en attente" (le menu ne change pas). */
    public static function modifier(int $id, int $utilisateurId, array $d, array $tarif): bool
    {
        $stmt = Database::get()->prepare(
            'UPDATE commande SET date_prestation = :date, heure_livraison = :heure, adresse_prestation = :adresse,
                ville_prestation = :ville, distance_km = :distance, nombre_personne = :nb,
                prix_menu = :prix_menu, remise = :remise, prix_livraison = :livraison, prix_total = :total
             WHERE commande_id = :id AND utilisateur_id = :utilisateur AND statut = \'en_attente\''
        );
        $stmt->execute([
            'date'        => $d['date_prestation'],
            'heure'       => $d['heure_livraison'],
            'adresse'     => $d['adresse_prestation'],
            'ville'       => $d['ville_prestation'],
            'distance'    => $tarif['distance_km'],
            'nb'          => $d['nombre_personne'],
            'prix_menu'   => $tarif['prix_menu'],
            'remise'      => $tarif['remise'],
            'livraison'   => $tarif['prix_livraison'],
            'total'       => $tarif['prix_total'],
            'id'          => $id,
            'utilisateur' => $utilisateurId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /** Annulation par le client (tant que la commande n'est pas acceptée) : le stock est rendu. */
    public static function annulerParClient(int $id, int $utilisateurId): bool
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'UPDATE commande SET statut = \'annulee\', motif_annulation = \'Annulée par le client\'
                 WHERE commande_id = :id AND utilisateur_id = :u AND statut = \'en_attente\''
            );
            $stmt->execute(['id' => $id, 'u' => $utilisateurId]);
            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }
            $pdo->prepare(
                'UPDATE menu SET quantite_restante = quantite_restante + 1
                 WHERE menu_id = (SELECT menu_id FROM commande WHERE commande_id = :id)'
            )->execute(['id' => $id]);
            self::ajouterSuivi($id, 'annulee');
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function ajouterSuivi(int $commandeId, string $statut): void
    {
        Database::get()->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, :statut)')
            ->execute(['id' => $commandeId, 'statut' => $statut]);
    }

    /** Commande complète avec menu, client, historique et avis. */
    public static function trouver(int $id): ?array
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            'SELECT c.*, m.titre AS menu_titre, m.conditions, m.nombre_personne_minimum, m.prix_par_personne,
                    m.delai_commande_jours,
                    u.nom, u.prenom, u.email, u.telephone
             FROM commande c
             JOIN menu m ON m.menu_id = c.menu_id
             JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
             WHERE c.commande_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $commande = $stmt->fetch();
        if (!$commande) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT statut, date_modification FROM commande_suivi WHERE commande_id = :id ORDER BY date_modification, suivi_id');
        $stmt->execute(['id' => $id]);
        $commande['suivi'] = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT note, description, statut, date_creation FROM avis WHERE commande_id = :id');
        $stmt->execute(['id' => $id]);
        $commande['avis'] = $stmt->fetch() ?: null;
        return $commande;
    }

    /** Commande appartenant à un utilisateur donné (empêche de consulter celle d'un autre client). */
    public static function trouverPourUtilisateur(int $id, int $utilisateurId): ?array
    {
        $commande = self::trouver($id);
        return ($commande && (int) $commande['utilisateur_id'] === $utilisateurId) ? $commande : null;
    }

    public static function listerPourUtilisateur(int $utilisateurId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT c.commande_id, c.numero_commande, c.date_commande, c.date_prestation, c.nombre_personne,
                    c.prix_total, c.statut, m.titre AS menu_titre,
                    (SELECT COUNT(*) FROM avis a WHERE a.commande_id = c.commande_id) AS a_avis
             FROM commande c JOIN menu m ON m.menu_id = c.menu_id
             WHERE c.utilisateur_id = :u
             ORDER BY c.date_commande DESC'
        );
        $stmt->execute(['u' => $utilisateurId]);
        return $stmt->fetchAll();
    }
}
