# Suivi Installation LAMP + VHosts Apache (WSL2 sur Windows)

---

## Etape 1.1 — Installation LAMP

Dans WSL :

```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation Apache, MySQL, PHP et extensions
sudo apt install apache2 mysql-server php php-pdo php-mysql php-mbstring php-xml php-curl unzip -y

# Démarrage et activation Apache
sudo systemctl start apache2
sudo systemctl enable apache2

# Démarrage et activation MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Installation Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Vérification
composer --version
sudo systemctl status apache2
```

### Fichiers importants

| Fichier/Dossier             | Rôle                            |
| --------------------------- | ------------------------------- |
| `/etc/apache2/apache2.conf` | Config principale Apache        |
| `/etc/apache2/`             | Dossier de configuration Apache |
| `/var/www/html/`            | Racine web par défaut           |

---

## Etape 1.2 — Configuration des VHosts

### 1. Créer les dossiers

```bash
sudo mkdir -p /var/www/cesi-site/public
sudo mkdir -p /var/www/cesi-static
sudo chown -R $USER:$USER /var/www/cesi-site
sudo chown -R $USER:$USER /var/www/cesi-static

# Pages de test
echo "<h1>CESI Site - OK</h1>" > /var/www/cesi-site/public/index.html
echo "<h1>CESI Static - OK</h1>" > /var/www/cesi-static/index.html
```

### 2. Créer le VHost principal — `/etc/apache2/sites-available/cesi-site.local.conf`

```apache
<VirtualHost *:80>
    ServerName cesi-site.local
    Redirect permanent / https://cesi-site.local/
</VirtualHost>

<VirtualHost *:443>
    ServerName cesi-site.local
    DocumentRoot /var/www/cesi-site/public

    SSLEngine on
    SSLCertificateFile    /etc/apache2/ssl/cesi-site.local+1.pem
    SSLCertificateKeyFile /etc/apache2/ssl/cesi-site.local+1-key.pem

    <Directory /var/www/cesi-site/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cesi-site-error.log
    CustomLog ${APACHE_LOG_DIR}/cesi-site-access.log combined
</VirtualHost>
```

### 3. Créer le VHost statique — `/etc/apache2/sites-available/cesi-static.local.conf`

```apache
<VirtualHost *:80>
    ServerName cesi-static.local
    Redirect permanent / https://cesi-static.local/
</VirtualHost>

<VirtualHost *:443>
    ServerName cesi-static.local
    DocumentRoot /var/www/cesi-static

    SSLEngine on
    SSLCertificateFile    /etc/apache2/ssl/cesi-site.local+1.pem
    SSLCertificateKeyFile /etc/apache2/ssl/cesi-site.local+1-key.pem

    <Directory /var/www/cesi-static>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/cesi-static-error.log
    CustomLog ${APACHE_LOG_DIR}/cesi-static-access.log combined
</VirtualHost>
```

### 4. Activer les vhosts et modules

```bash
sudo a2ensite cesi-site.local.conf
sudo a2ensite cesi-static.local.conf
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl reload apache2
```

### 5. Fichier .htaccess (racine `/var/www/cesi-site/public/.htaccess`)

```apache
Options -Indexes
RewriteEngine On
```

---

## Etape 1.2 (suite) — HTTPS avec mkcert (WSL2 + Windows)

> Sous WSL2, il faut générer les certificats avec mkcert **Windows** (pas WSL)
> pour que Chrome les reconnaisse.

### 1. Installer mkcert dans WSL (pour le CA local)

```bash
sudo apt install libnss3-tools -y
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert
mkcert -install
```

### 2. Installer mkcert sur Windows et installer le CA

Dans PowerShell admin :

```powershell
winget install FiloSottile.mkcert
# Fermer et rouvrir PowerShell admin
mkcert -install
```

### 3. Générer les certificats depuis Windows

Dans PowerShell admin :

```powershell
cd C:\Users\drwae\Documents
mkcert cesi-site.local cesi-static.local
```

Cela génère :

- `cesi-site.local+1.pem`
- `cesi-site.local+1-key.pem`

### 4. Copier les certificats vers WSL

Dans WSL :

```bash
sudo mkdir -p /etc/apache2/ssl
sudo cp /mnt/c/Users/drwae/Documents/cesi-site.local+1.pem /etc/apache2/ssl/
sudo cp /mnt/c/Users/drwae/Documents/cesi-site.local+1-key.pem /etc/apache2/ssl/
sudo systemctl reload apache2
```

---

## Etape 1.2 (suite) — Fichier hosts Windows (WSL2)

> Sous WSL2, le navigateur Windows ne peut pas accéder via `127.0.0.1`.
> Il faut utiliser l'IP réelle de WSL2.

### 1. Récupérer l'IP WSL2

