<?php
use App\Models\Commande;
$modifiable = in_array($c['statut'], Commande::STATUTS_MODIFIABLES, true);
$contact = ['telephone' => 'Téléphone', 'mail' => 'E-mail'];
?>
<div class="container py-4 pb-5">
    <?php partial('gestion-nav', ['actif' => 'commandes']); ?>
    <nav aria-label="Fil d'Ariane" class="fil pt-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/employe/commandes">Commandes</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($c['numero_commande']) ?></li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h1 class="mb-0">Commande <?= e($c['numero_commande']) ?></h1>
        <span class="statut statut-<?= e($c['statut']) ?> statut-grand"><?= e(Commande::libelleStatut($c['statut'])) ?></span>
    </div>
    <p class="text-aide">Passée le <?= e(date('d/m/Y à H\hi', strtotime($c['date_commande']))) ?></p>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="carte mb-4" aria-labelledby="t-client">
                <h2 id="t-client" class="h3">Client</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nom</dt><dd class="col-sm-8"><?= e($c['prenom'] . ' ' . $c['nom']) ?></dd>
                    <dt class="col-sm-4">Mobile</dt><dd class="col-sm-8"><a href="tel:<?= e(preg_replace('/[^\d+]/', '', (string) $c['telephone'])) ?>"><?= e($c['telephone'] ?? '—') ?></a></dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8"><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></dd>
                </dl>
            </section>

            <section class="carte mb-4" aria-labelledby="t-presta">
                <h2 id="t-presta" class="h3">Prestation</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Menu</dt><dd class="col-sm-8"><?= e($c['menu_titre']) ?></dd>
                    <dt class="col-sm-4">Date</dt><dd class="col-sm-8"><?= e(date('d/m/Y', strtotime($c['date_prestation']))) ?> à <?= e(substr($c['heure_livraison'], 0, 5)) ?></dd>
                    <dt class="col-sm-4">Adresse</dt><dd class="col-sm-8"><?= e($c['adresse_prestation']) ?>, <?= e($c['ville_prestation']) ?></dd>
                    <dt class="col-sm-4">Personnes</dt><dd class="col-sm-8"><?= (int) $c['nombre_personne'] ?></dd>
                    <dt class="col-sm-4">Prix</dt><dd class="col-sm-8"><?= prix($c['prix_menu']) ?><?= (float) $c['remise'] > 0 ? ' − ' . prix($c['remise']) . ' (remise)' : '' ?> + <?= prix($c['prix_livraison']) ?> (livraison) = <strong><?= prix($c['prix_total']) ?></strong></dd>
                    <dt class="col-sm-4">Matériel prêté</dt><dd class="col-sm-8"><?= $c['pret_materiel'] ? ($c['restitution_materiel'] ? 'Oui — restitué' : 'Oui — à restituer') : 'Non' ?></dd>
                    <?php if ($c['statut'] === 'annulee'): ?>
                        <dt class="col-sm-4">Annulation</dt>
                        <dd class="col-sm-8"><?= e($c['motif_annulation'] ?? '') ?><?= $c['mode_contact_annulation'] ? ' <span class="text-aide">(client contacté par ' . e(mb_strtolower($contact[$c['mode_contact_annulation']])) . ')</span>' : '' ?></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <section class="carte mb-4" aria-labelledby="t-suivi">
                <h2 id="t-suivi" class="h3">Historique</h2>
                <ol class="suivi">
                    <?php foreach ($c['suivi'] as $etape): ?>
                        <li><strong><?= e(Commande::libelleStatut($etape['statut'])) ?></strong>
                            <time datetime="<?= e(date('c', strtotime($etape['date_modification']))) ?>"><?= e(date('d/m/Y à H\hi', strtotime($etape['date_modification']))) ?></time></li>
                    <?php endforeach; ?>
                </ol>
            </section>
        </div>

        <div class="col-lg-5">
            <?php if ($etapes): ?>
                <section class="carte mb-4" aria-labelledby="t-statut">
                    <h2 id="t-statut" class="h3">Mettre à jour le statut</h2>
                    <form method="post" action="/employe/commande/statut">
                        <?= csrfField() ?>
                        <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
                        <?php if ($etapes === ['livre']): ?>
                            <input type="hidden" name="statut" value="livre">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="pret_materiel" name="pret_materiel" value="1">
                                <label class="form-check-label" for="pret_materiel">Du matériel a été prêté au client</label>
                            </div>
                            <button type="submit" class="btn btn-primaire w-100">Passer à « Livrée »</button>
                        <?php else: ?>
                            <?php foreach ($etapes as $etape): ?>
                                <button type="submit" name="statut" value="<?= e($etape) ?>" class="btn btn-primaire w-100 mb-2">Passer à « <?= e(Commande::libelleStatut($etape)) ?> »</button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (in_array('attente_retour_materiel', $etapes, true)): ?>
                            <p class="text-aide mb-0">Le client recevra un e-mail : matériel à rendre sous 10 jours ouvrés, sinon 600 € de frais.</p>
                        <?php elseif (in_array('terminee', $etapes, true)): ?>
                            <p class="text-aide mb-0">Le client recevra un e-mail l'invitant à donner son avis.</p>
                        <?php endif; ?>
                    </form>
                </section>
            <?php endif; ?>

            <?php if ($modifiable): ?>
                <section class="carte mb-4" aria-labelledby="t-modif">
                    <h2 id="t-modif" class="h3">Modifier la commande</h2>
                    <p class="text-aide">Uniquement après avoir contacté le client par téléphone ou par e-mail.</p>
                    <a href="/employe/commande/modifier?id=<?= (int) $c['commande_id'] ?>" class="btn btn-secondaire w-100">Modifier</a>
                </section>

                <section class="carte" id="annulation" aria-labelledby="t-annul">
                    <h2 id="t-annul" class="h3">Annuler la commande</h2>
                    <p class="text-aide">Contactez d'abord le client, puis indiquez le mode de contact et le motif.</p>
                    <form method="post" action="/employe/commande/annuler" class="form-confirmer" data-confirmation="Confirmer l'annulation de cette commande ?" novalidate>
                        <?= csrfField() ?>
                        <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
                        <?php partial('champs-contact-client', ['erreurs' => $erreurs, 'motifLabel' => 'Motif de l\'annulation']); ?>
                        <button type="submit" class="btn btn-danger-vg w-100 mt-3">Annuler la commande</button>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
