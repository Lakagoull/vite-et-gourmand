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
                ->query('SELECT jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY ordre')
                ->fetchAll();
        } catch (\Throwable $e) {
            // Le pied de page ne doit pas bloquer l'affichage si la base est indisponible
            error_log($e->getMessage());
            return [];
        }
    }

    /** "09:00:00" -> "09h00" */
    public static function format(?string $heure): string
    {
        return $heure ? str_replace(':', 'h', substr($heure, 0, 5)) : '';
    }
}
