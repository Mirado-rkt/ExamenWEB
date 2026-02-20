<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    .dispatch-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.5rem;
    }
    .stat-card .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .stat-card .stat-label {
        font-size: 0.85rem;
        color: #64748b;
    }
    .mode-card {
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }
    .mode-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }
    .mode-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }
    .mode-card .mode-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }
    .ville-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border-left: 4px solid;
        transition: transform 0.2s ease;
    }
    .ville-card:hover {
        transform: translateX(5px);
    }
    .ville-card.high { border-left-color: #10b981; }
    .ville-card.medium { border-left-color: #f59e0b; }
    .ville-card.low { border-left-color: #ef4444; }
    .progress-ring {
        width: 60px;
        height: 60px;
    }
    .dispatch-table {
        border-radius: 12px;
        overflow: hidden;
    }
    .dispatch-table thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        color: #475569;
        padding: 1rem;
    }
    .dispatch-table tbody td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
    }
    .dispatch-table tbody tr:hover {
        background: #f8fafc;
    }
    .action-btn-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .btn-dispatch {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-dispatch:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .filter-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
        background: white;
    }
    .filter-badge:hover, .filter-badge.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
</style>

<?php
    // Calcul des statistiques globales
    $total_besoins = 0;
    $total_dispatched = 0;
    $nb_villes = count($villes);
    foreach ($villes as $v) {
        $total_besoins += (float) $v['total_besoin'];
        $total_dispatched += (float) $v['total_dispatch'];
    }
    $taux_global = $total_besoins > 0 ? round(($total_dispatched / $total_besoins) * 100) : 0;
    $reste_global = $total_besoins - $total_dispatched;
?>

<!-- Hero Section avec statistiques -->
<div class="dispatch-hero">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h3 class="fw-bold mb-2">
                <i class="bi bi-arrow-left-right me-2"></i>Dispatch des Dons
            </h3>
            <p class="mb-0 opacity-75">
                Système intelligent de distribution automatique des dons aux sinistrés
            </p>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <div class="action-btn-group justify-content-lg-end">
                <form method="POST" action="<?= base_url('/dispatch/reinitialiser') ?>" class="d-inline"
                      onsubmit="return confirm('ATTENTION : Cette action va réinitialiser TOUTES les données. Continuer ?')">
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser
                    </button>
                </form>
                <form method="POST" action="<?= base_url('/dispatch/initialiser') ?>" class="d-inline"
                      onsubmit="return confirm('Cette action va supprimer tous les dispatches. Continuer ?')">
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Initialiser
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques globales -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value text-primary"><?= $taux_global ?>%</div>
            <div class="stat-label">Taux de couverture</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="stat-value text-success"><?= $nb_dispatches ?></div>
            <div class="stat-label">Attributions</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div class="stat-value text-info"><?= $nb_villes ?></div>
            <div class="stat-label">Villes couvertes</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-value text-danger"><?= format_ar($reste_global) ?></div>
            <div class="stat-label">Besoins restants</div>
        </div>
    </div>
</div>

<!-- Mode de dispatch -->
<form method="POST" action="<?= base_url('/dispatch/simuler') ?>" id="dispatchForm">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-header bg-white border-0 py-3" style="border-radius: 16px 16px 0 0;">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-sliders me-2 text-primary"></i>Configuration du Dispatch
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Sélectionnez l'algorithme de distribution qui convient le mieux à vos besoins :</p>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="mode-card d-block <?= ($current_mode ?? 'prioritaire') === 'prioritaire' ? 'active' : '' ?>" onclick="selectMode('prioritaire')">
                        <input type="radio" name="mode" value="prioritaire" class="d-none" <?= ($current_mode ?? 'prioritaire') === 'prioritaire' ? 'checked' : '' ?>>
                        <div class="mode-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Par Date (FIFO)</h6>
                        <small class="text-muted">Les premiers besoins saisis sont servis en priorité</small>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="mode-card d-block <?= ($current_mode ?? '') === 'minoritaire' ? 'active' : '' ?>" onclick="selectMode('minoritaire')">
                        <input type="radio" name="mode" value="minoritaire" class="d-none" <?= ($current_mode ?? '') === 'minoritaire' ? 'checked' : '' ?>>
                        <div class="mode-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-sort-numeric-down"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Minoritaire</h6>
                        <small class="text-muted">Les plus petits besoins sont servis en premier</small>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="mode-card d-block <?= ($current_mode ?? '') === 'proportionnelle' ? 'active' : '' ?>" onclick="selectMode('proportionnelle')">
                        <input type="radio" name="mode" value="proportionnelle" class="d-none" <?= ($current_mode ?? '') === 'proportionnelle' ? 'checked' : '' ?>>
                        <div class="mode-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-pie-chart"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Proportionnelle</h6>
                        <small class="text-muted">Distribution équitable selon les parts</small>
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2 justify-content-end flex-wrap">
                <button type="submit" formaction="<?= base_url('/dispatch/simuler') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-play-circle me-1"></i> Simuler
                </button>
                <button type="submit" formaction="<?= base_url('/dispatch/valider') ?>" class="btn-dispatch"
                        onclick="return confirm('Voulez-vous vraiment valider et appliquer le dispatch ?')">
                    <i class="bi bi-check-circle me-1"></i> Valider le Dispatch
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function selectMode(mode) {
    document.querySelectorAll('.mode-card').forEach(card => card.classList.remove('active'));
    document.querySelector(`input[value="${mode}"]`).checked = true;
    document.querySelector(`input[value="${mode}"]`).closest('.mode-card').classList.add('active');
}
</script>

<?php if (!empty($simulation_mode)): ?>
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert" style="border-radius: 12px;">
    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
    <div>
        <strong>Mode Simulation</strong><br>
        <small>Les résultats ci-dessous montrent une prévisualisation. Cliquez sur "Valider le Dispatch" pour appliquer.</small>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Résumé par ville -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center" style="border-radius: 16px 16px 0 0;">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-geo-alt-fill me-2 text-primary"></i>Répartition par Ville
        </h5>
        <div class="d-flex gap-2">
            <span class="filter-badge active" data-filter="all">Toutes</span>
            <span class="filter-badge" data-filter="high"><i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i>+75%</span>
            <span class="filter-badge" data-filter="medium"><i class="bi bi-circle-fill text-warning me-1" style="font-size: 8px;"></i>40-75%</span>
            <span class="filter-badge" data-filter="low"><i class="bi bi-circle-fill text-danger me-1" style="font-size: 8px;"></i>&lt;40%</span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($villes)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3 mb-0">Aucune ville avec des besoins enregistrés</p>
        </div>
        <?php else: ?>
        <div class="row g-3" id="villesContainer">
            <?php foreach ($villes as $v): ?>
            <?php
                $reste = (float) $v['total_besoin'] - (float) $v['total_dispatch'];
                $taux  = taux_couverture((float) $v['total_dispatch'], (float) $v['total_besoin']);
                $level = $taux >= 75 ? 'high' : ($taux >= 40 ? 'medium' : 'low');
                $color = $taux >= 75 ? '#10b981' : ($taux >= 40 ? '#f59e0b' : '#ef4444');
            ?>
            <div class="col-md-6 col-lg-4 ville-item" data-level="<?= $level ?>">
                <div class="ville-card <?= $level ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= e($v['ville']) ?></h6>
                            <span class="badge bg-light text-dark border mb-2"><?= e($v['region']) ?></span>
                            <div class="d-flex flex-column gap-1 small">
                                <span><i class="bi bi-arrow-down-circle text-primary me-1"></i>Besoins: <strong><?= format_ar((float) $v['total_besoin']) ?></strong></span>
                                <span><i class="bi bi-gift text-success me-1"></i>Reçu: <strong class="text-success"><?= format_ar((float) $v['total_dispatch']) ?></strong></span>
                                <span><i class="bi bi-hourglass-split text-danger me-1"></i>Reste: <strong class="text-danger"><?= format_ar($reste) ?></strong></span>
                            </div>
                        </div>
                        <div class="text-center">
                            <svg class="progress-ring" viewBox="0 0 36 36">
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      fill="none" stroke="#e2e8f0" stroke-width="3"/>
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      fill="none" stroke="<?= $color ?>" stroke-width="3"
                                      stroke-dasharray="<?= $taux ?>, 100" stroke-linecap="round"/>
                                <text x="18" y="20.5" text-anchor="middle" font-size="8" font-weight="bold" fill="<?= $color ?>"><?= $taux ?>%</text>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.filter-badge').forEach(badge => {
    badge.addEventListener('click', function() {
        document.querySelectorAll('.filter-badge').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.ville-item').forEach(item => {
            if (filter === 'all' || item.dataset.level === filter) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<!-- Détails des dispatches -->
<?php if (!empty($dispatches)): ?>
<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-white border-0 py-3" style="border-radius: 16px 16px 0 0;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-list-check me-2 text-primary"></i>Détails des Attributions
            </h5>
            <span class="badge bg-primary"><?= count($dispatches) ?> enregistrement(s)</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table dispatch-table mb-0">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Type de besoin</th>
                    <th>Catégorie</th>
                    <th>Donateur</th>
                    <th class="text-end">Qté demandée</th>
                    <th class="text-end">Qté attribuée</th>
                    <th class="text-end">Valeur</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dispatches as $dp): ?>
                <?php 
                    $ratio = $dp['besoin_qte'] > 0 ? ((float) $dp['quantite'] / (float) $dp['besoin_qte']) * 100 : 0;
                    $statusColor = $ratio >= 100 ? 'success' : ($ratio >= 50 ? 'warning' : 'danger');
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-<?= $statusColor ?> bg-opacity-10 text-<?= $statusColor ?> me-2" style="width: 8px; height: 8px; padding: 0; border-radius: 50%;"></span>
                            <span class="fw-semibold"><?= e($dp['ville_nom']) ?></span>
                        </div>
                    </td>
                    <td><?= e($dp['type_nom']) ?></td>
                    <td>
                        <span class="badge <?= categorie_badge($dp['categorie']) ?>">
                            <?= categorie_label($dp['categorie']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-muted">
                            <i class="bi bi-person-heart me-1"></i><?= e($dp['donateur']) ?>
                        </span>
                    </td>
                    <td class="text-end"><?= format_nb((float) $dp['besoin_qte'], 2) ?></td>
                    <td class="text-end">
                        <span class="fw-semibold text-<?= $statusColor ?>"><?= format_nb((float) $dp['quantite'], 2) ?></span>
                    </td>
                    <td class="text-end fw-bold"><?= format_ar((float) $dp['valeur']) ?></td>
                    <td>
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($dp['date_besoin'])) ?>
                        </small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.2;"></i>
        <h5 class="mt-3 text-muted">Aucune attribution effectuée</h5>
        <p class="text-muted mb-4">Sélectionnez un mode de dispatch et cliquez sur "Simuler" pour voir les résultats</p>
        <button type="button" class="btn-dispatch" onclick="document.getElementById('dispatchForm').querySelector('button[type=submit]').click()">
            <i class="bi bi-play-circle me-1"></i> Lancer la simulation
        </button>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
