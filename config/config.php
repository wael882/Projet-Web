<?php

// variable "d'environnement" a utiliser plus tard dans le code 

define('BASE_URL', 'http://cesi-site.local:8000/'); // J'ai corrigé le nom et ajouté le port pour tes futurs liens !
define('DB_HOST', '172.19.128.1');
define('DB_USER', 'cesi_user'); // On utilise le nouvel utilisateur !
define('DB_PASSWORD', 'Root1234!');
define('DB_NAME', 'cesi_db');
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');