<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="/dons/create" class="btn btn-teal">
        <i class="bi bi-plus-lg me-1"></i> Nouveau don
    </a>
</div>

<div class="card table-card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-gift me-2"></i>Dons reçus</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Donateur</th>
                    <th>Description</th>
                    <th class="text-center">Lignes</th>
                    <th class="text-end">Valeur totale</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dons)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="bi bi-gift"></i>
                        <p>Aucun don enregistré</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($dons as $d): ?>
                    <tr>
                        <td class="text-muted"><?= $d['id'] ?></td>
                        <td class="fw-semibold"><?= e($d['donateur']) ?></td>
                        <td class="text-muted"><?= e($d['description'] ?: '—') ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= $d['nb_lignes'] ?></span>
                        </td>
                        <td class="text-end fw-semibold"><?= format_ar((float) $d['valeur_totale']) ?></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($d['date_don'])) ?></td>
                        <td class="text-end">
                            <a href="/dons/show/<?= $d['id'] ?>" class="btn btn-sm btn-outline-info me-1">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="/dons/delete/<?= $d['id'] ?>" class="d-inline"
                                  onsubmit="return confirm('Supprimer ce don et ses détails ?')">
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
