<div class="container pb-5 page-etroite">
    <nav aria-label="Fil d'Ariane" class="fil">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact</li>
        </ol>
    </nav>
    <h1>Nous contacter</h1>
    <p>Une question sur un menu, une demande particulière ? Écrivez-nous, nous vous répondrons par e-mail.</p>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger" role="alert" tabindex="-1" id="erreurs-formulaire">
            <p class="mb-1"><strong>Le formulaire contient des erreurs :</strong></p>
            <ul class="mb-0">
                <?php foreach ($erreurs as $champ => $message): ?>
                    <li><?php if ($champ !== 'global'): ?><a href="#<?= e($champ) ?>"><?= e($message) ?></a><?php else: ?><?= e($message) ?><?php endif; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/contact" class="carte formulaire" novalidate>
        <?= csrfField() ?>
        <p class="text-aide">Tous les champs sont obligatoires.</p>

        <div class="mb-3">
            <label for="titre" class="form-label">Titre de votre demande</label>
            <input type="text" class="form-control<?= isset($erreurs['titre']) ? ' is-invalid' : '' ?>" id="titre" name="titre"
                   value="<?= old('titre') ?>" required minlength="3" maxlength="150"
                   <?= isset($erreurs['titre']) ? 'aria-invalid="true" aria-describedby="erreur-titre"' : '' ?>>
            <?php if (isset($erreurs['titre'])): ?><div id="erreur-titre" class="invalid-feedback"><?= e($erreurs['titre']) ?></div><?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Votre message</label>
            <textarea class="form-control<?= isset($erreurs['description']) ? ' is-invalid' : '' ?>" id="description" name="description"
                      rows="6" required minlength="10" maxlength="3000"
                      <?= isset($erreurs['description']) ? 'aria-invalid="true" aria-describedby="erreur-description"' : '' ?>><?= old('description') ?></textarea>
            <?php if (isset($erreurs['description'])): ?><div id="erreur-description" class="invalid-feedback"><?= e($erreurs['description']) ?></div><?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Votre adresse e-mail <span class="text-aide">(pour vous répondre, ex. : nom@domaine.fr)</span></label>
            <input type="email" class="form-control<?= isset($erreurs['email']) ? ' is-invalid' : '' ?>" id="email" name="email"
                   value="<?= old('email') ?>" required autocomplete="email"
                   <?= isset($erreurs['email']) ? 'aria-invalid="true" aria-describedby="erreur-email"' : '' ?>>
            <?php if (isset($erreurs['email'])): ?><div id="erreur-email" class="invalid-feedback"><?= e($erreurs['email']) ?></div><?php endif; ?>
        </div>

        <!-- Champ piège anti-robots, invisible pour les visiteurs -->
        <div class="champ-piege" aria-hidden="true">
            <label for="site_web">Ne pas remplir ce champ</label>
            <input type="text" id="site_web" name="site_web" tabindex="-1" autocomplete="off">
        </div>

        <p class="text-aide">Votre adresse e-mail est uniquement utilisée pour répondre à votre demande. Voir notre <a href="/confidentialite">politique de confidentialité</a>.</p>
        <button type="submit" class="btn btn-primaire">Envoyer ma demande</button>
    </form>
</div>
