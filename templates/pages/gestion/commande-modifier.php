<div class="container py-4 pb-5">
    <?php partial('gestion-nav', ['actif' => 'commandes']); ?>
    <nav aria-label="Fil d'Ariane" class="fil pt-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/employe/commandes">Commandes</a></li>
            <li class="breadcrumb-item"><a href="/employe/commande?id=<?= (int) $c['commande_id'] ?>"><?= e($c['numero_commande']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Modifier</li>
        </ol>
    </nav>
    <h1>Modifier la commande <?= e($c['numero_commande']) ?></h1>
    <p>Client : <strong><?= e($c['prenom'] . ' ' . $c['nom']) ?></strong> — <?= e($c['telephone'] ?? '') ?> — <?= e($c['email']) ?></p>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/employe/commande/modifier" id="form-commande" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
        <input type="hidden" id="menu_id" value="<?= (int) $menu['menu_id'] ?>" data-min="<?= (int) $menu['nombre_personne_minimum'] ?>" data-delai="0">
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="carte mb-4">
                    <h2 class="h3">Contact préalable avec le client</h2>
                    <?php partial('champs-contact-client', ['erreurs' => $erreurs, 'motifLabel' => 'Motif de la modification']); ?>
                </section>
                <section class="carte mb-4">
                    <h2 class="h3">Prestation — <?= e($menu['titre']) ?></h2>
                    <?php partial('champs-prestation', ['erreurs' => $erreurs, 'valeurs' => $valeurs, 'delai' => 0]); ?>
                    <div class="mt-3 champ-court">
                        <label for="nombre_personne" class="form-label">Nombre de personnes</label>
                        <input type="number" class="form-control<?= attrErreur($erreurs, 'nombre_personne') ?>" id="nombre_personne" name="nombre_personne"
                               value="<?= e($valeurs['nombre_personne'] ?? '') ?>" min="<?= (int) $menu['nombre_personne_minimum'] ?>" max="500" required data-devis aria-describedby="aide-nb-personnes">
                        <small id="aide-nb-personnes" class="text-aide">Minimum : <span id="nb-min"><?= (int) $menu['nombre_personne_minimum'] ?></span> personnes</small>
                        <?= msgErreur($erreurs, 'nombre_personne') ?>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <div class="colle">
                    <?php partial('recap-prix'); ?>
                    <button type="submit" class="btn btn-primaire btn-lg w-100 mt-3">Enregistrer et prévenir le client</button>
                    <a href="/employe/commande?id=<?= (int) $c['commande_id'] ?>" class="btn btn-secondaire w-100 mt-2">Retour</a>
                </div>
            </div>
        </div>
    </form>
</div>
