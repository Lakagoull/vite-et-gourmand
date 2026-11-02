-- =====================================================================
-- Vite & Gourmand - Jeu de données de démonstration
-- À exécuter APRÈS schema.sql
--
-- Comptes de test (mots de passe hachés en bcrypt) :
--   Administrateur : jose@vite-gourmand.fr     / Jose@Admin2026
--   Employée       : julie@vite-gourmand.fr    / Julie@Employe2026
--   Client         : client.demo@example.com   / Client@Demo2026
-- =====================================================================

USE vite_gourmand;
SET NAMES utf8mb4;

-- Rôles
INSERT INTO role (role_id, libelle) VALUES
(1, 'utilisateur'),
(2, 'employe'),
(3, 'administrateur');

-- Utilisateurs
-- Le compte administrateur est créé ici uniquement : l'application
-- ne permet pas de créer un compte administrateur.
INSERT INTO utilisateur (utilisateur_id, email, password, nom, prenom, telephone, ville, pays, adresse_postale, role_id, actif) VALUES
(1, 'jose@vite-gourmand.fr',   '$2y$10$Rm/jhcTV/wHKRQC19/AsN.MwNQ2ltpwPVPyr2FPLAzqsVIiVB8RGi', 'Martin',  'José',   '0601020304', 'Bordeaux', 'France', '12 rue Sainte-Catherine, 33000 Bordeaux', 3, TRUE),
(2, 'julie@vite-gourmand.fr',  '$2y$10$tmRA7Y.8ilGFe.P1HVeELeiWfbAdTagpz5GjhaWjlkin2W0oyEQUq', 'Durand',  'Julie',  '0605060708', 'Bordeaux', 'France', '12 rue Sainte-Catherine, 33000 Bordeaux', 2, TRUE),
(3, 'client.demo@example.com', '$2y$10$RM2kEir1EGb92aBmSCYVC.zTU15HakrQ87UQuQJboaQcKjbDsb7Ju', 'Bernard', 'Sophie', '0611223344', 'Bordeaux', 'France', '45 cours de l''Intendance, 33000 Bordeaux', 1, TRUE);

-- Thèmes
INSERT INTO theme (theme_id, libelle) VALUES
(1, 'Noël'),
(2, 'Pâques'),
(3, 'Classique'),
(4, 'Évènement');

-- Régimes
INSERT INTO regime (regime_id, libelle) VALUES
(1, 'Classique'),
(2, 'Végétarien'),
(3, 'Vegan'),
(4, 'Sans gluten'),
(5, 'Halal');

-- Les 14 allergènes à déclaration obligatoire (règlement UE n°1169/2011)
INSERT INTO allergene (allergene_id, libelle) VALUES
(1, 'Gluten'),
(2, 'Crustacés'),
(3, 'Œufs'),
(4, 'Poissons'),
(5, 'Arachides'),
(6, 'Soja'),
(7, 'Lait'),
(8, 'Fruits à coque'),
(9, 'Céleri'),
(10, 'Moutarde'),
(11, 'Graines de sésame'),
(12, 'Sulfites'),
(13, 'Lupin'),
(14, 'Mollusques');

-- Menus
INSERT INTO menu (menu_id, titre, description, nombre_personne_minimum, prix_par_personne, conditions, delai_commande_jours, quantite_restante, theme_id, regime_id) VALUES
(1, 'Menu de Noël Tradition',
    'Un repas de fête généreux autour du foie gras, de la volaille fermière et de la traditionnelle bûche.',
    6, 42.00,
    'À commander au minimum 14 jours avant la prestation. Les plats sont livrés froids : à conserver au réfrigérateur (entre 0 et 4 °C) et à consommer sous 48 h.',
    14, 8, 1, 1),
(2, 'Menu de Pâques Printanier',
    'L''agneau de lait à l''honneur, accompagné de légumes primeurs et d''un dessert chocolaté.',
    4, 36.00,
    'À commander au minimum 7 jours avant la prestation. Conserver au frais (entre 0 et 4 °C).',
    7, 10, 2, 1),
(3, 'Menu Classique Bordelais',
    'Les saveurs du Sud-Ouest : entrecôte à la bordelaise, gratin et cannelés maison.',
    2, 29.00,
    'À commander au minimum 3 jours avant la prestation. Conserver au frais et réchauffer avant dégustation.',
    3, 15, 3, 1),
