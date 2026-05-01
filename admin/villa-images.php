<?php
/**
 * admin/villa-images.php — Gestion des photos d'une villa
 * Upload simple + suppression individuelle
 */
require_once '../admin_guard.php';
require_once '../config/db.php';

define('UPLOAD_DIR', __DIR__ . '/../images/villas/');
define('UPLOAD_URL', 'images/villas/');
define('MAX_SIZE',   20 * 1024 * 1024);
$ALLOWED = ['image/jpeg','image/png','image/webp','image/gif'];

if (!is_dir(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }

// ID villa obligatoire
$villa_id = isset($_GET['villa_id']) && is_numeric($_GET['villa_id']) ? (int)$_GET['villa_id'] : 0;
if (!$villa_id) { header('Location: villas.php'); exit; }

// Charger la villa
$s = $pdo->prepare("SELECT * FROM villas WHERE id = ?");
$s->execute([$villa_id]);
$villa = $s->fetch();
if (!$villa) { header('Location: villas.php'); exit; }

$msg = ''; $msg_type = '';

// ── SUPPRESSION ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $img_id = (int)$_POST['delete_id'];
    $r = $pdo->prepare("SELECT url FROM images_villas WHERE id = ? AND villa_id = ?");
    $r->execute([$img_id, $villa_id]);
    $row = $r->fetch();
    if ($row) {
        $pdo->prepare("DELETE FROM images_villas WHERE id = ?")->execute([$img_id]);
        $file = __DIR__ . '/../' . $row['url'];
        if (file_exists($file)) { @unlink($file); }
        $msg = "Photo supprimée."; $msg_type = 'success';
    }
}

// ── UPLOAD ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    $uploaded = 0; $errors = [];
    foreach ($_FILES['photos']['tmp_name'] as $k => $tmp) {
        // Remplacer l'ancienne vérification par celle-ci :
        if ($_FILES['photos']['error'][$k] !== UPLOAD_ERR_OK) {
            $err_code = $_FILES['photos']['error'][$k];
            if ($err_code == UPLOAD_ERR_INI_SIZE) {
                $errors[] = htmlspecialchars($_FILES['photos']['name'][$k]) . " : dépasse la limite upload_max_filesize du php.ini.";
            } elseif ($err_code != UPLOAD_ERR_NO_FILE) {
                $errors[] = htmlspecialchars($_FILES['photos']['name'][$k]) . " : erreur d'upload code $err_code.";
            }
            continue;
        }
        if ($_FILES['photos']['size'][$k] > MAX_SIZE) {
            $errors[] = htmlspecialchars($_FILES['photos']['name'][$k]) . " : trop lourd (max 5 Mo).";
            continue;
        }
        if ($_FILES['photos']['size'][$k] > MAX_SIZE) {
            $errors[] = htmlspecialchars($_FILES['photos']['name'][$k]) . " : trop lourd (max 5 Mo).";
            continue;
        }
        $mime = mime_content_type($tmp);
        if (!in_array($mime, $ALLOWED)) {
            $errors[] = htmlspecialchars($_FILES['photos']['name'][$k]) . " : format non supporté.";
            continue;
        }
        $ext  = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'][$mime];
        $name = 'villa_' . $villa_id . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($tmp, UPLOAD_DIR . $name)) {
            $pdo->prepare("INSERT INTO images_villas (villa_id, url) VALUES (?,?)")
                ->execute([$villa_id, UPLOAD_URL . $name]);
            $uploaded++;
        }
    }
    if ($uploaded > 0) { $msg = "$uploaded photo(s) ajoutée(s) avec succès."; $msg_type = 'success'; }
    if ($errors)        { $msg .= ($msg ? ' ' : '') . implode(' ', $errors); $msg_type = $msg_type ?: 'error'; }
}

