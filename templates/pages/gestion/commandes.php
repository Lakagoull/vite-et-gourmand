<?php use App\Models\Commande; ?>
<div class="container py-4 pb-5">
    <h1>Commandes</h1>
    <?php partial('gestion-nav', ['actif' => 'commandes']); ?>

    <form method="get" action="/employe/commandes" class="carte filtres-commandes mb-4" role="search" aria-label="Filtrer les commandes">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="statut" class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach (Commande::STATUTS as $cle => $libelle): ?>
                        <option value="<?= e($cle) ?>"<?= $filtres['statut'] === $cle ? ' selected' : '' ?>><?= e($libelle) ?> (<?= (int) ($compteurs[$cle] ?? 0) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="client" class="form-label">Client <span class="text-aide">(nom, e-mail ou n° de commande)</span></label>
                <input type="search" id="client" name="client" class="form-control" value="<?= e($filtres['client']) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primaire flex-fill">Filtrer</button>
                <a href="/employe/commandes" class="btn btn-secondaire">Effacer</a>
            </div>
        </div>
    </form>

    <p class="resultats" role="status"><strong><?= count($commandes) ?></strong> commande(s)</p>
    <?php if ($commandes): ?>
        <?php partial('gestion-tableau-commandes', ['commandes' => $commandes]); ?>
    <?php else: ?>
        <p class="aucun-resultat">Aucune commande ne correspond à ces critères.</p>
    <?php endif; ?>
</div>
