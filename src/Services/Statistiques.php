<?php

declare(strict_types=1);

namespace App\Services;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;

/**
 * Base NoSQL (MongoDB) : copie des commandes pour les statistiques de l'administrateur.
 * Une erreur MongoDB ne doit jamais bloquer une commande : elle est seulement journalisée.
 */
final class Statistiques
{
    private const COLLECTION = 'statistiques_commandes';
    private static ?Manager $manager = null;

    private static function manager(): Manager
    {
        return self::$manager ??= new Manager(env('MONGO_URI', 'mongodb://127.0.0.1:27017'));
    }

    private static function espace(): string
    {
        return env('MONGO_DB', 'vite_gourmand') . '.' . self::COLLECTION;
    }

    /** Ajoute ou met à jour le document d'une commande. */
    public static function enregistrerCommande(array $commande, string $titreMenu): void
    {
        try {
            $bulk = new BulkWrite();
            $bulk->update(
                ['commande_id' => (int) $commande['commande_id']],
                ['$set' => [
                    'commande_id'     => (int) $commande['commande_id'],
                    'numero_commande' => $commande['numero_commande'],
                    'menu_id'         => (int) $commande['menu_id'],
                    'menu_titre'      => $titreMenu,
                    'nombre_personne' => (int) $commande['nombre_personne'],
                    'prix_total'      => (float) $commande['prix_total'],
                    'date_commande'   => new UTCDateTime(strtotime($commande['date_commande']) * 1000),
                    'statut'          => $commande['statut'],
                ]],
                ['upsert' => true]
            );
            self::manager()->executeBulkWrite(self::espace(), $bulk);
        } catch (\Throwable $e) {
            error_log('MongoDB (enregistrement) : ' . $e->getMessage());
        }
    }

    public static function majStatut(int $commandeId, string $statut): void
    {
        try {
            $bulk = new BulkWrite();
            $bulk->update(['commande_id' => $commandeId], ['$set' => ['statut' => $statut]]);
            self::manager()->executeBulkWrite(self::espace(), $bulk);
        } catch (\Throwable $e) {
            error_log('MongoDB (statut) : ' . $e->getMessage());
        }
    }

    /** Exécute un pipeline d'agrégation et retourne les résultats sous forme de tableaux. */
    public static function agreger(array $pipeline): array
    {
        $commande = new Command([
            'aggregate' => self::COLLECTION,
            'pipeline'  => $pipeline,
            'cursor'    => new \stdClass(),
        ]);
        $curseur = self::manager()->executeCommand(env('MONGO_DB', 'vite_gourmand'), $commande);
        $curseur->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        return $curseur->toArray();
    }

    /**
     * Nombre de commandes et chiffre d'affaires par menu (commandes annulées exclues).
     * Filtres optionnels : menu_id, du (AAAA-MM-JJ), au (AAAA-MM-JJ, inclus).
     */
    public static function parMenu(array $filtres = []): array
    {
        $match = ['statut' => ['$ne' => 'annulee']];
        if (!empty($filtres['menu_id'])) {
            $match['menu_id'] = (int) $filtres['menu_id'];
        }
        $periode = [];
        if (!empty($filtres['du'])) {
            $periode['$gte'] = new UTCDateTime(strtotime($filtres['du'] . ' 00:00:00') * 1000);
        }
        if (!empty($filtres['au'])) {
            $periode['$lt'] = new UTCDateTime(strtotime($filtres['au'] . ' 00:00:00 +1 day') * 1000);
        }
        if ($periode) {
            $match['date_commande'] = $periode;
        }

        $resultats = self::agreger([
            ['$match' => $match],
            ['$group' => [
                '_id'       => '$menu_id',
                'titre'     => ['$last' => '$menu_titre'],
                'commandes' => ['$sum' => 1],
                'personnes' => ['$sum' => '$nombre_personne'],
                'ca'        => ['$sum' => '$prix_total'],
            ]],
            ['$sort' => ['commandes' => -1, 'ca' => -1]],
        ]);

        return array_map(fn (array $r) => [
            'menu_id'   => (int) $r['_id'],
            'titre'     => (string) $r['titre'],
            'commandes' => (int) $r['commandes'],
            'personnes' => (int) $r['personnes'],
            'ca'        => round((float) $r['ca'], 2),
        ], $resultats);
    }
}
