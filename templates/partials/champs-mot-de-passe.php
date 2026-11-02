<div class="row g-3">
    <div class="col-md-6">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control<?= attrErreur($erreurs, 'password') ?>" id="password" name="password"
               required minlength="10" autocomplete="new-password" aria-describedby="regles-mdp">
        <?= msgErreur($erreurs, 'password') ?>
    </div>
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
        <input type="password" class="form-control<?= attrErreur($erreurs, 'password_confirmation') ?>" id="password_confirmation"
               name="password_confirmation" required autocomplete="new-password">
        <?= msgErreur($erreurs, 'password_confirmation') ?>
    </div>
</div>
<div class="regles-mdp mt-3" id="regles-mdp">
    <p class="mb-1">Le mot de passe doit contenir au minimum :</p>
    <ul class="mb-0">
        <li data-regle="longueur">10 caractères</li>
        <li data-regle="majuscule">une lettre majuscule</li>
        <li data-regle="minuscule">une lettre minuscule</li>
        <li data-regle="chiffre">un chiffre</li>
        <li data-regle="special">un caractère spécial (ex. : ! @ # ?)</li>
    </ul>
</div>
<div class="form-check mt-2">
    <input class="form-check-input" type="checkbox" id="afficher-mdp">
    <label class="form-check-label" for="afficher-mdp">Afficher les mots de passe</label>
</div>
