<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

/**
 * Accès aux données des menus.
 * Le "prix du menu" affiché correspond au prix pour le nombre minimum de personnes.
 */
final class Menu
{
    /**
     * Recherche des menus avec filtres optionnels.
     * Filtres acceptés : prix_max, prix_min, theme, regime, nb_personnes
     */
    public static function rechercher(array $filtres = []): array
    {
        $sql = "SELECT m.menu_id, m.titre, m.description, m.nombre_personne_minimum, m.prix_par_personne,
                       m.quantite_restante,
                       ROUND(m.prix_par_personne * m.nombre_personne_minimum, 2) AS prix_minimum,
                       t.libelle AS theme, r.libelle AS regime,
                       (SELECT i.chemin FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY i.ordre LIMIT 1) AS image,
                       (SELECT i.texte_alternatif FROM menu_image i WHERE i.menu_id = m.menu_id ORDER BY i.ordre LIMIT 1) AS image_alt
                FROM menu m
                JOIN theme t  ON t.theme_id  = m.theme_id
                JOIN regime r ON r.regime_id = m.regime_id
                WHERE 1 = 1";
        $params = [];

        if (isset($filtres['prix_max'])) {
            $sql .= ' AND m.prix_par_personne * m.nombre_personne_minimum <= :prix_max';
            $params['prix_max'] = $filtres['prix_max'];
        }
        if (isset($filtres['fourchette_max'])) {
            $sql .= ' AND m.prix_par_personne * m.nombre_personne_minimum <= :fourchette_max';
            $params['fourchette_max'] = $filtres['fourchette_max'];
        }
        if (isset($filtres['prix_min'])) {
            $sql .= ' AND m.prix_par_personne * m.nombre_personne_minimum >= :prix_min';
            $params['prix_min'] = $filtres['prix_min'];
        }
        if (isset($filtres['theme'])) {
            $sql .= ' AND m.theme_id = :theme';
            $params['theme'] = $filtres['theme'];
        }
        if (isset($filtres['regime'])) {
            $sql .= ' AND m.regime_id = :regime';
            $params['regime'] = $filtres['regime'];
        }
        if (isset($filtres['nb_personnes'])) {
            // Menus commandables pour ce nombre de convives
            $sql .= ' AND m.nombre_personne_minimum <= :nb_personnes';
            $params['nb_personnes'] = $filtres['nb_personnes'];
        }

        $sql .= ' ORDER BY prix_minimum ASC';
        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Valide et nettoie les filtres reçus (GET), en ignorant les valeurs incorrectes. */
    public static function filtresDepuis(array $source): array
    {
        $filtres = [];
        foreach (['prix_max', 'prix_min', 'fourchette_max'] as $cle) {
            $v = filter_var($source[$cle] ?? null, FILTER_VALIDATE_FLOAT);
            if ($v !== false && $v !== null && $v >= 0) {
                $filtres[$cle] = $v;
            }
        }
        foreach (['theme', 'regime', 'nb_personnes'] as $cle) {
            $v = filter_var($source[$cle] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($v !== false && $v !== null) {
                $filtres[$cle] = $v;
            }
        }
        return $filtres;
    }

    /** Détail complet d'un menu : informations, images, plats et allergènes. */
    public static function trouver(int $id): ?array
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            "SELECT m.*, ROUND(m.prix_par_personne * m.nombre_personne_minimum, 2) AS prix_minimum,
                    t.libelle AS theme, r.libelle AS regime
             FROM menu m
             JOIN theme t  ON t.theme_id  = m.theme_id
             JOIN regime r ON r.regime_id = m.regime_id
             WHERE m.menu_id = :id"
        );
        $stmt->execute(['id' => $id]);
        $menu = $stmt->fetch();
        if (!$menu) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT chemin, texte_alternatif FROM menu_image WHERE menu_id = :id ORDER BY ordre');
        $stmt->execute(['id' => $id]);
        $menu['images'] = $stmt->fetchAll();

        $stmt = $pdo->prepare(
            "SELECT p.plat_id, p.titre_plat, p.type_plat, p.photo,
                    GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR '|') AS allergenes
             FROM menu_plat mp
             JOIN plat p ON p.plat_id = mp.plat_id
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             WHERE mp.menu_id = :id
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat"
        );
        $stmt->execute(['id' => $id]);

