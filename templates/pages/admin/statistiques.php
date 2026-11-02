<?php
$periode = 'toutes périodes';
if ($filtres['du'] && $filtres['au']) {
    $periode = 'du ' . date('d/m/Y', strtotime($filtres['du'])) . ' au ' . date('d/m/Y', strtotime($filtres['au']));
} elseif ($filtres['du']) {
    $periode = 'depuis le ' . date('d/m/Y', strtotime($filtres['du']));
} elseif ($filtres['au']) {
    $periode = 'jusqu\'au ' . date('d/m/Y', strtotime($filtres['au']));
}
$lignesCommandes = array_map(fn ($l) => [
    'label' => $l['titre'], 'valeur' => $l['commandes'],
    'texte' => $l['commandes'] . ' commande' . ($l['commandes'] > 1 ? 's' : ''),
], $lignes);
$lignesCa = $lignes;
usort($lignesCa, fn ($a, $b) => $b['ca'] <=> $a['ca']);
$lignesCa = array_map(fn ($l) => ['label' => $l['titre'], 'valeur' => $l['ca'], 'texte' => prix($l['ca'])], $lignesCa);
?>
<div class="container py-4 pb-5">
    <h1>Statistiques</h1>
    <?php partial('gestion-nav', ['actif' => 'stats']); ?>
    <p class="text-aide">Données issues de la base NoSQL (MongoDB). Les commandes annulées ne sont pas comptées.</p>

    <form method="get" action="/admin/statistiques" class="carte filtres-stats mb-4" aria-label="Filtrer les statistiques">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="menu_id" class="form-label">Menu</label>
                <select id="menu_id" name="menu_id" class="form-select">
                    <option value="">Tous les menus</option>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= (int) $m['menu_id'] ?>"<?= $filtres['menu_id'] === (int) $m['menu_id'] ? ' selected' : '' ?>><?= e($m['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="du" class="form-label">Du</label>
                <input type="date" id="du" name="du" class="form-control" value="<?= e($filtres['du']) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="au" class="form-label">Au</label>
                <input type="date" id="au" name="au" class="form-control" value="<?= e($filtres['au']) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primaire flex-fill">Filtrer</button>
                <a href="/admin/statistiques" class="btn btn-secondaire" aria-label="Effacer les filtres">✕</a>
            </div>
        </div>
    </form>

    <?php if ($erreur): ?><div class="alert alert-warning" role="alert"><?= e($erreur) ?></div><?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="carte tuile">
                <span class="text-aide">Chiffre d'affaires — <?= e($periode) ?></span>
                <span class="tuile-chiffre"><?= prix($totalCa) ?></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="carte tuile">
                <span class="text-aide">Commandes — <?= e($periode) ?></span>
                <span class="tuile-chiffre"><?= (int) $totalCommandes ?></span>
            </div>
        </div>
    </div>

    <?php if ($lignes): ?>
        <div class="row g-4">
            <div class="col-12">
                <?php partial('graphique-barres', [
                    'id' => 'graphique-commandes', 'titre' => 'Nombre de commandes par menu',
                    'sousTitre' => ucfirst($periode), 'lignes' => $lignesCommandes,
                ]); ?>
            </div>
            <div class="col-12">
                <?php partial('graphique-barres', [
                    'id' => 'graphique-ca', 'titre' => 'Chiffre d\'affaires par menu',
                    'sousTitre' => ucfirst($periode) . ' — montants TTC, livraison comprise', 'lignes' => $lignesCa,
                ]); ?>
            </div>
        </div>

        <details class="carte mt-4">
            <summary class="fw-bold">Voir les données sous forme de tableau</summary>
            <div class="table-responsive mt-3">
                <table class="table table-vg mb-0">
                    <caption class="visually-hidden">Commandes et chiffre d'affaires par menu, <?= e($periode) ?></caption>
                    <thead><tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th><th scope="col" class="text-end">Personnes servies</th><th scope="col" class="text-end">Chiffre d'affaires</th></tr></thead>
                    <tbody>
                    <?php foreach ($lignes as $l): ?>
                        <tr><th scope="row"><?= e($l['titre']) ?></th><td class="text-end"><?= (int) $l['commandes'] ?></td><td class="text-end"><?= (int) $l['personnes'] ?></td><td class="text-end"><?= prix($l['ca']) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><th scope="row">Total</th><td class="text-end fw-bold"><?= (int) $totalCommandes ?></td><td class="text-end fw-bold"><?= (int) array_sum(array_column($lignes, 'personnes')) ?></td><td class="text-end fw-bold"><?= prix($totalCa) ?></td></tr></tfoot>
                </table>
            </div>
        </details>
    <?php endif; ?>
</div>
