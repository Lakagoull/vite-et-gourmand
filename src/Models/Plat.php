<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Plat
{
    public const TYPES = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];

    /** Tous les plats avec leurs allergènes et le nombre de menus qui les utilisent. */
    public static function tous(): array
    {
        return Database::get()->query(
            "SELECT p.*, GROUP_CONCAT(DISTINCT a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes,
                    (SELECT COUNT(*) FROM menu_plat mp WHERE mp.plat_id = p.plat_id) AS nb_menus
             FROM plat p
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat"
        )->fetchAll();
    }

    public static function trouver(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM plat WHERE plat_id = :id');
        $stmt->execute(['id' => $id]);
        $plat = $stmt->fetch();
        if (!$plat) {
            return null;
        }
        $stmt = Database::get()->prepare('SELECT allergene_id FROM plat_allergene WHERE plat_id = :id');
        $stmt->execute(['id' => $id]);
        $plat['allergenes'] = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        return $plat;
    }

    public static function allergenes(): array
    {
        return Database::get()->query('SELECT allergene_id, libelle FROM allergene ORDER BY libelle')->fetchAll();
    }

    /** Création (id null) ou modification d'un plat et de ses allergènes. */
    public static function enregistrer(?int $id, array $d, array $allergenes): int
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            if ($id === null) {
                $pdo->prepare('INSERT INTO plat (titre_plat, type_plat, photo) VALUES (:titre, :type, :photo)')
                    ->execute(['titre' => $d['titre_plat'], 'type' => $d['type_plat'], 'photo' => $d['photo']]);
                $id = (int) $pdo->lastInsertId();
            } else {
                $pdo->prepare('UPDATE plat SET titre_plat = :titre, type_plat = :type, photo = :photo WHERE plat_id = :id')
                    ->execute(['titre' => $d['titre_plat'], 'type' => $d['type_plat'], 'photo' => $d['photo'], 'id' => $id]);
                $pdo->prepare('DELETE FROM plat_allergene WHERE plat_id = :id')->execute(['id' => $id]);
            }
            $ins = $pdo->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (:p, :a)');
            foreach ($allergenes as $a) {
                $ins->execute(['p' => $id, 'a' => $a]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function supprimer(int $id): void
    {
        Database::get()->prepare('DELETE FROM plat WHERE plat_id = :id')->execute(['id' => $id]);
    }
}
