<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card form-card">
            <div class="card-header header-teal">
                <h6 class="mb-0 text-white fw-bold">
                    <i class="bi bi-map me-2"></i>
                    <?= $region ? 'Modifier la région' : 'Nouvelle région' ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= $region ? base_url('/regions/update/' . $region['id']) : base_url('/regions/store') ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label fw-semibold">Nom de la région</label>
                        <input type="text" class="form-control" id="nom" name="nom"
                               value="<?= e($region['nom'] ?? '') ?>" required autofocus
                               placeholder="Ex: Analamanga">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('/regions') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-teal">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $region ? 'Modifier' : 'Enregistrer' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
