<?php
/**
 * Fonctions utilitaires disponibles partout dans l'application.
 */

declare(strict_types=1);

/** Lit une variable de configuration. */
function env(string $cle, ?string $defaut = null): ?string
{
    $valeur = getenv($cle);
    return $valeur === false ? $defaut : $valeur;
}

/** Échappe une valeur pour l'afficher sans risque dans le HTML (protection XSS). */
function e(mixed $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Affiche un gabarit dans la mise en page générale. */
function render(string $gabarit, array $donnees = [], string $layout = 'layout'): void
{
    extract($donnees, EXTR_SKIP);
    ob_start();
    require ROOT . "/templates/pages/$gabarit.php";
    $contenu = ob_get_clean();
    require ROOT . "/templates/$layout.php";
}

/** Inclut un morceau de gabarit réutilisable. */
function partial(string $nom, array $donnees = []): void
{
    extract($donnees, EXTR_SKIP);
    require ROOT . "/templates/partials/$nom.php";
}

/** Redirige vers une autre page puis arrête le script. */
function redirect(string $chemin): void
{
    header('Location: ' . $chemin);
    exit;
}

/** Envoie une réponse JSON. */
function json(mixed $donnees, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Formate un prix : 252.5 -> "252,50 €" */
function prix(float|string $montant): string
{
    return number_format((float) $montant, 2, ',', ' ') . ' €';
}

/** Indique si le lien correspond à la page courante (menu actif). */
function estActif(string $chemin): bool
{
    $courant = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return $chemin === '/' ? $courant === '/' : str_starts_with($courant, $chemin);
}

// ---------------------------------------------------------------------
// Protection CSRF : un jeton secret par session, vérifié à chaque formulaire
// ---------------------------------------------------------------------

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function verifierCsrf(): void
{
    $jeton = $_POST['csrf'] ?? '';
    if (!is_string($jeton) || !hash_equals(csrfToken(), $jeton)) {
        http_response_code(419);
        render('erreur', ['titre' => 'Session expirée', 'message' => 'Le formulaire a expiré. Merci de recharger la page et de réessayer.']);
        exit;
    }
}

// ---------------------------------------------------------------------
// Messages flash : affichés une seule fois après une redirection
// ---------------------------------------------------------------------

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function lireFlash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Conserve les valeurs saisies pour réafficher un formulaire après une erreur. */
function old(string $champ, string $defaut = ''): string
{
    return e($_SESSION['old'][$champ] ?? $defaut);
}

// ---------------------------------------------------------------------
// Erreurs de formulaire accessibles (RGAA : erreur liée au champ)
// ---------------------------------------------------------------------

/** Classe et attributs ARIA d'un champ en erreur. */
function attrErreur(array $erreurs, string $champ): string
{
    return isset($erreurs[$champ])
        ? ' is-invalid" aria-invalid="true" aria-describedby="erreur-' . e($champ)
        : '';
}

/** Message d'erreur affiché sous le champ. */
function msgErreur(array $erreurs, string $champ): string
{
    return isset($erreurs[$champ])
        ? '<div id="erreur-' . e($champ) . '" class="invalid-feedback d-block">' . e($erreurs[$champ]) . '</div>'
        : '';
}

/**
 * Adresse IP du visiteur. Derrière le proxy de l'hébergeur (TRUST_PROXY=1), l'IP réelle
 * est transmise dans l'en-tête X-Forwarded-For. Cet en-tête peut être falsifié par un client :
 * il ne sert qu'au limiteur de tentatives par IP, la limite par e-mail restant la protection principale.
 */
function ipClient(): string
{
    if (env('TRUST_PROXY') === '1' && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'inconnue';
}
