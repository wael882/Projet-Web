# Projet Web — Plateforme de Stages CESI

Application web MVC en PHP permettant aux étudiants de rechercher des offres de stage, gérer leurs candidatures et leur wishlist.

---

## Stack technique

| Technologie | Rôle |
|-------------|------|
| PHP 8+      | Backend |
| Twig 3      | Moteur de templates |
| MySQL       | Base de données |
| PDO         | Connexion BDD |
| Apache      | Serveur web (WSL2) |
| Composer    | Gestion des dépendances |

---

## Structure du projet

```
Projet-Web-main/
├── config/
│   └── config.php              ← Constantes globales (BDD, BASE_URL, chemins)
├── database/
│   ├── schema.sql              ← Structure de la base de données
│   └── seed.sql                ← Données de test
├── public/                     ← DocumentRoot Apache (point d'entrée)
│   ├── index.php               ← Front Controller
│   └── .htaccess               ← Redirige tout vers index.php
├── src/
│   ├── Controllers/
│   │   └── PageController.php  ← Contrôleur principal
│   ├── Models/
│   │   ├── CandidatureModel.php
│   │   ├── EntrepriseModel.php
│   │   ├── OffreModel.php
│   │   ├── UtilisateurModel.php
│   │   └── WishlistModel.php
│   ├── Database.php            ← Connexion PDO singleton
│   ├── Router.php              ← Routeur HTTP
│   └── routes.php              ← Définition des routes
├── templates/                  ← Templates Twig
│   ├── base.html.twig          ← Template parent
│   ├── partials/               ← Composants réutilisables (header, footer, pagination)
│   ├── acceuil.twig
│   ├── rechercher.twig
│   ├── offre.twig
│   ├── entreprise.twig
│   ├── candidature.twig
│   ├── profil.twig
│   ├── favoris.twig
│   ├── identification.twig
│   └── inscription.twig
├── vendor/                     ← Dépendances Composer (ignoré par git)
├── composer.json
└── .gitignore
```

---

## Installation

### Prérequis

- WSL2 avec Ubuntu
- Apache, MySQL, PHP 8+
- Composer

### 1. Cloner le projet

```bash
git clone https://github.com/wael882/Projet-Web.git
cd Projet-Web
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer la base de données

```bash
mysql -u root -p
```

```sql
CREATE DATABASE cesi_stages;
USE cesi_stages;
SOURCE database/schema.sql;
SOURCE database/seed.sql;
```

### 4. Configurer l'application

Modifier `config/config.php` avec vos paramètres BDD :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cesi_stages');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Configurer Apache

Pointer le `DocumentRoot` vers le dossier `public/` du projet.

---

## Flux d'une requête

```
Navigateur → .htaccess → public/index.php → Router → PageController → Model → Twig → HTML
```

---

## Pages disponibles

| Page | Description |
|------|-------------|
| `/` | Accueil |
| `/rechercher` | Recherche d'offres de stage |
| `/offre` | Détail d'une offre |
| `/entreprise` | Détail d'une entreprise |
| `/candidature` | Gestion des candidatures |
| `/profil` | Profil étudiant |
| `/favoris` | Wishlist / favoris |
| `/identification` | Connexion |
| `/inscription` | Inscription |
| `/a-propos` | À propos |
