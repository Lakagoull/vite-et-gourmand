<?php $v = fn (string $k) => e($client[$k] ?? ''); ?>
<div class="container py-4 pb-5">
    <h1>Mon espace</h1>
    <?php partial('espace-nav', ['actif' => 'profil']); ?>

    <h2 class="mt-4">Mes informations</h2>
    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/espace/profil" class="carte formulaire page-etroite" novalidate>
        <?= csrfField() ?>
        <fieldset>
            <legend class="h3">Coordonnées</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'prenom') ?>" id="prenom" name="prenom" value="<?= $v('prenom') ?>" required maxlength="50" autocomplete="given-name">
                    <?= msgErreur($erreurs, 'prenom') ?>
                </div>
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'nom') ?>" id="nom" name="nom" value="<?= $v('nom') ?>" required maxlength="50" autocomplete="family-name">
                    <?= msgErreur($erreurs, 'nom') ?>
                </div>
                <div class="col-md-6">
                    <label for="telephone" class="form-label">Numéro de mobile</label>
                    <input type="tel" class="form-control<?= attrErreur($erreurs, 'telephone') ?>" id="telephone" name="telephone" value="<?= $v('telephone') ?>" required autocomplete="tel">
                    <?= msgErreur($erreurs, 'telephone') ?>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <input type="email" class="form-control<?= attrErreur($erreurs, 'email') ?>" id="email" name="email" value="<?= $v('email') ?>" required autocomplete="email">
                    <?= msgErreur($erreurs, 'email') ?>
                </div>
                <div class="col-md-8">
                    <label for="adresse_postale" class="form-label">Adresse postale</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'adresse_postale') ?>" id="adresse_postale" name="adresse_postale" value="<?= $v('adresse_postale') ?>" required autocomplete="street-address">
                    <?= msgErreur($erreurs, 'adresse_postale') ?>
                </div>
                <div class="col-md-4">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'ville') ?>" id="ville" name="ville" value="<?= $v('ville') ?>" required autocomplete="address-level2">
                    <?= msgErreur($erreurs, 'ville') ?>
                </div>
            </div>
        </fieldset>

        <fieldset class="mt-4">
            <legend class="h3">Changer de mot de passe <span class="text-aide fs-6">(facultatif)</span></legend>
            <div class="mb-3 champ-court">
                <label for="ancien_password" class="form-label">Mot de passe actuel</label>
                <input type="password" class="form-control<?= attrErreur($erreurs, 'ancien_password') ?>" id="ancien_password" name="ancien_password" autocomplete="current-password">
                <?= msgErreur($erreurs, 'ancien_password') ?>
            </div>
            <?php partial('champs-mot-de-passe', ['erreurs' => $erreurs]); ?>
        </fieldset>

        <button type="submit" class="btn btn-primaire mt-4">Enregistrer</button>
    </form>
</div>
