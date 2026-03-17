<?php

use App\Controllers\PageController;

$controller = new PageController($twig);

$router->add('GET', '/',               [$controller, 'index']);
$router->add('GET', '/index',          [$controller, 'index']);
$router->add('GET', '/acceuil',        [$controller, 'acceuil']);
$router->add('GET', '/identification', [$controller, 'identification']);
$router->add('GET', '/inscription',    [$controller, 'inscription']);
$router->add('GET', '/rechercher',     [$controller, 'rechercher']);
$router->add('GET', '/offre',          [$controller, 'offre']);
$router->add('GET', '/entreprise',     [$controller, 'entreprise']);
$router->add('GET', '/candidature',    [$controller, 'candidature']);
$router->add('GET', '/profil',         [$controller, 'profil']);
$router->add('GET', '/favoris',        [$controller, 'favoris']);
$router->add('GET', '/offre-index',    [$controller, 'offre_index']);
$router->add('GET', '/a-propos',       [$controller, 'a_propos']);
$router->add('POST', '/inscription',   [$controller, 'inscriptionPost']);
$router->add('POST', '/identification',   [$controller, 'login']);
$router->add('GET',  '/logout',           [$controller, 'logout']);