        $menu['plats'] = ['entree' => [], 'plat' => [], 'dessert' => []];
        foreach ($stmt->fetchAll() as $plat) {
            $plat['allergenes'] = $plat['allergenes'] ? explode('|', $plat['allergenes']) : [];
            $menu['plats'][$plat['type_plat']][] = $plat;
        }
        return $menu;
    }

    public static function themes(): array
    {
        return Database::get()->query('SELECT theme_id, libelle FROM theme ORDER BY theme_id')->fetchAll();
    }

    public static function regimes(): array
    {
        return Database::get()->query('SELECT regime_id, libelle FROM regime ORDER BY regime_id')->fetchAll();
    }

    // -----------------------------------------------------------------
    // Gestion (espace employé)
    // -----------------------------------------------------------------

    /** Liste pour l'espace employé, avec nombre de plats et de commandes. */
    public static function listeGestion(): array
    {
        return Database::get()->query(
            'SELECT m.menu_id, m.titre, m.nombre_personne_minimum, m.prix_par_personne, m.quantite_restante,
                    t.libelle AS theme, r.libelle AS regime,
                    (SELECT COUNT(*) FROM menu_plat mp WHERE mp.menu_id = m.menu_id) AS nb_plats,
                    (SELECT COUNT(*) FROM commande c WHERE c.menu_id = m.menu_id) AS nb_commandes
             FROM menu m
             JOIN theme t ON t.theme_id = m.theme_id
             JOIN regime r ON r.regime_id = m.regime_id
             ORDER BY m.titre'
        )->fetchAll();
    }

    /** Identifiants des plats d'un menu. */
    public static function platsIds(int $id): array
    {
        $stmt = Database::get()->prepare('SELECT plat_id FROM menu_plat WHERE menu_id = :id');
        $stmt->execute(['id' => $id]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** Création (id null) ou modification d'un menu et de sa composition. */
    public static function enregistrer(?int $id, array $d, array $plats): int
    {
        $pdo = Database::get();
        $champs = [
            'titre' => $d['titre'], 'description' => $d['description'],
            'nb' => $d['nombre_personne_minimum'], 'prix' => $d['prix_par_personne'],
            'conditions' => $d['conditions'], 'delai' => $d['delai_commande_jours'],
            'stock' => $d['quantite_restante'], 'theme' => $d['theme_id'], 'regime' => $d['regime_id'],
        ];
        $pdo->beginTransaction();
        try {
            if ($id === null) {
                $pdo->prepare(
                    'INSERT INTO menu (titre, description, nombre_personne_minimum, prix_par_personne, conditions,
                        delai_commande_jours, quantite_restante, theme_id, regime_id)
                     VALUES (:titre, :description, :nb, :prix, :conditions, :delai, :stock, :theme, :regime)'
                )->execute($champs);
                $id = (int) $pdo->lastInsertId();
            } else {
                $pdo->prepare(
                    'UPDATE menu SET titre = :titre, description = :description, nombre_personne_minimum = :nb,
                        prix_par_personne = :prix, conditions = :conditions, delai_commande_jours = :delai,
                        quantite_restante = :stock, theme_id = :theme, regime_id = :regime
                     WHERE menu_id = :id'
                )->execute($champs + ['id' => $id]);
                $pdo->prepare('DELETE FROM menu_plat WHERE menu_id = :id')->execute(['id' => $id]);
            }
            $ins = $pdo->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (:m, :p)');
            foreach ($plats as $p) {
                $ins->execute(['m' => $id, 'p' => $p]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function ajouterImage(int $menuId, string $chemin, string $alt): void
    {
        Database::get()->prepare(
            'INSERT INTO menu_image (menu_id, chemin, texte_alternatif, ordre)
             SELECT :m, :c, :a, COALESCE(MAX(ordre), 0) + 1 FROM menu_image WHERE menu_id = :m2'
        )->execute(['m' => $menuId, 'c' => $chemin, 'a' => $alt, 'm2' => $menuId]);
    }

    /** Supprime une image du menu et retourne son chemin (pour effacer le fichier). */
    public static function supprimerImage(int $imageId, int $menuId): ?string
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT chemin FROM menu_image WHERE image_id = :i AND menu_id = :m');
        $stmt->execute(['i' => $imageId, 'm' => $menuId]);
        $chemin = $stmt->fetchColumn();
        if ($chemin === false) {
            return null;
        }
        $pdo->prepare('DELETE FROM menu_image WHERE image_id = :i')->execute(['i' => $imageId]);
        return $chemin;
    }

    public static function imagesAvecId(int $menuId): array
    {
        $stmt = Database::get()->prepare('SELECT image_id, chemin, texte_alternatif FROM menu_image WHERE menu_id = :id ORDER BY ordre');
        $stmt->execute(['id' => $menuId]);
        return $stmt->fetchAll();
    }

    /**
     * Supprime un menu. Refusé s'il a déjà été commandé (historique et chiffre d'affaires conservés) :
     * il suffit alors de mettre son stock à 0 pour le retirer de la vente.
     */
    public static function supprimer(int $id): bool
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM commande WHERE menu_id = :id');
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
        $pdo->prepare('DELETE FROM menu WHERE menu_id = :id')->execute(['id' => $id]);
        return true;
    }
}
