<?php $scripts = ['menus']; ?>
<div class="container pb-5">
    <nav aria-label="Fil d'Ariane" class="fil">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nos menus</li>
        </ol>
    </nav>
    <h1>Nos menus</h1>

    <div class="row g-4 mt-1">
        <div class="col-lg-3">
            <button class="btn btn-filtres d-lg-none w-100" type="button" data-bs-toggle="collapse"
                    data-bs-target="#zoneFiltres" aria-expanded="false" aria-controls="zoneFiltres">
                Filtrer les menus (prix, thème, régime…)
            </button>
            <div class="collapse d-lg-block" id="zoneFiltres">
                <form id="form-filtres" class="filtres" method="get" action="/menus" role="search" aria-label="Filtrer les menus">
                    <h2 class="h3">Filtrer</h2>

                    <div class="mb-3">
                        <label for="prix_max" class="form-label">Prix maximum (€)</label>
                        <input type="number" min="0" step="1" class="form-control" id="prix_max" name="prix_max"
                               value="<?= e($filtres['prix_max'] ?? '') ?>" placeholder="Ex. : 300">
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label">Fourchette de prix (€)</legend>
                        <div class="row g-2">
                            <div class="col">
                                <label for="prix_min" class="visually-hidden">Prix minimum</label>
                                <input type="number" min="0" step="1" class="form-control" id="prix_min" name="prix_min"
                                       value="<?= e($filtres['prix_min'] ?? '') ?>" placeholder="Min">
                            </div>
                            <div class="col">
                                <label for="fourchette_max" class="visually-hidden">Prix maximum de la fourchette</label>
                                <input type="number" min="0" step="1" class="form-control" id="fourchette_max" name="fourchette_max"
                                       value="<?= e($filtres['fourchette_max'] ?? '') ?>" placeholder="Max">
                            </div>
                        </div>
                    </fieldset>

                    <div class="mb-3">
                        <label for="theme" class="form-label">Thème</label>
                        <select class="form-select" id="theme" name="theme">
                            <option value="">Tous les thèmes</option>
                            <?php foreach ($themes as $t): ?>
                                <option value="<?= (int) $t['theme_id'] ?>"<?= ($filtres['theme'] ?? null) === (int) $t['theme_id'] ? ' selected' : '' ?>><?= e($t['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="regime" class="form-label">Régime</label>
                        <select class="form-select" id="regime" name="regime">
                            <option value="">Tous les régimes</option>
                            <?php foreach ($regimes as $r): ?>
                                <option value="<?= (int) $r['regime_id'] ?>"<?= ($filtres['regime'] ?? null) === (int) $r['regime_id'] ? ' selected' : '' ?>><?= e($r['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nb_personnes" class="form-label">Nombre de personnes</label>
                        <input type="number" min="1" step="1" class="form-control" id="nb_personnes" name="nb_personnes"
                               value="<?= e($filtres['nb_personnes'] ?? '') ?>" placeholder="Ex. : 6" aria-describedby="aide-nb">
                        <small id="aide-nb" class="text-aide">Affiche les menus dont le minimum de personnes est compatible.</small>
                    </div>

                    <noscript><button type="submit" class="btn btn-primaire w-100 mb-2">Appliquer les filtres</button></noscript>
                    <a href="/menus" id="reinitialiser" class="btn btn-secondaire w-100">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <p class="resultats" id="nb-resultats" aria-live="polite">
                <strong><?= count($menus) ?> menu<?= count($menus) > 1 ? 's' : '' ?></strong>
                <?= count($menus) > 1 ? 'correspondent' : 'correspond' ?> à votre recherche
            </p>
            <div class="row g-4" id="liste-menus">
                <?php foreach ($menus as $menu): ?>
                    <div class="col-md-6 col-xl-4"><?php partial('menu-card', ['menu' => $menu]); ?></div>
                <?php endforeach; ?>
            </div>
            <p id="aucun-menu" class="aucun-resultat<?= $menus ? ' d-none' : '' ?>">
                Aucun menu ne correspond à ces critères. Essayez d'élargir votre recherche.
            </p>
        </div>
    </div>
</div>
