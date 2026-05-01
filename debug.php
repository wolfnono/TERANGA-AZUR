<?php
require_once 'config/db.php';

echo "<pre style='background:#000;color:#0f0;padding:20px;font-family:monospace;'>";

// 1. Vérifier les tables
echo "=== TABLES ===\n";
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'images%'")->fetchAll();
    echo "Tables images: " . count($tables) . "\n";
    foreach ($tables as $t) {
        echo "  - " . reset($t) . "\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

// 2. Vérifier le contenu
echo "\n=== IMAGES VILLAS ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) as c FROM images_villas")->fetch()['c'];
    echo "Total: $count images\n";
    $imgs = $pdo->query("SELECT * FROM images_villas LIMIT 5")->fetchAll();
    foreach ($imgs as $img) {
        echo "  ID: {$img['id']}, Villa: {$img['villa_id']}, URL: {$img['url']}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== IMAGES ACTIVITES ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) as c FROM images_activites")->fetch()['c'];
    echo "Total: $count images\n";
    $imgs = $pdo->query("SELECT * FROM images_activites LIMIT 5")->fetchAll();
    foreach ($imgs as $img) {
        echo "  ID: {$img['id']}, Activité: {$img['activite_id']}, URL: {$img['url']}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

// 3. Vérifier dossiers
echo "\n=== DOSSIERS ===\n";
echo "images/villas/ existe: " . (is_dir('images/villas') ? "OUI" : "NON") . "\n";
echo "images/activites/ existe: " . (is_dir('images/activites') ? "OUI" : "NON") . "\n";
if (is_dir('images/villas')) {
    $files = array_filter(scandir('images/villas'), fn($f) => $f !== '.' && $f !== '..');
    echo "  Fichiers: " . count($files) . "\n";
    foreach ($files as $f) echo "    - $f\n";
}
if (is_dir('images/activites')) {
    $files = array_filter(scandir('images/activites'), fn($f) => $f !== '.' && $f !== '..');
    echo "  Fichiers: " . count($files) . "\n";
    foreach ($files as $f) echo "    - $f\n";
}

echo "\n</pre>";
?>
<a href="activites.php" style="display:inline-block;margin:20px;padding:10px 20px;background:#1a3a2e;color:#fff;border-radius:8px;text-decoration:none;">← Retour</a>
