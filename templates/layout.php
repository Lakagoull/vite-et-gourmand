<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titrePage ?? 'Vite & Gourmand') ?> — Vite &amp; Gourmand</title>
    <meta name="description" content="Vite & Gourmand, traiteur à Bordeaux depuis 25 ans : menus de Noël, de Pâques, classiques et évènements, livrés chez vous.">
    <link rel="icon" href="/assets/images/site/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/fonts.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <a class="lien-evitement" href="#contenu">Aller au contenu principal</a>

    <?php partial('header'); ?>

    <main id="contenu" tabindex="-1">
        <?php partial('flash'); ?>
        <?= $contenu ?>
    </main>

    <?php partial('footer'); ?>

    <script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <?php foreach ($scripts ?? [] as $script): ?>
        <script src="/assets/js/<?= e($script) ?>.js"></script>
    <?php endforeach; ?>
</body>
</html>
