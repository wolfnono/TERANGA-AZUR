<?php
session_start();
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: villas.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM villas WHERE id = ?");
$stmt->execute([$id]);
$villa = $stmt->fetch();
if (!$villa) { header('Location: villas.php'); exit; }

$stmt2 = $pdo->prepare("SELECT * FROM images_villas WHERE villa_id = ?");
$stmt2->execute([$id]);
$images = $stmt2->fetchAll();

$stmt3 = $pdo->query("SELECT * FROM services_optionnels ORDER BY prix_journalier ASC");
$services = $stmt3->fetchAll();

$page_title = $villa['titre'];
$page_desc  = substr($villa['description'] ?? '', 0, 160);
$extra_css  = "villa-detail.css";

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
  if (!isset($_SESSION['client_id'])) {
    header('Location: connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
  }

  $client_id    = $_SESSION['client_id'];
  $date_arrivee = $_POST['date_arrivee'] ?? '';
  $date_depart  = $_POST['date_depart'] ?? '';
  $nb_voyageurs = (int)($_POST['nb_voyageurs'] ?? 0);

  if ($date_arrivee && $date_depart && $nb_voyageurs > 0) {
    $nuits = (strtotime($date_depart) - strtotime($date_arrivee)) / 86400;
    if ($nuits > 0 && $nb_voyageurs <= $villa['capacite_max']) {
      $prix_total = $nuits * $villa['prix_par_nuit'];

      $pdo->beginTransaction();
      try {
        $ins = $pdo->prepare("INSERT INTO reservations_villas (client_id, villa_id, date_arrivee, date_depart, nb_voyageurs, prix_total_sejour) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$client_id, $id, $date_arrivee, $date_depart, $nb_voyageurs, $prix_total]);
        $res_id = $pdo->lastInsertId();

        if (!empty($_POST['services'])) {
          foreach ($_POST['services'] as $service_id) {
            $qte    = (int)($_POST["qte_$service_id"] ?? 1);
            $nb_j   = (int)$nuits;
            $ins2   = $pdo->prepare("INSERT INTO options_choisies (reservation_villa_id, service_id, quantite, nb_jours) VALUES (?, ?, ?, ?)");
            $ins2->execute([$res_id, $service_id, $qte, $nb_j]);
          }
        }
        $pdo->commit();
        $message = "Votre réservation a bien été enregistrée ! Nous vous confirmerons dans les plus brefs délais.";
        $message_type = 'success';
      } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Une erreur est survenue. Veuillez réessayer.";
        $message_type = 'error';
      }
    } else {
      $message = "Dates invalides ou capacité dépassée.";
      $message_type = 'error';
    }
  } else {
    $message = "Veuillez remplir tous les champs.";
    $message_type = 'error';
  }
}

include 'includes/header.php';
?>

<div class="villa-gallery" style="margin-top:80px;">
  <?php
  $gallery_images = [];
  if (!empty($images)) {
      foreach ($images as $img) {
          $gallery_images[] = $img['url']; 
      }
  } else {
      $gallery_images = [
          'images/Vue-Balcon.villa2.png',
          'images/Jacuzzi-villa2.png',
          'images/Mini-salon_villa2.png',
          'images/piscine_vue_de_jour-villa2.png'
      ];
  }
  
  $main_img = $gallery_images[0];
  ?>

  <div class="gallery-main">
    <img src="<?= htmlspecialchars($main_img) ?>"
         onerror="this.src='images/Logo.png'"
         alt="<?= htmlspecialchars($villa['titre']) ?>"
         id="mainGalleryImg">
  </div>
  
  <div class="gallery-thumbs">
    <?php foreach ($gallery_images as $index => $src): ?>
    <img src="<?= htmlspecialchars($src) ?>"
         onerror="this.src='images/Logo.png'"
         alt="Vue <?= $index + 1 ?>"
         class="gallery-thumb <?= $index === 0 ? 'active' : '' ?>"
         onclick="changeMainImg(this)">
    <?php endforeach; ?>
  </div>
</div>

