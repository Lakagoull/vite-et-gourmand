<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Auth;

/**
 * Espaces réservés selon le rôle. Le contenu détaillé de chaque espace
 * est développé dans les fonctionnalités suivantes.
 */
final class EspaceController
{
    public function utilisateur(): void
    {
        $u = Auth::exiger('utilisateur');
        render('espace-accueil', ['titrePage' => 'Mon espace', 'u' => $u, 'espace' => 'client']);
    }

    public function employe(): void
    {
        $u = Auth::exiger('employe');
        render('espace-accueil', ['titrePage' => 'Espace employé', 'u' => $u, 'espace' => 'employé']);
    }

    public function admin(): void
    {
        $u = Auth::exiger('administrateur');
        render('espace-accueil', ['titrePage' => 'Espace administrateur', 'u' => $u, 'espace' => 'administrateur']);
    }
}
