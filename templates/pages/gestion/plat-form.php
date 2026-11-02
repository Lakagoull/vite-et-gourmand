<?php use App\Models\Plat; $v = fn (string $k) => e($valeurs[$k] ?? ''); ?>
<div class="container py-4 pb-5">
    <?php partial('gestion-nav', ['actif' => 'plats']); ?>
    <nav aria-label="Fil d'Ariane" class="fil pt-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/employe/plats">Plats</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($titrePage) ?></li>
        </ol>
    </nav>
    <h1><?= e($titrePage) ?></h1>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/employe/plat" enctype="multipart/form-data" class="carte formulaire page-etroite" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="plat_id" value="<?= (int) ($plat['plat_id'] ?? 0) ?>">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="titre_plat" class="form-label">Nom du plat</label>
                <input type="text" class="form-control<?= attrErreur($erreurs, 'titre_plat') ?>" id="titre_plat" name="titre_plat" value="<?= $v('titre_plat') ?>" required maxlength="100">
                <?= msgErreur($erreurs, 'titre_plat') ?>
            </div>
            <div class="col-md-4">
                <label for="type_plat" class="form-label">Type</label>
                <select class="form-select<?= attrErreur($erreurs, 'type_plat') ?>" id="type_plat" name="type_plat" required>
                    <?php foreach (Plat::TYPES as $cle => $libelle): ?><option value="<?= e($cle) ?>"<?= ($valeurs['type_plat'] ?? '') === $cle ? ' selected' : '' ?>><?= e($libelle) ?></option><?php endforeach; ?>
                </select>
                <?= msgErreur($erreurs, 'type_plat') ?>
            </div>
        </div>

        <fieldset class="mt-4" id="allergenes">
            <legend class="h3">Allergènes</legend>
            <?= msgErreur($erreurs, 'allergenes') ?>
            <div class="grille-allergenes">
                <?php foreach ($allergenes as $a): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="allergenes[]" value="<?= (int) $a['allergene_id'] ?>" id="al-<?= (int) $a['allergene_id'] ?>"
                            <?= in_array((int) $a['allergene_id'], array_map('intval', $allergenesPlat), true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="al-<?= (int) $a['allergene_id'] ?>"><?= e($a['libelle']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="mt-4">
            <?php if (!empty($plat['photo'])): ?>
                <p class="mb-2">Photo actuelle :</p>
                <img src="/<?= e($plat['photo']) ?>" alt="Photo actuelle : <?= e($plat['titre_plat']) ?>" width="200" height="150" class="miniature mb-3">
            <?php endif; ?>
            <label for="photo" class="form-label"><?= !empty($plat['photo']) ? 'Remplacer la photo' : 'Photo' ?> <span class="text-aide">(facultatif — JPEG, PNG ou WebP, 3 Mo max.)</span></label>
            <input type="file" class="form-control<?= attrErreur($erreurs, 'photo') ?>" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
            <?= msgErreur($erreurs, 'photo') ?>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primaire">Enregistrer le plat</button>
            <a href="/employe/plats" class="btn btn-secondaire">Retour</a>
        </div>
    </form>
</div>
