/**
 * Vue globale des menus : filtrage dynamique sans rechargement de page.
 * Les filtres sont envoyés à l'API /api/menus (fetch), puis la liste est reconstruite.
 * Sans JavaScript, le formulaire fonctionne quand même (envoi classique en GET).
 */
(function () {
    'use strict';

    const formulaire = document.getElementById('form-filtres');
    const liste = document.getElementById('liste-menus');
    const compteur = document.getElementById('nb-resultats');
    const aucun = document.getElementById('aucun-menu');
    const boutonReset = document.getElementById('reinitialiser');
    if (!formulaire || !liste) return;

    let minuteur = null;
    let requeteEnCours = null;

    /** Construit la chaîne de paramètres à partir des champs remplis. */
    function parametres() {
        const params = new URLSearchParams();
        new FormData(formulaire).forEach((valeur, cle) => {
            if (String(valeur).trim() !== '') params.append(cle, String(valeur).trim());
        });
        return params;
    }

    /** Crée un élément HTML avec classe et texte (textContent = pas d'injection XSS). */
    function el(balise, classe, texte) {
        const e = document.createElement(balise);
        if (classe) e.className = classe;
        if (texte !== undefined) e.textContent = texte;
        return e;
    }

    function carte(menu) {
        const col = el('div', 'col-md-6 col-xl-4');
        const article = el('article', 'carte-menu h-100');

        if (menu.image) {
            const img = el('img', 'carte-menu-img');
            img.src = menu.image;
            img.alt = '';
            img.loading = 'lazy';
            img.width = 900;
            img.height = 600;
            article.appendChild(img);
        }

        const corps = el('div', 'corps');
        const badges = el('div', 'badges');
        const badgeTheme = el('span', 'badge-vg');
        badgeTheme.append(el('span', 'visually-hidden', 'Thème : '), menu.theme);
        const badgeRegime = el('span', 'badge-vg regime');
        badgeRegime.append(el('span', 'visually-hidden', 'Régime : '), menu.regime);
        badges.append(badgeTheme, badgeRegime);

        const infos = el('div', 'infos-menu');
        const mini = el('div');
        mini.append(el('small', '', 'Minimum'), el('strong', '', menu.nb_minimum + ' personnes'));
        infos.append(mini, el('div', 'prix', menu.prix_texte));

        const lien = el('a', 'btn btn-primaire w-100', 'Voir le détail');
        lien.href = '/menu?id=' + encodeURIComponent(menu.id);
        lien.appendChild(el('span', 'visually-hidden', ' du ' + menu.titre));

        corps.append(badges, el('h3', '', menu.titre), el('p', '', menu.description), infos, lien);
        article.appendChild(corps);
        col.appendChild(article);
        return col;
    }

    function afficher(menus) {
        liste.replaceChildren(...menus.map(carte));
        const n = menus.length;
        compteur.innerHTML = '';
        compteur.append(el('strong', '', n + ' menu' + (n > 1 ? 's' : '')),
            ' ' + (n > 1 ? 'correspondent' : 'correspond') + ' à votre recherche');
        aucun.classList.toggle('d-none', n > 0);
    }

    async function filtrer() {
        const params = parametres();
        // Met à jour l'adresse de la page (lien partageable) sans la recharger
        history.replaceState(null, '', '/menus' + (params.toString() ? '?' + params : ''));

        if (requeteEnCours) requeteEnCours.abort();
        requeteEnCours = new AbortController();
        liste.setAttribute('aria-busy', 'true');
        try {
            const reponse = await fetch('/api/menus?' + params, {
                headers: { 'Accept': 'application/json' },
                signal: requeteEnCours.signal
            });
            if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
            afficher(await reponse.json());
        } catch (erreur) {
            if (erreur.name !== 'AbortError') {
                compteur.textContent = 'Impossible de charger les menus. Merci de réessayer.';
            }
        } finally {
            liste.removeAttribute('aria-busy');
        }
    }

    // Filtrage à chaque saisie, avec un léger délai pour ne pas surcharger le serveur
    formulaire.addEventListener('input', () => {
        clearTimeout(minuteur);
        minuteur = setTimeout(filtrer, 300);
    });
    formulaire.addEventListener('submit', (evenement) => {
        evenement.preventDefault();
        filtrer();
    });
    boutonReset.addEventListener('click', (evenement) => {
        evenement.preventDefault();
        formulaire.reset();
        formulaire.querySelectorAll('input').forEach(champ => { champ.value = ''; });
        formulaire.querySelectorAll('select').forEach(champ => { champ.selectedIndex = 0; });
        filtrer();
    });
})();
