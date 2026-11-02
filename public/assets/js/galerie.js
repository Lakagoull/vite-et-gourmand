/**
 * Galerie d'images de la vue détaillée : clic sur une vignette = affichage en grand.
 */
(function () {
    'use strict';

    const principale = document.getElementById('image-principale');
    const vignettes = document.querySelectorAll('.vignette');
    if (!principale) return;

    vignettes.forEach(bouton => {
        bouton.addEventListener('click', () => {
            principale.src = bouton.dataset.src;
            principale.alt = bouton.dataset.alt;
            vignettes.forEach(v => {
                v.classList.remove('selection');
                v.setAttribute('aria-pressed', 'false');
            });
            bouton.classList.add('selection');
            bouton.setAttribute('aria-pressed', 'true');
        });
    });
})();
