<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Avis
{
    /** Avis validés par un employé, affichés sur la page d'accueil. */
    public static function valides(int $limite = 6): array
    {
        $stmt = Database::get()->prepare(
            "SELECT a.note, a.description, a.date_creation, u.prenom,
                    LEFT(u.nom, 1) AS initiale, m.titre AS menu
             FROM avis a
             JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
             JOIN commande c    ON c.commande_id    = a.commande_id
             JOIN menu m        ON m.menu_id        = c.menu_id
             WHERE a.statut = 'valide'
             ORDER BY a.date_creation DESC
             LIMIT :limite"
        );
        $stmt->bindValue('limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
