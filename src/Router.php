<?php

declare(strict_types=1);

namespace App;

/**
 * Routeur minimaliste : associe une méthode HTTP + un chemin à une action.
 */
final class Router
{
    private array $routes = [];

    public function get(string $chemin, callable|array $action): void
    {
        $this->routes['GET'][$chemin] = $action;
    }

    public function post(string $chemin, callable|array $action): void
    {
        $this->routes['POST'][$chemin] = $action;
    }

    public function dispatch(string $methode, string $uri): void
    {
        $chemin = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/') ?: '/';
        $action = $this->routes[$methode][$chemin] ?? null;

        if ($action === null) {
            http_response_code(404);
            render('erreur', [
                'titre'   => 'Page introuvable',
                'message' => "La page demandée n'existe pas ou a été déplacée.",
            ]);
            return;
        }

        if (is_array($action)) {
            [$classe, $methodeAction] = $action;
            (new $classe())->$methodeAction();
            return;
        }
        $action();
    }
}
