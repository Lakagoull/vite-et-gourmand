<?php
$selection = null;
foreach ($menus as $m) {
    if ((int) $m['menu_id'] === (int) ($valeurs['menu_id'] ?? $menuId)) {
        $selection = $m;
    }
}
$nbDefaut = $valeurs['nombre_personne'] ?? ($selection['nombre_personne_minimum'] ?? '');
?>
<div class="container pb-5">
    <nav aria-label="Fil d'Ariane" class="fil">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/menus">Nos menus</a></li>
            <?php if ($selection): ?><li class="breadcrumb-item"><a href="/menu?id=<?= (int) $selection['menu_id'] ?>"><?= e($selection['titre']) ?></a></li><?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page">Commande</li>
        </ol>
    </nav>
    <h1>Commander un menu</h1>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert">
            <p class="mb-1"><strong>La commande n'a pas pu être enregistrée :</strong></p>
            <ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/commande" id="form-commande" novalidate>
        <?= csrfField() ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="carte mb-4" aria-labelledby="etape-1">
                    <h2 id="etape-1" class="h3"><span class="num-etape" aria-hidden="true">1</span> Vos informations</h2>
                    <p class="text-aide">Remplies automatiquement depuis votre compte. <a href="/espace/profil">Modifier mes informations</a></p>
                    <dl class="row mb-0 infos-client">
                        <dt class="col-sm-4">Nom</dt><dd class="col-sm-8"><?= e($client['prenom'] . ' ' . $client['nom']) ?></dd>
                        <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8"><?= e($client['email']) ?></dd>
                        <dt class="col-sm-4">Mobile</dt><dd class="col-sm-8"><?= e($client['telephone'] ?? '—') ?></dd>
                    </dl>
                </section>

                <section class="carte mb-4" aria-labelledby="etape-2">
                    <h2 id="etape-2" class="h3"><span class="num-etape" aria-hidden="true">2</span> La prestation</h2>
                    <?php partial('champs-prestation', ['erreurs' => $erreurs, 'valeurs' => $valeurs, 'delai' => $selection['delai_commande_jours'] ?? 1]); ?>
                </section>

                <section class="carte mb-4" aria-labelledby="etape-3">
                    <h2 id="etape-3" class="h3"><span class="num-etape" aria-hidden="true">3</span> Votre menu</h2>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="menu_id" class="form-label">Menu choisi</label>
                            <select class="form-select<?= attrErreur($erreurs, 'menu_id') ?>" id="menu_id" name="menu_id" required data-devis>
                                <option value="">— Choisir un menu —</option>
                                <?php foreach ($menus as $m): ?>
                                    <option value="<?= (int) $m['menu_id'] ?>"
                                            data-min="<?= (int) $m['nombre_personne_minimum'] ?>"
                                            data-delai="<?= (int) $m['delai_commande_jours'] ?>"
                                            data-conditions="<?= e($m['conditions']) ?>"
                                            <?= (int) $m['quantite_restante'] < 1 ? 'disabled' : '' ?>
                                            <?= $selection && (int) $m['menu_id'] === (int) $selection['menu_id'] ? 'selected' : '' ?>>
                                        <?= e($m['titre']) ?> — <?= prix($m['prix_par_personne']) ?>/pers.<?= (int) $m['quantite_restante'] < 1 ? ' (épuisé)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= msgErreur($erreurs, 'menu_id') ?>
                        </div>
                        <div class="col-md-4">
                            <label for="nombre_personne" class="form-label">Nombre de personnes</label>
                            <input type="number" class="form-control<?= attrErreur($erreurs, 'nombre_personne') ?>" id="nombre_personne" name="nombre_personne"
                                   value="<?= e($nbDefaut) ?>" min="<?= (int) ($selection['nombre_personne_minimum'] ?? 1) ?>" max="500" step="1" required
                                   aria-describedby="aide-nb-personnes" data-devis>
                            <small id="aide-nb-personnes" class="text-aide">Minimum : <span id="nb-min"><?= (int) ($selection['nombre_personne_minimum'] ?? 1) ?></span> personnes</small>
                            <?= msgErreur($erreurs, 'nombre_personne') ?>
                        </div>
                    </div>

                    <section class="conditions mt-4 mb-3<?= $selection && $selection['conditions'] ? '' : ' d-none' ?>" id="bloc-conditions" aria-labelledby="titre-conditions">
                        <h3 id="titre-conditions" class="titre-alerte"><span class="pastille-alerte" aria-hidden="true">!</span> Conditions de ce menu</h3>
                        <p id="texte-conditions"><?= e($selection['conditions'] ?? '') ?></p>
                    </section>
                    <div class="form-check">
                        <input class="form-check-input<?= attrErreur($erreurs, 'conditions_lues') ?>" type="checkbox" id="conditions_lues" name="conditions_lues" value="1" required>
                        <label class="form-check-label" for="conditions_lues">J'ai lu et j'accepte les conditions de ce menu et les <a href="/cgv" target="_blank" rel="noopener">conditions générales de vente</a><span class="visually-hidden"> (nouvel onglet)</span></label>
                        <?= msgErreur($erreurs, 'conditions_lues') ?>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <div class="colle">
                    <?php partial('recap-prix'); ?>
                    <button type="submit" class="btn btn-primaire btn-lg w-100 mt-3">Valider ma commande</button>
                    <p class="text-aide text-center mt-2">Le prix définitif est vérifié par nos services à la validation.</p>
                </div>
            </div>
        </div>
    </form>
</div>
