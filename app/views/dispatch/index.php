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
</div>

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
                    <th class="text-end">Dispatché</th>
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
                    <th class="text-end">Qté attribuée</th>
                    <th class="text-end">Valeur</th>
                    <th>Date</th>
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
                    <td class="text-end"><?= format_nb((float) $dp['quantite']) ?></td>
                    <td class="text-end fw-semibold"><?= format_ar((float) $dp['valeur']) ?></td>
                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($dp['date_dispatch'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
