<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card form-card">
            <div class="card-header header-indigo">
                <h6 class="mb-0 text-white fw-bold">
                    <i class="bi bi-tags me-2"></i>
                    <?= $type ? 'Modifier le type de besoin' : 'Nouveau type de besoin' ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= $type ? '/types-besoin/update/' . $type['id'] : '/types-besoin/store' ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label fw-semibold">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom"
                               value="<?= e($type['nom'] ?? '') ?>" required autofocus
                               placeholder="Ex: Riz (kg)">
                    </div>
                    <div class="mb-3">
                        <label for="categorie" class="form-label fw-semibold">Catégorie</label>
                        <select class="form-select" id="categorie" name="categorie" required>
                            <option value="">— Choisir —</option>
                            <option value="nature" <?= (($type['categorie'] ?? '') === 'nature') ? 'selected' : '' ?>>En nature (riz, huile...)</option>
                            <option value="materiau" <?= (($type['categorie'] ?? '') === 'materiau') ? 'selected' : '' ?>>Matériaux (tôle, clou...)</option>
                            <option value="argent" <?= (($type['categorie'] ?? '') === 'argent') ? 'selected' : '' ?>>Argent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="prix_unitaire" class="form-label fw-semibold">Prix unitaire (Ar)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire"
                                   value="<?= e($type['prix_unitaire'] ?? '') ?>" required min="0" step="0.01"
                                   placeholder="Ex: 2500">
                            <span class="input-group-text">Ar</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="/types-besoin" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-teal">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $type ? 'Modifier' : 'Enregistrer' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
