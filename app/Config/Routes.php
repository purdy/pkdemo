<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/check-email', 'Account::checkEmail');
$routes->post('/create-account', 'Account::createAccount');
$routes->get('/login-preflight', 'Account::loginPreflight');
$routes->post('/passkey-login', 'Account::passkeyLogin');
$routes->get('/account', 'Account::index');
$routes->get('/logout', 'Account::logout');