(4, 'Menu Végétarien du Marché',
    'Une cuisine de saison, colorée et sans viande ni poisson, idéale pour tous les convives.',
    4, 27.00,
    'À commander au minimum 5 jours avant la prestation. Conserver au frais (entre 0 et 4 °C).',
    5, 12, 3, 2),
(5, 'Menu Vegan Fraîcheur',
    'Un menu 100 % végétal, sans produit d''origine animale, frais et gourmand.',
    4, 28.00,
    'À commander au minimum 5 jours avant la prestation. Conserver au frais et consommer sous 24 h.',
    5, 5, 3, 3),
(6, 'Menu Réception & Évènement',
    'Pour vos mariages, anniversaires et séminaires : un menu raffiné pensé pour les grandes tablées. Vaisselle et matériel de service prêtés.',
    20, 48.00,
    'À commander au minimum 1 mois avant la prestation. Le matériel prêté doit être restitué sous 10 jours ouvrés, sinon 600 € de frais seront facturés (voir CGV).',
    30, 3, 4, 1);

-- Galerie d'images
INSERT INTO menu_image (menu_id, chemin, texte_alternatif, ordre) VALUES
(1, 'assets/images/menus/noel-1.jpg', 'Table de Noël dressée avec foie gras et volaille', 1),
(1, 'assets/images/menus/noel-2.jpg', 'Bûche de Noël au chocolat', 2),
(2, 'assets/images/menus/paques-1.jpg', 'Gigot d''agneau et légumes primeurs', 1),
(2, 'assets/images/menus/paques-2.jpg', 'Dessert de Pâques au chocolat', 2),
(3, 'assets/images/menus/classique-1.jpg', 'Entrecôte à la bordelaise', 1),
(3, 'assets/images/menus/classique-2.jpg', 'Assiette de cannelés bordelais', 2),
(4, 'assets/images/menus/vegetarien-1.jpg', 'Assiette de légumes de saison', 1),
(5, 'assets/images/menus/vegan-1.jpg', 'Bowl végétal coloré', 1),
(6, 'assets/images/menus/evenement-1.jpg', 'Buffet de réception dressé', 1),
(6, 'assets/images/menus/evenement-2.jpg', 'Pièce montée de mariage', 2);

-- Plats
INSERT INTO plat (plat_id, titre_plat, type_plat, photo) VALUES
-- Entrées
(1,  'Foie gras mi-cuit et chutney de figues', 'entree',  'assets/images/plats/foie-gras.jpg'),
(2,  'Velouté de potimarron',                  'entree',  'assets/images/plats/veloute.jpg'),
(3,  'Huîtres du bassin d''Arcachon',          'entree',  'assets/images/plats/huitres.jpg'),
(4,  'Salade de chèvre chaud au miel',         'entree',  'assets/images/plats/chevre.jpg'),
(5,  'Tartare de légumes et houmous',          'entree',  'assets/images/plats/tartare-legumes.jpg'),
-- Plats
(6,  'Chapon farci aux marrons',               'plat',    'assets/images/plats/chapon.jpg'),
(7,  'Gigot d''agneau de lait rôti',           'plat',    'assets/images/plats/agneau.jpg'),
(8,  'Entrecôte à la bordelaise',              'plat',    'assets/images/plats/entrecote.jpg'),
(9,  'Gratin dauphinois',                      'plat',    'assets/images/plats/gratin.jpg'),
(10, 'Risotto aux champignons',                'plat',    'assets/images/plats/risotto.jpg'),
(11, 'Curry de légumes au lait de coco',       'plat',    'assets/images/plats/curry.jpg'),
(12, 'Filet de bar au beurre blanc',           'plat',    'assets/images/plats/bar.jpg'),
-- Desserts
(13, 'Bûche de Noël chocolat-praliné',         'dessert', 'assets/images/plats/buche.jpg'),
(14, 'Nid de Pâques au chocolat',              'dessert', 'assets/images/plats/nid-paques.jpg'),
(15, 'Cannelés bordelais',                     'dessert', 'assets/images/plats/canneles.jpg'),
(16, 'Salade de fruits frais',                 'dessert', 'assets/images/plats/fruits.jpg'),
(17, 'Tarte aux pommes fine',                  'dessert', 'assets/images/plats/tarte.jpg');

