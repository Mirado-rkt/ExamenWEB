<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card form-card">
            <div class="card-header header-rose">
                <h6 class="mb-0 text-white fw-bold">
                    <i class="bi bi-card-checklist me-2"></i>
                    <?= $besoin ? 'Modifier le besoin' : 'Nouveau besoin' ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= $besoin ? base_url('/besoins/update/' . $besoin['id']) : base_url('/besoins/store') ?>">
                    <?php $selectedVilleId = (int) ($besoin['ville_id'] ?? ($selected_ville_id ?? 0)); ?>
                    <div class="mb-3">
                        <label for="ville_id" class="form-label fw-semibold">Ville</label>
                        <select class="form-select" id="ville_id" name="ville_id" required>
                            <option value="">— Choisir une ville —</option>
                            <?php foreach ($villes as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($selectedVilleId === (int) $v['id']) ? 'selected' : '' ?>>
                                <?= e($v['nom']) ?> (<?= e($v['region_nom']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="type_besoin_id" class="form-label fw-semibold">Type de besoin</label>
                        <select class="form-select" id="type_besoin_id" name="type_besoin_id" required>
                            <option value="">— Choisir un type —</option>
                            <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= (($besoin['type_besoin_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                                <?= e($t['nom']) ?> — <?= categorie_label($t['categorie']) ?> — <?= format_ar((float) $t['prix_unitaire']) ?>/unité
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantite" class="form-label fw-semibold">Quantité</label>
                        <input type="number" class="form-control" id="quantite" name="quantite"
                               value="<?= e($besoin['quantite'] ?? '') ?>" required min="0.01" step="0.01"
                               placeholder="Ex: 500">
                    </div>
                    <div class="mb-3">
                        <label for="date_saisie" class="form-label fw-semibold">Date de saisie</label>
                        <input type="datetime-local" class="form-control" id="date_saisie" name="date_saisie"
                               value="<?= e($besoin['date_saisie'] ?? date('Y-m-d\TH:i')) ?>" required>
                        <small class="text-muted">Date à utiliser comme critère de priorité pour le dispatch</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('/besoins') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-coral">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $besoin ? 'Modifier' : 'Enregistrer' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
