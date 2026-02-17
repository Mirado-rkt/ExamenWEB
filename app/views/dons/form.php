<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card form-card">
            <div class="card-header header-emerald">
                <h6 class="mb-0 text-white fw-bold">
                    <i class="bi bi-gift me-2"></i> Enregistrer un nouveau don
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/dons/store" id="donForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="donateur" class="form-label fw-semibold">Donateur</label>
                            <input type="text" class="form-control" id="donateur" name="donateur"
                                placeholder="Ex: Croix-Rouge (laisser vide = Anonyme)">
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <input type="text" class="form-control" id="description" name="description"
                                placeholder="Ex: Don alimentaire d'urgence">
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bi bi-list-ul me-2"></i>Détails du don</h6>

                    <div id="detailRows">
                        <div class="don-detail-row d-flex gap-3 align-items-end">
                            <div class="flex-grow-1">
                                <label class="form-label small fw-semibold">Type de besoin</label>
                                <select class="form-select" name="type_besoin_id[]" required>
                                    <option value="">— Choisir —</option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= $t['id'] ?>">
                                            <?= e($t['nom']) ?> (<?= categorie_label($t['categorie']) ?>) —
                                            <?= format_ar((float) $t['prix_unitaire']) ?>/u
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="width: 150px;">
                                <label class="form-label small fw-semibold">Quantité</label>
                                <input type="number" class="form-control" name="detail_quantite[]" min="0.01"
                                    step="0.01" required placeholder="Qté">
                            </div>

                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="/dons" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-emerald">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer le don
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>