-- Composition des menus (un plat peut être dans plusieurs menus)
INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1), (1, 3), (1, 6), (1, 9), (1, 13),
(2, 4), (2, 7), (2, 9), (2, 14),
(3, 2), (3, 8), (3, 9), (3, 15),
(4, 4), (4, 10), (4, 17),
(5, 5), (5, 11), (5, 16),
(6, 1), (6, 3), (6, 12), (6, 6), (6, 15), (6, 17);

-- Allergènes des plats
INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(1, 1), (1, 12),
(2, 7), (2, 9),
(3, 14),
(4, 1), (4, 7), (4, 8),
(5, 11),
(6, 1), (6, 3), (6, 8),
(7, 12),
(8, 7), (8, 12),
(9, 7),
(10, 7), (10, 12),
(11, 9),
(12, 4), (12, 7),
(13, 1), (13, 3), (13, 7), (13, 8),
(14, 1), (14, 3), (14, 6), (14, 7),
(15, 1), (15, 3), (15, 7),
(17, 1), (17, 3), (17, 7);

-- Horaires (du lundi au dimanche)
INSERT INTO horaire (jour, ordre, heure_ouverture, heure_fermeture) VALUES
('Lundi',    1, '09:00:00', '18:00:00'),
('Mardi',    2, '09:00:00', '18:00:00'),
('Mercredi', 3, '09:00:00', '18:00:00'),
('Jeudi',    4, '09:00:00', '18:00:00'),
('Vendredi', 5, '09:00:00', '20:00:00'),
('Samedi',   6, '10:00:00', '20:00:00'),
('Dimanche', 7, NULL, NULL);

-- Commandes de démonstration
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison,
                      adresse_prestation, ville_prestation, distance_km, nombre_personne, prix_menu, remise, prix_livraison, prix_total,
                      statut, pret_materiel, restitution_materiel) VALUES
(1, 'VG-2026-0001', 3, 3, '2026-09-10 14:20:00', '2026-09-20', '12:00:00',
    '45 cours de l''Intendance', 'Bordeaux', 0, 4, 116.00, 0, 0, 116.00, 'terminee', FALSE, FALSE),
(2, 'VG-2026-0002', 3, 2, '2026-10-01 10:05:00', '2026-10-12', '19:30:00',
    '8 avenue de la Libération', 'Mérignac', 9.50, 9, 324.00, 32.40, 10.61, 302.21, 'terminee', FALSE, FALSE),
(3, 'VG-2026-0003', 3, 1, '2026-10-28 18:45:00', '2026-12-24', '18:00:00',
    '45 cours de l''Intendance', 'Bordeaux', 0, 6, 252.00, 0, 0, 252.00, 'en_attente', FALSE, FALSE);

-- Historique des statuts
INSERT INTO commande_suivi (commande_id, statut, date_modification) VALUES
(1, 'en_attente',         '2026-09-10 14:20:00'),
(1, 'accepte',            '2026-09-11 09:15:00'),
(1, 'en_preparation',     '2026-09-20 08:00:00'),
(1, 'en_cours_livraison', '2026-09-20 11:20:00'),
(1, 'livre',              '2026-09-20 11:55:00'),
(1, 'terminee',           '2026-09-20 12:00:00'),
(2, 'en_attente',         '2026-10-01 10:05:00'),
(2, 'accepte',            '2026-10-01 16:30:00'),
(2, 'en_preparation',     '2026-10-12 15:00:00'),
(2, 'en_cours_livraison', '2026-10-12 18:40:00'),
(2, 'livre',              '2026-10-12 19:25:00'),
(2, 'terminee',           '2026-10-12 19:30:00'),
(3, 'en_attente',         '2026-10-28 18:45:00');

-- Commandes historiques supplémentaires (statistiques de démonstration)
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison,
                      adresse_prestation, ville_prestation, distance_km, nombre_personne, prix_menu, remise, prix_livraison, prix_total,
                      statut, pret_materiel, restitution_materiel) VALUES
(4, 'VG-2026-0004', 3, 3, '2026-06-03 09:12:00', '2026-06-08', '12:30:00',
    '3 place de la Bourse', 'Bordeaux', 0.00, 2, 58.00, 0.00, 0.00, 58.00, 'terminee', FALSE, FALSE),
