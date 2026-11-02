<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Connexion unique à la base relationnelle via PDO.
 * Requêtes préparées obligatoires : les valeurs ne sont jamais concaténées dans le SQL.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'),
                env('DB_PORT', '3306'),
                env('DB_NAME', 'vite_gourmand')
            );
            self::$pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASSWORD', ''), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // vraies requêtes préparées côté serveur
            ]);
        }
        return self::$pdo;
    }
}
