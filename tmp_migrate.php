<?php
require_once 'config/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS images_activites (
        id       INT AUTO_INCREMENT PRIMARY KEY,
        activite_id INT NOT NULL,
        url      VARCHAR(255) NOT NULL,
        ordre    INT DEFAULT 0,
        FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE
    )");
    echo "Table images_activites created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
