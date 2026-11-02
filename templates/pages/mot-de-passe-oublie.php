<div class="container pb-5 page-formulaire">
    <h1 class="pt-4">Mot de passe oublié</h1>
    <p>Indiquez l'adresse e-mail de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>

    <form method="post" action="/mot-de-passe-oublie" class="carte formulaire">
        <?= csrfField() ?>
        <div class="mb-4">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" class="form-control" id="email" name="email" required autocomplete="email" autofocus>
        </div>
        <button type="submit" class="btn btn-primaire w-100">Recevoir le lien</button>
        <p class="text-center mt-3 mb-0"><a href="/connexion">Retour à la connexion</a></p>
    </form>
</div>
