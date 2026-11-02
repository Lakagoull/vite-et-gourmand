<div class="container pb-5 page-etroite">
    <h1 class="pt-4">Choisir un nouveau mot de passe</h1>

    <form method="post" action="/reinitialiser-mot-de-passe" class="carte formulaire" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="token" value="<?= e($jeton) ?>">
        <?php partial('champs-mot-de-passe', ['erreurs' => $erreurs]); ?>
        <button type="submit" class="btn btn-primaire w-100 mt-4">Enregistrer le nouveau mot de passe</button>
    </form>
</div>