(5, 'VG-2026-0005', 3, 6, '2026-06-05 15:40:00', '2026-07-11', '18:00:00',
    '15 avenue Thiers', 'Bordeaux', 0.00, 25, 1200.00, 120.00, 0.00, 1080.00, 'terminee', TRUE, TRUE),
(6, 'VG-2026-0006', 3, 4, '2026-06-18 11:05:00', '2026-06-25', '19:30:00',
    '22 rue Judaïque', 'Bordeaux', 0.00, 6, 162.00, 0.00, 0.00, 162.00, 'terminee', FALSE, FALSE),
(7, 'VG-2026-0007', 3, 3, '2026-07-02 17:22:00', '2026-07-07', '12:00:00',
    '5 allée des Pins', 'Pessac', 7.40, 8, 232.00, 23.20, 9.37, 218.17, 'terminee', FALSE, FALSE),
(8, 'VG-2026-0008', 3, 5, '2026-07-09 10:48:00', '2026-07-16', '20:00:00',
    '40 quai des Chartrons', 'Bordeaux', 0.00, 4, 112.00, 0.00, 0.00, 112.00, 'terminee', FALSE, FALSE),
(9, 'VG-2026-0009', 3, 3, '2026-07-21 08:30:00', '2026-07-26', '13:00:00',
    '9 rue Sainte-Catherine', 'Bordeaux', 0.00, 4, 116.00, 0.00, 0.00, 116.00, 'terminee', FALSE, FALSE),
(10, 'VG-2026-0010', 3, 6, '2026-07-28 14:15:00', '2026-09-05', '17:30:00',
    '2 chemin du Lac', 'Talence', 4.10, 40, 1920.00, 192.00, 7.42, 1735.42, 'terminee', TRUE, TRUE),
(11, 'VG-2026-0011', 3, 2, '2026-08-04 16:02:00', '2026-08-15', '12:30:00',
    '18 cours Victor Hugo', 'Bordeaux', 0.00, 6, 216.00, 0.00, 0.00, 216.00, 'terminee', FALSE, FALSE),
(12, 'VG-2026-0012', 3, 4, '2026-08-20 12:40:00', '2026-08-29', '19:00:00',
    '7 rue du Port', 'Bègles', 3.60, 9, 243.00, 24.30, 7.12, 225.82, 'terminee', FALSE, FALSE),
(13, 'VG-2026-0013', 3, 3, '2026-09-01 09:55:00', '2026-09-06', '12:15:00',
    '31 rue Fondaudège', 'Bordeaux', 0.00, 3, 87.00, 0.00, 0.00, 87.00, 'terminee', FALSE, FALSE),
(14, 'VG-2026-0014', 3, 5, '2026-09-15 18:20:00', '2026-09-24', '20:30:00',
    '12 rue Notre-Dame', 'Bordeaux', 0.00, 5, 140.00, 0.00, 0.00, 140.00, 'terminee', FALSE, FALSE),
(15, 'VG-2026-0015', 3, 6, '2026-10-06 11:11:00', '2026-11-14', '18:30:00',
    '4 avenue de Paris', 'Mérignac', 6.20, 22, 1056.00, 0.00, 8.66, 1064.66, 'accepte', TRUE, FALSE);

