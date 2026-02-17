<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Info du don -->
        <div class="card mb-4">
            <div class="card-header header-emerald d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-white fw-bold">
                    <i class="bi bi-gift me-2"></i> Don #<?= $don['id'] ?>
                </h6>
                <span class="badge bg-white text-dark"><?= date('d/m/Y H:i', strtotime($don['date_don'])) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Donateur :</strong> <?= e($don['donateur']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Description :</strong> <?= e($don['description'] ?: '—') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails -->
        <div class="card table-card">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-2"></i>Détails du don</h6>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type de besoin</th>
                            <th>Catégorie</th>
                            <th class="text-end">Quantité</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-end">Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($details as $d):
                            $total += (float) $d['valeur'];
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= e($d['type_nom']) ?></td>
                            <td>
                                <span class="badge <?= categorie_badge($d['categorie']) ?>">
                                    <?= categorie_label($d['categorie']) ?>
                                </span>
                            </td>
                            <td class="text-end"><?= format_nb((float) $d['quantite']) ?></td>
                            <td class="text-end"><?= format_ar((float) $d['prix_unitaire']) ?></td>
                            <td class="text-end fw-semibold"><?= format_ar((float) $d['valeur']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-end fw-bold">Total :</td>
                            <td class="text-end fw-bold" style="font-size: 1.1rem; color: var(--tk-emerald);">
                                <?= format_ar($total) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="/dons" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Retour à la liste
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
