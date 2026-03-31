<?php
// config/db.php — Connexion à la base de données

define('DB_HOST', 'localhost');
define('DB_NAME', 'teranga_azur_db');
define('DB_USER', 'root');       // Modifier selon votre configuration
define('DB_PASS', 'root');           // Modifier selon votre configuration
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Affiche l'erreur réelle pour comprendre le blocage
    die("Erreur PDO : " . $e->getMessage()); 
}
