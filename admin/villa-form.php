<?php
require_once '../admin_guard.php';
require_once '../config/db.php';

$id       = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit  = $id !== null;
$villa    = [];
$msg      = '';
$msg_type = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM villas WHERE id = ?");
    $stmt->execute([$id]);
    $villa = $stmt->fetch();
    if (!$villa) { header('Location: villas.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $prix         = (float)($_POST['prix_par_nuit'] ?? 0);
    $capacite     = (int)($_POST['capacite_max'] ?? 1);
    $chambres     = (int)($_POST['chambres'] ?? 1);
    $piscine      = isset($_POST['piscine']) ? 1 : 0;
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
            $new_id = (int)$pdo->lastInsertId();
            header("Location: villa-images.php?villa_id=$new_id");
            exit;
        }
    } else {
        $msg      = implode(' ', $errors);
        $msg_type = 'error';
        $villa    = compact('titre','description','localisation');
        $villa['prix_par_nuit'] = $prix;
        $villa['capacite_max']  = $capacite;
        $villa['chambres']      = $chambres;
        $villa['piscine']       = $piscine;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $is_edit ? 'Modifier la villa' : 'Ajouter une villa' ?> — Admin</title>
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
    <a href="villas.php" class="active"><i class="fas fa-home"></i> Villas</a>
    <a href="activites.php"><i class="fas fa-compass"></i> Activités</a>
    <a href="clients.php"><i class="fas fa-users"></i> Clients</a>
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
      <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-home" style="color:var(--a-or);margin-right:9px;"></i>
          <?= $is_edit ? 'Informations de la villa' : 'Nouvelle villa' ?>
        </h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" class="admin-form">

          <div class="field">
            <label for="titre">Titre de la villa *</label>
            <input type="text" id="titre" name="titre" placeholder="Ex : Villa Océane Prestige"
                   value="<?= htmlspecialchars($villa['titre'] ?? '') ?>" required>
          </div>

          <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Décrivez la villa, ses équipements, son atmosphère..."><?= htmlspecialchars($villa['description'] ?? '') ?></textarea>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="prix_par_nuit">Prix par nuit (XOF) *</label>
              <input type="number" id="prix_par_nuit" name="prix_par_nuit" min="1" step="500"
                     placeholder="Ex : 180000"
                     value="<?= htmlspecialchars($villa['prix_par_nuit'] ?? '') ?>" required>
            </div>

            <div class="field">
              <label for="localisation">Localisation</label>
              <input type="text" id="localisation" name="localisation" placeholder="Ex : Saly Portudal, Mbour"
                     value="<?= htmlspecialchars($villa['localisation'] ?? '') ?>">
            </div>

            <div class="field">
              <label for="capacite_max">Capacité max (personnes) *</label>
              <input type="number" id="capacite_max" name="capacite_max" min="1" max="50"
                     placeholder="Ex : 10"
                     value="<?= htmlspecialchars($villa['capacite_max'] ?? '') ?>" required>
            </div>

            <div class="field">
              <label for="chambres">Nombre de chambres *</label>
              <input type="number" id="chambres" name="chambres" min="1" max="20"
                     placeholder="Ex : 4"
                     value="<?= htmlspecialchars($villa['chambres'] ?? '') ?>" required>
            </div>
          </div>

          <div class="field">
            <div class="checkbox-field">
              <input type="checkbox" id="piscine" name="piscine" value="1"
                     <?= !empty($villa['piscine']) ? 'checked' : '' ?>>
              <label for="piscine"><i class="fas fa-swimming-pool"></i> Cette villa dispose d'une piscine privée</label>
            </div>
          </div>

          <div style="display:flex;gap:14px;margin-top:18px;">
            <button type="submit" class="btn-admin btn-admin-primary" style="padding:11px 28px;font-size:0.9rem;">
              <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les modifications' : 'Créer la villa' ?>
            </button>
            <a href="villas.php" class="btn-admin btn-admin-ghost" style="padding:11px 20px;font-size:0.9rem;">
              Annuler
            </a>
          </div>

        </form>
      </div>
    </div>

    <?php if ($is_edit): ?>
    <div class="admin-card" style="background:rgba(74,140,125,.06);border-color:rgba(74,140,125,.2);">
      <div class="admin-card-header" style="padding:16px 22px;">
        <div>
          <h2 style="font-size:1rem;margin-bottom:4px;">
            <i class="fas fa-images" style="color:var(--a-bleu-l);margin-right:8px;"></i>
            Gérer les photos
          </h2>
          <p style="font-size:0.82rem;color:var(--a-muted);margin:0;">
            Les photos sont gérées séparément pour plus de flexibilité.
          </p>
        </div>
        <a href="villa-images.php?villa_id=<?= $id ?>" class="btn-admin btn-admin-primary">
          <i class="fas fa-image"></i> Aller aux photos
        </a>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

</body>
</html>
