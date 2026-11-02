<?php
/**
 * Exécute les scripts SQL (schema.sql puis data.sql) sur la base configurée.
 * Utile quand le client "mysql" en ligne de commande n'est pas disponible
 * (ou incompatible avec l'authentification MySQL 8 de l'hébergeur).
 *
 * Usage : php database/import.php [fichier.env]
 *   - sans argument : utilise le fichier .env du projet
 *   - avec un fichier : utilise ses accès (ex. accès de production)
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Script réservé à la ligne de commande.');
}

$racine = dirname(__DIR__);
$fichierEnv = $argv[1] ?? $racine . '/.env';
$cfg = [];
foreach (file($fichierEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
    if ($ligne[0] !== '#' && str_contains($ligne, '=')) {
        [$cle, $valeur] = array_map('trim', explode('=', $ligne, 2));
        $cfg[$cle] = trim($valeur, "\"'");
    }
}

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if (!empty($cfg['DB_SSL_CA'])) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $cfg['DB_SSL_CA'];
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
}
// Connexion sans base : schema.sql crée lui-même la base
$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $cfg['DB_HOST'], $cfg['DB_PORT'] ?? '3306'),
    $cfg['DB_USER'],
    $cfg['DB_PASSWORD'] ?? '',
    $options
);

foreach (['schema.sql', 'data.sql'] as $script) {
    $sql = file_get_contents(__DIR__ . '/' . $script);
    // Découpage instruction par instruction (point-virgule en fin de ligne)
    $instructions = array_filter(array_map('trim', preg_split('/;\s*(\r?\n|$)/', $sql)));
    $n = 0;
    foreach ($instructions as $instruction) {
        // Ignore les blocs ne contenant que des commentaires
        if (trim(preg_replace('/^\s*--.*$/m', '', $instruction)) === '') {
            continue;
        }
        $pdo->exec($instruction);
        $n++;
    }
    echo "$script : $n instruction(s) exécutée(s)\n";
}

$pdo->exec('USE ' . ($cfg['DB_NAME'] ?? 'vite_gourmand'));
foreach (['utilisateur', 'menu', 'plat', 'commande', 'avis'] as $table) {
    echo "  $table : " . $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn() . " ligne(s)\n";
}
