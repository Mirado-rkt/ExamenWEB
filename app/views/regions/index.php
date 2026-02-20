<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="<?= base_url('/regions/create') ?>" class="btn btn-teal">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle région
    </a>
</div>

<div class="card table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-map me-2"></i>Liste des régions</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th class="text-center">Nb. villes</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($regions)): ?>
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="bi bi-map"></i>
                        <p>Aucune région enregistrée</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($regions as $r): ?>
                    <tr>
                        <td class="text-muted"><?= $r['id'] ?></td>
                        <td class="fw-semibold"><?= e($r['nom']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= $r['nb_villes'] ?></span>
                        </td>
                        <td class="text-end">
                            <a href="<?= base_url('/regions/edit/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= base_url('/regions/delete/' . $r['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette région ?')">
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
