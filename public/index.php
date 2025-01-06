<?php
// connect Composer autoloader
require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Core\Router;

App::getInstance();

// Run router
$router = new Router();
$router->dispatch();
