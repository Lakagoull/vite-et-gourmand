<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

final class Horaire
{
    /** Horaires du lundi au dimanche (affichés dans le pied de page). */
    public static function tous(): array
    {
        try {
            return Database::get()
                ->query('SELECT horaire_id, jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY ordre')
                ->fetchAll();
        } catch (\Throwable $e) {
            // Le pied de page ne doit pas bloquer l'affichage si la base est indisponible
            error_log($e->getMessage());
            return [];
        }
    }

    /** Enregistre les horaires d'un jour (null = fermé). */
    public static function enregistrer(int $id, ?string $ouverture, ?string $fermeture): void
    {
        Database::get()->prepare(
            'UPDATE horaire SET heure_ouverture = :o, heure_fermeture = :f WHERE horaire_id = :id'
        )->execute(['o' => $ouverture, 'f' => $fermeture, 'id' => $id]);
    }

    /** "09:00:00" -> "09h00" */
    public static function format(?string $heure): string
    {
        return $heure ? str_replace(':', 'h', substr($heure, 0, 5)) : '';
    }
}