// Charger toutes les images
$images = $pdo->prepare("SELECT * FROM images_villas WHERE villa_id = ? ORDER BY id");
$images->execute([$villa_id]);
$images = $images->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Photos — <?= htmlspecialchars($villa['titre']) ?> — Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    .photo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px,1fr));
      gap: 14px;
      padding: 22px;
    }
    .photo-card {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid var(--a-border);
      background: var(--a-input);
      aspect-ratio: 4/3;
    }
    .photo-card img {
      width: 100%; height: 100%;
      object-fit: cover; display: block;
    }
    .photo-card .del-btn {
      position: absolute; top: 7px; right: 7px;
      background: rgba(239,68,68,.92); color: #fff;
      border: none; border-radius: 6px;
      width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; font-size: 0.78rem;
      transition: background .15s;
      opacity: 0;
      transition: opacity .15s;
    }
    .photo-card:hover .del-btn { opacity: 1; }
    .photo-card .del-btn:hover { background: #dc2626; }

    .empty-photos {
      padding: 48px 22px;
      text-align: center;
      color: var(--a-muted);
    }
    .empty-photos i { font-size: 2.5rem; opacity: .25; display: block; margin-bottom: 12px; }

    /* Zone upload */
    .upload-box {
      padding: 22px;
      border-top: 1px solid var(--a-border);
      background: rgba(0,0,0,.15);
    }
    .upload-box label.upload-label {
      display: inline-flex; align-items: center; gap: 9px;
      padding: 11px 22px; border-radius: 9px;
      background: linear-gradient(135deg, var(--a-bleu-m) 0%, var(--a-bleu-l) 100%);
      color: #fff; font-size: 0.88rem; font-weight: 600;
      cursor: pointer; transition: box-shadow .18s, transform .18s;
      box-shadow: 0 2px 10px rgba(45,90,79,.4);
      text-transform: none; letter-spacing: 0;
    }
    .upload-box label.upload-label:hover {
      box-shadow: 0 4px 18px rgba(45,90,79,.55);
      transform: translateY(-1px);
    }
    .upload-box input[type="file"] { display: none; }
    .upload-box .preview-strip {
      display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px;
    }
    .preview-strip .thumb {
      width: 80px; height: 60px; border-radius: 7px;
      object-fit: cover; border: 1px solid var(--a-border);
    }
    .upload-box .hint {
      font-size: 0.75rem; color: var(--a-muted); margin-top: 8px;
    }
    .upload-actions {
      margin-top: 14px; display: flex; gap: 12px; align-items: center;
    }
    #submitBtn { display: none; }
  </style>
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
      <h1><i class="fas fa-images" style="color:var(--a-or);margin-right:10px;font-size:1.1rem;"></i>Photos de la villa</h1>
      <div class="admin-breadcrumb">
        <a href="dashboard.php">Dashboard</a> /
        <a href="villas.php">Villas</a> /
        <a href="villa-form.php?id=<?= $villa_id ?>"><?= htmlspecialchars($villa['titre']) ?></a> /
        Photos
      </div>
    </div>
    <a href="villa-form.php?id=<?= $villa_id ?>" class="btn-admin btn-admin-ghost">
      <i class="fas fa-arrow-left"></i> Retour à la villa
    </a>
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
        <h2>
          <i class="fas fa-home" style="color:var(--a-or);margin-right:8px;"></i>
          <?= htmlspecialchars($villa['titre']) ?>
          <span style="font-size:.8rem;font-weight:400;color:var(--a-muted);margin-left:10px;">
            · <?= count($images) ?> photo<?= count($images) > 1 ? 's' : '' ?>
          </span>
        </h2>
      </div>

      <!-- Photos existantes -->
      <?php if (!empty($images)): ?>
      <div class="photo-grid">
        <?php foreach ($images as $img): ?>
        <div class="photo-card">
          <img src="../<?= htmlspecialchars($img['url']) ?>" alt=""
               onerror="this.src='../images/Logo.png';this.style.objectFit='contain';this.style.padding='20px'">
          <form method="POST" style="display:contents;"
                onsubmit="return confirm('Supprimer cette photo ?')">
            <input type="hidden" name="delete_id" value="<?= $img['id'] ?>">
            <button type="submit" class="del-btn" title="Supprimer">
              <i class="fas fa-trash"></i>
            </button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="empty-photos">
        <i class="fas fa-images"></i>
        <p>Aucune photo pour cette villa.</p>
        <p style="font-size:.82rem;">Ajoutez des photos ci-dessous.</p>
      </div>
      <?php endif; ?>

      <!-- Zone d'upload -->
      <div class="upload-box">
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
          <label for="photoInput" class="upload-label">
            <i class="fas fa-upload"></i> Choisir des photos
          </label>
          <input type="file" id="photoInput" name="photos[]" multiple
                 accept="image/jpeg,image/png,image/webp,image/gif"
                 onchange="previewPhotos(this)">

          <div class="preview-strip" id="previewStrip"></div>
          <div class="hint" id="hintText">JPG, PNG, WebP — 5 Mo max par photo — sélection multiple possible</div>

          <div class="upload-actions">
            <button type="submit" id="submitBtn" class="btn-admin btn-admin-primary">
              <i class="fas fa-cloud-upload-alt"></i> Envoyer les photos
            </button>
            <button type="button" id="cancelBtn" onclick="cancelUpload()" class="btn-admin btn-admin-ghost" style="display:none;">
              <i class="fas fa-times"></i> Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</main>

<script>
function previewPhotos(input) {
  const strip  = document.getElementById('previewStrip');
  const submit = document.getElementById('submitBtn');
  const cancel = document.getElementById('cancelBtn');
  const hint   = document.getElementById('hintText');
  strip.innerHTML = '';

  if (!input.files.length) {
    submit.style.display = 'none';
    cancel.style.display = 'none';
    hint.textContent = 'JPG, PNG, WebP — 5 Mo max par photo — sélection multiple possible';
    return;
  }

  let count = 0;
  Array.from(input.files).forEach(file => {
    if (!file.type.startsWith('image/')) return;
    count++;
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'thumb';
      img.title = file.name;
      strip.appendChild(img);
    };
    reader.readAsDataURL(file);
  });

  hint.textContent = `${count} photo(s) sélectionnée(s) — prête(s) à envoyer`;
  submit.style.display = 'inline-flex';
  cancel.style.display = 'inline-flex';
}

function cancelUpload() {
  document.getElementById('photoInput').value = '';
  document.getElementById('previewStrip').innerHTML = '';
  document.getElementById('submitBtn').style.display = 'none';
  document.getElementById('cancelBtn').style.display = 'none';
  document.getElementById('hintText').textContent = 'JPG, PNG, WebP — 5 Mo max par photo — sélection multiple possible';
}
</script>

</body>
</html>
