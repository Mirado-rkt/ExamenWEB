<?php include __DIR__ . '/../layout/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-arrow-left-right me-2"></i>Dispatch des dons
        </h4>
        <p class="text-muted mb-0 small">
            Attribution automatique des dons aux sinistrés —
            <?php if ($nb_dispatches > 0): ?>
                <span class="text-success fw-semibold"><?= $nb_dispatches ?> attribution(s) effectuée(s)</span>
            <?php else: ?>
                <span class="text-muted">Aucune attribution pour l'instant</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <form method="POST" action="<?= base_url('/dispatch/reinitialiser') ?>" class="d-inline"
              onsubmit="return confirm('ATTENTION : Cette action va réinitialiser TOUTES les données (besoins, dons, dispatch, achats) à l\'état original. Continuer ?')">
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser les données
            </button>
        </form>
        <form method="POST" action="<?= base_url('/dispatch/initialiser') ?>" class="d-inline"
              onsubmit="return confirm('Cette action va supprimer tous les dispatches et achats. Continuer ?')">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-arrow-repeat me-1"></i> Initialiser le dispatch
            </button>
        </form>
    </div>
</div>

<!-- Sélection du mode de dispatch + actions -->
<form method="POST" action="<?= base_url('/dispatch/simuler') ?>">
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="bi bi-gear me-2 text-primary"></i>Mode de dispatch</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label for="dispatch_mode" class="form-label fw-semibold">Sélectionner le mode de distribution</label>
                    <select class="form-select" id="dispatch_mode" name="mode">
                        <option value="prioritaire" <?= ($current_mode ?? 'prioritaire') === 'prioritaire' ? 'selected' : '' ?>>
                            Par date
                        </option>
                        <option value="minoritaire" <?= ($current_mode ?? '') === 'minoritaire' ? 'selected' : '' ?>>
                            Minoritaire
                        </option>
                        <option value="proportionnelle" <?= ($current_mode ?? '') === 'proportionnelle' ? 'selected' : '' ?>>
                            Proportionnelle
                        </option>
                    </select>
                </div>
                <div class="col-md-6 d-flex gap-2 justify-content-end align-items-start mt-3 mt-md-0">
                    <button type="submit" formaction="<?= base_url('/dispatch/simuler') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-play-circle me-1"></i> Simuler
                    </button>
                    <button type="submit" formaction="<?= base_url('/dispatch/valider') ?>" class="btn btn-success"
                            onclick="return confirm('Voulez-vous vraiment valider et appliquer le dispatch ?')">
                        <i class="bi bi-check-circle me-1"></i> Valider le dispatch
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if (!empty($simulation_mode)): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Mode Simulation :</strong> Les résultats ci-dessous montrent une simulation. Cliquez sur "Valider le dispatch" pour appliquer les changements.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Résumé par ville -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Résumé par ville</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Région</th>
                    <th class="text-end">Besoins (valeur)</th>
                    <th class="text-end">Don</th>
                    <th class="text-end">Reste</th>
                    <th style="width: 180px;">Couverture</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($villes)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        <p class="mt-2">Aucune ville avec des besoins enregistrés</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($villes as $v): ?>
                    <?php
                        $reste = (float) $v['total_besoin'] - (float) $v['total_dispatch'];
                        $taux  = taux_couverture((float) $v['total_dispatch'], (float) $v['total_besoin']);
                        $pc    = $taux >= 75 ? 'bg-success' : ($taux >= 40 ? 'bg-warning' : 'bg-danger');
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($v['ville']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= e($v['region']) ?></span></td>
                        <td class="text-end"><?= format_ar((float) $v['total_besoin']) ?></td>
                        <td class="text-end text-success fw-semibold"><?= format_ar((float) $v['total_dispatch']) ?></td>
                        <td class="text-end text-danger"><?= format_ar($reste) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 7px;">
                                    <div class="progress-bar <?= $pc ?>" style="width: <?= $taux ?>%"></div>
                                </div>
                                <small class="fw-bold text-muted" style="min-width: 38px;"><?= $taux ?>%</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Détails des dispatches -->
<?php if (!empty($dispatches)): ?>
<div class="card">
    <div class="card-header bg-white">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Détails des attributions</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Type de besoin</th>
                    <th>Catégorie</th>
                    <th>Donateur</th>
                    <th class="text-end">Qté demandée</th>
                    <th class="text-end">Qté attribuée</th>
                    <th class="text-end">Valeur</th>
                    <th>Date besoin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dispatches as $dp): ?>
                <tr>
                    <td class="fw-semibold"><?= e($dp['ville_nom']) ?></td>
                    <td><?= e($dp['type_nom']) ?></td>
                    <td>
                        <span class="badge <?= categorie_badge($dp['categorie']) ?>">
                            <?= categorie_label($dp['categorie']) ?>
                        </span>
                    </td>
                    <td class="text-muted"><?= e($dp['donateur']) ?></td>
                    <td class="text-end"><?= format_nb((float) $dp['besoin_qte'], 2) ?></td>
                    <td class="text-end"><?= format_nb((float) $dp['quantite'], 2) ?></td>
                    <td class="text-end fw-semibold"><?= format_ar((float) $dp['valeur']) ?></td>
                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($dp['date_besoin'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
