<?php

namespace Config;

use App\Controllers\Api;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}
/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Api');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Api::notfound');
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Api::index');
$routes->group('api', function($routes) {
    // Auth
    $routes->post('auth', 'Api::auth');
    $routes->post('register', 'Api::register');
    $routes->get('users', 'Api::getAllUsers', ['filter' => 'authFilter']);

    // Attendance
    $routes->post('getAttendance', 'Api::getAllAttendance', ['filter' => 'authFilter']);
    $routes->post('setAttendance', 'Api::setAttendance', ['filter' => 'authFilter']);

    // Group
    $routes->post('getGroupData', 'Api::getGroupData', ['filter' => 'authFilter']);
});

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
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
