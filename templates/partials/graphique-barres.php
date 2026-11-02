<?php
/**
 * Graphique en barres horizontales (SVG généré côté serveur, une seule série).
 * Variables : $id, $titre, $sousTitre, $lignes = [['label' => ..., 'valeur' => float, 'texte' => '...'], ...]
 *
 * Choix : une seule série = une seule couleur (bordeaux de la charte) et pas de légende ;
 * barres fines à extrémité arrondie, grille discrète, valeur affichée en bout de barre ;
 * infobulle au survol et au clavier (graphique.js) ; tableau de données équivalent sur la page.
 * Deux mises en page : "large" (libellé à gauche) et "étroite" pour mobile (libellé au-dessus).
 */

// Graduation "ronde" : 1, 2, 2,5 ou 5 x 10^n
$max = (float) max(array_column($lignes, 'valeur') ?: [0]);
if ($max <= 0) {
    $echelle = 1;
    $graduations = [0, 1];
} else {
    $brut = $max / 4;
    $puissance = 10 ** floor(log10($brut));
    $pas = 10 * $puissance;
    foreach ([1, 2, 2.5, 5, 10] as $m) {
        if ($m * $puissance >= $brut) {
            $pas = $m * $puissance;
            break;
        }
    }
    $echelle = ceil($max / $pas) * $pas;
    $graduations = range(0, $echelle, $pas);
}

/** Dessine le graphique pour une mise en page donnée. */
$dessiner = function (bool $etroit) use ($id, $titre, $lignes, $echelle, $graduations): void {
    $largeur = $etroit ? 360 : 820;
    $xBarre = $etroit ? 0 : 250;
    $largeurBarres = $etroit ? 260 : 470;
    $hauteurLigne = $etroit ? 50 : 40;
    $epaisseur = $etroit ? 14 : 16;
    $haut = 8;
    $hauteur = $haut + count($lignes) * $hauteurLigne + 30;
    $x = fn (float $v) => $xBarre + ($v / $echelle) * $largeurBarres;
    $suffixe = $etroit ? '-etroit' : '';
    ?>
    <svg viewBox="0 0 <?= $largeur ?> <?= $hauteur ?>" width="100%" role="img" aria-labelledby="<?= e($id . $suffixe) ?>-desc"
         class="graphique-svg <?= $etroit ? 'version-etroite' : 'version-large' ?>">
        <desc id="<?= e($id . $suffixe) ?>-desc"><?= e($titre) ?> : <?= e(implode(' ; ', array_map(fn ($l) => $l['label'] . ' ' . $l['texte'], $lignes))) ?></desc>

        <?php foreach ($graduations as $g): $gx = round($x((float) $g), 1); ?>
            <line x1="<?= $gx ?>" x2="<?= $gx ?>" y1="<?= $haut ?>" y2="<?= $hauteur - 26 ?>" class="grille<?= $g == 0 ? ' axe' : '' ?>"/>
            <text x="<?= $gx ?>" y="<?= $hauteur - 8 ?>" class="graduation" text-anchor="<?= $g == 0 ? 'start' : 'middle' ?>"><?= e(number_format((float) $g, 0, ',', ' ')) ?></text>
        <?php endforeach; ?>

        <?php foreach ($lignes as $i => $l):
            $yLigne = $haut + $i * $hauteurLigne;
            $y = $etroit ? $yLigne + 24 : $yLigne + ($hauteurLigne - $epaisseur) / 2;
            $w = max(0, round($x((float) $l['valeur']) - $xBarre, 1));
            $limite = $etroit ? 40 : 30;
            $label = mb_strlen($l['label']) > $limite ? mb_substr($l['label'], 0, $limite - 1) . '…' : $l['label'];
        ?>
            <?php if ($etroit): ?>
                <text x="0" y="<?= $yLigne + 14 ?>" class="libelle-barre"><?= e($label) ?></text>
            <?php else: ?>
                <text x="0" y="<?= $y + $epaisseur / 2 ?>" dominant-baseline="middle" class="libelle-barre"><?= e($label) ?></text>
            <?php endif; ?>
            <g class="barre" tabindex="0" role="img" aria-label="<?= e($l['label'] . ' : ' . $l['texte']) ?>" data-info="<?= e($l['label'] . ' — ' . $l['texte']) ?>">
                <!-- zone de survol plus grande que la barre -->
                <rect x="<?= $xBarre ?>" y="<?= $y - ($etroit ? 6 : 10) ?>" width="<?= $largeurBarres ?>" height="<?= $epaisseur + ($etroit ? 12 : 20) ?>" class="zone-survol"/>
                <?php if ($w > 0): ?>
                    <rect x="<?= $xBarre ?>" y="<?= $y ?>" width="<?= $w ?>" height="<?= $epaisseur ?>" rx="4" class="remplissage"/>
                    <rect x="<?= $xBarre ?>" y="<?= $y ?>" width="<?= min(4, $w) ?>" height="<?= $epaisseur ?>" class="remplissage"/>
                <?php endif; ?>
                <title><?= e($l['label'] . ' : ' . $l['texte']) ?></title>
            </g>
            <text x="<?= $xBarre + $w + 8 ?>" y="<?= $y + $epaisseur / 2 ?>" dominant-baseline="middle" class="valeur-barre"><?= e($l['texte']) ?></text>
        <?php endforeach; ?>
    </svg>
    <?php
};
?>
<figure class="graphique carte" id="<?= e($id) ?>">
    <figcaption>
        <h2 class="h3 mb-1"><?= e($titre) ?></h2>
        <?php if (!empty($sousTitre)): ?><p class="text-aide mb-3"><?= e($sousTitre) ?></p><?php endif; ?>
    </figcaption>
    <div class="graphique-zone">
        <?php $dessiner(false); $dessiner(true); ?>
        <div class="infobulle" role="status" hidden></div>
    </div>
</figure>
