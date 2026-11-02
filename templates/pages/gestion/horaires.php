<div class="container py-4 pb-5">
    <h1>Horaires</h1>
    <?php partial('gestion-nav', ['actif' => 'horaires']); ?>
    <p>Ces horaires sont affichés dans le pied de page de toutes les pages du site.</p>

    <?php if ($erreurs): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($erreurs as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="/employe/horaires" class="carte page-etroite p-0">
        <?= csrfField() ?>
        <table class="table table-vg mb-0">
            <caption class="visually-hidden">Horaires d'ouverture du lundi au dimanche</caption>
            <thead><tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr></thead>
            <tbody>
            <?php foreach ($horaires as $h): $id = (int) $h['horaire_id']; $ferme = $h['heure_ouverture'] === null; ?>
                <tr<?= isset($erreurs[$id]) ? ' class="ligne-erreur"' : '' ?>>
                    <th scope="row"><?= e($h['jour']) ?></th>
                    <td>
                        <label for="o-<?= $id ?>" class="visually-hidden">Ouverture le <?= e($h['jour']) ?></label>
                        <input type="time" class="form-control" id="o-<?= $id ?>" name="ouverture[<?= $id ?>]" value="<?= e(substr((string) $h['heure_ouverture'], 0, 5)) ?>">
                    </td>
                    <td>
                        <label for="f-<?= $id ?>" class="visually-hidden">Fermeture le <?= e($h['jour']) ?></label>
                        <input type="time" class="form-control" id="f-<?= $id ?>" name="fermeture[<?= $id ?>]" value="<?= e(substr((string) $h['heure_fermeture'], 0, 5)) ?>">
                    </td>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ferme-<?= $id ?>" name="ferme[<?= $id ?>]" value="1"<?= $ferme ? ' checked' : '' ?>>
                            <label class="form-check-label" for="ferme-<?= $id ?>"><span class="visually-hidden"><?= e($h['jour']) ?> : </span>Fermé</label>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="p-3"><button type="submit" class="btn btn-primaire">Enregistrer les horaires</button></div>
    </form>
</div>
