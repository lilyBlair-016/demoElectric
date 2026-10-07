<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/about', 'About::index');
$routes->get('/services', 'Services::index');
$routes->match(['get', 'post'], '/contact', 'Contact::index');
$routes->get('/register', 'Register::index');
$routes->post('/register', 'Register::create');

// Keep Home::index and all existing public pages above.
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => ['auth', 'csrf']]);

$routes->group('', ['filter' => ['auth', 'csrf']], static function ($routes) {
    $routes->get('dashboard', 'CustomerAccounts::index');
    $routes->get('customer-accounts', 'CustomerAccounts::index');
    $routes->get('customer-accounts/create', 'CustomerAccounts::create');
    $routes->post('customer-accounts/store', 'CustomerAccounts::store');
    $routes->get('customer-accounts/view/(:num)', 'CustomerAccounts::view/$1');
    $routes->get('customer-accounts/edit/(:num)', 'CustomerAccounts::edit/$1');
    $routes->post('customer-accounts/update/(:num)', 'CustomerAccounts::update/$1');
    $routes->post('customer-accounts/delete/(:num)', 'CustomerAccounts::delete/$1');
});
