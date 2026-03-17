# Justification du Login Wall

## Définition
Le "Login Wall" est le choix de restreindre l'accès à toutes les fonctionnalités du site aux seuls utilisateurs inscrits et connectés. Seules les pages `index.html`, `inscription.html` et `login.html` sont accessibles sans compte.

---

## Arguments justifiant ce choix

### 1. Protection des données partenaires
Les fiches entreprises et offres contiennent des données sensibles (contacts RH, adresses, conditions de stage). Restreindre l'accès évite le scraping automatisé et protège les entreprises partenaires qui ont accepté de partager ces informations dans un cadre fermé.

### 2. Traçabilité des candidatures
Pour qu'un étudiant postule à une offre, le système doit identifier qui postule. Sans compte, aucune candidature ne peut être associée à une personne. L'inscription est donc une nécessité fonctionnelle, pas un simple choix.

### 3. Personnalisation des résultats
Un compte utilisateur permet de filtrer les offres selon le profil de l'étudiant (formation, campus, spécialité, localisation). Sans profil, l'affichage ne peut pas être pertinent.

### 4. Plateforme réservée aux étudiants CESI
Stagio n'est pas un site public. C'est un outil interne au CESI. L'inscription, couplée à une vérification d'adresse email `@cesi.fr`, garantit que seuls les étudiants de l'école accèdent aux ressources.

### 5. Responsabilité juridique
En cas de candidature frauduleuse, de harcèlement d'un recruteur ou d'utilisation abusive des données entreprises, l'inscription permet d'identifier l'auteur. Sans compte, aucune responsabilité ne peut être établie.

### 6. Limitation du spam et des abus
Forcer une inscription avec email valide (idéalement `@cesi.fr`) constitue une barrière contre les bots et les utilisateurs malveillants. Un bot peut difficilement créer des comptes en masse si une vérification email est en place.

### 7. Gestion des rôles utilisateurs
La plateforme distingue plusieurs types d'utilisateurs : étudiant, pilote de promotion, administrateur, représentant d'entreprise. Cette distinction n'est possible qu'avec un système de comptes.

---

## Formulation synthétique (pour oral ou rapport)

> "L'accès restreint aux utilisateurs inscrits garantit la confidentialité des données partenaires, assure la traçabilité des candidatures, permet la personnalisation des résultats et réserve la plateforme aux seuls membres de l'école CESI."

---

## Commandes de lancement

### Démarrer le serveur

```bash
sudo systemctl start apache2
sudo systemctl start mysql
```

### Vérifier que tout tourne

```bash
sudo systemctl status apache2
sudo systemctl status mysql
```

### Accéder au site

```
https://cesi-site.local/
```

### Arrêter le serveur

```bash
sudo systemctl stop apache2
sudo systemctl stop mysql
```

### En cas d'erreur — consulter les logs

```bash
sudo tail -20 /var/log/apache2/cesi-site-error.log
```

### Si `mod_rewrite` n'est pas activé (erreur .htaccess)

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Récupérer l'IP WSL2 (si le site est inaccessible après redémarrage)

```bash
ip addr show eth0 | grep inet
```

Puis mettre à jour `C:\Windows\System32\drivers\etc\hosts` dans PowerShell admin :

```powershell
# Remplacer l'ancienne IP par la nouvelle
notepad C:\Windows\System32\drivers\etc\hosts
ipconfig /flushdns
```
