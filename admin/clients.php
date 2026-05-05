<?php
require_once '../admin_guard.php';
require_once '../config/db.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = "role='client'";
$params = [];

if ($search) {
    $where .= " AND (nom LIKE :q OR prenom LIKE :q2 OR email LIKE :q3)";
    $params[':q'] = "%$search%";
    $params[':q2'] = "%$search%";
    $params[':q3'] = "%$search%";
}

$stmt = $pdo->prepare("
    SELECT
        c.*,
        (SELECT COUNT(*) FROM reservations_villas WHERE client_id = c.id) AS nb_reservations_villas,
        (SELECT COUNT(*) FROM reservations WHERE client_id = c.id) AS nb_reservations_new
    FROM clients c
    WHERE $where
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$clients = $stmt->fetchAll();

$msg = '';
$msg_type = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'deleted') {
        $msg = "Client supprimé avec succès.";
        $msg_type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Clients — Admin Teranga Azur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">

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

<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <h1>Gestion des Clients</h1>
      <div class="admin-breadcrumb"><a href="dashboard.php">Dashboard</a> / Clients</div>
    </div>
  </div>

  <div class="admin-content">

    <?php if ($msg): ?>
    <div class="admin-alert admin-alert-<?= $msg_type ?>">
      <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="admin-card" style="margin-bottom:24px;">
      <form method="GET" action="clients.php" style="display:flex;gap:12px;align-items:flex-end;">
        <div style="flex:1;">
          <label style="display:block;font-size:0.85rem;font-weight:600;color:var(--a-text);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Rechercher un client</label>
          <input type="text" name="q" placeholder="Nom, prénom, email..."
                 value="<?= htmlspecialchars($search) ?>"
                 style="width:100%;padding:12px 14px;border:1px solid #e8e2d8;border-radius:9px;font-size:0.95rem;font-family:inherit;">
        </div>
        <button type="submit" class="btn-admin btn-admin-primary">
          <i class="fas fa-search"></i> Rechercher
        </button>
        <?php if ($search): ?>
        <a href="clients.php" class="btn-admin btn-admin-ghost">
          <i class="fas fa-times"></i> Réinitialiser
        </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-users" style="color:var(--a-or);margin-right:8px;"></i> <?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?></h2>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Réservations</th>
              <th>Date d'inscription</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $client):
              $total_reservations = ($client['nb_reservations_villas'] ?? 0) + ($client['nb_reservations_new'] ?? 0);
            ?>
            <tr>
              <td style="color:var(--a-muted);font-size:0.8rem;">#<?= $client['id'] ?></td>
              <td class="title-cell"><?= htmlspecialchars($client['nom']) ?></td>
              <td><?= htmlspecialchars($client['prenom']) ?></td>
              <td style="color:var(--a-muted);font-size:0.9rem;">
                <i class="fas fa-envelope" style="color:var(--a-or);margin-right:5px;"></i>
                <a href="mailto:<?= htmlspecialchars($client['email']) ?>" style="color:var(--a-muted);text-decoration:none;border-bottom:1px dotted var(--a-muted);">
                  <?= htmlspecialchars($client['email']) ?>
                </a>
              </td>
              <td style="color:var(--a-muted);">
                <?php if (!empty($client['telephone'])): ?>
                  <i class="fas fa-phone" style="color:var(--a-or);margin-right:5px;"></i>
                  <?= htmlspecialchars($client['telephone']) ?>
                <?php else: ?>
                  <span style="color:#999;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($total_reservations > 0): ?>
                  <span class="badge badge-blue"><?= $total_reservations ?> réservation<?= $total_reservations > 1 ? 's' : '' ?></span>
                <?php else: ?>
                  <span class="badge badge-gray">Aucune</span>
                <?php endif; ?>
              </td>
              <td style="color:var(--a-muted);font-size:0.9rem;">
                <?= date('d/m/Y', strtotime($client['created_at'])) ?>
              </td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <a href="client-detail.php?id=<?= $client['id'] ?>" class="btn-admin btn-admin-ghost" style="padding:6px 12px;font-size:0.8rem;" title="Voir détails">
                    <i class="fas fa-eye"></i> Voir
                  </a>
                  <form method="POST" action="clients-delete.php" style="display:inline;" onsubmit="return confirm('Supprimer le compte client «<?= htmlspecialchars(addslashes($client['prenom'])) ?> <?= htmlspecialchars(addslashes($client['nom'])) ?>» ? Cette action est irréversible.')">
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                    <button type="submit" class="btn-admin btn-admin-danger" style="padding:6px 12px;font-size:0.8rem;border:none;cursor:pointer;" title="Supprimer">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($clients)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--a-muted);">
              <?php if ($search): ?>
                Aucun client ne correspond à votre recherche.
              <?php else: ?>
                Aucun client pour le moment.
              <?php endif; ?>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

</body>
</html>
