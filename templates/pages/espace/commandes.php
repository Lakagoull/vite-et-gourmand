<?php use App\Models\Commande; ?>
<div class="container py-4 pb-5">
    <h1>Mon espace</h1>
    <p class="lead">Bonjour <?= e($u['prenom']) ?> !</p>
    <?php partial('espace-nav', ['actif' => 'commandes']); ?>

    <h2 class="mt-4">Mes commandes</h2>
    <?php if (!$commandes): ?>
        <div class="carte text-center">
            <p>Vous n'avez pas encore passé de commande.</p>
            <a href="/menus" class="btn btn-primaire">Découvrir nos menus</a>
        </div>
    <?php else: ?>
        <div class="table-responsive carte p-0">
            <table class="table table-vg mb-0">
                <caption class="visually-hidden">Liste de mes commandes, de la plus récente à la plus ancienne</caption>
                <thead>
                    <tr>
                        <th scope="col">N° de commande</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Prestation</th>
                        <th scope="col">Personnes</th>
                        <th scope="col">Total</th>
                        <th scope="col">Statut</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $c): ?>
                    <tr>
                        <th scope="row"><?= e($c['numero_commande']) ?></th>
                        <td><?= e($c['menu_titre']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($c['date_prestation']))) ?></td>
                        <td><?= (int) $c['nombre_personne'] ?></td>
                        <td><?= prix($c['prix_total']) ?></td>
                        <td>
                            <span class="statut statut-<?= e($c['statut']) ?>"><?= e(Commande::libelleStatut($c['statut'])) ?></span>
                            <?php if ($c['statut'] === 'terminee' && !$c['a_avis']): ?><br><small class="text-aide">Avis à donner</small><?php endif; ?>
                        </td>
                        <td><a href="/espace/commande?id=<?= (int) $c['commande_id'] ?>" class="btn btn-secondaire btn-sm">Détail<span class="visually-hidden"> de la commande <?= e($c['numero_commande']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
