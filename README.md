# Projet Web — Plateforme de Stages CESI

Application web MVC en PHP permettant aux étudiants de rechercher des offres de stage, gérer leurs candidatures et leur wishlist.

---

## Stack technique

| Technologie | Rôle                    |
| ----------- | ----------------------- |
| PHP 8+      | Backend                 |
| Twig 3      | Moteur de templates     |
| MySQL       | Base de données         |
| PDO         | Connexion BDD           |
| Apache      | Serveur web (WSL2)      |
| Composer    | Gestion des dépendances |

---

## Structure du projet

```
Projet-Web/
├── config/
│   ├── config.php              ← Constantes globales (BDD, BASE_URL, chemins)
│   └── config.php.exemple      ← Exemple de configuration
├── database/
│   ├── schema.sql              ← Structure de la base de données
│   ├── seed.sql                ← Données de test
│   └── migration_*.sql         ← Migrations (wishlist, évaluation, détachement entreprise)
├── public/                     ← DocumentRoot Apache (point d'entrée)
│   ├── index.php               ← Front Controller
│   ├── .htaccess               ← Redirige tout vers index.php
│   ├── css/
│   │   ├── main.css            ← Styles globaux (variables, reset, typographie)
│   │   ├── components/
│   │   │   └── form.css        ← Styles des formulaires
│   │   └── pages/              ← Styles spécifiques à chaque page
│   ├── js/
│   │   ├── main.js             ← Script global
│   │   ├── components/
│   │   │   └── carrousel.js    ← Composant carrousel
│   │   └── pages/
│   │       ├── entreprise.js
│   │       ├── etudiant-formulaire.js
│   │       └── offre-formulaire.js
│   └── image/
│       ├── logo.webp
│       └── etudiante.png
├── src/
│   ├── Controllers/
│   │   └── PageController.php  ← Contrôleur principal
│   ├── Models/
│   │   ├── CandidatureModel.php
│   │   ├── EntrepriseModel.php
│   │   ├── EtudiantModel.php
│   │   ├── OffreModel.php
│   │   ├── PiloteModel.php
│   │   ├── UtilisateurModel.php
│   │   └── WishlistModel.php
│   ├── Test/                   ← Tests unitaires
│   │   ├── TestCandidatureModel.php
│   │   ├── TestEntrepriseModel.php
│   │   ├── TestOffreModel.php
│   │   ├── TestUtilisateurModel.php
│   │   └── TestWishlistModel.php
│   ├── Database.php            ← Connexion PDO singleton
│   ├── Router.php              ← Routeur HTTP
│   └── routes.php              ← Définition des routes
├── templates/                  ← Templates Twig
│   ├── base.html.twig          ← Template parent (utilisateurs connectés)
│   ├── base-index.html.twig    ← Template parent (page d'accueil publique)
│   ├── partials/               ← Composants réutilisables
│   │   ├── header.html.twig
│   │   ├── header-index.html.twig
│   │   ├── footer.html.twig
│   │   └── pagination.html.twig
│   ├── admin/                  ← Vues espace administrateur
│   │   ├── dashboard.twig
│   │   ├── etudiants.twig
│   │   ├── etudiant.twig
│   │   ├── etudiant-creer.twig
│   │   ├── etudiant-modifier.twig
│   │   ├── pilotes.twig
│   │   ├── pilote.twig
│   │   ├── pilote-creer.twig
│   │   ├── pilote-modifier.twig
│   │   ├── entreprises.twig
│   │   ├── entreprises-gerer.twig
│   │   ├── entreprise-modifier.twig
│   │   ├── modifications.twig
│   │   └── suppressions.twig
│   ├── pilote/                 ← Vues espace pilote
│   │   ├── dashboard.twig
│   │   ├── acceuil.twig
│   │   ├── etudiant.twig
│   │   ├── etudiant-modifier.twig
│   │   └── entreprises.twig
│   ├── index.twig              ← Page d'accueil publique
│   ├── acceuil.twig            ← Accueil connecté
│   ├── rechercher.twig
│   ├── offre.twig
│   ├── offre-index.twig
│   ├── offre-creer.twig
│   ├── offre-modifier.twig
│   ├── offre-statistiques.twig
│   ├── entreprise.twig
│   ├── entreprises.twig
│   ├── entreprise-inscription.twig
│   ├── entreprise-modifier.twig
│   ├── mes-entreprises.twig
│   ├── candidature.twig
│   ├── postuler.twig
│   ├── profil.twig
│   ├── favoris.twig
│   ├── identification.twig
│   ├── inscription.twig
│   ├── oubli-mdp.twig
│   ├── reinit-mdp.twig
│   └── a-propos.twig
├── vendor/                     ← Dépendances Composer (ignoré par git)
├── composer.json
├── Makefile
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

Modifier `config/config.php` avec vos paramètres BDD et l'URL de base :

```php
define('BASE_URL', 'http://localhost:8000/'); // ← changer ici si changement de domaine
define('DB_HOST', 'localhost');
define('DB_NAME', 'cesi_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
```

### 5. Configurer Apache

Pointer le `DocumentRoot` vers le dossier `public/` du projet.

### 6. Changer le nom de domaine

**a) Modifier `config/config.php` :**

```php
define('BASE_URL', 'http://ton-domaine.com/');
```

**b) Modifier le VirtualHost Apache :**

```bash
sudo nano /etc/apache2/sites-available/projet-web.conf
# Changer : ServerName localhost
# Par :     ServerName ton-domaine.com
```

**c) Si domaine local (développement), l'ajouter dans `/etc/hosts` :**

```bash
sudo nano /etc/hosts
# Ajouter la ligne :
127.0.0.1   ton-domaine.com
```

**d) Redémarrer Apache :**

```bash
sudo service apache2 restart
```

---

## Flux d'une requête

```
Navigateur → .htaccess → public/index.php → Router → PageController → Model → Twig → HTML
```

---

## Authentification

- Inscription avec vérification d'email existant et hashage du mot de passe (`password_hash`)
- Connexion avec vérification (`password_verify`) et stockage en `$_SESSION`
- Limite de 3 tentatives de connexion avant redirection vers `/oubliMdp`
- Messages de succès/erreur via session flash

---

## Pages disponibles

| Page              | Description                 |
| ----------------- | --------------------------- |
| `/`               | Accueil                     |
| `/rechercher`     | Recherche d'offres de stage |
| `/offre`          | Détail d'une offre          |
| `/entreprise`     | Détail d'une entreprise     |
| `/candidature`    | Gestion des candidatures    |
| `/profil`         | Profil étudiant             |
| `/favoris`        | Wishlist / favoris          |
| `/identification` | Connexion                   |
| `/inscription`    | Inscription                 |
| `/a-propos`       | À propos                    |
