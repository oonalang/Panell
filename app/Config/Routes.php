<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
if (ENVIRONMENT === 'development') {
    $routes->get('dbg', 'Auth::index');
}
$routes->get('logout', 'Auth::logout');
$routes->get('dashboard', 'User::index');
$routes->match(['get', 'post'], '/', 'Auth::login');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->match(['get', 'post'], 'register', 'Auth::register');//Server



//
$routes->match(['get', 'post'], 'settings', 'User::settings');
$routes->match(['get', 'post'], 'verify_pass', 'User::verify_pass');
$routes->match(['get', 'post'], 'token', 'User::token');
$routes->match(['get', 'post'], 'Forget', 'User::Forget');
$routes->match(['get', 'post'], 'update', 'User::update');
$routes->match(['get', 'post'], 'Server', 'User::Server');
$routes->match(['get'],'alter','Keys::alterUser');
$routes->match(['get', 'post'], 'lib', 'User::lib');
$routes->match(['get', 'post'], 'PrivateDashboard', 'User::PrivateDashboard');
$routes->match(['get', 'post'], 'ManageHackingAttempt', 'User::ManageHackingAttempt');
$routes->match(['get', 'post'], 'ExtendDuration', 'User::ExtendDuration');
$routes->match(['get', 'post'], 'ManageShortenerLinks', 'User::ManageShortenerLinks');

$routes->match(['get', 'post'], 'Links', 'User::Links');
$routes->match(['get', 'post'], 'DeleteAllHistory', 'User::DeleteAllHistory');
$routes->match(['get', 'post'], 'ClearAllHistory', 'User::ClearAllHistory');





//
//testing
$routes->match(['get', 'post'], 'New', 'Home::index');
//$routes->get('server', 'User::server');
//

$routes->group('keys', function ($routes) {
	$routes->match(['get', 'post'], '/', 'Keys::index');
	$routes->match(['get', 'post'], 'generate', 'Keys::generate');
	$routes->match(['get', 'post'], 'ExtendDurationKey', 'Keys::ExtendDurationKey');
	$routes->get('(:num)', 'Keys::edit_key/$1');
	$routes->get('reset', 'Keys::api_key_reset');
	$routes->post('edit', 'Keys::edit_key');
	$routes->match(['get', 'post'], 'api', 'Keys::api_get_keys');
	
	$routes->match(['get'],'alter','Keys::alterKeys');
	$routes->match(['get'],'download/all','Keys::download_all_Keys');
	$routes->match(['get'],'download/new','Keys::download_new_Keys');
	$routes->get('delete', 'Keys::api_key_delete');
	$routes->match(['get'],'delete','Keys::deleteKeys');
	$routes->match(['get'],'start','Keys::startDate');
	$routes->match(['get'],'DelKeygen','Keys::deleteallkeygen');
    $routes->match(['get'],'Un-UsedKeygen','Keys::Unusedkeygen');
    $routes->match(['get'],'Del-EXPKeygen','Keys::deleteEXPkeygen');
    $routes->match(['get'],'DelblockKeys','Keys::deleteBLOCKKeys');
    $routes->match(['get'],'DelEXPKeys','Keys::deleteEXPKeys');
    $routes->match(['get'],'resetAll','Keys::resetDeviceKey');
});

$routes->group('admin', ['filter' => 'admin'], function ($routes) {
	$routes->match(['get', 'post'], 'create-referral', 'User::ref_index');
	$routes->match(['get', 'post'], 'manage-users', 'User::manage_users');
	$routes->match(['get', 'post'], 'user/(:num)', 'User::user_edit/$1');
	$routes->match(['get'],'user/delete/(:num)','User::user_delete/$1');
	
	$routes->match(['get', 'post'], 'referral/(:num)', 'User::referral_edit/$1');
	$routes->match(['get'],'referral/delete/(:num)','User::referral_delete/$1');
	
	$routes->match(['get', 'post'], 'EditReferral', 'User::EditReferral');

	/* --------------------------- Admin API Grouping -------------------------- */
	$routes->group('api', function ($routes) {
		$routes->match(['get', 'post'], 'users', 'User::api_get_users');
	});
});

// Read-only public catalog. The legacy alias preserves the original loader path.
$routes->get('loader/catalog', 'LoaderCatalog::index');
$routes->get('Loader/index.php', 'LoaderCatalog::index');
$routes->get('admin/loader-catalog', 'LoaderCatalogAdmin::index', ['filter' => 'admin']);
$routes->post('admin/loader-catalog/save', 'LoaderCatalogAdmin::save', ['filter' => 'admin']);
$routes->post('admin/loader-catalog/remove', 'LoaderCatalogAdmin::remove', ['filter' => 'admin']);

$routes->match(['get', 'post'], 'connect', 'Connect::index');
$routes->match(['get', 'post'], 'member-key', 'Keys::memberKey');
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
