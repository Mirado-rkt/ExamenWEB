<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="/besoins/create" class="btn btn-teal">
        <i class="bi bi-plus-lg me-1"></i> Nouveau besoin
    </a>
</div>

<div class="card table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-card-checklist me-2"></i>Besoins enregistrés</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ville</th>
                    <th>Région</th>
                    <th>Type de besoin</th>
                    <th>Catégorie</th>
                    <th class="text-end">Quantité</th>
                    <th class="text-end">P.U.</th>
                    <th class="text-end">Valeur totale</th>
                    <th>Date saisie</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($besoins)): ?>
                <tr>
                    <td colspan="10" class="empty-state">
                        <i class="bi bi-card-checklist"></i>
                        <p>Aucun besoin enregistré</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($besoins as $b): ?>
                    <tr>
                        <td class="text-muted"><?= $b['id'] ?></td>
                        <td class="fw-semibold"><?= e($b['ville_nom']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= e($b['region_nom']) ?></span></td>
                        <td><?= e($b['type_nom']) ?></td>
                        <td>
                            <span class="badge <?= categorie_badge($b['categorie']) ?>">
                                <?= categorie_label($b['categorie']) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= format_nb((float) $b['quantite']) ?></td>
                        <td class="text-end"><?= format_ar((float) $b['prix_unitaire']) ?></td>
                        <td class="text-end fw-semibold"><?= format_ar((float) $b['valeur_totale']) ?></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($b['date_saisie'])) ?></td>
                        <td class="text-end">
                            <a href="/besoins/edit/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="/besoins/delete/<?= $b['id'] ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce besoin ?')">
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
