<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color: #1e293b;">
            <i class="bi bi-cart-plus me-2"></i>Nouvel achat
        </h4>
        <p class="text-muted mb-0 small">
            Achat avec les dons en argent pour satisfaire un besoin
        </p>
    </div>
    <a href="<?= base_url('/besoins-restants') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour aux besoins restants
    </a>
</div>

<!-- Informations sur le besoin -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Informations sur le besoin</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="text-muted small">Ville / Région</label>
                <p class="fw-semibold"><?= e($besoin['ville_nom']) ?> <span class="badge bg-light text-dark border"><?= e($besoin['region_nom']) ?></span></p>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Type de besoin</label>
                <p class="fw-semibold">
                    <?= e($besoin['type_nom']) ?>
                    <span class="badge <?= categorie_badge($besoin['categorie']) ?>"><?= categorie_label($besoin['categorie']) ?></span>
                </p>
            </div>
            <div class="col-md-2">
                <label class="text-muted small">Quantité totale</label>
                <p class="fw-semibold"><?= format_nb((float) $besoin['quantite']) ?></p>
            </div>
            <div class="col-md-2">
                <label class="text-muted small">Quantité restante</label>
                <p class="fw-bold text-danger"><?= format_nb($quantite_restante) ?></p>
            </div>
            <div class="col-md-2">
                <label class="text-muted small">Prix unitaire</label>
                <p class="fw-semibold"><?= format_ar((float) $besoin['prix_unitaire']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Alerte frais -->
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Attention :</strong> Des frais d'achat de <strong><?= $frais_pourcent ?>%</strong> seront appliqués au montant de l'achat.
    <br>
    <small class="text-muted">Exemple : Pour acheter 100 unités à <?= format_ar((float) $besoin['prix_unitaire']) ?>/u, le coût sera de <?= format_ar(100 * (float) $besoin['prix_unitaire'] * (1 + $frais_pourcent / 100)) ?> (dont <?= format_ar(100 * (float) $besoin['prix_unitaire'] * $frais_pourcent / 100) ?> de frais).</small>
</div>

<?php if (empty($dons_argent)): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-circle me-2"></i>
    <strong>Aucun don en argent disponible.</strong> Il n'est pas possible de faire un achat sans fonds disponibles.
</div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <h6 class="fw-bold mb-0"><i class="bi bi-cash me-2"></i>Formulaire d'achat</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('/achats/store') ?>" id="achatForm">
            <input type="hidden" name="besoin_id" value="<?= $besoin['id'] ?>">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="don_detail_id" class="form-label">Source de financement (Don en argent) <span class="text-danger">*</span></label>
                    <select name="don_detail_id" id="don_detail_id" class="form-select" required onchange="updateMaxQuantite()">
                        <?php if (count($dons_argent) > 1): ?>
                        <option value="">-- Sélectionner un don --</option>
                        <?php endif; ?>
                        <?php foreach ($dons_argent as $index => $da): ?>
                            <option value="<?= $da['don_detail_id'] ?>" 
                                    data-montant="<?= $da['montant_disponible'] ?>"
                                    <?= (count($dons_argent) == 1) ? 'selected' : '' ?>>
                                <?= e($da['donateur']) ?> - Disponible: <?= format_ar((float) $da['montant_disponible']) ?> 
                                (<?= date('d/m/Y', strtotime($da['date_don'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="quantite" class="form-label">Quantité à acheter <span class="text-danger">*</span></label>
                    <input type="number" name="quantite" id="quantite" class="form-control" 
                           min="1" max="<?= $quantite_restante ?>" step="0.01" required
                           onchange="calculerMontant()" oninput="calculerMontant()">
                    <div class="form-text">Maximum : <?= format_nb($quantite_restante) ?> unités (besoin restant)</div>
                </div>
            </div>

            <!-- Simulation du montant -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-calculator me-2"></i>Simulation du montant</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="text-muted small">Montant de base</label>
                            <p class="fw-semibold" id="montant_base">0 Ar</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Frais (<?= $frais_pourcent ?>%)</label>
                            <p class="fw-semibold text-warning" id="montant_frais">0 Ar</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Montant total</label>
                            <p class="fw-bold text-success" id="montant_total">0 Ar</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Disponible</label>
                            <p class="fw-semibold" id="montant_disponible">-</p>
                        </div>
                    </div>
                    <div id="alerte_depassement" class="alert alert-danger mt-2 d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>Le montant total dépasse le montant disponible !
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success" id="btnSubmit">
                    <i class="bi bi-check-lg me-1"></i> Valider l'achat
                </button>
                <a href="<?= base_url('/besoins-restants') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
const prixUnitaire = <?= (float) $besoin['prix_unitaire'] ?>;
const fraisPourcent = <?= $frais_pourcent ?>;
const quantiteRestante = <?= $quantite_restante ?>;

function formatAr(montant) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(montant) + ' Ar';
}

function updateMaxQuantite() {
    const select = document.getElementById('don_detail_id');
    const option = select.options[select.selectedIndex];
    const montantDispo = parseFloat(option.dataset.montant || 0);
    
    document.getElementById('montant_disponible').textContent = formatAr(montantDispo);
    calculerMontant();
}

function calculerMontant() {
    const quantite = parseFloat(document.getElementById('quantite').value) || 0;
    const select = document.getElementById('don_detail_id');
    const option = select.options[select.selectedIndex];
    const montantDispo = parseFloat(option.dataset.montant || 0);
    
    const montantBase = quantite * prixUnitaire;
    const montantFrais = montantBase * (fraisPourcent / 100);
    const montantTotal = montantBase + montantFrais;
    
    document.getElementById('montant_base').textContent = formatAr(montantBase);
    document.getElementById('montant_frais').textContent = formatAr(montantFrais);
    document.getElementById('montant_total').textContent = formatAr(montantTotal);
    
    const alerteDepassement = document.getElementById('alerte_depassement');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (montantTotal > montantDispo && montantDispo > 0) {
        alerteDepassement.classList.remove('d-none');
        btnSubmit.disabled = true;
    } else {
        alerteDepassement.classList.add('d-none');
        btnSubmit.disabled = false;
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updateMaxQuantite();
});
</script>

<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
