<?php use App\Models\Plat; ?>
<div class="container py-4 pb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h1 class="mb-0">Plats</h1>
        <a href="/employe/plat" class="btn btn-primaire">+ Nouveau plat</a>
    </div>
    <div class="mt-3"><?php partial('gestion-nav', ['actif' => 'plats']); ?></div>

    <div class="table-responsive carte p-0">
        <table class="table table-vg mb-0">
            <caption class="visually-hidden">Liste des plats</caption>
            <thead>
                <tr>
                    <th scope="col">Photo</th>
                    <th scope="col">Plat</th>
                    <th scope="col">Type</th>
                    <th scope="col">Allergènes</th>
                    <th scope="col">Menus</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($plats as $p): ?>
                <tr>
                    <td><?php if ($p['photo']): ?><img src="/<?= e($p['photo']) ?>" alt="" width="64" height="48" class="miniature" loading="lazy"><?php endif; ?></td>
                    <th scope="row"><?= e($p['titre_plat']) ?></th>
                    <td><?= e(Plat::TYPES[$p['type_plat']] ?? $p['type_plat']) ?></td>
                    <td><?= $p['allergenes'] ? e($p['allergenes']) : '<span class="text-aide">Aucun</span>' ?></td>
                    <td><?= (int) $p['nb_menus'] ?></td>
                    <td class="text-nowrap">
                        <a href="/employe/plat?id=<?= (int) $p['plat_id'] ?>" class="btn btn-secondaire btn-sm">Modifier<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></a>
                        <form method="post" action="/employe/plat/supprimer" class="d-inline form-confirmer"
                              data-confirmation="Supprimer « <?= e($p['titre_plat']) ?> » ? Il sera retiré des <?= (int) $p['nb_menus'] ?> menu(s) qui le contiennent.">
                            <?= csrfField() ?>
                            <input type="hidden" name="plat_id" value="<?= (int) $p['plat_id'] ?>">
                            <button type="submit" class="btn btn-danger-vg btn-sm">Supprimer<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
