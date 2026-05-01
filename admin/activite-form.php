<?php
/**
 * admin/activite-form.php — Formulaire ajout / édition activité (avec upload image simple)
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

define('ACT_DIR', __DIR__ . '/../images/activites/');
define('ACT_URL', 'images/activites/');
if (!is_dir(ACT_DIR)) { mkdir(ACT_DIR, 0755, true); }

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $id !== null;
$act = []; $msg = ''; $msg_type = '';

if ($is_edit) {
    $s = $pdo->prepare("SELECT * FROM activites WHERE id = ?");
    $s->execute([$id]);
    $act = $s->fetch();
    if (!$act) { header('Location: activites.php'); exit; }
}

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom_activite'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $duree  = (int)($_POST['duree_heures'] ?? 1);
    $prix   = (float)($_POST['prix_par_personne'] ?? 0);
    $lieu   = trim($_POST['lieu_depart'] ?? '');
    $img    = $act['image_url'] ?? null;

    $errors = [];
    if (!$nom)      $errors[] = "Le nom est requis.";
    if ($duree < 1) $errors[] = "La durée doit être au moins 1h.";
    if ($prix <= 0) $errors[] = "Le prix doit être > 0.";

    // Upload image
    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['image']['tmp_name'];
        $mime = mime_content_type($tmp);
        if ($_FILES['image']['size'] > 5*1024*1024) {
            $errors[] = "Image trop lourde (5 Mo max).";
        } elseif (!in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'])) {
            $errors[] = "Format non supporté (JPG, PNG, WebP, GIF).";
        } else {
            $ext = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'][$mime];
            $fn  = 'act_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($tmp, ACT_DIR . $fn)) {
                // Supprimer l'ancienne image
                if (!empty($act['image_url'])) {
                    $old = __DIR__ . '/../' . $act['image_url'];
                    if (file_exists($old)) { @unlink($old); }
                }
                $img = ACT_URL . $fn;
            } else {
                $errors[] = "Erreur lors de l'enregistrement de l'image.";
            }
        }
    }

    if (empty($errors)) {
        if ($is_edit) {
            $s = $pdo->prepare("UPDATE activites SET nom_activite=?, description=?, duree_heures=?, prix_par_personne=?, lieu_depart=?, image_url=? WHERE id=?");
            $s->execute([$nom, $desc, $duree, $prix, $lieu, $img, $id]);
            header('Location: activites.php?success=updated');
            exit;
        } else {
            $s = $pdo->prepare("INSERT INTO activites (nom_activite, description, duree_heures, prix_par_personne, lieu_depart, image_url) VALUES (?,?,?,?,?,?)");
            $s->execute([$nom, $desc, $duree, $prix, $lieu, $img]);
            $new_id = (int)$pdo->lastInsertId();
            header("Location: activite-images.php?activite_id=$new_id");
            exit;
        }
    } else {
        $msg = implode(' ', $errors); $msg_type = 'error';
        $act = [
            'nom_activite'      => $nom,
            'description'       => $desc,
            'duree_heures'      => $duree,
            'prix_par_personne' => $prix,
            'lieu_depart'       => $lieu,
            'image_url'         => $img
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $is_edit ? "Modifier l'activité" : "Ajouter une activité" ?> — Admin</title>
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
    <a href="clients.php"><i class="fas fa-users"></i> Clients</a>
    <div class="admin-nav-label" style="margin-top:18px;">Site public</div>
    <a href="../activites.php" target="_blank"><i class="fas fa-external-link-alt"></i> Voir les activités</a>
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
      <h1><?= $is_edit ? "Modifier l'activité" : "Ajouter une activité" ?></h1>
      <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a> /
        <a href="activites.php">Activités</a> /
        <?= $is_edit ? htmlspecialchars($act['nom_activite']) : 'Nouvelle activité' ?>
      </div>
    </div>
    <a href="activites.php" class="btn-admin btn-admin-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
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
        <h2><i class="fas fa-compass" style="color:var(--a-or);margin-right:9px;"></i>
          <?= $is_edit ? "Informations de l'activité" : "Nouvelle activité" ?>
        </h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data" class="admin-form">

          <!-- Nom -->
          <div class="field">
            <label for="nom">Nom de l'activité *</label>
            <input type="text" id="nom" name="nom_activite" placeholder="Ex : Tour en pirogue sur le Saloum"
                   value="<?= htmlspecialchars($act['nom_activite'] ?? '') ?>" required>
          </div>

          <!-- Description -->
          <div class="field">
            <label for="desc">Description</label>
            <textarea id="desc" name="description" placeholder="Décrivez l'activité, ce qui est inclus..."><?= htmlspecialchars($act['description'] ?? '') ?></textarea>
          </div>

          <div class="form-grid">
            <!-- Durée -->
            <div class="field">
              <label for="duree">Durée (heures) *</label>
              <input type="number" id="duree" name="duree_heures" min="1" max="48"
                     value="<?= htmlspecialchars($act['duree_heures'] ?? '') ?>" required>
            </div>

            <!-- Prix -->
            <div class="field">
              <label for="prix">Prix par personne (XOF) *</label>
              <input type="number" id="prix" name="prix_par_personne" min="1" step="500"
                     value="<?= htmlspecialchars($act['prix_par_personne'] ?? '') ?>" required>
            </div>

            <!-- Lieu -->
            <div class="field form-grid-span">
              <label for="lieu">Lieu de départ</label>
              <input type="text" id="lieu" name="lieu_depart" placeholder="Ex : Port de Mbour"
                     value="<?= htmlspecialchars($act['lieu_depart'] ?? '') ?>">
            </div>
          </div>

          <!-- Photo actuelle -->
          <?php if (!empty($act['image_url'])): ?>
          <div class="field" style="background:rgba(74,140,125,.05);padding:18px;border-radius:10px;border:1px solid rgba(74,140,125,.15);">
            <div style="display:flex;align-items:flex-start;gap:16px;">
              <img src="../<?= htmlspecialchars($act['image_url']) ?>" alt="Photo actuelle"
                   style="width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--a-border);">
              <div style="flex:1;">
                <p style="margin:0 0 10px;font-size:0.88rem;color:var(--a-text);font-weight:500;">
                  Photo actuelle
                </p>
                <p style="margin:0;font-size:0.78rem;color:var(--a-muted);">
                  Uploader une nouvelle photo pour la remplacer
                </p>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <!-- Upload image -->
          <div class="field">
            <label for="image"><?= !empty($act['image_url']) ? 'Remplacer la photo' : 'Photo de l\'activité' ?></label>
            <div style="position:relative;">
              <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                     onchange="previewAct(this)">
              <label for="image" style="display:block;padding:20px;text-align:center;border:2px dashed var(--a-border-l);border-radius:10px;cursor:pointer;background:rgba(74,140,125,.04);transition:all .2s;font-weight:500;color:var(--a-muted);">
                <i class="fas fa-image" style="display:block;margin-bottom:8px;font-size:1.6rem;opacity:.5;"></i>
                Cliquez pour choisir une photo
              </label>
            </div>
            <div id="prevAct" style="margin-top:12px;"></div>
            <p style="font-size:0.75rem;color:var(--a-muted);margin-top:8px;">JPG, PNG, WebP — 5 Mo max</p>
          </div>

          <!-- Boutons -->
          <div style="display:flex;gap:14px;margin-top:18px;">
            <button type="submit" class="btn-admin btn-admin-primary" style="padding:11px 28px;font-size:0.9rem;">
              <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les modifications' : "Créer l'activité" ?>
            </button>
            <a href="activites.php" class="btn-admin btn-admin-ghost" style="padding:11px 20px;font-size:0.9rem;">
              Annuler
            </a>
          </div>

        </form>
      </div>
    </div>

    <!-- Info photos -->
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
        <a href="activite-images.php?activite_id=<?= $id ?>" class="btn-admin btn-admin-primary">
          <i class="fas fa-image"></i> Aller aux photos
        </a>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

<script>
function previewAct(input) {
  const d = document.getElementById('prevAct');
  d.innerHTML = '';
  const f = input.files[0];
  if (!f || !f.type.startsWith('image/')) return;
  const r = new FileReader();
  r.onload = e => {
    d.innerHTML = `<img src="${e.target.result}" alt="Aperçu" style="width:150px;height:110px;object-fit:cover;border-radius:8px;border:1px solid var(--a-border);">`;
  };
  r.readAsDataURL(f);
}
</script>

</body>
</html>
