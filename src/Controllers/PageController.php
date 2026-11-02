<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Avis;

final class PageController
{
    public function accueil(): void
    {
        render('accueil', [
            'titrePage' => 'Traiteur à Bordeaux depuis 25 ans',
            'avis'      => Avis::valides(6),
        ]);
    }

    public function mentionsLegales(): void
    {
        render('mentions-legales', ['titrePage' => 'Mentions légales']);
    }

    public function cgv(): void
    {
        render('cgv', ['titrePage' => 'Conditions générales de vente']);
    }

    public function confidentialite(): void
    {
        render('confidentialite', ['titrePage' => 'Politique de confidentialité']);
    }
}
