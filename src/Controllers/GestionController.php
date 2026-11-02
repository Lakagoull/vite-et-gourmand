<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Auth;

/**
 * Espaces employé et administrateur (développés dans les fonctionnalités suivantes).
 */
final class GestionController
{
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
