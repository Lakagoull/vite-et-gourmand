<div class="container pb-5 page-formulaire">
    <h1 class="pt-4">Connexion</h1>

    <?php if ($erreur): ?>
        <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
    <?php endif; ?>

    <form method="post" action="/connexion" class="carte formulaire">
        <?= csrfField() ?>
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>"
                   required autocomplete="username" autofocus>
        </div>
        <div class="mb-2">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
        </div>
        <p class="mb-4"><a href="/mot-de-passe-oublie">Mot de passe oublié ?</a></p>

        <button type="submit" class="btn btn-primaire w-100">Se connecter</button>
    </form>

    <div class="carte mt-4 text-center">
        <h2 class="h3">Pas encore de compte ?</h2>
        <p>Créez votre compte en une minute pour commander nos menus et suivre vos commandes.</p>
        <a href="/inscription<?= $redirect ? '?redirect=' . urlencode($redirect) : '' ?>" class="btn btn-secondaire">Créer un compte</a>
    </div>
</div>
