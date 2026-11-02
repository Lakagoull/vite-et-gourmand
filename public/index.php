<?php
/**
 * Point d'entrée unique de l'application (front controller).
 */

declare(strict_types=1);

// Serveur PHP intégré : laisser servir directement les fichiers statiques (CSS, JS, images)
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\EspaceController;
use App\Controllers\MenuController;
use App\Controllers\PageController;
use App\Router;

$router = new Router();

// Pages publiques
$router->get('/', [PageController::class, 'accueil']);
$router->get('/menus', [MenuController::class, 'index']);
$router->get('/menu', [MenuController::class, 'detail']);
$router->get('/api/menus', [MenuController::class, 'api']);
$router->get('/contact', [ContactController::class, 'formulaire']);
$router->post('/contact', [ContactController::class, 'envoyer']);
$router->get('/mentions-legales', [PageController::class, 'mentionsLegales']);
$router->get('/cgv', [PageController::class, 'cgv']);
$router->get('/confidentialite', [PageController::class, 'confidentialite']);

// Comptes et authentification
$router->get('/inscription', [AuthController::class, 'inscriptionForm']);
$router->post('/inscription', [AuthController::class, 'inscrire']);
$router->get('/connexion', [AuthController::class, 'connexionForm']);
$router->post('/connexion', [AuthController::class, 'connecter']);
$router->post('/deconnexion', [AuthController::class, 'deconnecter']);
$router->get('/mot-de-passe-oublie', [AuthController::class, 'oubliForm']);
$router->post('/mot-de-passe-oublie', [AuthController::class, 'envoyerLien']);
$router->get('/reinitialiser-mot-de-passe', [AuthController::class, 'reinitialiserForm']);
$router->post('/reinitialiser-mot-de-passe', [AuthController::class, 'reinitialiser']);

// Espaces réservés
$router->get('/espace', [EspaceController::class, 'utilisateur']);
$router->get('/employe', [EspaceController::class, 'employe']);
$router->get('/admin', [EspaceController::class, 'admin']);

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    if (env('APP_ENV') === 'dev') {
        echo '<pre>' . e((string) $e) . '</pre>';
    } else {
        render('erreur', ['titre' => 'Erreur', 'message' => 'Une erreur inattendue est survenue. Merci de réessayer plus tard.']);
    }
}
