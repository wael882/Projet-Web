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
$router->add('GET',  '/deconnexion',      [$controller, 'deconnexion']);
$router->add('GET',  '/entreprises',           [$controller, 'entreprises']);
$router->add('GET',  '/entreprise/inscription',        [$controller, 'entrepriseInscription']);
$router->add('POST', '/entreprise/inscription',        [$controller, 'entrepriseInscriptionPost']);
$router->add('GET',  '/admin/entreprises',             [$controller, 'adminEntreprises']);
$router->add('POST', '/admin/entreprise/approuver',    [$controller, 'adminEntrepriseApprouver']);
$router->add('POST', '/admin/entreprise/rejeter',      [$controller, 'adminEntrepriseRejeter']);
$router->add('POST', '/entreprise/evaluer',          [$controller, 'entrepriseEvaluer']);
$router->add('POST', '/entreprise/evaluer/modifier',  [$controller, 'entrepriseEvaluerModifier']);
$router->add('POST', '/entreprise/evaluer/supprimer', [$controller, 'entrepriseEvaluerSupprimer']);
$router->add('POST', '/favoris',           [$controller, 'favorisPost']);
$router->add('POST', '/favoris/supprimer', [$controller, 'favorisDelete']);
$router->add('POST', '/profil',            [$controller, 'profilPost']);
$router->add('GET',  '/pilote',            [$controller, 'pilote']);
$router->add('GET',  '/pilote/etudiant',   [$controller, 'piloteEtudiant']);
$router->add('POST', '/pilote/statut',          [$controller, 'piloteUpdateStatut']);
$router->add('POST', '/pilote/creer-etudiant',    [$controller, 'piloteCreerEtudiant']);
$router->add('POST', '/pilote/supprimer-etudiant', [$controller, 'piloteSupprimerEtudiant']);
$router->add('GET',  '/postuler',          [$controller, 'postuler']);
$router->add('POST', '/candidature',       [$controller, 'candidaturePost']);
$router->add('GET',  '/oubliMdp',          [$controller, 'oubliMdp']);
$router->add('POST', '/oubliMdp',          [$controller, 'oubliMdpPost']);
$router->add('GET',  '/reinitMdp',         [$controller, 'reinitMdp']);
$router->add('POST', '/reinitMdp',         [$controller, 'reinitMdpPost']);
