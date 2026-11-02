// =====================================================================
// Vite & Gourmand - Initialisation de la base NoSQL (MongoDB)
// Usage : mongosh "mongodb://127.0.0.1:27017" database/mongo-init.js
//         puis : php database/sync-mongo.php (copie des commandes depuis MySQL)
//
// La collection "statistiques_commandes" reçoit un document par commande
// (copie dénormalisée depuis MySQL, mise à jour à chaque changement de statut).
// Elle alimente le graphique et le calcul du chiffre d'affaires de l'espace admin.
// =====================================================================

const base = db.getSiblingDB('vite_gourmand');

base.statistiques_commandes.drop();
base.createCollection('statistiques_commandes', {
    validator: {
        $jsonSchema: {
            bsonType: 'object',
            required: ['commande_id', 'numero_commande', 'menu_id', 'menu_titre',
                       'nombre_personne', 'prix_total', 'date_commande', 'statut'],
            properties: {
                commande_id:     { bsonType: 'int' },
                numero_commande: { bsonType: 'string' },
                menu_id:         { bsonType: 'int' },
                menu_titre:      { bsonType: 'string' },
                nombre_personne: { bsonType: 'int', minimum: 1 },
                prix_total:      { bsonType: ['double', 'decimal', 'int'] },
                date_commande:   { bsonType: 'date' },
                statut:          { bsonType: 'string' }
            }
        }
    }
});

// Index : unicité de la commande, et recherche par menu + période (filtres du chiffre d'affaires)
base.statistiques_commandes.createIndex({ commande_id: 1 }, { unique: true });
base.statistiques_commandes.createIndex({ menu_id: 1, date_commande: 1 });

print('Collection statistiques_commandes prête (lancer ensuite : php database/sync-mongo.php)');
