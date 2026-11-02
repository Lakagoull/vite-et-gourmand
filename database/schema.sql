-- =====================================================================
-- Vite & Gourmand - Création de la base de données relationnelle
-- SGBD : MySQL 8 / MariaDB 10.4+
-- Basé sur le MCD de l'annexe 1, complété selon le cahier des charges.
-- =====================================================================

DROP DATABASE IF EXISTS vite_gourmand;
CREATE DATABASE vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vite_gourmand;

-- ---------------------------------------------------------------------
-- Rôles : utilisateur, employe, administrateur
-- ---------------------------------------------------------------------
CREATE TABLE role (
    role_id  INT          NOT NULL AUTO_INCREMENT,
    libelle  VARCHAR(50)  NOT NULL,
    PRIMARY KEY (role_id),
    UNIQUE KEY uk_role_libelle (libelle)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Utilisateurs (clients, employés, administrateur)
-- Le mot de passe est stocké haché (bcrypt via password_hash en PHP).
-- "actif" permet de rendre un compte employé inutilisable.
-- ---------------------------------------------------------------------
CREATE TABLE utilisateur (
    utilisateur_id  INT           NOT NULL AUTO_INCREMENT,
    email           VARCHAR(180)  NOT NULL,
    password        VARCHAR(255)  NOT NULL,
    nom             VARCHAR(50)   NOT NULL,
    prenom          VARCHAR(50)   NOT NULL,
    telephone       VARCHAR(20)   NULL,
    ville           VARCHAR(80)   NULL,
    pays            VARCHAR(50)   NULL DEFAULT 'France',
    adresse_postale VARCHAR(255)  NULL,
    role_id         INT           NOT NULL,
    actif           BOOLEAN       NOT NULL DEFAULT TRUE,
    date_creation   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id),
    UNIQUE KEY uk_utilisateur_email (email),
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role (role_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Réinitialisation de mot de passe (lien envoyé par mail)
-- On ne stocke que le hash du jeton, jamais le jeton en clair.
-- ---------------------------------------------------------------------
CREATE TABLE reinitialisation_mdp (
    reinitialisation_id INT          NOT NULL AUTO_INCREMENT,
    utilisateur_id      INT          NOT NULL,
    token_hash          CHAR(64)     NOT NULL,
    date_expiration     DATETIME     NOT NULL,
    utilise             BOOLEAN      NOT NULL DEFAULT FALSE,
    PRIMARY KEY (reinitialisation_id),
    UNIQUE KEY uk_reinit_token (token_hash),
    CONSTRAINT fk_reinit_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Référentiels du menu
-- ---------------------------------------------------------------------
CREATE TABLE theme (
    theme_id  INT          NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL,
    PRIMARY KEY (theme_id),
    UNIQUE KEY uk_theme_libelle (libelle)
) ENGINE=InnoDB;

CREATE TABLE regime (
    regime_id  INT          NOT NULL AUTO_INCREMENT,
    libelle    VARCHAR(50)  NOT NULL,
    PRIMARY KEY (regime_id),
    UNIQUE KEY uk_regime_libelle (libelle)
) ENGINE=InnoDB;

CREATE TABLE allergene (
    allergene_id  INT          NOT NULL AUTO_INCREMENT,
    libelle       VARCHAR(50)  NOT NULL,
    PRIMARY KEY (allergene_id),
    UNIQUE KEY uk_allergene_libelle (libelle)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Menus
-- Prix en DECIMAL (et non DOUBLE) pour éviter les erreurs d'arrondi.
-- ---------------------------------------------------------------------
CREATE TABLE menu (
    menu_id                  INT            NOT NULL AUTO_INCREMENT,
    titre                    VARCHAR(100)   NOT NULL,
    description              TEXT           NOT NULL,
    nombre_personne_minimum  INT            NOT NULL,
    prix_par_personne        DECIMAL(8,2)   NOT NULL,
    conditions               TEXT           NULL,
    quantite_restante        INT            NOT NULL DEFAULT 0,
    theme_id                 INT            NOT NULL,
    regime_id                INT            NOT NULL,
    PRIMARY KEY (menu_id),
    CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme (theme_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime (regime_id),
    CONSTRAINT ck_menu_nb_personne CHECK (nombre_personne_minimum >= 1),
    CONSTRAINT ck_menu_prix        CHECK (prix_par_personne >= 0),
    CONSTRAINT ck_menu_stock       CHECK (quantite_restante >= 0)
) ENGINE=InnoDB;

-- Galerie d'images d'un menu (on stocke le chemin du fichier)
CREATE TABLE menu_image (
    image_id          INT           NOT NULL AUTO_INCREMENT,
    menu_id           INT           NOT NULL,
    chemin            VARCHAR(255)  NOT NULL,
    texte_alternatif  VARCHAR(255)  NOT NULL,
    ordre             INT           NOT NULL DEFAULT 0,
    PRIMARY KEY (image_id),
    CONSTRAINT fk_image_menu FOREIGN KEY (menu_id) REFERENCES menu (menu_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Plats (entrée, plat, dessert) - un plat peut être dans plusieurs menus
-- ---------------------------------------------------------------------
CREATE TABLE plat (
    plat_id     INT           NOT NULL AUTO_INCREMENT,
    titre_plat  VARCHAR(100)  NOT NULL,
    type_plat   ENUM('entree','plat','dessert') NOT NULL,
    photo       VARCHAR(255)  NULL,
    PRIMARY KEY (plat_id)
) ENGINE=InnoDB;

-- Association "propose" : menu <-> plat (n,n)
CREATE TABLE menu_plat (
    menu_id  INT NOT NULL,
    plat_id  INT NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id) REFERENCES menu (menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id) REFERENCES plat (plat_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Association "contient" : plat <-> allergene (n,n)
CREATE TABLE plat_allergene (
    plat_id       INT NOT NULL,
    allergene_id  INT NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    CONSTRAINT fk_pa_plat      FOREIGN KEY (plat_id)      REFERENCES plat (plat_id) ON DELETE CASCADE,
    CONSTRAINT fk_pa_allergene FOREIGN KEY (allergene_id) REFERENCES allergene (allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Horaires (affichés dans le pied de page, modifiables par les employés)
-- heure_ouverture NULL = fermé ce jour-là
-- ---------------------------------------------------------------------
CREATE TABLE horaire (
    horaire_id       INT          NOT NULL AUTO_INCREMENT,
    jour             VARCHAR(10)  NOT NULL,
    ordre            TINYINT      NOT NULL,
    heure_ouverture  TIME         NULL,
    heure_fermeture  TIME         NULL,
    PRIMARY KEY (horaire_id),
    UNIQUE KEY uk_horaire_jour (jour)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Commandes
-- Les prix sont figés au moment de la commande (historique fiable).
-- ---------------------------------------------------------------------
CREATE TABLE commande (
    commande_id              INT            NOT NULL AUTO_INCREMENT,
    numero_commande          VARCHAR(20)    NOT NULL,
    utilisateur_id           INT            NOT NULL,
    menu_id                  INT            NOT NULL,
    date_commande            DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation          DATE           NOT NULL,
    heure_livraison          TIME           NOT NULL,
    adresse_prestation       VARCHAR(255)   NOT NULL,
    ville_prestation         VARCHAR(80)    NOT NULL,
    distance_km              DECIMAL(6,2)   NOT NULL DEFAULT 0,
    nombre_personne          INT            NOT NULL,
    prix_menu                DECIMAL(10,2)  NOT NULL,
    remise                   DECIMAL(10,2)  NOT NULL DEFAULT 0,
    prix_livraison           DECIMAL(10,2)  NOT NULL DEFAULT 0,
    prix_total               DECIMAL(10,2)  NOT NULL,
    statut                   ENUM('en_attente','accepte','en_preparation','en_cours_livraison',
                                  'livre','attente_retour_materiel','terminee','annulee')
                             NOT NULL DEFAULT 'en_attente',
    pret_materiel            BOOLEAN        NOT NULL DEFAULT FALSE,
    restitution_materiel     BOOLEAN        NOT NULL DEFAULT FALSE,
    motif_annulation         TEXT           NULL,
    mode_contact_annulation  ENUM('telephone','mail') NULL,
    PRIMARY KEY (commande_id),
    UNIQUE KEY uk_commande_numero (numero_commande),
    KEY idx_commande_statut (statut),
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id),
    CONSTRAINT fk_commande_menu        FOREIGN KEY (menu_id)        REFERENCES menu (menu_id),
    CONSTRAINT ck_commande_nb_personne CHECK (nombre_personne >= 1)
) ENGINE=InnoDB;

-- Historique des statuts (suivi de commande avec date et heure)
CREATE TABLE commande_suivi (
    suivi_id           INT       NOT NULL AUTO_INCREMENT,
    commande_id        INT       NOT NULL,
    statut             VARCHAR(30) NOT NULL,
    date_modification  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (suivi_id),
    CONSTRAINT fk_suivi_commande FOREIGN KEY (commande_id) REFERENCES commande (commande_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Avis clients (un avis par commande terminée, validé par un employé)
-- ---------------------------------------------------------------------
CREATE TABLE avis (
    avis_id         INT       NOT NULL AUTO_INCREMENT,
    commande_id     INT       NOT NULL,
    utilisateur_id  INT       NOT NULL,
    note            TINYINT   NOT NULL,
    description     TEXT      NOT NULL,
    statut          ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    date_creation   DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (avis_id),
    UNIQUE KEY uk_avis_commande (commande_id),
    CONSTRAINT fk_avis_commande    FOREIGN KEY (commande_id)    REFERENCES commande (commande_id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id),
    CONSTRAINT ck_avis_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB;
