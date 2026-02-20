<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-clipboard-data me-2"></i>Récapitulation
        </h4>
        <p class="text-muted mb-0 small">
            Vue d'ensemble des besoins, dons et dispatches
        </p>
    </div>
    <a href="<?= base_url('/recap') ?>" class="btn btn-primary">
        <i class="bi bi-arrow-clockwise me-1"></i> Actualiser
    </a>
</div>

<!-- Cartes de résumé -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2"><i class="bi bi-card-checklist me-1"></i> Total Besoins</h6>
                <h3 class="fw-bold text-primary"><?= format_ar($total_besoins) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2"><i class="bi bi-check-circle me-1"></i> Besoins Satisfaits</h6>
                <h3 class="fw-bold text-success"><?= format_ar($total_satisfait) ?></h3>
                <div class="small text-muted">
                    <span>Dispatch: <?= format_ar($total_dispatch) ?></span> | 
                    <span>Achats: <?= format_ar($total_achats) ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-danger">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2"><i class="bi bi-exclamation-circle me-1"></i> Besoins Restants</h6>
                <h3 class="fw-bold text-danger"><?= format_ar($total_restant) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Barre de progression -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold">Taux de couverture global</span>
            <span class="fw-bold"><?= $taux_couverture ?>%</span>
        </div>
        <div class="progress" style="height: 25px;">
            <?php
            $progress_class = 'bg-danger';
            if ($taux_couverture >= 75) $progress_class = 'bg-success';
            elseif ($taux_couverture >= 40) $progress_class = 'bg-warning';
            ?>
            <div class="progress-bar <?= $progress_class ?>" role="progressbar" style="width: <?= $taux_couverture ?>%;">
                <?= $taux_couverture ?>%
            </div>
        </div>
    </div>
</div>

<!-- Détail par type de besoin -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="fw-bold mb-0"><i class="bi bi-tags me-2 text-primary"></i>Détail par type de besoin</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Catégorie</th>
                    <th class="text-end">Besoin total</th>
                    <th class="text-end">Dispatché</th>
                    <th class="text-end">Acheté</th>
                    <th class="text-end">Restant</th>
                    <th style="width: 150px;">Couverture</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($details_types)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Aucune donnée</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($details_types as $t): ?>
                    <?php
                    $besoin_val = (float) $t['total_besoin_valeur'];
                    $dispatch_val = (float) $t['total_dispatch_valeur'];
                    $achat_val = (float) $t['total_achat_valeur'];
                    $restant_val = $besoin_val - $dispatch_val - $achat_val;
                    if ($restant_val < 0) $restant_val = 0;
                    $taux = $besoin_val > 0 ? round((($dispatch_val + $achat_val) / $besoin_val) * 100) : 0;
                    $taux_class = 'bg-danger';
                    if ($taux >= 75) $taux_class = 'bg-success';
                    elseif ($taux >= 40) $taux_class = 'bg-warning';
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($t['nom']) ?></td>
                        <td>
                            <span class="badge <?= categorie_badge($t['categorie']) ?>">
                                <?= categorie_label($t['categorie']) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= format_ar($besoin_val) ?></td>
                        <td class="text-end text-success"><?= format_ar($dispatch_val) ?></td>
                        <td class="text-end text-info"><?= format_ar($achat_val) ?></td>
                        <td class="text-end text-danger"><?= format_ar($restant_val) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 7px;">
                                    <div class="progress-bar <?= $taux_class ?>" style="width: <?= $taux ?>%"></div>
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

<!-- Détail par ville -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>Détail par ville</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Région</th>
                    <th class="text-end">Besoin total</th>
                    <th class="text-end">Dispatché</th>
                    <th class="text-end">Acheté</th>
                    <th class="text-end">Restant</th>
                    <th style="width: 150px;">Couverture</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($details_villes)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Aucune donnée</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($details_villes as $v): ?>
                    <?php
                    $besoin_v = (float) $v['total_besoin'];
                    $dispatch_v = (float) $v['total_dispatch'];
                    $achat_v = (float) $v['total_achat'];
                    $restant_v = $besoin_v - $dispatch_v - $achat_v;
                    if ($restant_v < 0) $restant_v = 0;
                    $taux_v = $besoin_v > 0 ? round((($dispatch_v + $achat_v) / $besoin_v) * 100) : 0;
                    $taux_v_class = 'bg-danger';
                    if ($taux_v >= 75) $taux_v_class = 'bg-success';
                    elseif ($taux_v >= 40) $taux_v_class = 'bg-warning';
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= e($v['ville']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= e($v['region']) ?></span></td>
                        <td class="text-end"><?= format_ar($besoin_v) ?></td>
                        <td class="text-end text-success"><?= format_ar($dispatch_v) ?></td>
                        <td class="text-end text-info"><?= format_ar($achat_v) ?></td>
                        <td class="text-end text-danger"><?= format_ar($restant_v) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 7px;">
                                    <div class="progress-bar <?= $taux_v_class ?>" style="width: <?= $taux_v ?>%"></div>
                                </div>
                                <small class="fw-bold text-muted" style="min-width: 38px;"><?= $taux_v ?>%</small>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
