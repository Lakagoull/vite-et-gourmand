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
}
