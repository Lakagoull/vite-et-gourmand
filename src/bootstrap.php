<?php
/**
 * Démarrage de l'application : configuration, session sécurisée, en-têtes HTTP.
 */

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

// --- Chargement du fichier .env (variables de configuration) ---
$envFile = ROOT . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || str_starts_with($ligne, '#') || !str_contains($ligne, '=')) {
            continue;
        }
        [$cle, $valeur] = array_map('trim', explode('=', $ligne, 2));
        $valeur = trim($valeur, "\"'");
        // Une variable déjà définie par l'hébergeur est prioritaire
        if (getenv($cle) === false) {
            putenv("$cle=$valeur");
            $_ENV[$cle] = $valeur;
        }
    }
}

// --- Affichage des erreurs : uniquement en développement ---
$dev = env('APP_ENV', 'prod') === 'dev';
ini_set('display_errors', $dev ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');

// --- Session sécurisée ---
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name('VGSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $https,   // cookie envoyé uniquement en HTTPS en production
    'httponly' => true,     // inaccessible en JavaScript (protection XSS)
    'samesite' => 'Lax',    // limite les attaques CSRF
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_start();

// --- En-têtes de sécurité HTTP ---
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
if ($https) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
