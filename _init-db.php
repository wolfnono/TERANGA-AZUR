<?php
/**
 * Script d'initialisation automatique de la base de données et des dossiers
 * S'exécute une seule fois au premier chargement
 */

// Flag d'exécution
$init_flag = __DIR__ . '/.init_done';

if (file_exists($init_flag)) {
    http_response_code(304);
    exit('Système déjà initialisé');
}

require_once 'config/db.php';

// 1. Créer la table images_villas
try {
    $pdo->query("SELECT 1 FROM images_villas LIMIT 1");
} catch (Exception $e) {
    $sql = "CREATE TABLE images_villas (
      id INT PRIMARY KEY AUTO_INCREMENT,
      villa_id INT NOT NULL,
      url VARCHAR(255) NOT NULL,
      ordre INT DEFAULT 0,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE,
      INDEX (villa_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
}

// 2. Créer la table images_activites
try {
    $pdo->query("SELECT 1 FROM images_activites LIMIT 1");
} catch (Exception $e) {
    $sql = "CREATE TABLE images_activites (
      id INT PRIMARY KEY AUTO_INCREMENT,
      activite_id INT NOT NULL,
      url VARCHAR(255) NOT NULL,
      ordre INT DEFAULT 0,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE,
      INDEX (activite_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $pdo->exec($sql);
}

// 3. Créer les dossiers
@mkdir(__DIR__ . '/images/villas', 0755, true);
@mkdir(__DIR__ . '/images/activites', 0755, true);

// 4. Créer le flag d'initialisation
file_put_contents($init_flag, date('Y-m-d H:i:s'));

echo json_encode([
    'success' => true,
    'message' => 'Système initialisé avec succès!',
    'tables' => ['images_villas', 'images_activites'],
    'folders' => ['images/villas', 'images/activites']
]);
