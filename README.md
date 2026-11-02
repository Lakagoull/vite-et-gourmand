# Vite & Gourmand

Application web de commande de menus pour le traiteur bordelais « Vite & Gourmand » (ECF Développeur Web et Web Mobile).

**Stack :** HTML5 / CSS (Bootstrap 5) / JavaScript — PHP 8 (PDO, sans framework) — MySQL / MariaDB — MongoDB — PHPMailer.

## Prérequis

- PHP 8.0 ou plus, avec les extensions `pdo_mysql`, `mongodb`, `mbstring`, `openssl`, `gd`
- [Composer](https://getcomposer.org/)
- MySQL 8 ou MariaDB 10.4+ (par exemple via XAMPP)
- MongoDB 6+ et `mongosh`
- Git

## Installation en local

```bash
# 1. Récupérer le projet
git clone https://github.com/Lakagoull/vite-et-gourmand.git
cd vite-et-gourmand

# 2. Installer les dépendances PHP
composer install

# 3. Créer le fichier de configuration
cp .env.example .env
#    puis adapter les accès à la base (DB_USER, DB_PASSWORD...)

# 4. Créer la base relationnelle et insérer les données de démonstration
mysql -u root -p < database/schema.sql
mysql -u root -p < database/data.sql

# 5. Initialiser la base NoSQL puis y copier les commandes (statistiques)
mongosh "mongodb://127.0.0.1:27017" database/mongo-init.js
php database/sync-mongo.php

# 6. Lancer le serveur de développement
php -S localhost:8000 -t public public/index.php
```

Le site est alors disponible sur **http://localhost:8000**.

> En local, si `SMTP_HOST` est vide dans `.env`, les e-mails ne sont pas envoyés : ils sont enregistrés dans `storage/mails/` (fichiers `.eml` lisibles avec un client mail).

## Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | jose@vite-gourmand.fr | Jose@Admin2026 |
| Employée | julie@vite-gourmand.fr | Julie@Employe2026 |
| Client | client.demo@example.com | Client@Demo2026 |

## Organisation du code

```
database/      Scripts SQL (création + données) et initialisation MongoDB
docs/          Charte graphique, maquettes et documentations (PDF)
public/        Racine web : index.php (point d'entrée unique) et assets (CSS, JS, images)
src/           Code PHP : Controllers, Models, Services, routeur, connexion PDO
templates/     Gabarits HTML (layout, pages, éléments réutilisables)
storage/       Fichiers générés (mails en développement)
```

## Branches Git

- `main` : version stable, déployée
- `develop` : intégration des fonctionnalités testées
- `feature/...` : une branche par fonctionnalité, fusionnée dans `develop` après test
