<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-cart me-2"></i>Liste des achats
        </h4>
        <p class="text-muted mb-0 small">
            Achats effectués avec les dons en argent
        </p>
    </div>
    <a href="<?= base_url('/besoins-restants') ?>" class="btn btn-success">
        <i class="bi bi-cart-plus me-1"></i> Acheter
    </a>
</div>

<!-- Filtre par ville -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('/achats') ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="ville_id" class="form-label">Filtrer par ville</label>
                <select name="ville_id" id="ville_id" class="form-select">
                    <option value="0">-- Toutes les villes --</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= $ville_id == $v['id'] ? 'selected' : '' ?>>
                            <?= e($v['nom']) ?> (<?= e($v['region_nom']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i> Filtrer
                </button>
            </div>
            <?php if ($ville_id > 0): ?>
            <div class="col-md-2">
                <a href="<?= base_url('/achats') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Réinitialiser
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Total des achats -->
<?php if (!empty($achats)): ?>
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Total des achats affichés :</strong> <?= format_ar($total_achats) ?>
</div>
<?php endif; ?>

<!-- Tableau des achats -->
<div class="card table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-cart-check me-2"></i>Achats enregistrés</h6>
    </div>
    <div>
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Type</th>
                    <th class="text-end">Qté</th>
                    <th class="text-end">Frais</th>
                    <th class="text-end">Montant</th>
                    <th>Donateur</th>
                    <th>Date</th>
                    <th class="text-center">Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($achats)): ?>
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="bi bi-cart"></i>
                        <p>Aucun achat enregistré</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($achats as $a): ?>
                    <tr>
                        <td class="fw-semibold" title="<?= e($a['region_nom']) ?>"><?= e($a['ville_nom']) ?></td>
                        <td><?= e($a['type_nom']) ?></td>
                        <td class="text-end"><?= format_nb((float) $a['quantite']) ?></td>
                        <td class="text-end text-warning fw-semibold"><?= format_nb((float) $a['frais_pourcent']) ?>%</td>
                        <td class="text-end fw-semibold text-success"><?= format_ar((float) $a['montant_total']) ?></td>
                        <td class="text-muted"><?= e($a['donateur']) ?></td>
                        <td class="text-muted small"><?= date('d/m/Y', strtotime($a['date_achat'])) ?></td>
                        <td class="text-center">
                            <form method="POST" action="<?= base_url('/achats/delete/' . $a['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer cet achat ?')">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
