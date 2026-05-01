<?php
/**
 * Installation du système de paiement fictif
 * Page d'installation et de test du système de réservation
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db.php';

$status = [];
$all_ok = true;

// 1. Vérifier l'existence de la table reservations
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
    $status['reservations_table'] = ['ok', 'Table reservations existe'];
} catch (Exception $e) {
    // Créer la table
    try {
        $sql = "CREATE TABLE IF NOT EXISTS reservations (
          id INT PRIMARY KEY AUTO_INCREMENT,
          type VARCHAR(50) NOT NULL,
          item_id INT NOT NULL,
          client_id INT,
          date_arrivee DATE NOT NULL,
          date_depart DATE NOT NULL,
          nombre_nuits INT DEFAULT 0,
          nombre_personnes INT DEFAULT 1,
          prix_total DECIMAL(10,2) NOT NULL,
          statut VARCHAR(50) DEFAULT 'en_attente',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $pdo->exec($sql);
        $status['reservations_table'] = ['created', 'Table reservations créée'];
    } catch (Exception $e2) {
        $status['reservations_table'] = ['error', 'Erreur: ' . $e2->getMessage()];
        $all_ok = false;
    }
}

// 2. Vérifier l'existence des fichiers
$files_to_check = [
    'checkout.php' => 'Page de sélection des dates',
    'paiement.php' => 'Page de paiement fictif',
    'confirmation.php' => 'Page de confirmation',
];

foreach ($files_to_check as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $status[$file] = ['ok', $desc];
    } else {
        $status[$file] = ['error', $desc . ' - MANQUANT!'];
        $all_ok = false;
    }
}

// 3. Vérifier les fichiers modifiés
$modified_files = [
    'villas.php' => 'Bouton Réserver ajouté',
    'activites.php' => 'Bouton Réserver mis à jour',
];

foreach ($modified_files as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, 'checkout.php') !== false) {
            $status[$file] = ['ok', $desc];
        } else {
            $status[$file] = ['warning', $desc . ' - À VÉRIFIER'];
        }
    } else {
        $status[$file] = ['error', $desc . ' - MANQUANT!'];
        $all_ok = false;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Installation Système de Paiement</title>
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
    .install-container {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 10px 40px rgba(26,58,46,.3);
      max-width: 700px;
      width: 100%;
      padding: 40px;
    }
    .install-header {
      text-align: center;
      margin-bottom: 40px;
    }
    .install-header h1 {
      color: #1a3a2e;
      font-size: 1.8rem;
      margin-bottom: 10px;
    }
    .install-header p {
      color: #546474;
      font-size: 0.95rem;
    }
    .status-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .status-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px;
      border-radius: 9px;
      background: #f8f5f0;
      border-left: 4px solid #e8e2d8;
    }
    .status-item.ok {
      border-left-color: #22c55e;
      background: #f0fdf4;
    }
    .status-item.ok i {
      color: #22c55e;
      font-size: 1.3rem;
    }
    .status-item.error {
      border-left-color: #ef4444;
      background: #fef2f2;
    }
    .status-item.error i {
      color: #ef4444;
      font-size: 1.3rem;
    }
    .status-item.warning {
      border-left-color: #f59e0b;
      background: #fffbeb;
    }
    .status-item.warning i {
      color: #f59e0b;
      font-size: 1.3rem;
    }
    .status-item.created {
      border-left-color: #3b82f6;
      background: #eff6ff;
    }
    .status-item.created i {
      color: #3b82f6;
      font-size: 1.3rem;
    }
    .status-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .status-label {
      font-weight: 600;
      color: #1a3a2e;
      font-size: 0.9rem;
    }
    .status-desc {
      color: #546474;
      font-size: 0.8rem;
      margin-top: 2px;
    }
    .install-actions {
      display: flex;
      gap: 12px;
      margin-top: 40px;
      flex-wrap: wrap;
    }
    .btn {
      flex: 1;
      min-width: 150px;
      padding: 12px 24px;
      border: none;
      border-radius: 9px;
      font-size: 0.9rem;
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
      box-shadow: 0 2px 12px rgba(45,90,79,.4);
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
    .btn-secondary:hover {
      background: #e8e2d8;
    }
    .alert {
      padding: 16px;
      border-radius: 9px;
      margin-bottom: 20px;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }
    .alert-success {
      background: #f0fdf4;
      border: 1px solid #86efac;
      color: #15803d;
    }
    .alert-success i {
      color: #22c55e;
      font-size: 1.3rem;
    }
    .alert-warning {
      background: #fef3c7;
      border: 1px solid #fcd34d;
      color: #92400e;
    }
    .alert-warning i {
      color: #f59e0b;
      font-size: 1.3rem;
    }
    .test-section {
      margin-top: 40px;
      padding-top: 40px;
      border-top: 1px solid #e8e2d8;
    }
    .test-section h3 {
      color: #1a3a2e;
      margin-bottom: 16px;
      font-size: 1.1rem;
    }
    .test-flow {
      background: #f8f5f0;
      border-radius: 9px;
      padding: 16px;
      font-size: 0.9rem;
      color: #546474;
      line-height: 1.8;
    }
    .test-flow ol {
      margin-left: 20px;
    }
    .test-flow li {
      margin-bottom: 8px;
    }
  </style>
</head>
<body>

<div class="install-container">
  <div class="install-header">
    <h1><i class="fas fa-cog" style="color:#d4af5a;margin-right:10px;"></i>Installation</h1>
    <p>Système de paiement fictif Teranga Azur</p>
  </div>

  <?php if ($all_ok): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <div>
      <strong>Excellent!</strong> Tous les composants sont en place et prêts à être testés.
    </div>
  </div>
  <?php else: ?>
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
      <strong>Attention!</strong> Certains composants manquent ou nécessitent attention.
    </div>
  </div>
  <?php endif; ?>

  <div class="status-list">
    <?php foreach ($status as $item => $data): ?>
    <div class="status-item <?= $data[0] ?>">
      <i class="fas fa-<?= $data[0] === 'ok' ? 'check-circle' : ($data[0] === 'error' ? 'times-circle' : ($data[0] === 'created' ? 'database' : 'question-circle')) ?>"></i>
      <div class="status-content">
        <div class="status-label"><?= htmlspecialchars($item) ?></div>
        <div class="status-desc"><?= htmlspecialchars($data[1]) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="test-section">
    <h3><i class="fas fa-play" style="color:#d4af5a;margin-right:8px;"></i>Procédure de test</h3>
    <div class="test-flow">
      <ol>
        <li>Allez sur la page des <strong>Villas</strong> ou <strong>Activités</strong></li>
        <li>Cliquez sur le bouton <strong>"Réserver"</strong> d'une villa ou activité</li>
        <li>Sélectionnez les dates d'arrivée et de départ</li>
        <li>Vérifiez le calcul automatique du prix</li>
        <li>Cliquez sur <strong>"Continuer vers le paiement"</strong></li>
        <li>Entrez les informations de paiement fictif (n'importe quel numéro de carte valide)</li>
        <li>Confirmez le paiement</li>
        <li>Vous verrez la page de confirmation avec le numéro de réservation</li>
      </ol>
    </div>
  </div>

  <div class="install-actions">
    <a href="villas.php" class="btn btn-primary">
      <i class="fas fa-home"></i> Tester avec les Villas
    </a>
    <a href="activites.php" class="btn btn-primary">
      <i class="fas fa-compass"></i> Tester avec les Activités
    </a>
    <a href="index.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </a>
  </div>
</div>

</body>
</html>
