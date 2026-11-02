<?php $onglets = ['en_attente' => 'À modérer', 'valide' => 'Validés', 'refuse' => 'Refusés']; ?>
<div class="container py-4 pb-5">
    <h1>Avis clients</h1>
    <?php partial('gestion-nav', ['actif' => 'avis']); ?>

    <ul class="nav nav-tabs mb-4">
        <?php foreach ($onglets as $cle => $libelle): ?>
            <li class="nav-item"><a class="nav-link<?= $statut === $cle ? ' active' : '' ?>" href="/employe/avis?statut=<?= e($cle) ?>"<?= $statut === $cle ? ' aria-current="page"' : '' ?>><?= e($libelle) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <?php if (!$avis): ?>
        <p class="aucun-resultat">Aucun avis dans cette catégorie.</p>
    <?php endif; ?>
    <div class="row g-4">
        <?php foreach ($avis as $a): ?>
            <div class="col-md-6">
                <article class="carte h-100">
                    <div class="d-flex justify-content-between">
                        <div class="etoiles" role="img" aria-label="Note : <?= (int) $a['note'] ?> sur 5"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></div>
                        <small class="text-aide"><?= e(date('d/m/Y', strtotime($a['date_creation']))) ?></small>
                    </div>
                    <p>« <?= e($a['description']) ?> »</p>
                    <p class="text-aide"><?= e($a['prenom'] . ' ' . $a['nom']) ?> — <?= e($a['menu']) ?> — commande <?= e($a['numero_commande']) ?></p>
                        <form method="post" action="/employe/avis" class="d-flex gap-2">
                            <?= csrfField() ?>
                            <input type="hidden" name="avis_id" value="<?= (int) $a['avis_id'] ?>">
                            <?php if ($a['statut'] !== 'valide'): ?>
                                <button type="submit" name="decision" value="valide" class="btn btn-primaire btn-sm">Valider<span class="visually-hidden"> l'avis de <?= e($a['prenom']) ?></span></button>
                            <?php endif; ?>
                            <?php if ($a['statut'] !== 'refuse'): ?>
                                <button type="submit" name="decision" value="refuse" class="btn btn-danger-vg btn-sm">Refuser<span class="visually-hidden"> l'avis de <?= e($a['prenom']) ?></span></button>
                            <?php endif; ?>
                        </form>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</div>
