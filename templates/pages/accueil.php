<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <p class="surtitre">Traiteur à Bordeaux depuis 25 ans</p>
                <h1>Des menus de fête et du quotidien, livrés chez vous</h1>
                <p class="lead">Julie et José cuisinent pour vos repas de Noël, de Pâques et tous vos évènements, avec des produits frais du Sud-Ouest.</p>
                <a href="/menus" class="btn btn-or btn-lg">Découvrir nos menus</a>
            </div>
            <div class="col-lg-6">
                <img src="/assets/images/site/hero.jpg" class="img-arrondie" alt="Table de fête dressée par Vite & Gourmand" width="1200" height="800">
            </div>
        </div>
    </div>
</section>

<section class="bloc" aria-labelledby="titre-presentation">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-lg-1">
                <img src="/assets/images/site/equipe.jpg" class="img-arrondie" alt="Julie et José dans leur cuisine" width="1000" height="700" loading="lazy">
            </div>
            <div class="col-lg-6 order-lg-2">
                <h2 id="titre-presentation">Qui sommes-nous ?</h2>
                <p>Depuis 25 ans, <strong>Vite &amp; Gourmand</strong> accompagne les Bordelais pour leurs repas en famille et leurs évènements. Julie et José imaginent des menus qui évoluent au fil des saisons et des fêtes : Noël, Pâques, repas classiques ou grandes réceptions.</p>
                <p>Commandez en ligne, choisissez votre date et votre heure de livraison : nous préparons et livrons votre repas à Bordeaux et dans ses environs.</p>
                <a href="/menus" class="btn btn-secondaire">Voir tous les menus</a>
            </div>
        </div>
    </div>
</section>

<section class="bloc pt-0" aria-labelledby="titre-equipe">
    <div class="container">
        <div class="titre-centre">
            <h2 id="titre-equipe">Une équipe de professionnels</h2>
            <p>Ce qui fait notre différence</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="carte h-100">
                    <div class="icone" aria-hidden="true">25</div>
                    <h3>25 ans d'expérience</h3>
                    <p>Un savoir-faire reconnu à Bordeaux pour tous vos repas, du plus simple au plus festif.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte h-100">
                    <div class="icone" aria-hidden="true">✓</div>
                    <h3>Produits frais et locaux</h3>
                    <p>Nous travaillons avec des producteurs du Sud-Ouest et cuisinons tout dans notre atelier.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte h-100">
                    <div class="icone" aria-hidden="true">➜</div>
                    <h3>Livraison à l'heure</h3>
                    <p>Livraison à Bordeaux et dans les environs, à l'heure que vous choisissez lors de la commande.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bloc pt-0" aria-labelledby="titre-avis">
    <div class="container">
        <div class="titre-centre">
            <h2 id="titre-avis">Ils nous ont fait confiance</h2>
            <p>Avis vérifiés de nos clients</p>
        </div>
        <?php if ($avis): ?>
            <div class="row g-4">
                <?php foreach ($avis as $a): ?>
                    <div class="col-md-4">
                        <figure class="carte h-100 m-0">
                            <div class="etoiles" role="img" aria-label="Note : <?= (int) $a['note'] ?> sur 5">
                                <?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?>
                            </div>
                            <blockquote class="m-0"><p>« <?= e($a['description']) ?> »</p></blockquote>
                            <figcaption class="auteur"><?= e($a['prenom']) ?> <?= e($a['initiale']) ?>. — <?= e($a['menu']) ?></figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Aucun avis pour le moment.</p>
        <?php endif; ?>
    </div>
</section>
