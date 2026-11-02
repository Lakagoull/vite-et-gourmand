<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Règles de calcul du prix d'une commande (cahier des charges) :
 *  - prix du menu = prix par personne x nombre de personnes
 *  - réduction de 10 % si nombre de personnes >= minimum du menu + 5
 *  - livraison gratuite à Bordeaux, sinon 5 € + 0,59 € par kilomètre
 * Le calcul est toujours refait côté serveur : le prix affiché en JavaScript n'est qu'indicatif.
 */
final class Tarification
{
    public const TAUX_REMISE = 0.10;
    public const PERSONNES_EN_PLUS_POUR_REMISE = 5;
    public const FORFAIT_LIVRAISON = 5.00;
    public const PRIX_KM = 0.59;

    public static function calculer(array $menu, int $nbPersonnes, bool $bordeaux, float $distanceKm): array
    {
        $prixMenu = round((float) $menu['prix_par_personne'] * $nbPersonnes, 2);

        $seuilRemise = (int) $menu['nombre_personne_minimum'] + self::PERSONNES_EN_PLUS_POUR_REMISE;
        $remise = $nbPersonnes >= $seuilRemise ? round($prixMenu * self::TAUX_REMISE, 2) : 0.0;

        $livraison = $bordeaux ? 0.0 : round(self::FORFAIT_LIVRAISON + self::PRIX_KM * $distanceKm, 2);

        return [
            'prix_menu'      => $prixMenu,
            'remise'         => $remise,
            'seuil_remise'   => $seuilRemise,
            'prix_livraison' => $livraison,
            'distance_km'    => $bordeaux ? 0.0 : $distanceKm,
            'bordeaux'       => $bordeaux,
            'prix_total'     => round($prixMenu - $remise + $livraison, 2),
        ];
    }
}
