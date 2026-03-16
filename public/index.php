<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Database;
use App\Router;
use App\Controllers\PageController;

// Twig
$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig   = new \Twig\Environment($loader);

// Test connexion BDD
$db = Database::getInstance()->getPdo();

// Router
$router = new Router();
require_once dirname(__DIR__) . '/src/routes.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $uri);
