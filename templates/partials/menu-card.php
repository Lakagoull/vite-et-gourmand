<article class="carte-menu h-100">
    <?php if (!empty($menu['image'])): ?>
        <img src="/<?= e($menu['image']) ?>" alt="" class="carte-menu-img" loading="lazy" width="900" height="600">
    <?php endif; ?>
    <div class="corps">
        <div class="badges">
            <span class="badge-vg"><span class="visually-hidden">Thème : </span><?= e($menu['theme']) ?></span>
            <span class="badge-vg regime"><span class="visually-hidden">Régime : </span><?= e($menu['regime']) ?></span>
        </div>
        <h3><?= e($menu['titre']) ?></h3>
        <p><?= e($menu['description']) ?></p>
        <div class="infos-menu">
            <div><small>Minimum</small><strong><?= (int) $menu['nombre_personne_minimum'] ?> personnes</strong></div>
            <div class="prix"><?= prix($menu['prix_minimum']) ?></div>
        </div>
        <a href="/menu?id=<?= (int) $menu['menu_id'] ?>" class="btn btn-primaire w-100">
            Voir le détail<span class="visually-hidden"> du <?= e($menu['titre']) ?></span>
        </a>
    </div>
</article>
