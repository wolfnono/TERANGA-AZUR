<?php
// config/db.php — Connexion à la base de données

define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    3306);          
define('DB_NAME',    'teranga_azur_db');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST
         . ";port="      . DB_PORT
         . ";dbname="    . DB_NAME
         . ";charset="   . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Erreur DB : " . $e->getMessage());
    die(json_encode(['error' => 'Connexion à la base de données impossible.']));
}
