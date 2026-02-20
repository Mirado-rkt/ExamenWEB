<?php

use app\controllers\DashboardController;
use app\controllers\RegionController;
use app\controllers\VilleController;
use app\controllers\TypeBesoinController;
use app\controllers\BesoinController;
use app\controllers\DonController;
use app\controllers\DispatchController;
use app\controllers\AchatController;
use app\controllers\BesoinsRestantsController;
use app\controllers\RecapController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\net\Router;

/**
 * @var Router $router
 */

$router->group('', function (Router $router) {

	// ===== TABLEAU DE BORD =====
	$router->get('/', [DashboardController::class, 'index']);
	$router->post('/reinitialiser', [DashboardController::class, 'reinitialiser']);

	// ===== RÉGIONS =====
	$router->get('/regions', [RegionController::class, 'index']);
	$router->get('/regions/create', [RegionController::class, 'create']);
	$router->post('/regions/store', [RegionController::class, 'store']);
	$router->get('/regions/edit/@id', [RegionController::class, 'edit']);
	$router->post('/regions/update/@id', [RegionController::class, 'update']);
	$router->post('/regions/delete/@id', [RegionController::class, 'delete']);

	// ===== VILLES =====
	$router->get('/villes', [VilleController::class, 'index']);
	$router->get('/villes/create', [VilleController::class, 'create']);
	$router->post('/villes/store', [VilleController::class, 'store']);
	$router->get('/villes/edit/@id', [VilleController::class, 'edit']);
	$router->post('/villes/update/@id', [VilleController::class, 'update']);
	$router->post('/villes/delete/@id', [VilleController::class, 'delete']);

	// ===== TYPES DE BESOIN =====
	$router->get('/types-besoin', [TypeBesoinController::class, 'index']);
	$router->get('/types-besoin/create', [TypeBesoinController::class, 'create']);
	$router->post('/types-besoin/store', [TypeBesoinController::class, 'store']);
	$router->get('/types-besoin/edit/@id', [TypeBesoinController::class, 'edit']);
	$router->post('/types-besoin/update/@id', [TypeBesoinController::class, 'update']);
	$router->post('/types-besoin/delete/@id', [TypeBesoinController::class, 'delete']);

	// ===== BESOINS =====
	$router->get('/besoins', [BesoinController::class, 'index']);
	$router->get('/besoins/create', [BesoinController::class, 'create']);
	$router->post('/besoins/store', [BesoinController::class, 'store']);
	$router->get('/besoins/edit/@id', [BesoinController::class, 'edit']);
	$router->post('/besoins/update/@id', [BesoinController::class, 'update']);
	$router->post('/besoins/delete/@id', [BesoinController::class, 'delete']);

	// ===== DONS =====
	$router->get('/dons', [DonController::class, 'index']);
	$router->get('/dons/create', [DonController::class, 'create']);
	$router->post('/dons/store', [DonController::class, 'store']);
	$router->get('/dons/show/@id', [DonController::class, 'show']);
	$router->post('/dons/delete/@id', [DonController::class, 'delete']);

	// ===== DISPATCH =====
	$router->get('/dispatch', [DispatchController::class, 'index']);
	$router->post('/dispatch/initialiser', [DispatchController::class, 'initialiser']);
	$router->post('/dispatch/reinitialiser', [DispatchController::class, 'reinitialiser']);
	$router->post('/dispatch/simuler', [DispatchController::class, 'simuler']);
	$router->post('/dispatch/valider', [DispatchController::class, 'valider']);

	// ===== BESOINS RESTANTS =====
	$router->get('/besoins-restants', [BesoinsRestantsController::class, 'index']);

	// ===== ACHATS =====
	$router->get('/achats', [AchatController::class, 'index']);
	$router->get('/achats/create', [AchatController::class, 'create']);
	$router->post('/achats/store', [AchatController::class, 'store']);
	$router->post('/achats/delete/@id', [AchatController::class, 'delete']);

	// ===== RÉCAPITULATION =====
	$router->get('/recap', [RecapController::class, 'index']);
	$router->get('/recap/data', [RecapController::class, 'getData']);

}, [SecurityHeadersMiddleware::class]);