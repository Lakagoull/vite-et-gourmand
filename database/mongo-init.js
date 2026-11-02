// =====================================================================
// Vite & Gourmand - Initialisation de la base NoSQL (MongoDB)
// Usage : mongosh "mongodb://127.0.0.1:27017" database/mongo-init.js
//
// La collection "statistiques_commandes" reçoit un document à chaque
// commande validée (copie dénormalisée depuis MySQL). Elle alimente
// le graphique et le calcul du chiffre d'affaires de l'espace admin.
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

base.statistiques_commandes.createIndex({ commande_id: 1 }, { unique: true });
base.statistiques_commandes.createIndex({ menu_id: 1, date_commande: 1 });

// Mêmes commandes que dans data.sql
base.statistiques_commandes.insertMany([
    { commande_id: 1, numero_commande: 'VG-2026-0001', menu_id: 3, menu_titre: 'Menu Classique Bordelais',
      nombre_personne: 4, prix_total: 116.00, date_commande: new Date('2026-09-10T14:20:00'), statut: 'terminee' },
    { commande_id: 2, numero_commande: 'VG-2026-0002', menu_id: 2, menu_titre: 'Menu de Pâques Printanier',
      nombre_personne: 9, prix_total: 302.21, date_commande: new Date('2026-10-01T10:05:00'), statut: 'terminee' },
    { commande_id: 3, numero_commande: 'VG-2026-0003', menu_id: 1, menu_titre: 'Menu de Noël Tradition',
      nombre_personne: 6, prix_total: 252.00, date_commande: new Date('2026-10-28T18:45:00'), statut: 'en_attente' }
]);

print('Collection statistiques_commandes : ' + base.statistiques_commandes.countDocuments() + ' documents');