Dans WSL :

```bash
ip addr show eth0 | grep inet
# Ex : 172.20.128.168
```

### 2. Ajouter au fichier hosts Windows

Dans PowerShell admin :

```powershell
Add-Content -Path "C:\Windows\System32\drivers\etc\hosts" -Value "172.20.128.168   cesi-site.local"
Add-Content -Path "C:\Windows\System32\drivers\etc\hosts" -Value "172.20.128.168   cesi-static.local"

ipconfig /flushdns
```

> **Important :** L'IP WSL2 peut changer à chaque redémarrage.
> Si les sites deviennent inaccessibles, relancer `ip addr show eth0 | grep inet`
> et mettre à jour `C:\Windows\System32\drivers\etc\hosts` avec la nouvelle IP.

---

## Etape 1.3 — Initialisation Git & Composer

### 1. Git init et .gitignore

```bash
cd /var/www/cesi-site
git init

nano .gitignore
```

Contenu du `.gitignore` :

```
vendor/
uploads/
.env
logs/
```

### 2. Créer le `composer.json`

```bash
nano /var/www/cesi-site/composer.json
```

Contenu :

```json
{
  "name": "cesi/site",
  "require": {
    "twig/twig": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

### 3. Installer les dépendances

```bash
cd /var/www/cesi-site
composer install
```

### 4. Créer le dossier templates et tester l'autoload

```bash
mkdir /var/www/cesi-site/templates
echo "Autoload OK - Twig chargé !" > /var/www/cesi-site/templates/test.html
```

Créer `/var/www/cesi-site/public/test.php` :

```php
<?php
require '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

echo "Autoload OK - Twig chargé !";
```

Tester : **https://cesi-site.local/test.php** → `Autoload OK - Twig chargé !`

### 5. Premier commit

> Si un `.git` existe dans `public/`, le supprimer d'abord :
>
> ```bash
> rm -rf /var/www/cesi-site/public/.git
> ```

```bash
cd /var/www/cesi-site
git add .
git commit -m "Init projet – structure de base"
git log
```

### Structure finale du projet

```
/var/www/cesi-site/
├── public/          ← DocumentRoot Apache
│   ├── index.html
│   └── test.php
├── src/             ← autoload PSR-4 (App\)
├── templates/       ← templates Twig
├── vendor/          ← généré par Composer (ignoré par git)
├── .gitignore
└── composer.json
```

---

## Etape 3.1 — Structure MVC

### Fichiers créés

| Fichier                              | Rôle                                                                |
| ------------------------------------ | ------------------------------------------------------------------- |
| `public/index.php`                   | Front Controller — point d'entrée unique, router via `$_GET['uri']` |
| `public/.htaccess`                   | Redirige toutes les requêtes vers `index.php`                       |
| `config/config.php`                  | Constantes globales : `BASE_URL`, `ROOT_PATH`, `UPLOAD_PATH`, BDD   |
| `src/Controllers/PageController.php` | Controller unique avec une méthode par page                         |
| `templates/*.twig`                   | 10 templates Twig (migration depuis les `.html` statiques)          |

### Structure finale du projet

```
/var/www/cesi-site/
├── config/
│   └── config.php          ← constantes globales
├── public/                 ← DocumentRoot Apache
│   ├── index.php           ← Front Controller (point d'entrée unique)
│   ├── .htaccess           ← redirige tout vers index.php
│   ├── style.css
│   └── image/
├── src/
│   └── Controllers/
│       └── PageController.php
├── templates/              ← templates Twig
│   ├── acceuil.twig
│   ├── identification.twig
│   ├── inscription.twig
│   ├── rechercher.twig
│   ├── offre.twig
│   ├── entreprise.twig
│   ├── candidature.twig
│   ├── profil.twig
│   └── favoris.twig
├── vendor/                 ← Composer (ignoré par git)
├── .gitignore
└── composer.json
```

### Flux d'une requête

```
Navigateur → .htaccess → index.php → PageController → Twig → HTML
```

---

## Verification finale

| Test                 | Commande / URL                   | Résultat attendu              |
| -------------------- | -------------------------------- | ----------------------------- |
| Apache actif         | `sudo systemctl status apache2`  | active (running)              |
| Config Apache        | `sudo apache2ctl configtest`     | Syntax OK                     |
| Site principal HTTP  | http://cesi-site.local           | Redirige vers HTTPS           |
| Site principal HTTPS | https://cesi-site.local          | Cadenas vert                  |
| Site statique HTTPS  | https://cesi-static.local        | Cadenas vert                  |
| Composer             | `composer --version`             | Version affichée              |
| Twig                 | https://cesi-site.local/test.php | "Autoload OK - Twig chargé !" |
| Git                  | `git log`                        | Commit visible                |
