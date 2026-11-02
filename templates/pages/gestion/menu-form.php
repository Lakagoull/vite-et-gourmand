<?php
use App\Models\Plat;
$v = fn (string $k) => e($valeurs[$k] ?? '');
$platsParType = [];
foreach ($plats as $p) {
    $platsParType[$p['type_plat']][] = $p;
}
?>
<div class="container py-4 pb-5">
    <?php partial('gestion-nav', ['actif' => 'menus']); ?>
    <nav aria-label="Fil d'Ariane" class="fil pt-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/employe/menus">Menus</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($titrePage) ?></li>
        </ol>
    </nav>
    <h1><?= e($menu ? $menu['titre'] : 'Nouveau menu') ?></h1>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/employe/menu" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="menu_id" value="<?= (int) ($menu['menu_id'] ?? 0) ?>">

        <section class="carte mb-4">
            <h2 class="h3">Informations</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'titre') ?>" id="titre" name="titre" value="<?= $v('titre') ?>" required maxlength="100">
                    <?= msgErreur($erreurs, 'titre') ?>
                </div>
                <div class="col-md-2">
                    <label for="theme_id" class="form-label">Thème</label>
                    <select class="form-select<?= attrErreur($erreurs, 'theme_id') ?>" id="theme_id" name="theme_id" required>
                        <?php foreach ($themes as $t): ?><option value="<?= (int) $t['theme_id'] ?>"<?= (int) ($valeurs['theme_id'] ?? 0) === (int) $t['theme_id'] ? ' selected' : '' ?>><?= e($t['libelle']) ?></option><?php endforeach; ?>
                    </select>
                    <?= msgErreur($erreurs, 'theme_id') ?>
                </div>
                <div class="col-md-2">
                    <label for="regime_id" class="form-label">Régime</label>
                    <select class="form-select<?= attrErreur($erreurs, 'regime_id') ?>" id="regime_id" name="regime_id" required>
                        <?php foreach ($regimes as $r): ?><option value="<?= (int) $r['regime_id'] ?>"<?= (int) ($valeurs['regime_id'] ?? 0) === (int) $r['regime_id'] ? ' selected' : '' ?>><?= e($r['libelle']) ?></option><?php endforeach; ?>
                    </select>
                    <?= msgErreur($erreurs, 'regime_id') ?>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description (présentation du menu)</label>
                    <textarea class="form-control<?= attrErreur($erreurs, 'description') ?>" id="description" name="description" rows="3" required><?= $v('description') ?></textarea>
                    <?= msgErreur($erreurs, 'description') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="nombre_personne_minimum" class="form-label">Personnes minimum</label>
                    <input type="number" class="form-control<?= attrErreur($erreurs, 'nombre_personne_minimum') ?>" id="nombre_personne_minimum" name="nombre_personne_minimum" value="<?= $v('nombre_personne_minimum') ?>" min="1" max="500" required>
                    <?= msgErreur($erreurs, 'nombre_personne_minimum') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="prix_par_personne" class="form-label">Prix par personne (€)</label>
                    <input type="text" inputmode="decimal" class="form-control<?= attrErreur($erreurs, 'prix_par_personne') ?>" id="prix_par_personne" name="prix_par_personne" value="<?= $v('prix_par_personne') ?>" required>
                    <?= msgErreur($erreurs, 'prix_par_personne') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="delai_commande_jours" class="form-label">Délai de commande (jours)</label>
                    <input type="number" class="form-control<?= attrErreur($erreurs, 'delai_commande_jours') ?>" id="delai_commande_jours" name="delai_commande_jours" value="<?= $v('delai_commande_jours') ?>" min="0" max="365" required>
                    <?= msgErreur($erreurs, 'delai_commande_jours') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="quantite_restante" class="form-label">Stock (commandes possibles)</label>
                    <input type="number" class="form-control<?= attrErreur($erreurs, 'quantite_restante') ?>" id="quantite_restante" name="quantite_restante" value="<?= $v('quantite_restante') ?>" min="0" required>
                    <?= msgErreur($erreurs, 'quantite_restante') ?>
                </div>
                <div class="col-12">
                    <label for="conditions" class="form-label">Conditions du menu <span class="text-aide">(délai, conservation… affichées en évidence aux clients)</span></label>
                    <textarea class="form-control" id="conditions" name="conditions" rows="2"><?= $v('conditions') ?></textarea>
                </div>
            </div>
        </section>

        <section class="carte mb-4" aria-labelledby="t-plats">
            <h2 id="t-plats" class="h3">Composition <span class="text-aide fs-6">(un plat peut appartenir à plusieurs menus)</span></h2>
            <?= msgErreur($erreurs, 'plats') ?>
            <div class="row g-4" id="plats">
                <?php foreach (Plat::TYPES as $type => $libelle): ?>
                    <fieldset class="col-md-4">
                        <legend class="h5"><?= e($libelle) ?>s</legend>
                        <?php foreach ($platsParType[$type] ?? [] as $p): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="plats[]" value="<?= (int) $p['plat_id'] ?>" id="plat-<?= (int) $p['plat_id'] ?>"
                                    <?= in_array((int) $p['plat_id'], array_map('intval', $platsMenu), true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="plat-<?= (int) $p['plat_id'] ?>"><?= e($p['titre_plat']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
            </div>
            <p class="mt-3 mb-0"><a href="/employe/plat">Créer un nouveau plat</a></p>
        </section>

        <section class="carte mb-4" id="images" aria-labelledby="t-images">
            <h2 id="t-images" class="h3">Galerie d'images</h2>
            <?php if ($images): ?>
                <ul class="galerie-gestion">
                    <?php foreach ($images as $img): ?>
                        <li>
                            <img src="/<?= e($img['chemin']) ?>" alt="<?= e($img['texte_alternatif']) ?>" width="180" height="120" loading="lazy">
                            <button type="submit" form="suppr-image-<?= (int) $img['image_id'] ?>" class="btn btn-danger-vg btn-sm w-100 mt-1">Supprimer<span class="visually-hidden"> l'image : <?= e($img['texte_alternatif']) ?></span></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="images-input" class="form-label">Ajouter des images <span class="text-aide">(JPEG, PNG ou WebP, 3 Mo max., 6 à la fois)</span></label>
                    <input type="file" class="form-control<?= attrErreur($erreurs, 'images') ?>" id="images-input" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
                    <?= msgErreur($erreurs, 'images') ?>
                </div>
                <div class="col-md-6">
                    <label for="texte_alternatif" class="form-label">Description des images <span class="text-aide">(pour les personnes malvoyantes)</span></label>
                    <input type="text" class="form-control<?= attrErreur($erreurs, 'texte_alternatif') ?>" id="texte_alternatif" name="texte_alternatif" value="<?= $v('texte_alternatif') ?>" maxlength="255" placeholder="Ex. : Bûche de Noël au chocolat">
                    <?= msgErreur($erreurs, 'texte_alternatif') ?>
                </div>
            </div>
        </section>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primaire btn-lg">Enregistrer le menu</button>
            <a href="/employe/menus" class="btn btn-secondaire btn-lg">Retour</a>
        </div>
    </form>

    <?php foreach ($images as $img): ?>
        <form method="post" action="/employe/menu/image/supprimer" id="suppr-image-<?= (int) $img['image_id'] ?>" class="form-confirmer" data-confirmation="Supprimer cette image ?">
            <?= csrfField() ?>
            <input type="hidden" name="menu_id" value="<?= (int) $menu['menu_id'] ?>">
            <input type="hidden" name="image_id" value="<?= (int) $img['image_id'] ?>">
        </form>
    <?php endforeach; ?>
</div>