<section class="section villa-detail-section">
  <div class="villa-detail-grid">

    <div class="villa-main">
      <div class="villa-breadcrumb">
        <a href="index.php">Accueil</a> <span>/</span>
        <a href="villas.php">Villas</a> <span>/</span>
        <span><?= htmlspecialchars($villa['titre']) ?></span>
      </div>

      <h1 class="villa-title"><?= htmlspecialchars($villa['titre']) ?></h1>
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <span style="color:var(--texte-gris);display:flex;align-items:center;gap:6px;">
          <i class="fas fa-map-marker-alt" style="color:var(--bleu-moyen);"></i>
          <?= htmlspecialchars($villa['localisation']) ?>
        </span>
        <div style="display:flex;gap:6px;">
          <i class="fas fa-star" style="color:var(--or-sable);"></i>
          <i class="fas fa-star" style="color:var(--or-sable);"></i>
          <i class="fas fa-star" style="color:var(--or-sable);"></i>
          <i class="fas fa-star" style="color:var(--or-sable);"></i>
          <i class="fas fa-star" style="color:var(--or-sable);"></i>
        </div>
      </div>

      <div class="villa-specs">
        <div class="spec-item">
          <i class="fas fa-users"></i>
          <strong><?= $villa['capacite_max'] ?></strong>
          <span>Voyageurs</span>
        </div>
        <div class="spec-item">
          <i class="fas fa-door-open"></i>
          <strong><?= $villa['chambres'] ?></strong>
          <span>Chambres</span>
        </div>
        <div class="spec-item">
          <i class="fas fa-swimming-pool" style="color:<?= $villa['piscine'] ? 'var(--bleu-moyen)' : '#ccc' ?>;"></i>
          <strong><?= $villa['piscine'] ? 'Oui' : 'Non' ?></strong>
          <span>Piscine</span>
        </div>
        <div class="spec-item">
          <i class="fas fa-moon"></i>
          <strong><?= number_format($villa['prix_par_nuit'], 0, ',', ' ') ?></strong>
          <span>XOF/nuit</span>
        </div>
      </div>

      <div class="villa-description">
        <h2>À propos de cette villa</h2>
        <p><?= nl2br(htmlspecialchars($villa['description'] ?? 'Description en cours de rédaction.')) ?></p>
      </div>

      <?php if (!empty($services)): ?>
      <div class="villa-services">
        <h2>Services optionnels disponibles</h2>
        <div class="services-grid">
          <?php foreach ($services as $service): ?>
          <div class="service-card">
            <h4><?= htmlspecialchars($service['nom_service']) ?></h4>
            <p><?= htmlspecialchars($service['description'] ?? '') ?></p>
            <span class="service-price"><?= number_format($service['prix_journalier'], 0, ',', ' ') ?> XOF/jour</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="villa-booking-sidebar">
      <div class="booking-card">
        <div class="booking-price">
          <span class="price-amount"><?= number_format($villa['prix_par_nuit'], 0, ',', ' ') ?> XOF</span>
          <span class="price-unit">/ nuit</span>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
          <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
          <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="booking-form" id="bookingForm">
          <div class="form-group">
            <label class="form-label">Date d'arrivée</label>
            <input type="date" name="date_arrivee" class="form-input" min="<?= date('Y-m-d') ?>" required id="dateArrivee">
          </div>
          <div class="form-group">
            <label class="form-label">Date de départ</label>
            <input type="date" name="date_depart" class="form-input" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required id="dateDepart">
          </div>
          <div class="form-group">
            <label class="form-label">Nombre de voyageurs</label>
            <select name="nb_voyageurs" class="form-select" required>
              <option value="">Choisir...</option>
              <?php for ($i = 1; $i <= $villa['capacite_max']; $i++): ?>
              <option value="<?= $i ?>"><?= $i ?> personne<?= $i > 1 ? 's' : '' ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <?php if (!empty($services)): ?>
          <div class="form-group">
            <label class="form-label">Services optionnels</label>
            <?php foreach ($services as $service): ?>
            <div class="service-option">
              <label>
                <input type="checkbox" name="services[]" value="<?= $service['id'] ?>" class="service-checkbox">
                <?= htmlspecialchars($service['nom_service']) ?>
                <small><?= number_format($service['prix_journalier'], 0, ',', ' ') ?> XOF/j</small>
              </label>
              <input type="number" name="qte_<?= $service['id'] ?>" value="1" min="1" class="qty-input" style="display:none;">
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="price-summary" id="priceSummary" style="display:none;">
            <div class="price-row">
              <span id="priceLabel">... nuits × ... XOF</span>
              <span id="priceTotal"></span>
            </div>
            <div class="price-row total">
              <span>Total estimé</span>
              <span id="priceGrandTotal"></span>
            </div>
          </div>

          <?php if (isset($_SESSION['client_id'])): ?>
          <button type="submit" name="reserver" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;">
            <i class="fas fa-check"></i> Réserver cette villa
          </button>
          <?php else: ?>
          <a href="connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;">
            <i class="fas fa-lock"></i> Connectez-vous pour réserver
          </a>
          <?php endif; ?>
        </form>

        <p style="text-align:center;font-size:0.78rem;color:var(--texte-gris);margin-top:14px;">
          <i class="fas fa-shield-alt" style="color:var(--bleu-moyen);"></i>
          Paiement sécurisé — Annulation flexible
        </p>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function changeMainImg(thumb) {
  document.getElementById('mainGalleryImg').src = thumb.src;
  document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

const arrivee = document.getElementById('dateArrivee');
const depart  = document.getElementById('dateDepart');
const summary = document.getElementById('priceSummary');
const prixNuit = <?= $villa['prix_par_nuit'] ?>;

function updatePrice() {
  if (!arrivee.value || !depart.value) { summary.style.display = 'none'; return; }
  const d1 = new Date(arrivee.value), d2 = new Date(depart.value);
  const nuits = Math.round((d2 - d1) / 86400000);
  if (nuits < 1) { summary.style.display = 'none'; return; }
  const total = nuits * prixNuit;
  document.getElementById('priceLabel').textContent = `${nuits} nuit${nuits > 1 ? 's' : ''} × ${prixNuit.toLocaleString('fr-FR')} XOF`;
  document.getElementById('priceTotal').textContent = total.toLocaleString('fr-FR') + ' XOF';
  document.getElementById('priceGrandTotal').textContent = total.toLocaleString('fr-FR') + ' XOF';
  summary.style.display = 'block';
}

arrivee.addEventListener('change', () => {
  if (arrivee.value) {
    const next = new Date(arrivee.value);
    next.setDate(next.getDate() + 1);
    depart.min = next.toISOString().split('T')[0];
  }
  updatePrice();
});
depart.addEventListener('change', updatePrice);

document.querySelectorAll('.service-checkbox').forEach(cb => {
  cb.addEventListener('change', function() {
    this.closest('.service-option').querySelector('.qty-input').style.display = this.checked ? 'block' : 'none';
  });
});
</script>
