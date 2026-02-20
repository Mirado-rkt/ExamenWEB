<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-exclamation-circle me-2"></i>Besoins restants
        </h4>
        <p class="text-muted mb-0 small">
            Besoins non encore satisfaits par les dons directs ou les achats
        </p>
    </div>
</div>

<!-- Filtre par ville -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" Acheter="<?= base_url('/besoins-restants') ?>" class="row g-3 align-items-end">
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
                <a href="<?= base_url('/besoins-restants') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Réinitialiser
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Résumé -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Total des besoins restants :</strong> <?= format_ar($total_restant) ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert <?= $dons_argent_disponibles > 0 ? 'alert-info' : 'alert-danger' ?> mb-0 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-cash me-2"></i>
                <strong>Dons en argent disponibles :</strong> <?= format_ar($dons_argent_disponibles) ?>
                <?php if ($dons_argent_disponibles <= 0): ?>
                    <span class="badge bg-danger ms-2">Épuisé</span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('/dons/create') ?>" class="btn btn-sm <?= $dons_argent_disponibles > 0 ? 'btn-outline-primary' : 'btn-danger' ?>">
                <i class="bi bi-plus-circle me-1"></i> Ajouter un don
            </a>
        </div>
    </div>
</div>

<!-- Tableau des besoins restants -->
<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-card-checklist me-2"></i>Liste des besoins non satisfaits</h6>
        <a href="<?= base_url('/achats') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-cart me-1"></i> Voir les achats effectués
        </a>
    </div>
    <div class="table-responsive-sm">
        <table class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Type</th>
                    <th>Cat.</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Disp.</th>
                    <th class="text-end">Ach.</th>
                    <th class="text-end">Reste</th>
                    <th class="text-end">Valeur</th>
                    <th class="text-center">Acheter</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($besoins)): ?>
                <tr>
                    <td colspan="9" class="empty-state">
                        <i class="bi bi-check-circle text-success"></i>
                        <p class="text-success">Tous les besoins sont satisfaits !</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($besoins as $b): ?>
                    <tr>
                        <td class="fw-semibold" title="<?= e($b['region_nom']) ?>"><?= e($b['ville_nom']) ?></td>
                        <td><?= e($b['type_nom']) ?></td>
                        <td>
                            <span class="badge <?= categorie_badge($b['categorie']) ?>">
                                <?= substr(categorie_label($b['categorie']), 0, 3) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= format_nb((float) $b['quantite']) ?></td>
                        <td class="text-end text-success"><?= format_nb((float) $b['quantite_dispatchee']) ?></td>
                        <td class="text-end text-info"><?= format_nb((float) $b['quantite_achetee']) ?></td>
                        <td class="text-end fw-bold text-danger"><?= format_nb((float) $b['quantite_restante']) ?></td>
                        <td class="text-end fw-semibold"><?= format_ar((float) $b['valeur_restante']) ?></td>
                        <td class="text-center">
                            <?php if ($b['categorie'] !== 'argent'): ?>
                            <a href="<?= base_url('/achats/create?besoin_id=' . $b['id']) ?>" 
                               class="btn btn-sm btn-success" title="Acheter ce besoin">
                                <i class="bi bi-cart-plus"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
