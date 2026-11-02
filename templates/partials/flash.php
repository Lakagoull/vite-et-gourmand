<?php $messages = lireFlash(); if ($messages): ?>
<div class="container pt-4">
    <?php foreach ($messages as $m): ?>
        <div class="alert alert-<?= e($m['type']) ?>" role="<?= $m['type'] === 'danger' ? 'alert' : 'status' ?>">
            <?= e($m['message']) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
