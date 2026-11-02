<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Menu;

final class MenuController
{
    /** Vue globale des menus (rendu serveur, fonctionne aussi sans JavaScript). */
    public function index(): void
    {
        $filtres = Menu::filtresDepuis($_GET);
        render('menus', [
            'titrePage' => 'Nos menus',
            'menus'     => Menu::rechercher($filtres),
            'filtres'   => $filtres,
            'themes'    => Menu::themes(),
            'regimes'   => Menu::regimes(),
        ]);
    }

    /** API JSON utilisée par le JavaScript pour filtrer sans recharger la page. */
    public function api(): void
    {
        $menus = Menu::rechercher(Menu::filtresDepuis($_GET));
        json(array_map(fn (array $m) => [
            'id'           => (int) $m['menu_id'],
            'titre'        => $m['titre'],
            'description'  => $m['description'],
            'theme'        => $m['theme'],
            'regime'       => $m['regime'],
            'nb_minimum'   => (int) $m['nombre_personne_minimum'],
            'prix_minimum' => (float) $m['prix_minimum'],
            'prix_texte'   => prix($m['prix_minimum']),
            'stock'        => (int) $m['quantite_restante'],
            'image'        => $m['image'] ? '/' . $m['image'] : null,
            'image_alt'    => $m['image_alt'] ?? '',
        ], $menus));
    }

    /** Vue détaillée d'un menu. */
    public function detail(): void
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $menu = $id ? Menu::trouver($id) : null;

        if ($menu === null) {
            http_response_code(404);
            render('erreur', ['titre' => 'Menu introuvable', 'message' => "Ce menu n'existe pas ou n'est plus proposé."]);
            return;
        }

        render('menu-detail', ['titrePage' => $menu['titre'], 'menu' => $menu]);
    }
}
