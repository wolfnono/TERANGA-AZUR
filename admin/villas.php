<?php
/**
 * admin/villas.php — Liste et gestion des villas
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

// Suppression
$msg = ''; $msg_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && is_numeric($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $pdo->prepare("DELETE FROM villas WHERE id = ?")->execute([$id]);
    $msg = "Villa supprimée avec succès.";
    $msg_type = 'success';
}
if (isset($_GET['success'])) {
    $msg = $_GET['success'] === 'created' ? "Villa ajoutée avec succès !" : "Villa mise à jour avec succès !";
    $msg_type = 'success';
}

$villas = $pdo->query("SELECT v.*, (SELECT COUNT(*) FROM images_villas WHERE villa_id = v.id) AS nb_images FROM villas v ORDER BY v.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion Villas — Admin Teranga Azur</title>
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
    <a href="villas.php" class="active"><i class="fas fa-home"></i> Villas</a>
    <a href="activites.php"><i class="fas fa-compass"></i> Activités</a>
    <a href="clients.php"><i class="fas fa-users"></i> Clients</a>
    <div class="admin-nav-label" style="margin-top:20px;">Site public</div>
    <a href="../villas.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les villas</a>
    <a href="../carte.php" target="_blank"><i class="fas fa-map-marked-alt"></i> Carte interactive</a>
    <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> Site public</a>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['client_prenom'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div style="font-weight:600;font-size:0.85rem;color:var(--admin-text);"><?= htmlspecialchars($_SESSION['client_prenom'] . ' ' . $_SESSION['client_nom']) ?></div>
        <div style="font-size:0.75rem;color:var(--admin-accent);">Administrateur</div>
      </div>
    </div>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </div>
</aside>

<!-- MAIN -->
<main class="admin-main">
  <div class="admin-topbar">
    <div>
      <h1>Gestion des Villas</h1>
      <div class="admin-breadcrumb"><a href="dashboard.php">Dashboard</a> / Villas</div>
    </div>
    <a href="villa-form.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Ajouter une villa</a>
  </div>

  <div class="admin-content">

    <?php if ($msg): ?>
    <div class="admin-alert admin-alert-<?= $msg_type ?>">
      <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-home" style="color:var(--admin-accent);margin-right:8px;"></i> <?= count($villas) ?> villa<?= count($villas) > 1 ? 's' : '' ?></h2>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Localisation</th>
              <th>Chambres</th>
              <th>Capacité</th>
              <th>Prix/nuit</th>
              <th>Piscine</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($villas as $v): ?>
            <tr>
              <td style="color:var(--admin-muted);font-size:0.8rem;">#<?= $v['id'] ?></td>
              <td class="title-cell"><?= htmlspecialchars($v['titre']) ?></td>
              <td style="color:var(--admin-muted)"><i class="fas fa-map-marker-alt" style="color:var(--admin-accent);margin-right:5px;"></i><?= htmlspecialchars($v['localisation']) ?></td>
              <td><?= $v['chambres'] ?> ch.</td>
              <td><?= $v['capacite_max'] ?> pers.</td>
              <td><span class="badge badge-blue"><?= number_format($v['prix_par_nuit'], 0, ',', ' ') ?> XOF</span></td>
              <td>
                <?php if ($v['piscine']): ?>
                <span class="badge badge-green"><i class="fas fa-swimming-pool"></i> Oui</span>
                <?php else: ?>
                <span class="badge badge-red"><i class="fas fa-times"></i> Non</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <a href="villa-form.php?id=<?= $v['id'] ?>" class="btn-admin btn-admin-ghost" style="padding:6px 12px;font-size:0.8rem;" title="Modifier infos">
                    <i class="fas fa-edit"></i> Infos
                  </a>
                  <a href="villa-images.php?villa_id=<?= $v['id'] ?>" class="btn-admin btn-admin-ghost" style="padding:6px 12px;font-size:0.8rem;" title="Gérer les photos">
                    <i class="fas fa-images"></i> Photos
                  </a>
                  <form method="POST" action="villas.php" style="display:inline;" onsubmit="return confirm('Supprimer la villa «<?= htmlspecialchars(addslashes($v['titre'])) ?>» ? Cette action est irréversible.')">
                    <input type="hidden" name="delete" value="<?= $v['id'] ?>">
                    <button type="submit" class="btn-admin btn-admin-danger" style="padding:6px 12px;font-size:0.8rem;border:none;cursor:pointer;" title="Supprimer">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($villas)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--admin-muted);">Aucune villa pour le moment.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

</body>
</html>