INSERT INTO commande_suivi (commande_id, statut, date_modification) VALUES
(4, 'en_attente', '2026-06-03 09:12:00'),
(4, 'accepte', '2026-06-03 13:12:00'),
(4, 'en_preparation', '2026-06-08 07:30:00'),
(4, 'en_cours_livraison', '2026-06-08 11:30:00'),
(4, 'livre', '2026-06-08 12:25:00'),
(4, 'terminee', '2026-06-08 13:00:00'),
(5, 'en_attente', '2026-06-05 15:40:00'),
(5, 'accepte', '2026-06-05 19:40:00'),
(5, 'en_preparation', '2026-07-11 13:00:00'),
(5, 'en_cours_livraison', '2026-07-11 17:00:00'),
(5, 'livre', '2026-07-11 17:55:00'),
(5, 'attente_retour_materiel', '2026-07-11 18:30:00'),
(5, 'terminee', '2026-07-14 18:00:00'),
(6, 'en_attente', '2026-06-18 11:05:00'),
(6, 'accepte', '2026-06-18 15:05:00'),
(6, 'en_preparation', '2026-06-25 14:30:00'),
(6, 'en_cours_livraison', '2026-06-25 18:30:00'),
(6, 'livre', '2026-06-25 19:25:00'),
(6, 'terminee', '2026-06-25 20:00:00'),
(7, 'en_attente', '2026-07-02 17:22:00'),
(7, 'accepte', '2026-07-02 21:22:00'),
(7, 'en_preparation', '2026-07-07 07:00:00'),
(7, 'en_cours_livraison', '2026-07-07 11:00:00'),
(7, 'livre', '2026-07-07 11:55:00'),
(7, 'terminee', '2026-07-07 12:30:00'),
(8, 'en_attente', '2026-07-09 10:48:00'),
(8, 'accepte', '2026-07-09 14:48:00'),
(8, 'en_preparation', '2026-07-16 15:00:00'),
(8, 'en_cours_livraison', '2026-07-16 19:00:00'),
(8, 'livre', '2026-07-16 19:55:00'),
(8, 'terminee', '2026-07-16 20:30:00'),
(9, 'en_attente', '2026-07-21 08:30:00'),
(9, 'accepte', '2026-07-21 12:30:00'),
(9, 'en_preparation', '2026-07-26 08:00:00'),
(9, 'en_cours_livraison', '2026-07-26 12:00:00'),
(9, 'livre', '2026-07-26 12:55:00'),
(9, 'terminee', '2026-07-26 13:30:00'),
(10, 'en_attente', '2026-07-28 14:15:00'),
(10, 'accepte', '2026-07-28 18:15:00'),
(10, 'en_preparation', '2026-09-05 12:30:00'),
(10, 'en_cours_livraison', '2026-09-05 16:30:00'),
(10, 'livre', '2026-09-05 17:25:00'),
(10, 'attente_retour_materiel', '2026-09-05 18:00:00'),
(10, 'terminee', '2026-09-08 17:30:00'),
(11, 'en_attente', '2026-08-04 16:02:00'),
(11, 'accepte', '2026-08-04 20:02:00'),
(11, 'en_preparation', '2026-08-15 07:30:00'),
(11, 'en_cours_livraison', '2026-08-15 11:30:00'),
(11, 'livre', '2026-08-15 12:25:00'),
(11, 'terminee', '2026-08-15 13:00:00'),
(12, 'en_attente', '2026-08-20 12:40:00'),
(12, 'accepte', '2026-08-20 16:40:00'),
(12, 'en_preparation', '2026-08-29 14:00:00'),
(12, 'en_cours_livraison', '2026-08-29 18:00:00'),
(12, 'livre', '2026-08-29 18:55:00'),
(12, 'terminee', '2026-08-29 19:30:00'),
(13, 'en_attente', '2026-09-01 09:55:00'),
(13, 'accepte', '2026-09-01 13:55:00'),
(13, 'en_preparation', '2026-09-06 07:15:00'),
(13, 'en_cours_livraison', '2026-09-06 11:15:00'),
(13, 'livre', '2026-09-06 12:10:00'),
(13, 'terminee', '2026-09-06 12:45:00'),
(14, 'en_attente', '2026-09-15 18:20:00'),
(14, 'accepte', '2026-09-15 22:20:00'),
(14, 'en_preparation', '2026-09-24 15:30:00'),
(14, 'en_cours_livraison', '2026-09-24 19:30:00'),
(14, 'livre', '2026-09-24 20:25:00'),
(14, 'terminee', '2026-09-24 21:00:00'),
(15, 'en_attente', '2026-10-06 11:11:00'),
(15, 'accepte', '2026-10-06 16:11:00');

-- Avis clients
INSERT INTO avis (commande_id, utilisateur_id, note, description, statut, date_creation) VALUES
(1, 3, 5, 'Entrecôte parfaite et cannelés délicieux, livraison à l''heure. Je recommande !', 'valide',     '2026-09-21 10:00:00'),
(2, 3, 4, 'Très bon agneau, un petit retard mais l''équipe a prévenu. Merci Julie et José.', 'en_attente', '2026-10-13 09:30:00');
