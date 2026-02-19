<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= base_url('/types-besoin/create') ?>" class="btn btn-teal">
        <i class="bi bi-plus-lg me-1"></i> Nouveau type
    </a>
</div>

<div class="card table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-tags me-2"></i>Types de besoin</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th class="text-end">Prix unitaire</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($types)): ?>
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="bi bi-tags"></i>
                        <p>Aucun type de besoin enregistré</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($types as $t): ?>
                    <tr>
                        <td class="text-muted"><?= $t['id'] ?></td>
                        <td class="fw-semibold"><?= e($t['nom']) ?></td>
                        <td>
                            <span class="badge <?= categorie_badge($t['categorie']) ?>">
                                <?= categorie_label($t['categorie']) ?>
                            </span>
                        </td>
                        <td class="text-end"><?= format_ar((float) $t['prix_unitaire']) ?></td>
                        <td class="text-end">
                            <a href="<?= base_url('/types-besoin/edit/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= base_url('/types-besoin/delete/' . $t['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce type ?')">
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
