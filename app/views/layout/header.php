<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'BNGRC') ?> — BNGRC</title>
    <link rel="stylesheet" href="<?= base_url('/assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/app.css') ?>">
</head>
<body>

<!-- ===== Top Navbar ===== -->
<nav class="navbar navbar-bngrc navbar-expand-lg sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('/') ?>">
            <div class="brand-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <span class="brand-text">BNGRC</span>
                <small class="d-none d-md-block brand-sub">Suivi des Collectes &amp; Distributions</small>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('/') ?>">
                        <i class="bi bi-speedometer2 me-1"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($active_menu ?? '', ['regions', 'villes', 'types']) ? 'active' : '' ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-database me-1"></i> Référentiel
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?= ($active_menu ?? '') === 'regions' ? 'active' : '' ?>" href="<?= base_url('/regions') ?>">
                            <i class="bi bi-map me-2"></i>Régions</a></li>
                        <li><a class="dropdown-item <?= ($active_menu ?? '') === 'villes' ? 'active' : '' ?>" href="<?= base_url('/villes') ?>">
                            <i class="bi bi-building me-2"></i>Villes</a></li>
                        <li><a class="dropdown-item <?= ($active_menu ?? '') === 'types' ? 'active' : '' ?>" href="<?= base_url('/types-besoin') ?>">
                            <i class="bi bi-tags me-2"></i>Types de besoin</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($active_menu ?? '', ['besoins', 'besoins-restants']) ? 'active' : '' ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-card-checklist me-1"></i> Besoins
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?= ($active_menu ?? '') === 'besoins' ? 'active' : '' ?>" href="<?= base_url('/besoins') ?>">
                            <i class="bi bi-list-ul me-2"></i>Tous les besoins</a></li>
                        <li><a class="dropdown-item <?= ($active_menu ?? '') === 'besoins-restants' ? 'active' : '' ?>" href="<?= base_url('/besoins-restants') ?>">
                            <i class="bi bi-exclamation-circle me-2"></i>Besoins restants</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu ?? '') === 'dons' ? 'active' : '' ?>" href="<?= base_url('/dons') ?>">
                        <i class="bi bi-gift me-1"></i> Dons
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu ?? '') === 'dispatch' ? 'active' : '' ?>" href="<?= base_url('/dispatch') ?>">
                        <i class="bi bi-arrow-left-right me-1"></i> Dispatch
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu ?? '') === 'achats' ? 'active' : '' ?>" href="<?= base_url('/achats') ?>">
                        <i class="bi bi-cart me-1"></i> Achats
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu ?? '') === 'recap' ? 'active' : '' ?>" href="<?= base_url('/recap') ?>">
                        <i class="bi bi-clipboard-data me-1"></i> Récap
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-lg-inline">
                    <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y') ?>
                </span>
            </div>
        </div>
    </div>
</nav>

<!-- ===== Main Content ===== -->
<main class="container py-4">

        <?php
        $flash_success = flash('success');
        $flash_error   = flash('error');
        ?>
        <?php if ($flash_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= e($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= e($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
