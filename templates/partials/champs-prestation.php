<?php
/** Champs communs à la commande et à sa modification. Variables : $erreurs, $valeurs, $delai */
$v = fn (string $k) => e($valeurs[$k] ?? '');
$dateMin = (new DateTimeImmutable('today'))->modify('+' . max(1, (int) ($delai ?? 1)) . ' days')->format('Y-m-d');
?>
<div class="row g-3">
    <div class="col-md-8">
        <label for="adresse_prestation" class="form-label">Adresse de la prestation <span class="text-aide">(numéro, rue, code postal)</span></label>
        <input type="text" class="form-control<?= attrErreur($erreurs, 'adresse_prestation') ?>" id="adresse_prestation" name="adresse_prestation"
               value="<?= $v('adresse_prestation') ?>" required maxlength="255" autocomplete="street-address" data-devis>
        <?= msgErreur($erreurs, 'adresse_prestation') ?>
    </div>
    <div class="col-md-4">
        <label for="ville_prestation" class="form-label">Ville</label>
        <input type="text" class="form-control<?= attrErreur($erreurs, 'ville_prestation') ?>" id="ville_prestation" name="ville_prestation"
               value="<?= $v('ville_prestation') ?>" required maxlength="80" autocomplete="address-level2" data-devis>
        <?= msgErreur($erreurs, 'ville_prestation') ?>
    </div>
    <div class="col-12">
        <p class="text-aide mb-0" id="adresse-verifiee" aria-live="polite"></p>
    </div>
    <div class="col-md-6">
        <label for="date_prestation" class="form-label">Date de la prestation</label>
        <input type="date" class="form-control<?= attrErreur($erreurs, 'date_prestation') ?>" id="date_prestation" name="date_prestation"
               value="<?= $v('date_prestation') ?>" min="<?= e($dateMin) ?>" required aria-describedby="aide-date">
        <small id="aide-date" class="text-aide">Première date possible pour ce menu : <span id="date-min-texte"><?= e(date('d/m/Y', strtotime($dateMin))) ?></span></small>
        <?= msgErreur($erreurs, 'date_prestation') ?>
    </div>
    <div class="col-md-6">
        <label for="heure_livraison" class="form-label">Heure de livraison souhaitée <span class="text-aide">(entre 08h00 et 22h00)</span></label>
        <input type="time" class="form-control<?= attrErreur($erreurs, 'heure_livraison') ?>" id="heure_livraison" name="heure_livraison"
               value="<?= $v('heure_livraison') ?>" min="08:00" max="22:00" required>
        <?= msgErreur($erreurs, 'heure_livraison') ?>
    </div>
</div>
