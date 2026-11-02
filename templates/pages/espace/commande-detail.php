<?php
use App\Models\Commande;
$enAttente = $c['statut'] === 'en_attente';
// Le suivi est accessible une fois la commande acceptée
$suiviVisible = !in_array($c['statut'], ['en_attente'], true);
?>
<div class="container py-4 pb-5">
    <nav aria-label="Fil d'Ariane" class="fil pt-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/espace">Mon espace</a></li>
            <li class="breadcrumb-item active" aria-current="page">Commande <?= e($c['numero_commande']) ?></li>
        </ol>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h1 class="mb-0">Commande <?= e($c['numero_commande']) ?></h1>
        <span class="statut statut-<?= e($c['statut']) ?> statut-grand"><?= e(Commande::libelleStatut($c['statut'])) ?></span>
    </div>
    <p class="text-aide">Passée le <?= e(date('d/m/Y à H\hi', strtotime($c['date_commande']))) ?></p>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <section class="carte mb-4" aria-labelledby="titre-prestation">
                <h2 id="titre-prestation" class="h3">Prestation</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Menu</dt><dd class="col-sm-8"><?= e($c['menu_titre']) ?></dd>
                    <dt class="col-sm-4">Date</dt><dd class="col-sm-8"><?= e(date('d/m/Y', strtotime($c['date_prestation']))) ?> à <?= e(str_replace(':', 'h', substr($c['heure_livraison'], 0, 5))) ?></dd>
                    <dt class="col-sm-4">Adresse</dt><dd class="col-sm-8"><?= e($c['adresse_prestation']) ?>, <?= e($c['ville_prestation']) ?></dd>
                    <dt class="col-sm-4">Personnes</dt><dd class="col-sm-8"><?= (int) $c['nombre_personne'] ?></dd>
                    <dt class="col-sm-4">Client</dt><dd class="col-sm-8"><?= e($c['prenom'] . ' ' . $c['nom']) ?> — <?= e($c['email']) ?> — <?= e($c['telephone'] ?? '') ?></dd>
                    <?php if ($c['pret_materiel']): ?>
                        <dt class="col-sm-4">Matériel prêté</dt><dd class="col-sm-8"><?= $c['restitution_materiel'] ? 'Restitué' : 'À restituer sous 10 jours ouvrés (sinon 600 € de frais)' ?></dd>
                    <?php endif; ?>
                    <?php if ($c['statut'] === 'annulee' && $c['motif_annulation']): ?>
                        <dt class="col-sm-4">Motif d'annulation</dt><dd class="col-sm-8"><?= e($c['motif_annulation']) ?></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <?php if ($c['conditions']): ?>
                <section class="conditions mb-4" aria-labelledby="titre-cond">
                    <h2 id="titre-cond" class="titre-alerte"><span class="pastille-alerte" aria-hidden="true">!</span> Conditions du menu</h2>
                    <p><?= e($c['conditions']) ?></p>
                </section>
            <?php endif; ?>

            <?php if ($suiviVisible): ?>
                <section class="carte mb-4" aria-labelledby="titre-suivi">
                    <h2 id="titre-suivi" class="h3">Suivi de la commande</h2>
                    <ol class="suivi">
                        <?php foreach ($c['suivi'] as $etape): ?>
                            <li>
                                <strong><?= e(Commande::libelleStatut($etape['statut'])) ?></strong>
                                <time datetime="<?= e(date('c', strtotime($etape['date_modification']))) ?>"><?= e(date('d/m/Y à H\hi', strtotime($etape['date_modification']))) ?></time>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>
            <?php endif; ?>

            <?php if ($c['statut'] === 'terminee'): ?>
                <section class="carte mb-4" id="avis" aria-labelledby="titre-avis">
                    <h2 id="titre-avis" class="h3">Votre avis</h2>
                    <?php if ($c['avis']): ?>
                        <div class="etoiles" role="img" aria-label="Note : <?= (int) $c['avis']['note'] ?> sur 5"><?= str_repeat('★', (int) $c['avis']['note']) . str_repeat('☆', 5 - (int) $c['avis']['note']) ?></div>
                        <p>« <?= e($c['avis']['description']) ?> »</p>
                        <p class="text-aide mb-0">
                            <?= ['en_attente' => 'En cours de vérification par notre équipe.', 'valide' => 'Publié sur notre site. Merci !', 'refuse' => 'Cet avis n\'a pas été retenu pour publication.'][$c['avis']['statut']] ?? '' ?>
                        </p>
                    <?php else: ?>
                        <p>Votre commande est terminée : dites-nous ce que vous en avez pensé !</p>
                        <form method="post" action="/espace/avis" novalidate>
                            <?= csrfField() ?>
                            <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
                            <fieldset class="mb-3">
                                <legend class="form-label">Note <span class="text-aide">(de 1 à 5)</span></legend>
                                <div class="choix-note">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" class="btn-check" name="note" id="note-<?= $i ?>" value="<?= $i ?>" required>
                                        <label class="btn btn-secondaire" for="note-<?= $i ?>"><?= $i ?> <span aria-hidden="true">★</span><span class="visually-hidden"> sur 5</span></label>
                                    <?php endfor; ?>
                                </div>
                                <?= msgErreur($erreurs, 'note') ?>
                            </fieldset>
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire</label>
                                <textarea class="form-control<?= attrErreur($erreurs, 'commentaire') ?>" id="commentaire" name="commentaire" rows="4" required minlength="10" maxlength="1000"><?= old('commentaire') ?></textarea>
                                <?= msgErreur($erreurs, 'commentaire') ?>
                            </div>
                            <button type="submit" class="btn btn-primaire">Envoyer mon avis</button>
                        </form>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <section class="carte recap-prix mb-4" aria-labelledby="titre-prix">
                <h2 id="titre-prix" class="h3">Détail du prix</h2>
                <dl class="mb-0">
                    <div class="ligne-prix"><dt>Menu <span class="text-aide">(<?= (int) $c['nombre_personne'] ?> × <?= prix($c['prix_par_personne']) ?>)</span></dt><dd><?= prix($c['prix_menu']) ?></dd></div>
                    <?php if ((float) $c['remise'] > 0): ?>
                        <div class="ligne-prix remise"><dt>Réduction 10 %</dt><dd>− <?= prix($c['remise']) ?></dd></div>
                    <?php endif; ?>
                    <div class="ligne-prix"><dt>Livraison <?= (float) $c['distance_km'] > 0 ? '<span class="text-aide">(' . e(number_format((float) $c['distance_km'], 1, ',', ' ')) . ' km)</span>' : '<span class="text-aide">(Bordeaux)</span>' ?></dt><dd><?= prix($c['prix_livraison']) ?></dd></div>
                    <div class="ligne-prix total"><dt>Total</dt><dd><?= prix($c['prix_total']) ?></dd></div>
                </dl>
            </section>

            <?php if ($enAttente): ?>
                <section class="carte" aria-labelledby="titre-actions">
                    <h2 id="titre-actions" class="h3">Modifier ou annuler</h2>
                    <p class="text-aide">Possible tant que notre équipe n'a pas accepté la commande.</p>
                    <a href="/espace/commande/modifier?id=<?= (int) $c['commande_id'] ?>" class="btn btn-secondaire w-100 mb-2">Modifier la commande</a>
                    <form method="post" action="/espace/commande/annuler" class="form-confirmer" data-confirmation="Voulez-vous vraiment annuler cette commande ?">
                        <?= csrfField() ?>
                        <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
                        <button type="submit" class="btn btn-danger-vg w-100">Annuler la commande</button>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
