<?php
/**
 * setup-images.php — Initialise les tables d'images et configure le système
 */
require_once 'config/db.php';

$status = [];

// 1. Créer la table images_villas si elle n'existe pas
try {
    $pdo->query("SELECT 1 FROM images_villas LIMIT 1");
    $status['images_villas'] = ['ok', 'Table images_villas existe'];
} catch (Exception $e) {
    try {
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
        $status['images_villas'] = ['created', 'Table images_villas créée'];
    } catch (Exception $e2) {
        $status['images_villas'] = ['error', $e2->getMessage()];
    }
}

// 2. Créer la table images_activites si elle n'existe pas
try {
    $pdo->query("SELECT 1 FROM images_activites LIMIT 1");
    $status['images_activites'] = ['ok', 'Table images_activites existe'];
} catch (Exception $e) {
    try {
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
        $status['images_activites'] = ['created', 'Table images_activites créée'];
    } catch (Exception $e2) {
        $status['images_activites'] = ['error', $e2->getMessage()];
    }
}

// 3. Vérifier les dossiers
$dirs = [
    'images/villas' => 'Dossier des images de villas',
    'images/activites' => 'Dossier des images d\'activités'
];

foreach ($dirs as $dir => $desc) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        if (@mkdir($path, 0755, true)) {
            $status[$dir] = ['created', "$desc créé"];
        } else {
            $status[$dir] = ['error', "$desc impossible à créer"];
        }
    } else {
        $status[$dir] = ['ok', "$desc existe"];
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Configuration Images</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #1a3a2e 0%, #2d5a4f 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .container {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 10px 40px rgba(26,58,46,.3);
      max-width: 700px;
      width: 100%;
      padding: 40px;
    }
    h1 { color: #1a3a2e; font-size: 1.8rem; margin-bottom: 10px; }
    .status-list { display: flex; flex-direction: column; gap: 12px; margin: 30px 0; }
    .status-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px;
      border-radius: 9px;
      background: #f8f5f0;
      border-left: 4px solid #e8e2d8;
    }
    .status-item.ok { border-left-color: #22c55e; background: #f0fdf4; }
    .status-item.ok i { color: #22c55e; }
    .status-item.created { border-left-color: #3b82f6; background: #eff6ff; }
    .status-item.created i { color: #3b82f6; }
    .status-item.error { border-left-color: #ef4444; background: #fef2f2; }
    .status-item.error i { color: #ef4444; }
    .status-content { flex: 1; }
    .status-label { font-weight: 600; color: #1a3a2e; }
    .status-desc { color: #546474; font-size: 0.85rem; margin-top: 2px; }
    .actions {
      display: flex;
      gap: 14px;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    .btn {
      flex: 1;
      min-width: 140px;
      padding: 12px 24px;
      border: none;
      border-radius: 9px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all .2s;
    }
    .btn-primary {
      background: linear-gradient(135deg, #2d5a4f 0%, #4a8c7d 100%);
      color: #fff;
    }
    .btn-primary:hover {
      box-shadow: 0 4px 20px rgba(45,90,79,.55);
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: #f0f0f0;
      color: #1a3a2e;
      border: 1px solid #e8e2d8;
    }
    .btn-secondary:hover { background: #e8e2d8; }
  </style>
</head>
<body>
<div class="container">
  <h1><i class="fas fa-images" style="color:#d4af5a;margin-right:10px;"></i>Configuration du système d'images</h1>

  <div class="status-list">
    <?php foreach ($status as $item => $data): ?>
    <div class="status-item <?= $data[0] ?>">
      <i class="fas fa-<?= $data[0] === 'ok' ? 'check-circle' : ($data[0] === 'created' ? 'database' : 'times-circle') ?>"></i>
      <div class="status-content">
        <div class="status-label"><?= htmlspecialchars($item) ?></div>
        <div class="status-desc"><?= htmlspecialchars($data[1]) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="actions">
    <a href="admin/villas.php" class="btn btn-primary">
      <i class="fas fa-home"></i> Gérer les villas
    </a>
    <a href="index.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </a>
  </div>
</div>
</body>
</html>
