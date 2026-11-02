/**
 * Aide à la saisie du mot de passe : les règles se cochent au fur et à mesure.
 * La vérification définitive est toujours faite côté serveur.
 */
(function () {
    'use strict';

    const champ = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const afficher = document.getElementById('afficher-mdp');
    if (!champ) return;

    const regles = {
        longueur:  v => v.length >= 10,
        majuscule: v => /[A-Z]/.test(v),
        minuscule: v => /[a-z]/.test(v),
        chiffre:   v => /\d/.test(v),
        special:   v => /[^A-Za-z\d]/.test(v)
    };

    function verifier() {
        Object.entries(regles).forEach(([nom, test]) => {
            const ligne = document.querySelector('[data-regle="' + nom + '"]');
            if (!ligne) return;
            const ok = test(champ.value);
            ligne.classList.toggle('ok', ok);
            // Texte lu par les lecteurs d'écran
            let etat = ligne.querySelector('.visually-hidden');
            if (!etat) {
                etat = document.createElement('span');
                etat.className = 'visually-hidden';
                ligne.appendChild(etat);
            }
            etat.textContent = ok ? ' (respecté)' : ' (non respecté)';
        });
    }

    champ.addEventListener('input', verifier);
    verifier();

    if (afficher) {
        afficher.addEventListener('change', () => {
            const type = afficher.checked ? 'text' : 'password';
            champ.type = type;
            if (confirmation) confirmation.type = type;
        });
    }
})();
