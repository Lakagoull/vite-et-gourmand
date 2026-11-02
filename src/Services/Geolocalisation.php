<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Localisation d'une adresse via l'API Adresse officielle (Base Adresse Nationale).
 * Gratuite, sans clé, hébergée par l'État : https://adresse.data.gouv.fr
 */
final class Geolocalisation
{
    /** Coordonnées de l'atelier Vite & Gourmand (12 rue Sainte-Catherine, Bordeaux). */
    public const LAT_ENTREPRISE = 44.841405;
    public const LON_ENTREPRISE = -0.574364;

    /** Code INSEE de la commune de Bordeaux. */
    public const CODE_BORDEAUX = '33063';

    /**
     * Retourne ['libelle', 'ville', 'code_commune', 'lat', 'lon', 'distance_km', 'bordeaux'] ou null.
     */
    public static function localiser(string $adresse, string $ville): ?array
    {
        $requete = trim($adresse . ' ' . $ville);
        if (mb_strlen($requete) < 5) {
            return null;
        }

        // Petit cache en session pour éviter d'interroger l'API à chaque frappe
        $cle = md5(mb_strtolower($requete));
        if (isset($_SESSION['geo'][$cle])) {
            return $_SESSION['geo'][$cle];
        }

        $url = 'https://api-adresse.data.gouv.fr/search/?' . http_build_query(['q' => $requete, 'limit' => 1]);
        $contexte = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $reponse = @file_get_contents($url, false, $contexte);
        if ($reponse === false) {
            error_log('API Adresse injoignable');
            return null;
        }

        $donnees = json_decode($reponse, true);
        $lieu = $donnees['features'][0] ?? null;
        // Score de confiance trop faible = adresse non reconnue
        if ($lieu === null || ($lieu['properties']['score'] ?? 0) < 0.4) {
            return null;
        }

        [$lon, $lat] = $lieu['geometry']['coordinates'];
        $resultat = [
            'libelle'      => $lieu['properties']['label'],
            'ville'        => $lieu['properties']['city'],
            'code_commune' => $lieu['properties']['citycode'],
            'lat'          => (float) $lat,
            'lon'          => (float) $lon,
            'bordeaux'     => $lieu['properties']['citycode'] === self::CODE_BORDEAUX,
            'distance_km'  => round(self::distance(self::LAT_ENTREPRISE, self::LON_ENTREPRISE, (float) $lat, (float) $lon), 1),
        ];
        $_SESSION['geo'][$cle] = $resultat;
        return $resultat;
    }

    /** Distance en km entre deux points GPS (formule de haversine). */
    public static function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371; // rayon de la Terre en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
