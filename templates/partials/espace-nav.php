<nav class="onglets-espace" aria-label="Navigation de mon espace">
    <ul class="nav nav-pills gap-2">
        <li class="nav-item"><a class="nav-link<?= ($actif ?? '') === 'commandes' ? ' active' : '' ?>" href="/espace"<?= ($actif ?? '') === 'commandes' ? ' aria-current="page"' : '' ?>>Mes commandes</a></li>
        <li class="nav-item"><a class="nav-link<?= ($actif ?? '') === 'profil' ? ' active' : '' ?>" href="/espace/profil"<?= ($actif ?? '') === 'profil' ? ' aria-current="page"' : '' ?>>Mes informations</a></li>
        <li class="nav-item"><a class="nav-link" href="/menus">Commander un menu</a></li>
    </ul>
</nav>
