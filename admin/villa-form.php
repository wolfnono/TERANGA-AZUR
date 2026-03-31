<?php
/**
 * admin/villa-form.php — Formulaire ajout / édition d'une villa
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

$id       = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit  = $id !== null;
$villa    = [];
$msg      = '';
$msg_type = '';

// Chargement en mode édition
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM villas WHERE id = ?");
    $stmt->execute([$id]);
    $villa = $stmt->fetch();
    if (!$villa) { header('Location: villas.php'); exit; }
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix        = (float)($_POST['prix_par_nuit'] ?? 0);
    $capacite    = (int)($_POST['capacite_max'] ?? 1);
    $chambres    = (int)($_POST['chambres'] ?? 1);
    $piscine     = isset($_POST['piscine']) ? 1 : 0;
    $localisation = trim($_POST['localisation'] ?? '');

    $errors = [];
    if (!$titre)       $errors[] = "Le titre est requis.";
    if ($prix <= 0)    $errors[] = "Le prix doit être supérieur à 0.";
    if ($capacite < 1) $errors[] = "La capacité doit être au moins 1.";
    if ($chambres < 1) $errors[] = "Le nombre de chambres doit être au moins 1.";

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE villas SET titre=?, description=?, prix_par_nuit=?, capacite_max=?, chambres=?, piscine=?, localisation=? WHERE id=?");
            $stmt->execute([$titre, $description, $prix, $capacite, $chambres, $piscine, $localisation, $id]);
            header('Location: villas.php?success=updated');
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO villas (titre, description, prix_par_nuit, capacite_max, chambres, piscine, localisation) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$titre, $description, $prix, $capacite, $chambres, $piscine, $localisation]);
            header('Location: villas.php?success=created');
            exit;
        }
    } else {
        $msg = implode(' ', $errors);
        $msg_type = 'error';
        // Repeupler
        $villa = compact('titre','description','prix_par_nuit','capacite_max','chambres','piscine','localisation');
        $villa['prix_par_nuit'] = $prix;
        $villa['capacite_max']  = $capacite;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $is_edit ? 'Modifier la villa' : 'Ajouter une villa' ?> — Admin Teranga Azur</title>
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
    <div class="admin-nav-label" style="margin-top:20px;">Site public</div>
    <a href="../villas.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les villas</a>
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
      <h1><?= $is_edit ? 'Modifier la villa' : 'Ajouter une villa' ?></h1>
      <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a> /
        <a href="villas.php">Villas</a> /
        <?= $is_edit ? htmlspecialchars($villa['titre']) : 'Nouvelle villa' ?>
      </div>
    </div>
    <a href="villas.php" class="btn-admin btn-admin-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
  </div>

  <div class="admin-content">

    <?php if ($msg): ?>
    <div class="admin-alert admin-alert-<?= $msg_type ?>">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-home" style="color:var(--admin-accent);margin-right:8px;"></i>
          <?= $is_edit ? 'Modifier les informations' : 'Informations de la villa' ?>
        </h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" class="admin-form">

          <!-- Titre -->
          <div class="field">
            <label for="titre">Titre de la villa *</label>
            <input type="text" id="titre" name="titre" placeholder="Ex : Villa Océane Prestige"
                   value="<?= htmlspecialchars($villa['titre'] ?? '') ?>" required>
          </div>

          <!-- Description -->
          <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Décrivez la villa..."><?= htmlspecialchars($villa['description'] ?? '') ?></textarea>
          </div>

          <div class="form-grid">
            <!-- Prix -->
            <div class="field">
              <label for="prix_par_nuit">Prix par nuit (XOF) *</label>
              <input type="number" id="prix_par_nuit" name="prix_par_nuit" min="1" step="500"
                     placeholder="Ex : 180000"
                     value="<?= htmlspecialchars($villa['prix_par_nuit'] ?? '') ?>" required>
            </div>

            <!-- Localisation -->
            <div class="field">
              <label for="localisation">Localisation</label>
              <input type="text" id="localisation" name="localisation" placeholder="Ex : Saly Portudal, Mbour"
                     value="<?= htmlspecialchars($villa['localisation'] ?? '') ?>">
            </div>

            <!-- Capacité -->
            <div class="field">
              <label for="capacite_max">Capacité max (personnes) *</label>
              <input type="number" id="capacite_max" name="capacite_max" min="1" max="50"
                     placeholder="Ex : 10"
                     value="<?= htmlspecialchars($villa['capacite_max'] ?? '') ?>" required>
            </div>

            <!-- Chambres -->
            <div class="field">
              <label for="chambres">Nombre de chambres *</label>
              <input type="number" id="chambres" name="chambres" min="1" max="20"
                     placeholder="Ex : 4"
                     value="<?= htmlspecialchars($villa['chambres'] ?? '') ?>" required>
            </div>
          </div>

          <!-- Piscine -->
          <div class="field">
            <div class="checkbox-field">
              <input type="checkbox" id="piscine" name="piscine" value="1"
                     <?= !empty($villa['piscine']) ? 'checked' : '' ?>>
              <label for="piscine"><i class="fas fa-swimming-pool" style="color:var(--admin-accent);"></i> Cette villa dispose d'une piscine privée</label>
            </div>
          </div>

          <!-- Boutons -->
          <div style="display:flex;gap:14px;margin-top:12px;">
            <button type="submit" class="btn-admin btn-admin-primary" style="padding:12px 28px;font-size:0.9rem;">
              <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les modifications' : 'Créer la villa' ?>
            </button>
            <a href="villas.php" class="btn-admin btn-admin-ghost" style="padding:12px 20px;font-size:0.9rem;">
              Annuler
            </a>
          </div>

        </form>
      </div>
    </div>

  </div>
</main>

</body>
</html>
