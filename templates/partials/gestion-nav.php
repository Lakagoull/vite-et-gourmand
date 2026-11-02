<?php
$role = $_SESSION['utilisateur']['role'] ?? '';
$liens = [
    'tableau'   => [$role === 'administrateur' ? '/admin' : '/employe', 'Tableau de bord'],
    'commandes' => ['/employe/commandes', 'Commandes'],
    'menus'     => ['/employe/menus', 'Menus'],
    'plats'     => ['/employe/plats', 'Plats'],
    'horaires'  => ['/employe/horaires', 'Horaires'],
    'avis'      => ['/employe/avis', 'Avis'],
];
if ($role === 'administrateur') {
    $liens['employes'] = ['/admin/employes', 'Employés'];
    $liens['stats'] = ['/admin/statistiques', 'Statistiques'];
}
?>
<nav class="onglets-espace mb-4" aria-label="Navigation de l'espace de gestion">
    <ul class="nav nav-pills gap-2">
        <?php foreach ($liens as $cle => [$url, $libelle]): ?>
            <li class="nav-item">
                <a class="nav-link<?= ($actif ?? '') === $cle ? ' active' : '' ?>" href="<?= e($url) ?>"<?= ($actif ?? '') === $cle ? ' aria-current="page"' : '' ?>><?= e($libelle) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
