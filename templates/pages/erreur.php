<?php $titrePage = $titre ?? 'Erreur'; ?>
<div class="container py-5 text-center page-etroite">
    <h1><?= e($titre ?? 'Erreur') ?></h1>
    <p class="lead"><?= e($message ?? '') ?></p>
    <a href="/" class="btn btn-primaire">Retour à l'accueil</a>
    <a href="/menus" class="btn btn-secondaire ms-2">Voir nos menus</a>
</div>
