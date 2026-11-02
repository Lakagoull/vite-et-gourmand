<?php
$scripts = ['galerie'];
$types = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
$enStock = (int) $menu['quantite_restante'] > 0;
?>
<div class="container">
    <nav aria-label="Fil d'Ariane" class="fil">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/menus">Nos menus</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
        </ol>
    </nav>

    <div class="row g-5 detail">
        <div class="col-lg-6">
            <?php if ($menu['images']): ?>
                <div class="galerie">
                    <img src="/<?= e($menu['images'][0]['chemin']) ?>" alt="<?= e($menu['images'][0]['texte_alternatif']) ?>"
                         id="image-principale" class="img-arrondie grande" width="900" height="600">
                    <?php if (count($menu['images']) > 1): ?>
                        <ul class="vignettes" aria-label="Galerie d'images du menu">
                            <?php foreach ($menu['images'] as $i => $img): ?>
                                <li>
                                    <button type="button" class="vignette<?= $i === 0 ? ' selection' : '' ?>"
                                            data-src="/<?= e($img['chemin']) ?>" data-alt="<?= e($img['texte_alternatif']) ?>"
                                            aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                                        <img src="/<?= e($img['chemin']) ?>" alt="Afficher : <?= e($img['texte_alternatif']) ?>" width="300" height="200" loading="lazy">
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-6">
            <div class="badges">
                <span class="badge-vg"><span class="visually-hidden">Thème : </span><?= e($menu['theme']) ?></span>
                <span class="badge-vg regime"><span class="visually-hidden">Régime : </span><?= e($menu['regime']) ?></span>
            </div>
            <h1><?= e($menu['titre']) ?></h1>
            <p><?= nl2br(e($menu['description'])) ?></p>

            <div class="boite-prix">
                <div>
                    <div class="lib">Minimum</div>
                    <div class="valeur"><?= (int) $menu['nombre_personne_minimum'] ?> pers.</div>
                </div>
                <div>
                    <div class="lib">Prix (<?= (int) $menu['nombre_personne_minimum'] ?> pers.)</div>
                    <div class="valeur"><?= prix($menu['prix_minimum']) ?></div>
                </div>
                <p class="info-prix">Soit <?= prix($menu['prix_par_personne']) ?> par personne. 10 % de réduction à partir de <?= (int) $menu['nombre_personne_minimum'] + 5 ?> personnes.</p>
                <p class="stock<?= $enStock ? '' : ' epuise' ?>">
                    <?= $enStock
                        ? 'Plus que ' . (int) $menu['quantite_restante'] . ' commande' . ((int) $menu['quantite_restante'] > 1 ? 's' : '') . ' possible' . ((int) $menu['quantite_restante'] > 1 ? 's' : '') . ' pour ce menu'
                        : 'Ce menu n\'est plus disponible à la commande pour le moment' ?>
                </p>
            </div>

            <?php if (!empty($menu['conditions'])): ?>
                <section class="conditions" aria-labelledby="titre-conditions">
                    <h2 id="titre-conditions" class="titre-alerte">
                        <span class="pastille-alerte" aria-hidden="true">!</span>
                        Conditions de ce menu — à lire avant de commander
                    </h2>
                    <p><?= nl2br(e($menu['conditions'])) ?></p>
                </section>
            <?php endif; ?>

            <?php if ($enStock): ?>
                <a href="/commande?menu=<?= (int) $menu['menu_id'] ?>" class="btn btn-primaire btn-lg w-100">Commander ce menu</a>
                <?php if (empty($_SESSION['utilisateur'])): ?>
                    <p class="text-aide mt-2 text-center">Vous devrez vous connecter ou créer un compte pour commander.</p>
                <?php endif; ?>
            <?php else: ?>
                <button type="button" class="btn btn-primaire btn-lg w-100" disabled>Menu indisponible</button>
            <?php endif; ?>
        </div>
    </div>

    <section class="pb-5" aria-labelledby="titre-compo">
        <h2 id="titre-compo">Composition du menu</h2>
        <div class="row g-4">
            <?php foreach ($types as $cle => $libelle): ?>
                <div class="col-md-4">
                    <div class="carte h-100">
                        <h3 class="titre-compo"><?= $libelle ?></h3>
                        <?php if (!$menu['plats'][$cle]): ?>
                            <p class="text-aide">Aucun plat dans cette catégorie.</p>
                        <?php endif; ?>
                        <?php foreach ($menu['plats'][$cle] as $plat): ?>
                            <div class="plat">
                                <strong><?= e($plat['titre_plat']) ?></strong>
                                <?php if ($plat['allergenes']): ?>
                                    <p class="visually-hidden">Allergènes :</p>
                                    <ul class="liste-allergenes">
                                        <?php foreach ($plat['allergenes'] as $al): ?>
                                            <li class="allergene"><?= e($al) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-aide mb-0">Aucun allergène déclaré</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
