<?php
/**
 * admin/dashboard.php — Tableau de bord administrateur
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

$page_title = "Dashboard Admin";

// Statistiques
$nb_villas    = $pdo->query("SELECT COUNT(*) FROM villas")->fetchColumn();
$nb_activites = $pdo->query("SELECT COUNT(*) FROM activites")->fetchColumn();
$nb_clients   = $pdo->query("SELECT COUNT(*) FROM clients WHERE role='client'")->fetchColumn();
$nb_reserv    = $pdo->query("SELECT COUNT(*) FROM reservations_villas")->fetchColumn();

// Dernières villas
$last_villas = $pdo->query("SELECT id, titre, prix_par_nuit, localisation FROM villas ORDER BY created_at DESC LIMIT 5")->fetchAll();
// Dernières activités
$last_acts   = $pdo->query("SELECT id, nom_activite, prix_par_personne, lieu_depart FROM activites ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?> — Teranga Azur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">

<!-- ======= SIDEBAR ======= -->
<aside class="admin-sidebar">
  <a href="../index.php" class="admin-sidebar-logo">
    <img src="../images/Logo.png" alt="Teranga Azur">
    <div>
      <span>Teranga Azur</span>
      <small>Panneau Admin</small>
    </div>
  </a>

  <nav class="admin-nav">
    <div class="admin-nav-label">Navigation</div>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
    <a href="villas.php"><i class="fas fa-home"></i> Villas</a>
    <a href="activites.php"><i class="fas fa-compass"></i> Activités</a>

    <div class="admin-nav-label" style="margin-top:20px;">Site public</div>
    <a href="../villas.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les villas</a>
    <a href="../activites.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les activités</a>
    <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> Site public</a>
  </nav>

  <div class="admin-sidebar-footer">
    <div class="admin-user">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['client_prenom'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div style="font-weight:600;font-size:0.85rem;color:var(--admin-text);">
          <?= htmlspecialchars($_SESSION['client_prenom'] . ' ' . $_SESSION['client_nom']) ?>
        </div>
        <div style="font-size:0.75rem;color:var(--admin-accent);">Administrateur</div>
      </div>
    </div>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </div>
</aside>

<!-- ======= MAIN ======= -->
<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <h1>Tableau de bord</h1>
      <div class="admin-breadcrumb">Bienvenue, <?= htmlspecialchars($_SESSION['client_prenom']) ?> !</div>
    </div>
    <a href="villa-form.php" class="btn-admin btn-admin-primary">
      <i class="fas fa-plus"></i> Ajouter une villa
    </a>
  </div>

  <div class="admin-content">

    <!-- Statistiques -->
    <div class="admin-stats">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-home"></i></div>
        <div>
          <div class="stat-number"><?= $nb_villas ?></div>
          <div class="stat-label">Villas</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-compass"></i></div>
        <div>
          <div class="stat-number"><?= $nb_activites ?></div>
          <div class="stat-label">Activités</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-users"></i></div>
        <div>
          <div class="stat-number"><?= $nb_clients ?></div>
          <div class="stat-label">Clients</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-calendar-check"></i></div>
        <div>
          <div class="stat-number"><?= $nb_reserv ?></div>
          <div class="stat-label">Réservations</div>
        </div>
      </div>
    </div>

    <!-- Dernières villas -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-home" style="color:var(--admin-accent);margin-right:8px;"></i> Dernières villas</h2>
        <div style="display:flex;gap:10px;">
          <a href="villas.php" class="btn-admin btn-admin-ghost"><i class="fas fa-list"></i> Gérer</a>
          <a href="villa-form.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Ajouter</a>
        </div>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Titre</th>
              <th>Localisation</th>
              <th>Prix/nuit</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($last_villas as $v): ?>
            <tr>
              <td class="title-cell"><?= htmlspecialchars($v['titre']) ?></td>
              <td style="color:var(--admin-muted)"><i class="fas fa-map-marker-alt" style="color:var(--admin-accent);margin-right:5px;"></i><?= htmlspecialchars($v['localisation']) ?></td>
              <td><span class="badge badge-blue"><?= number_format($v['prix_par_nuit'], 0, ',', ' ') ?> XOF</span></td>
              <td>
                <div style="display:flex;gap:8px;">
                  <a href="villa-form.php?id=<?= $v['id'] ?>" class="btn-admin btn-admin-ghost" style="padding:6px 12px;font-size:0.8rem;">
                    <i class="fas fa-edit"></i> Modifier
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Dernières activités -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-compass" style="color:var(--admin-accent);margin-right:8px;"></i> Dernières activités</h2>
        <div style="display:flex;gap:10px;">
          <a href="activites.php" class="btn-admin btn-admin-ghost"><i class="fas fa-list"></i> Gérer</a>
          <a href="activite-form.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Ajouter</a>
        </div>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Lieu de départ</th>
              <th>Prix/pers.</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($last_acts as $a): ?>
            <tr>
              <td class="title-cell"><?= htmlspecialchars($a['nom_activite']) ?></td>
              <td style="color:var(--admin-muted)"><i class="fas fa-map-marker-alt" style="color:var(--admin-accent);margin-right:5px;"></i><?= htmlspecialchars($a['lieu_depart']) ?></td>
              <td><span class="badge badge-green"><?= number_format($a['prix_par_personne'], 0, ',', ' ') ?> XOF</span></td>
              <td>
                <a href="activite-form.php?id=<?= $a['id'] ?>" class="btn-admin btn-admin-ghost" style="padding:6px 12px;font-size:0.8rem;">
                  <i class="fas fa-edit"></i> Modifier
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- / admin-content -->
</main>

</body>
</html>
