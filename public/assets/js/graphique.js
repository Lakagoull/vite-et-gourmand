/**
 * Infobulle des graphiques : au survol de la souris ET au focus clavier (Tab).
 */
(function () {
    'use strict';

    document.querySelectorAll('.graphique').forEach(graphique => {
        const zone = graphique.querySelector('.graphique-zone');
        const bulle = graphique.querySelector('.infobulle');
        if (!zone || !bulle) return;

        function afficher(barre, x, y) {
            bulle.textContent = barre.dataset.info;
            bulle.hidden = false;
            const cadre = zone.getBoundingClientRect();
            // Position près du pointeur, sans sortir du graphique
            const gauche = Math.min(Math.max(x - cadre.left + 12, 0), cadre.width - bulle.offsetWidth);
            bulle.style.left = gauche + 'px';
            bulle.style.top = Math.max(y - cadre.top - bulle.offsetHeight - 10, 0) + 'px';
        }
        function masquer() { bulle.hidden = true; }

        graphique.querySelectorAll('.barre').forEach(barre => {
            barre.addEventListener('mousemove', e => afficher(barre, e.clientX, e.clientY));
            barre.addEventListener('mouseleave', masquer);
            barre.addEventListener('focus', () => {
                const r = barre.querySelector('.remplissage, .zone-survol').getBoundingClientRect();
                afficher(barre, r.right, r.top);
            });
            barre.addEventListener('blur', masquer);
            barre.addEventListener('keydown', e => { if (e.key === 'Escape') masquer(); });
        });
    });
})();
