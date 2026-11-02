<fieldset class="mb-3">
    <legend class="form-label">Client contacté par</legend>
    <div class="form-check form-check-inline">
        <input class="form-check-input<?= attrErreur($erreurs, 'mode_contact') ?>" type="radio" name="mode_contact" id="contact-tel" value="telephone" required>
        <label class="form-check-label" for="contact-tel">Téléphone (GSM)</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="mode_contact" id="contact-mail" value="mail">
        <label class="form-check-label" for="contact-mail">E-mail</label>
    </div>
    <?= msgErreur($erreurs, 'mode_contact') ?>
</fieldset>
<div>
    <label for="motif" class="form-label"><?= e($motifLabel) ?></label>
    <textarea class="form-control<?= attrErreur($erreurs, 'motif') ?>" id="motif" name="motif" rows="3" required minlength="5" maxlength="1000"><?= old('motif') ?></textarea>
    <?= msgErreur($erreurs, 'motif') ?>
</div>
