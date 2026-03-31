<?php
/**
 * admin/activite-form.php — Formulaire ajout / édition d'une activité
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

$id       = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit  = $id !== null;
$activite = [];
$msg      = '';
$msg_type = '';

// Chargement en mode édition
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM activites WHERE id = ?");
    $stmt->execute([$id]);
    $activite = $stmt->fetch();
    if (!$activite) { header('Location: activites.php'); exit; }
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom_activite'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duree       = (int)($_POST['duree_heures'] ?? 1);
    $prix        = (float)($_POST['prix_par_personne'] ?? 0);
    $lieu        = trim($_POST['lieu_depart'] ?? '');

    $errors = [];
    if (!$nom)      $errors[] = "Le nom est requis.";
    if ($duree < 1) $errors[] = "La durée doit être d'au moins 1 heure.";
    if ($prix <= 0) $errors[] = "Le prix doit être supérieur à 0.";

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE activites SET nom_activite=?, description=?, duree_heures=?, prix_par_personne=?, lieu_depart=? WHERE id=?");
            $stmt->execute([$nom, $description, $duree, $prix, $lieu, $id]);
            header('Location: activites.php?success=updated');
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO activites (nom_activite, description, duree_heures, prix_par_personne, lieu_depart) VALUES (?,?,?,?,?)");
            $stmt->execute([$nom, $description, $duree, $prix, $lieu]);
            header('Location: activites.php?success=created');
            exit;
        }
    } else {
        $msg = implode(' ', $errors);
        $msg_type = 'error';
        $activite = [
            'nom_activite'     => $nom,
            'description'      => $description,
            'duree_heures'     => $duree,
            'prix_par_personne'=> $prix,
            'lieu_depart'      => $lieu,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $is_edit ? 'Modifier l\'activité' : 'Ajouter une activité' ?> — Admin Teranga Azur</title>
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
    <a href="activites.php" class="active"><i class="fas fa-compass"></i> Activités</a>
    <div class="admin-nav-label" style="margin-top:20px;">Site public</div>
    <a href="../activites.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les activités</a>
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
      <h1><?= $is_edit ? 'Modifier l\'activité' : 'Ajouter une activité' ?></h1>
      <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a> /
        <a href="activites.php">Activités</a> /
        <?= $is_edit ? htmlspecialchars($activite['nom_activite']) : 'Nouvelle activité' ?>
      </div>
    </div>
    <a href="activites.php" class="btn-admin btn-admin-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
  </div>

  <div class="admin-content">

    <?php if ($msg): ?>
    <div class="admin-alert admin-alert-<?= $msg_type ?>">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2><i class="fas fa-compass" style="color:var(--admin-accent);margin-right:8px;"></i>
          <?= $is_edit ? 'Modifier les informations' : 'Informations de l\'activité' ?>
        </h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" class="admin-form">

          <!-- Nom -->
          <div class="field">
            <label for="nom_activite">Nom de l'activité *</label>
            <input type="text" id="nom_activite" name="nom_activite"
                   placeholder="Ex : Tour en pirogue sur le Saloum"
                   value="<?= htmlspecialchars($activite['nom_activite'] ?? '') ?>" required>
          </div>

          <!-- Description -->
          <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                      placeholder="Décrivez l'activité, ce qui est inclus, les points forts..."><?= htmlspecialchars($activite['description'] ?? '') ?></textarea>
          </div>

          <div class="form-grid">
            <!-- Durée -->
            <div class="field">
              <label for="duree_heures">Durée (en heures) *</label>
              <input type="number" id="duree_heures" name="duree_heures" min="1" max="48"
                     placeholder="Ex : 4"
                     value="<?= htmlspecialchars($activite['duree_heures'] ?? '') ?>" required>
            </div>

            <!-- Prix -->
            <div class="field">
              <label for="prix_par_personne">Prix par personne (XOF) *</label>
              <input type="number" id="prix_par_personne" name="prix_par_personne" min="1" step="500"
                     placeholder="Ex : 25000"
                     value="<?= htmlspecialchars($activite['prix_par_personne'] ?? '') ?>" required>
            </div>

            <!-- Lieu de départ -->
            <div class="field">
              <label for="lieu_depart">Lieu de départ</label>
              <input type="text" id="lieu_depart" name="lieu_depart"
                     placeholder="Ex : Port de Mbour"
                     value="<?= htmlspecialchars($activite['lieu_depart'] ?? '') ?>">
            </div>
          </div>

          <!-- Boutons -->
          <div style="display:flex;gap:14px;margin-top:12px;">
            <button type="submit" class="btn-admin btn-admin-primary" style="padding:12px 28px;font-size:0.9rem;">
              <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les modifications' : 'Créer l\'activité' ?>
            </button>
            <a href="activites.php" class="btn-admin btn-admin-ghost" style="padding:12px 20px;font-size:0.9rem;">
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
