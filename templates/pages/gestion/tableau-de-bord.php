<?php use App\Models\Commande; ?>
<div class="container py-4 pb-5">
    <h1><?= e($titrePage) ?></h1>
    <p class="lead">Bonjour <?= e($u['prenom']) ?> !</p>
    <?php partial('gestion-nav', ['actif' => 'tableau']); ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a class="carte tuile" href="/employe/commandes?statut=en_attente">
                <span class="tuile-chiffre"><?= (int) ($compteurs['en_attente'] ?? 0) ?></span>
                <span>commande(s) à valider</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a class="carte tuile" href="/employe/commandes?statut=en_preparation">
                <span class="tuile-chiffre"><?= (int) ($compteurs['accepte'] ?? 0) + (int) ($compteurs['en_preparation'] ?? 0) ?></span>
                <span>à préparer</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a class="carte tuile" href="/employe/commandes?statut=attente_retour_materiel">
                <span class="tuile-chiffre"><?= (int) ($compteurs['attente_retour_materiel'] ?? 0) ?></span>
                <span>matériel à récupérer</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a class="carte tuile" href="/employe/avis">
                <span class="tuile-chiffre"><?= (int) $avisAttente ?></span>
                <span>avis à modérer</span>
            </a>
        </div>
    </div>

    <h2 class="h3">Prochaines commandes en cours</h2>
    <?php if (!$prochaines): ?>
        <p>Aucune commande en cours.</p>
    <?php else: ?>
        <?php partial('gestion-tableau-commandes', ['commandes' => $prochaines]); ?>
    <?php endif; ?>
</div>
