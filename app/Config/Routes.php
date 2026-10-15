<?php
namespace Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes = Services::routes();
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);
$routes->get('/', 'Home::index');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::create');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate');
$routes->post('logout', 'Auth::logout');
$routes->get('requests', 'ServiceRequests::form');
$routes->get('employee/requests', 'ServiceRequests::index');
$routes->post('requests', 'ServiceRequests::create');
