/**
 * Comportements communs à toutes les pages.
 */
(function () {
    'use strict';

    // Demande de confirmation avant une action importante (ex. : annulation de commande)
    document.querySelectorAll('form.form-confirmer').forEach(form => {
        form.addEventListener('submit', evenement => {
            if (!window.confirm(form.dataset.confirmation || 'Confirmer cette action ?')) {
                evenement.preventDefault();
            }
        });
    });
})();
