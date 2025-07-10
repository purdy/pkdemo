<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/check-email', 'Account::checkEmail');
$routes->get('/account', 'Account::index');

