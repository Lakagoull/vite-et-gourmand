<div class="container py-4 pb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h1 class="mb-0">Menus</h1>
        <a href="/employe/menu" class="btn btn-primaire">+ Nouveau menu</a>
    </div>
    <div class="mt-3"><?php partial('gestion-nav', ['actif' => 'menus']); ?></div>

    <div class="table-responsive carte p-0">
        <table class="table table-vg mb-0">
            <caption class="visually-hidden">Liste des menus</caption>
            <thead>
                <tr>
                    <th scope="col">Menu</th>
                    <th scope="col">Thème / régime</th>
                    <th scope="col">Minimum</th>
                    <th scope="col">Prix / pers.</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Plats</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($menus as $m): ?>
                <tr>
                    <th scope="row"><?= e($m['titre']) ?></th>
                    <td><?= e($m['theme']) ?> / <?= e($m['regime']) ?></td>
                    <td><?= (int) $m['nombre_personne_minimum'] ?> pers.</td>
                    <td><?= prix($m['prix_par_personne']) ?></td>
                    <td><?= (int) $m['quantite_restante'] === 0 ? '<span class="statut statut-attente_retour_materiel">Épuisé</span>' : (int) $m['quantite_restante'] ?></td>
                    <td><?= (int) $m['nb_plats'] ?></td>
                    <td class="text-nowrap">
                        <a href="/employe/menu?id=<?= (int) $m['menu_id'] ?>" class="btn btn-secondaire btn-sm">Modifier<span class="visually-hidden"> <?= e($m['titre']) ?></span></a>
                        <a href="/menu?id=<?= (int) $m['menu_id'] ?>" class="btn btn-secondaire btn-sm">Voir<span class="visually-hidden"> <?= e($m['titre']) ?> sur le site</span></a>
                        <form method="post" action="/employe/menu/supprimer" class="d-inline form-confirmer" data-confirmation="Supprimer définitivement le menu « <?= e($m['titre']) ?> » ?">
                            <?= csrfField() ?>
                            <input type="hidden" name="menu_id" value="<?= (int) $m['menu_id'] ?>">
                            <button type="submit" class="btn btn-danger-vg btn-sm">Supprimer<span class="visually-hidden"> <?= e($m['titre']) ?></span></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
