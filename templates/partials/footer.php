<?php use App\Models\Horaire; $horaires = Horaire::tous(); ?>
<footer class="pied">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h2 class="titre-pied">Nos horaires</h2>
                <table class="horaires">
                    <caption class="visually-hidden">Horaires d'ouverture du lundi au dimanche</caption>
                    <tbody>
                    <?php foreach ($horaires as $h): ?>
                        <tr>
                            <th scope="row"><?= e($h['jour']) ?></th>
                            <td>
                                <?= $h['heure_ouverture']
                                    ? e(Horaire::format($h['heure_ouverture'])) . ' – ' . e(Horaire::format($h['heure_fermeture']))
                                    : 'Fermé' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-3">
                <h2 class="titre-pied">Vite &amp; Gourmand</h2>
                <address>
                    12 rue Sainte-Catherine<br>
                    33000 Bordeaux<br>
                    <a href="tel:+33556000000">05 56 00 00 00</a>
                </address>
            </div>
            <div class="col-md-4">
                <h2 class="titre-pied">Informations</h2>
                <ul class="liens-pied">
                    <li><a href="/mentions-legales">Mentions légales</a></li>
                    <li><a href="/cgv">Conditions générales de vente</a></li>
                    <li><a href="/confidentialite">Politique de confidentialité</a></li>
                    <li><a href="/contact">Contact</a></li>
                </ul>
            </div>
        </div>
        <p class="copyright">© <?= date('Y') ?> Vite &amp; Gourmand — Traiteur à Bordeaux</p>
    </div>
</footer>
