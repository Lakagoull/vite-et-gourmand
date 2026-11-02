<div class="container pb-5 page-etroite">
    <h1 class="pt-4">Créer un compte</h1>
    <p>Votre compte vous permet de commander nos menus, de suivre vos commandes et de donner votre avis.</p>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <p class="mb-1"><strong>Le formulaire contient <?= count($erreurs) ?> erreur<?= count($erreurs) > 1 ? 's' : '' ?> :</strong></p>
            <ul class="mb-0">
                <?php foreach ($erreurs as $champ => $message): ?>
                    <li><a href="#<?= e($champ) ?>"><?= e($message) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/inscription" class="carte formulaire" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <p class="text-aide">Tous les champs sont obligatoires.</p>

        <fieldset class="mb-2">
            <legend class="h3">Vos coordonnées</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'prenom') ?>" id="prenom" name="prenom"
                           value="<?= old('prenom') ?>" required maxlength="50" autocomplete="given-name">
                    <?= msgErreur($erreurs, 'prenom') ?>
                </div>
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'nom') ?>" id="nom" name="nom"
                           value="<?= old('nom') ?>" required maxlength="50" autocomplete="family-name">
                    <?= msgErreur($erreurs, 'nom') ?>
                </div>
                <div class="col-md-6">
                    <label for="telephone" class="form-label">Numéro de mobile <span class="text-aide">(ex. : 06 12 34 56 78)</span></label>
                    <input type="tel" class="form-control<?= attrErreur($erreurs, 'telephone') ?>" id="telephone" name="telephone"
                           value="<?= old('telephone') ?>" required autocomplete="tel">
                    <?= msgErreur($erreurs, 'telephone') ?>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Adresse e-mail <span class="text-aide">(identifiant)</span></label>
                    <input type="email" class="form-control<?= attrErreur($erreurs, 'email') ?>" id="email" name="email"
                           value="<?= old('email') ?>" required maxlength="180" autocomplete="email">
                    <?= msgErreur($erreurs, 'email') ?>
                </div>
                <div class="col-md-8">
                    <label for="adresse_postale" class="form-label">Adresse postale <span class="text-aide">(numéro, rue, code postal)</span></label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'adresse_postale') ?>" id="adresse_postale" name="adresse_postale"
                           value="<?= old('adresse_postale') ?>" required maxlength="255" autocomplete="street-address">
                    <?= msgErreur($erreurs, 'adresse_postale') ?>
                </div>
                <div class="col-md-4">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'ville') ?>" id="ville" name="ville"
                           value="<?= old('ville') ?>" required maxlength="80" autocomplete="address-level2">
                    <?= msgErreur($erreurs, 'ville') ?>
                </div>
            </div>
        </fieldset>

        <fieldset class="mt-4">
            <legend class="h3">Votre mot de passe</legend>
            <?php partial('champs-mot-de-passe', ['erreurs' => $erreurs]); ?>
        </fieldset>

        <div class="form-check my-4">
            <input class="form-check-input<?= attrErreur($erreurs, 'cgu') ?>" type="checkbox" id="cgu" name="cgu" value="1" required>
            <label class="form-check-label" for="cgu">
                J'accepte les <a href="/cgv" target="_blank" rel="noopener">conditions générales de vente</a>
                et la <a href="/confidentialite" target="_blank" rel="noopener">politique de confidentialité</a>
                <span class="visually-hidden">(s'ouvrent dans un nouvel onglet)</span>
            </label>
            <?= msgErreur($erreurs, 'cgu') ?>
        </div>

        <button type="submit" class="btn btn-primaire w-100">Créer mon compte</button>
        <p class="text-center mt-3 mb-0">Déjà un compte ? <a href="/connexion">Se connecter</a></p>
    </form>
</div>
