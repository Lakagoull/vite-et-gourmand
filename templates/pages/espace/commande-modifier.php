<div class="container py-4 pb-5">
    <nav aria-label="Fil d'Ariane" class="fil pt-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/espace">Mon espace</a></li>
            <li class="breadcrumb-item"><a href="/espace/commande?id=<?= (int) $c['commande_id'] ?>">Commande <?= e($c['numero_commande']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Modifier</li>
        </ol>
    </nav>
    <h1>Modifier la commande <?= e($c['numero_commande']) ?></h1>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/espace/commande/modifier" id="form-commande" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
        <!-- Le menu n'est pas modifiable : simple information pour le calcul du prix -->
        <input type="hidden" id="menu_id" value="<?= (int) $menu['menu_id'] ?>"
               data-min="<?= (int) $menu['nombre_personne_minimum'] ?>" data-delai="<?= (int) $menu['delai_commande_jours'] ?>">
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="carte mb-4">
                    <h2 class="h3">Menu</h2>
                    <p class="mb-0"><strong><?= e($menu['titre']) ?></strong> <span class="text-aide">— le menu ne peut pas être changé. Pour un autre menu, annulez puis passez une nouvelle commande.</span></p>
                </section>
                <section class="carte mb-4">
                    <h2 class="h3">La prestation</h2>
                    <?php partial('champs-prestation', ['erreurs' => $erreurs, 'valeurs' => $valeurs, 'delai' => $menu['delai_commande_jours']]); ?>
                    <div class="mt-3 champ-court">
                        <label for="nombre_personne" class="form-label">Nombre de personnes</label>
                        <input type="number" class="form-control<?= attrErreur($erreurs, 'nombre_personne') ?>" id="nombre_personne" name="nombre_personne"
                               value="<?= e($valeurs['nombre_personne'] ?? '') ?>" min="<?= (int) $menu['nombre_personne_minimum'] ?>" max="500" required
                               aria-describedby="aide-nb-personnes" data-devis>
                        <small id="aide-nb-personnes" class="text-aide">Minimum : <span id="nb-min"><?= (int) $menu['nombre_personne_minimum'] ?></span> personnes</small>
                        <?= msgErreur($erreurs, 'nombre_personne') ?>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <div class="colle">
                    <?php partial('recap-prix'); ?>
                    <button type="submit" class="btn btn-primaire btn-lg w-100 mt-3">Enregistrer les modifications</button>
                    <a href="/espace/commande?id=<?= (int) $c['commande_id'] ?>" class="btn btn-secondaire w-100 mt-2">Annuler les changements</a>
                </div>
            </div>
        </div>
    </form>
</div>
