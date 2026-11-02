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

    /**
     * Dépôt d'un avis par le client : la commande doit lui appartenir, être terminée
     * et ne pas avoir déjà d'avis. L'avis est publié seulement après validation par un employé.
     */
    public static function creer(int $commandeId, int $utilisateurId, int $note, string $description): bool
    {
        $stmt = Database::get()->prepare(
            "INSERT INTO avis (commande_id, utilisateur_id, note, description, statut)
             SELECT c.commande_id, c.utilisateur_id, :note, :description, 'en_attente'
             FROM commande c
             WHERE c.commande_id = :id AND c.utilisateur_id = :u AND c.statut = 'terminee'
               AND NOT EXISTS (SELECT 1 FROM avis a WHERE a.commande_id = c.commande_id)"
        );
        $stmt->execute(['note' => $note, 'description' => $description, 'id' => $commandeId, 'u' => $utilisateurId]);
        return $stmt->rowCount() > 0;
    }
}
