<?php
/**
 * Synchronise la base NoSQL (MongoDB) à partir des commandes de la base relationnelle.
 * Usage : php database/sync-mongo.php
 * À lancer après l'import de data.sql (ou à tout moment pour reconstruire les statistiques).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Script réservé à la ligne de commande.');
}

$_SERVER['REQUEST_URI'] = '/';
define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';

// Chargement de la configuration (.env) sans démarrer de session
foreach (is_file(ROOT . '/.env') ? file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $ligne) {
    if ($ligne[0] !== '#' && str_contains($ligne, '=')) {
        [$cle, $valeur] = array_map('trim', explode('=', $ligne, 2));
        if (getenv($cle) === false) {
            putenv($cle . '=' . trim($valeur, "\"'"));
        }
    }
}

use App\Database;
use App\Services\Statistiques;

$commandes = Database::get()->query(
    'SELECT c.*, m.titre AS menu_titre FROM commande c JOIN menu m ON m.menu_id = c.menu_id ORDER BY c.commande_id'
)->fetchAll();

foreach ($commandes as $c) {
    Statistiques::enregistrerCommande($c, $c['menu_titre']);
}
echo count($commandes) . " commande(s) synchronisée(s) dans MongoDB.\n";
