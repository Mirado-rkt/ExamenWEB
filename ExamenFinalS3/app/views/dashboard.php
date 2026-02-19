<?php include __DIR__ . '/layout/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e293b;"><i class="bi bi-speedometer2 me-2" style="color: #3b82f6;"></i>Tableau de bord</h4>
        <p class="text-muted mb-0 small">Vue d'ensemble des besoins des sinistrés et des dons attribués</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/besoins/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Saisir un besoin
        </a>
        <a href="/dons/create" class="btn btn-success btn-sm">
            <i class="bi bi-gift me-1"></i> Saisir un don
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card stat-card-blue">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-blue">
                    <i class="bi bi-map-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $stats['regions'] ?></div>
                    <div class="stat-label">Régions</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card stat-card-teal">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-teal">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $stats['villes'] ?></div>
                    <div class="stat-label">Villes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card stat-card-orange">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-orange">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $stats['nb_besoins'] ?></div>
                    <div class="stat-label">Besoins saisis</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card stat-card-emerald">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-emerald">
                    <i class="bi bi-gift-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $stats['nb_dons'] ?></div>
                    <div class="stat-label">Dons reçus</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des villes avec besoins et dons -->
<?php if (empty($villes)): ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">Aucun besoin enregistré. <a href="/besoins/create">Saisir un besoin</a></p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($villes as $v): ?>
    <div class="card mb-4 ville-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-geo-alt-fill me-1" style="color: #0891b2;"></i>
                    <?= e($v['ville']) ?>
                </h6>
                <small class="text-muted">Région : <?= e($v['region']) ?></small>
            </div>
            <a href="/besoins/create?ville_id=<?= (int) $v['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i> Ajouter besoin
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type de besoin</th>
                            <th>Catégorie</th>
                            <th class="text-end">Besoin (qté)</th>
                            <th class="text-end">Don attribué (qté)</th>
                            <th style="width: 180px;">Couverture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($v['besoins'] as $b): ?>
                        <?php
                            // Trouver le don correspondant
                            $don_qte = 0;
                            foreach ($v['dons_attribues'] as $da) {
                                if ($da['type_nom'] === $b['type_nom']) {
                                    $don_qte = (float) $da['quantite'];
                                    break;
                                }
                            }
                            $besoin_qte = (float) $b['quantite'];
                            $taux = taux_couverture($don_qte, $besoin_qte);
                            $pc = $taux >= 75 ? 'bg-success' : ($taux >= 40 ? 'bg-warning' : 'bg-danger');
                        ?>
                        <tr>
                            <td class="fw-medium"><?= e($b['type_nom']) ?></td>
                            <td>
                                <span class="badge <?= categorie_badge($b['categorie']) ?>">
                                    <?= categorie_label($b['categorie']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold"><?= format_nb($besoin_qte) ?></td>
                            <td class="text-end">
                                <?php if ($don_qte > 0): ?>
                                    <span class="text-success fw-semibold"><?= format_nb($don_qte) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
