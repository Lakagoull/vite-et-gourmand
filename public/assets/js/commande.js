/**
 * Formulaire de commande : mise à jour en direct du prix, du minimum de personnes,
 * de la date minimale et des conditions du menu choisi.
 * Le calcul est demandé au serveur (/api/devis) : une seule règle de calcul, côté serveur.
 */
(function () {
    'use strict';

    const form = document.getElementById('form-commande');
    if (!form) return;

    const champMenu = document.getElementById('menu_id');
    const champNb = document.getElementById('nombre_personne');
    const champAdresse = document.getElementById('adresse_prestation');
    const champVille = document.getElementById('ville_prestation');
    const champDate = document.getElementById('date_prestation');
    const $ = (id) => document.getElementById(id);

    let minuteur = null;
    let requete = null;

    /** Informations du menu choisi (option sélectionnée, ou champ caché en modification). */
    function infosMenu() {
        const source = champMenu.tagName === 'SELECT' ? champMenu.selectedOptions[0] : champMenu;
        if (!source || !champMenu.value) return null;
        return {
            min: parseInt(source.dataset.min, 10) || 1,
            delai: Math.max(1, parseInt(source.dataset.delai, 10) || 1),
            conditions: source.dataset.conditions
        };
    }

    function formatDate(d) {
        return d.toISOString().slice(0, 10);
    }

    /** Met à jour les contraintes du formulaire selon le menu. */
    function appliquerMenu(changementDeMenu) {
        const menu = infosMenu();
        if (!menu) return;

        champNb.min = menu.min;
        $('nb-min').textContent = menu.min;
        if (changementDeMenu && (parseInt(champNb.value, 10) || 0) < menu.min) {
            champNb.value = menu.min;
        }

        const dateMin = new Date();
        dateMin.setDate(dateMin.getDate() + menu.delai);
        champDate.min = formatDate(dateMin);
        $('date-min-texte').textContent = dateMin.toLocaleDateString('fr-FR');

        const bloc = $('bloc-conditions');
        if (bloc && menu.conditions !== undefined) {
            $('texte-conditions').textContent = menu.conditions;
            bloc.classList.toggle('d-none', !menu.conditions);
        }
    }

    async function calculer() {
        if (!champMenu.value) return;
        const params = new URLSearchParams({
            menu_id: champMenu.value,
            nombre_personne: champNb.value || 0,
            adresse: champAdresse.value,
            ville: champVille.value
        });

        if (requete) requete.abort();
        requete = new AbortController();
        try {
            const rep = await fetch('/api/devis?' + params, { signal: requete.signal, headers: { Accept: 'application/json' } });
            if (!rep.ok) return;
            const d = await rep.json();
            const t = d.tarif;

            $('recap-menu').textContent = d.textes.prix_menu;
            $('recap-detail-menu').textContent = '(' + (champNb.value || 0) + ' pers.)';
            $('ligne-remise').classList.toggle('d-none', t.remise <= 0);
            $('recap-remise').textContent = d.textes.remise;
            $('recap-livraison').textContent = d.textes.prix_livraison;
            $('recap-total').textContent = d.textes.prix_total;

            const verif = $('adresse-verifiee');
            if (champAdresse.value.trim().length >= 5 && champVille.value.trim()) {
                if (d.adresse_trouvee) {
                    verif.textContent = 'Adresse reconnue : ' + d.adresse + (t.bordeaux ? ' — livraison offerte' : ' — ' + String(t.distance_km).replace('.', ',') + ' km');
                    $('recap-detail-livraison').textContent = t.bordeaux ? '(Bordeaux)' : '(' + String(t.distance_km).replace('.', ',') + ' km)';
                } else {
                    verif.textContent = 'Adresse non reconnue : vérifiez le numéro, la rue et le code postal.';
                    $('recap-detail-livraison').textContent = '';
                }
            }

            $('recap-remise-info').textContent = t.remise > 0
                ? 'Réduction de 10 % appliquée.'
                : 'Astuce : 10 % de réduction à partir de ' + t.seuil_remise + ' personnes.';
            if (d.sous_minimum) {
                $('recap-remise-info').textContent = 'Ce menu se commande pour ' + d.minimum + ' personnes minimum.';
            }
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        }
    }

    function planifier() {
        clearTimeout(minuteur);
        minuteur = setTimeout(calculer, 400);
    }

    if (champMenu.tagName === 'SELECT') {
        champMenu.addEventListener('change', () => { appliquerMenu(true); calculer(); });
    }
    form.querySelectorAll('[data-devis]').forEach(champ => champ.addEventListener('input', planifier));

    appliquerMenu(false);
    calculer();
})();
