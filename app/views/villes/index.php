<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= base_url('/villes/create') ?>" class="btn btn-teal">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle ville
    </a>
</div>

<div class="card table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Liste des villes</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Région</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($villes)): ?>
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="bi bi-building"></i>
                        <p>Aucune ville enregistrée</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($villes as $v): ?>
                    <tr>
                        <td class="text-muted"><?= $v['id'] ?></td>
                        <td class="fw-semibold"><?= e($v['nom']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= e($v['region_nom']) ?></span></td>
                        <td class="text-end">
                            <a href="<?= base_url('/villes/edit/' . $v['id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= base_url('/villes/delete/' . $v['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette ville ?')">
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
