<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Database;
use App\Router;
use App\Controllers\PageController;

// Twig
$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new \Twig\Environment($loader, ['cache' => dirname(__DIR__) . '/cache/twig', 'auto_reload' => true]);
$twig->addGlobal('base_url', '/');
$twig->addGlobal('user', $_SESSION['user'] ?? null);

// Génération du token CSRF (une fois par session)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Variables globales disponibles dans tous les templates
$twig->addGlobal('csrf_token', $_SESSION['csrf_token']);
$twig->addGlobal('user', $_SESSION['user'] ?? null);
$twig->addGlobal('base_url', BASE_URL);

// Test connexion BDD
$db = Database::getInstance()->getPdo();

// Entreprise de l'utilisateur connecté (global Twig)
$aEntreprise = false;
if (!empty($_SESSION['user'])) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM ENTREPRISE WHERE id_utilisateur = :id AND statut = "approuvee" AND active = TRUE');
    $stmt->execute([':id' => $_SESSION['user']['id_utilisateur']]);
    $aEntreprise = (int) $stmt->fetchColumn() > 0;
}
$twig->addGlobal('aEntreprise', $aEntreprise);

// Router
$router = new Router();
require_once dirname(__DIR__) . '/src/routes.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $uri);
