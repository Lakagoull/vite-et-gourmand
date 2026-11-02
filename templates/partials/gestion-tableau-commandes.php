<?php use App\Models\Commande; ?>
<div class="table-responsive carte p-0">
    <table class="table table-vg mb-0">
        <caption class="visually-hidden">Liste des commandes</caption>
        <thead>
            <tr>
                <th scope="col">N°</th>
                <th scope="col">Client</th>
                <th scope="col">Menu</th>
                <th scope="col">Prestation</th>
                <th scope="col">Pers.</th>
                <th scope="col">Total</th>
                <th scope="col">Statut</th>
                <th scope="col"><span class="visually-hidden">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($commandes as $c): ?>
            <tr>
                <th scope="row"><?= e($c['numero_commande']) ?></th>
                <td><?= e($c['prenom'] . ' ' . $c['nom']) ?><br><small class="text-aide"><?= e($c['email']) ?></small></td>
                <td><?= e($c['menu_titre']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($c['date_prestation']))) ?> à <?= e(substr($c['heure_livraison'], 0, 5)) ?><br><small class="text-aide"><?= e($c['ville_prestation']) ?></small></td>
                <td><?= (int) $c['nombre_personne'] ?></td>
                <td><?= prix($c['prix_total']) ?></td>
                <td><span class="statut statut-<?= e($c['statut']) ?>"><?= e(Commande::libelleStatut($c['statut'])) ?></span></td>
                <td><a class="btn btn-secondaire btn-sm" href="/employe/commande?id=<?= (int) $c['commande_id'] ?>">Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
