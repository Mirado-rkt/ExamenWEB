<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card form-card">
            <div class="card-header header-coral">
                <h6 class="mb-0 text-white fw-bold">
                    <i class="bi bi-building me-2"></i>
                    <?= $ville ? 'Modifier la ville' : 'Nouvelle ville' ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= $ville ? base_url('/villes/update/' . $ville['id']) : base_url('/villes/store') ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label fw-semibold">Nom de la ville</label>
                        <input type="text" class="form-control" id="nom" name="nom"
                               value="<?= e($ville['nom'] ?? '') ?>" required autofocus
                               placeholder="Ex: Antananarivo">
                    </div>
                    <div class="mb-3">
                        <label for="region_id" class="form-label fw-semibold">Région</label>
                        <select class="form-select" id="region_id" name="region_id" required>
                            <option value="">— Choisir une région —</option>
                            <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= (($ville['region_id'] ?? '') == $r['id']) ? 'selected' : '' ?>>
                                <?= e($r['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('/villes') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-coral">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $ville ? 'Modifier' : 'Enregistrer' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
