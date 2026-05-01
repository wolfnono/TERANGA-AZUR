<?php
/**
 * admin/client-detail.php — Détails d'un client
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

$client_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$client_id) {
    header('Location: clients.php');
    exit;
}

// Récupérer le client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: clients.php');
    exit;
}

// Récupérer les réservations villas
$stmt2 = $pdo->prepare("
    SELECT rv.*, v.titre, v.prix_par_nuit
    FROM reservations_villas rv
    JOIN villas v ON rv.villa_id = v.id
    WHERE rv.client_id = ?
    ORDER BY rv.created_at DESC
");
$stmt2->execute([$client_id]);
$reservations_villas = $stmt2->fetchAll();

// Récupérer les réservations nouvelles
$stmt3 = $pdo->prepare("
    SELECT r.*,
           v.titre as villa_titre, a.nom_activite
    FROM reservations r
    LEFT JOIN villas v ON r.type='villa' AND r.item_id=v.id
    LEFT JOIN activites a ON r.type='activite' AND r.item_id=a.id
    WHERE r.client_id = ?
    ORDER BY r.created_at DESC
");
$stmt3->execute([$client_id]);
$reservations_new = $stmt3->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client — Admin Teranga Azur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">

<!-- SIDEBAR -->
<aside class="admin-sidebar">
  <a href="../index.php" class="admin-sidebar-logo">
    <img src="../images/Logo.png" alt="Teranga Azur">
    <div><span>Teranga Azur</span><small>Panneau Admin</small></div>
  </a>
  <nav class="admin-nav">
    <div class="admin-nav-label">Navigation</div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
    <a href="villas.php"><i class="fas fa-home"></i> Villas</a>
    <a href="activites.php"><i class="fas fa-compass"></i> Activités</a>
    <a href="clients.php" class="active"><i class="fas fa-users"></i> Clients</a>
    <div class="admin-nav-label" style="margin-top:18px;">Site public</div>
    <a href="../villas.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les villas</a>
    <a href="../carte.php" target="_blank"><i class="fas fa-map-marked-alt"></i> Carte interactive</a>
    <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> Site public</a>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['client_prenom'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div style="font-weight:600;font-size:0.85rem;color:var(--a-text);"><?= htmlspecialchars(($_SESSION['client_prenom'] ?? '') . ' ' . ($_SESSION['client_nom'] ?? '')) ?></div>
        <div style="font-size:0.72rem;color:var(--a-or);">Administrateur</div>
      </div>
    </div>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </div>
</aside>

<!-- MAIN -->
<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <h1><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></h1>
      <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a> /
        <a href="clients.php">Clients</a> /
        <?= htmlspecialchars($client['prenom']) ?>
      </div>
    </div>
    <a href="clients.php" class="btn-admin btn-admin-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
  </div>

  <div class="admin-content">

    <!-- Informations client -->
    <div class="admin-card" style="margin-bottom:24px;">
      <div class="admin-card-header">
        <h2><i class="fas fa-user" style="color:var(--a-or);margin-right:8px;"></i> Informations personnelles</h2>
      </div>
      <div class="admin-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;line-height:1.8;">
          <div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Prénom</label>
              <div style="font-size:1rem;color:var(--a-text);"><?= htmlspecialchars($client['prenom']) ?></div>
            </div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Nom</label>
              <div style="font-size:1rem;color:var(--a-text);"><?= htmlspecialchars($client['nom']) ?></div>
            </div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Email</label>
              <div style="font-size:1rem;color:var(--a-text);">
                <a href="mailto:<?= htmlspecialchars($client['email']) ?>" style="color:var(--a-or);text-decoration:none;">
                  <?= htmlspecialchars($client['email']) ?>
                </a>
              </div>
            </div>
            <div>
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Téléphone</label>
              <div style="font-size:1rem;color:var(--a-text);">
                <?= htmlspecialchars($client['telephone'] ?? '—') ?>
              </div>
            </div>
          </div>
          <div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Statut</label>
              <div style="font-size:1rem;color:var(--a-text);">
                <span class="badge badge-blue"><?= $client['role'] === 'admin' ? 'Administrateur' : 'Client' ?></span>
              </div>
            </div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">ID Client</label>
              <div style="font-size:1rem;color:var(--a-text);">#<?= $client['id'] ?></div>
            </div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Date d'inscription</label>
              <div style="font-size:1rem;color:var(--a-text);">
                <?= date('d/m/Y à H:i', strtotime($client['created_at'])) ?>
              </div>
            </div>
            <div>
              <label style="display:block;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--a-muted);font-weight:600;margin-bottom:4px;">Adresse</label>
              <div style="font-size:1rem;color:var(--a-text);">
                <?= htmlspecialchars($client['adresse'] ?? '—') ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Réservations -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-calendar-alt" style="color:var(--a-or);margin-right:8px;"></i> Réservations</h2>
      </div>

      <?php
      $total_reservations = count($reservations_villas) + count($reservations_new);
      if ($total_reservations === 0):
      ?>
      <div style="padding:40px;text-align:center;color:var(--a-muted);">
        <i class="fas fa-calendar" style="font-size:2rem;margin-bottom:12px;display:block;opacity:0.5;"></i>
        <p>Ce client n'a pas de réservations.</p>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Offre</th>
              <th>Dates réservation</th>
              <th>Montant</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <!-- Anciennes réservations -->
            <?php foreach ($reservations_villas as $res): ?>
            <tr>
              <td style="color:var(--a-muted);font-size:0.9rem;">
                <?= date('d/m/Y', strtotime($res['created_at'])) ?>
              </td>
              <td><span class="badge badge-blue">Villa</span></td>
              <td class="title-cell"><?= htmlspecialchars($res['titre']) ?></td>
              <td style="color:var(--a-muted);font-size:0.9rem;">
                <?= date('d/m', strtotime($res['date_arrivee'])) ?> → <?= date('d/m', strtotime($res['date_depart'])) ?>
              </td>
              <td><?= number_format($res['prix_total_sejour'], 0, ',', ' ') ?> XOF</td>
              <td><span class="badge badge-green">Confirmée</span></td>
            </tr>
            <?php endforeach; ?>

            <!-- Nouvelles réservations -->
            <?php foreach ($reservations_new as $res): ?>
            <tr>
              <td style="color:var(--a-muted);font-size:0.9rem;">
                <?= date('d/m/Y', strtotime($res['created_at'])) ?>
              </td>
              <td>
                <span class="badge <?= $res['type'] === 'villa' ? 'badge-blue' : 'badge-gold' ?>">
                  <?= $res['type'] === 'villa' ? 'Villa' : 'Activité' ?>
                </span>
              </td>
              <td class="title-cell">
                <?= $res['type'] === 'villa' ? htmlspecialchars($res['villa_titre']) : htmlspecialchars($res['nom_activite']) ?>
              </td>
              <td style="color:var(--a-muted);font-size:0.9rem;">
                <?= date('d/m', strtotime($res['date_arrivee'])) ?> → <?= date('d/m', strtotime($res['date_depart'])) ?>
              </td>
              <td><?= number_format($res['prix_total'], 0, ',', ' ') ?> XOF</td>
              <td>
                <span class="badge <?= $res['statut'] === 'confirmée' ? 'badge-green' : 'badge-gray' ?>">
                  <?= ucfirst($res['statut']) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div>
</main>

</body>
</html>
