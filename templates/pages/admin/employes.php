<div class="container py-4 pb-5">
    <h1>Comptes employés</h1>
    <?php partial('gestion-nav', ['actif' => 'employes']); ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <h2 class="h3">Employés</h2>
            <?php if (!$employes): ?>
                <p class="aucun-resultat">Aucun compte employé.</p>
            <?php else: ?>
                <div class="table-responsive carte p-0">
                    <table class="table table-vg mb-0">
                        <caption class="visually-hidden">Liste des comptes employés</caption>
                        <thead><tr><th scope="col">Employé</th><th scope="col">Créé le</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Action</span></th></tr></thead>
                        <tbody>
                        <?php foreach ($employes as $emp): $nomComplet = trim($emp['prenom'] . ' ' . $emp['nom']) ?: $emp['email']; ?>
                            <tr>
                                <th scope="row"><?= e($nomComplet) ?><br><small class="text-aide fw-normal"><?= e($emp['email']) ?></small></th>
                                <td><?= e(date('d/m/Y', strtotime($emp['date_creation']))) ?></td>
                                <td><?= $emp['actif'] ? '<span class="statut statut-terminee">Actif</span>' : '<span class="statut statut-annulee">Désactivé</span>' ?></td>
                                <td>
                                    <form method="post" action="/admin/employes/statut" class="form-confirmer"
                                          data-confirmation="<?= $emp['actif'] ? 'Désactiver le compte de ' . e($nomComplet) . ' ? Il ne pourra plus se connecter.' : 'Réactiver le compte de ' . e($nomComplet) . ' ?' ?>">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="utilisateur_id" value="<?= (int) $emp['utilisateur_id'] ?>">
                                        <input type="hidden" name="actif" value="<?= $emp['actif'] ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= $emp['actif'] ? 'btn-danger-vg' : 'btn-secondaire' ?>">
                                            <?= $emp['actif'] ? 'Désactiver' : 'Réactiver' ?><span class="visually-hidden"> le compte de <?= e($nomComplet) ?></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <section class="carte" aria-labelledby="t-creer">
                <h2 id="t-creer" class="h3">Créer un compte employé</h2>
                <p class="text-aide">L'employé recevra un e-mail l'informant de la création de son compte, <strong>sans le mot de passe</strong> : transmettez-le-lui de vive voix. Il n'est pas possible de créer un compte administrateur.</p>
                <?php if ($erreurs): ?>
                    <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($erreurs as $champ => $msg): ?><li><a href="#<?= e($champ) ?>"><?= e($msg) ?></a></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" action="/admin/employes" novalidate>
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse e-mail <span class="text-aide">(identifiant de connexion)</span></label>
                        <input type="email" class="form-control<?= attrErreur($erreurs, 'email') ?>" id="email" name="email" value="<?= old('email') ?>" required autocomplete="off">
                        <?= msgErreur($erreurs, 'email') ?>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="prenom" class="form-label">Prénom <span class="text-aide">(facultatif)</span></label>
                            <input type="text" class="form-control<?= attrErreur($erreurs, 'prenom') ?>" id="prenom" name="prenom" value="<?= old('prenom') ?>" maxlength="50" autocomplete="off">
                            <?= msgErreur($erreurs, 'prenom') ?>
                        </div>
                        <div class="col-6">
                            <label for="nom" class="form-label">Nom <span class="text-aide">(facultatif)</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= old('nom') ?>" maxlength="50" autocomplete="off">
                        </div>
                    </div>
                    <?php partial('champs-mot-de-passe', ['erreurs' => $erreurs]); ?>
                    <button type="submit" class="btn btn-primaire w-100 mt-4">Créer le compte employé</button>
                </form>
            </section>
        </div>
    </div>
</div>
