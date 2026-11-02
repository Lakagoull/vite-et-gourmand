<?php $utilisateur = $_SESSION['utilisateur'] ?? null; ?>
<header class="entete">
    <nav class="navbar navbar-expand-lg" aria-label="Menu principal">
        <div class="container">
            <a class="logo-site navbar-brand" href="/">Vite <span>&amp;</span> Gourmand</a>
            <button class="navbar-toggler burger" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link<?= estActif('/') ? ' active' : '' ?>" href="/"<?= estActif('/') ? ' aria-current="page"' : '' ?>>Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= estActif('/menu') ? ' active' : '' ?>" href="/menus"<?= estActif('/menu') ? ' aria-current="page"' : '' ?>>Nos menus</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= estActif('/contact') ? ' active' : '' ?>" href="/contact"<?= estActif('/contact') ? ' aria-current="page"' : '' ?>>Contact</a>
                    </li>
                    <?php if ($utilisateur): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= estActif('/espace') ? ' active' : '' ?>" href="/espace">Mon espace</a>
                        </li>
                        <li class="nav-item">
                            <form method="post" action="/deconnexion" class="d-inline">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-secondaire">Déconnexion</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-primaire" href="/connexion">Connexion</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
