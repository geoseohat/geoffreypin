<?php
// Configuration - fichier sans accents pour eviter tout probleme d'encodage

// Base de donnees MySQL (o2switch)
define('DB_HOST', 'localhost');
define('DB_NAME', 'VOTRE_BASE');
define('DB_USER', 'VOTRE_UTILISATEUR');
define('DB_PASS', 'VOTRE_MOT_DE_PASSE');

// Mot de passe pour entrer dans l'outil
define('APP_PASSWORD', 'CHOISISSEZ_UN_MOT_DE_PASSE_FORT');

// Adresses d'envoi
$EXPEDITEURS = [
    [ 'label' => 'geoffrey-pin.fr', 'email' => 'contact@geoffrey-pin.fr', 'nom' => 'Geoffrey Pin' ],
    [ 'label' => 'Mon Gmail perso', 'email' => 'geoffreypin@gmail.com', 'nom' => 'Geoffrey Pin' ],
];

// Reglages d'envoi
define('DELAI_DEFAUT_MIN', 20);
define('FUSEAU', 'Europe/Paris');

// Connexion Gmail (OAuth)
define('GOOGLE_CLIENT_ID', 'VOTRE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'VOTRE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'https://votre-domaine.example/mailer/oauth.php');

date_default_timezone_set(FUSEAU);

// Connexion (ne pas modifier)
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

