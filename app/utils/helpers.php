<?php

/**
 * Helpers utilitaires pour l'application BNGRC
 */

/**
 * Retourne la base URL de l'application
 * Détecte automatiquement le chemin de base - 100% dynamique
 */
function base_url(string $path = ''): string {
    static $baseUrl = null;
    
    if ($baseUrl === null) {
        // Obtenir le chemin du dossier racine de l'application
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        $appRoot = rtrim(str_replace('\\', '/', dirname(__DIR__, 2)), '/');
        
        // Calculer le chemin relatif entre document root et app root
        if ($docRoot && strpos($appRoot, $docRoot) === 0) {
            $baseUrl = substr($appRoot, strlen($docRoot));
        } else {
            // Fallback: utiliser SCRIPT_NAME
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $baseUrl = rtrim(dirname($scriptName), '/\\');
        }
        
        // Nettoyer le résultat
        if ($baseUrl === '.' || $baseUrl === '\\' || $baseUrl === false) {
            $baseUrl = '';
        }
    }
    
    if ($path === '') {
        return $baseUrl ?: '/';
    }
    // S'assurer que le path commence par /
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    return $baseUrl . $path;
}

/**
 * Échapper une chaîne pour l'affichage HTML
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formater un montant en Ariary
 */
function format_ar(float $amount): string {
    return number_format($amount, 0, ',', ' ') . ' Ar';
}

/**
 * Formater un nombre avec séparateur d'espace
 */
function format_nb(float $nb, int $decimals = 0): string {
    return number_format($nb, $decimals, ',', ' ');
}

/**
 * Obtenir le libellé d'une catégorie de besoin
 */
function categorie_label(string $cat): string {
    $labels = [
        'nature'   => 'En nature',
        'materiau' => 'Matériaux',
        'argent'   => 'Argent',
    ];
    return $labels[$cat] ?? $cat;
}

/**
 * Obtenir la classe CSS pour une catégorie
 */
function categorie_badge(string $cat): string {
    $badges = [
        'nature'   => 'bg-success',
        'materiau' => 'bg-warning text-dark',
        'argent'   => 'bg-info text-dark',
    ];
    return $badges[$cat] ?? 'bg-secondary';
}

/**
 * Message flash : setter/getter
 */
function flash(string $key, ?string $value = null): ?string {
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

/**
 * Calculer le pourcentage de couverture
 */
function taux_couverture(float $attribue, float $besoin): float {
    if ($besoin <= 0) {
        return 0;
    }
    return min(100, round(($attribue / $besoin) * 100, 1));
}
