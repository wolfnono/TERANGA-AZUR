<?php
/**
 * checkout.php — Panier & sélection des dates
 * Calcul automatique du prix basé sur la durée du séjour
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : null;
$item_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$type || !$item_id || !in_array($type, ['villa', 'activite'])) {
    header('Location: index.php');
    exit;
}

$item = null;
$prix_unitaire = 0;
$label_prix = '';

if ($type === 'villa') {
    $s = $pdo->prepare("SELECT id, titre, localisation, prix_par_nuit FROM villas WHERE id = ?");
    $s->execute([$item_id]);
    $item = $s->fetch();
    $label_prix = 'par nuit';
    $prix_unitaire = $item['prix_par_nuit'] ?? 0;
} else {
    $s = $pdo->prepare("SELECT id, nom_activite, lieu_depart, prix_par_personne FROM activites WHERE id = ?");
    $s->execute([$item_id]);
    $item = $s->fetch();
    $label_prix = 'par personne';
    $prix_unitaire = $item['prix_par_personne'] ?? 0;
}

if (!$item) {
    header('Location: index.php');
    exit;
}

$errors = [];
$prix_total = 0;
$nuits = 0;

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arrivee = trim($_POST['date_arrivee'] ?? '');
    $depart  = trim($_POST['date_depart'] ?? '');
    $qty     = (int)($_POST['quantite'] ?? 1);

    if (!$arrivee) $errors[] = "Date d'arrivée requise.";
    if (!$depart) $errors[] = "Date de départ requise.";
    if ($qty < 1) $errors[] = "Quantité invalide.";

    if (!$errors) {
        $d1 = new DateTime($arrivee);
        $d2 = new DateTime($depart);

        if ($d2 <= $d1) {
            $errors[] = "La date de départ doit être après l'arrivée.";
        } else {
            $nuits = $d2->diff($d1)->days;
            if ($type === 'villa') {
                $prix_total = $nuits * $prix_unitaire;
            } else {
                $prix_total = $qty * $prix_unitaire;
            }

            // Sauvegarde en session et redirection
            $_SESSION['checkout'] = [
                'type'         => $type,
                'item_id'      => $item_id,
                'item_nom'     => $type === 'villa' ? $item['titre'] : $item['nom_activite'],
                'date_arrivee' => $arrivee,
                'date_depart'  => $depart,
                'nuits'        => $nuits,
                'quantite'     => $qty,
                'prix_unitaire'=> $prix_unitaire,
                'prix_total'   => $prix_total,
                'label_prix'   => $label_prix
            ];

            header('Location: paiement.php');
            exit;
        }
    }
}

$page_title = 'Réservation — ' . ($type === 'villa' ? $item['titre'] : $item['nom_activite']);
include 'includes/header.php';
?>

<style>
.checkout-hero {
  background: linear-gradient(135deg, #1a3a2e 0%, #2d5a4f 100%);
  padding: 60px 5% 40px;
  color: #fff;
  text-align: center;
}
.checkout-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; margin: 0; }
.checkout-hero p { opacity: .8; margin: 10px 0 0; max-width: 540px; margin-left: auto; margin-right: auto; }

.checkout-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 5%;
}

.checkout-card {
  background: #fff;
  border: 1px solid #e8e2d8;
  border-radius: 14px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(26,58,46,.12);
  margin-bottom: 24px;
}

.item-summary {
  display: flex;
  gap: 20px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e8e2d8;
  margin-bottom: 24px;
}
.item-summary-icon {
  width: 80px;
  height: 80px;
  border-radius: 10px;
  background: #e0eef9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.2rem;
  flex-shrink: 0;
  color: #1a3a2e;
}
.item-summary-info h2 { margin: 0 0 6px; font-size: 1.3rem; color: #1a3a2e; }
.item-summary-info p { margin: 4px 0; font-size: 0.9rem; color: #546474; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.form-field {
  display: flex;
  flex-direction: column;
}
.form-field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a3a2e;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.form-field input,
.form-field select {
  padding: 12px 14px;
  border: 1px solid #e8e2d8;
  border-radius: 9px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: border-color .2s, box-shadow .2s;
}
.form-field input:focus,
.form-field select:focus {
  outline: none;
  border-color: #d4af5a;
  box-shadow: 0 0 0 3px rgba(212,175,90,.15);
}

.price-summary {
  background: #f8f5f0;
  border: 1px solid #e8e2d8;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 24px;
}
.price-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #546474;
}
.price-line:last-child {
  margin-bottom: 0;
  padding-top: 12px;
  border-top: 2px solid #e8e2d8;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a3a2e;
}
.price-line strong { color: #1a3a2e; }

.btn-paiement {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 14px 32px;
  background: linear-gradient(135deg, #2d5a4f 0%, #4a8c7d 100%);
  color: #fff;
  border: none;
  border-radius: 9px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: box-shadow .2s, transform .2s;
  box-shadow: 0 2px 12px rgba(45,90,79,.4);
  width: 100%;
  justify-content: center;
}
.btn-paiement:hover {
  box-shadow: 0 4px 20px rgba(45,90,79,.55);
  transform: translateY(-1px);
}

.error-msg {
  background: #fee;
  border-left: 4px solid #ef4444;
  padding: 14px;
  border-radius: 8px;
  color: #991b1b;
  margin-bottom: 20px;
  font-size: 0.9rem;
}

.info-nuits {
  font-size: 0.85rem;
  color: #546474;
  margin-top: 4px;
}
</style>

<div class="checkout-hero">
  <h1>Finaliser votre réservation</h1>
  <p>Sélectionnez vos dates et confirmez votre tarif</p>
</div>

<div class="checkout-container">

  <?php if ($errors): ?>
  <div class="error-msg">
    <strong>Erreur :</strong> <?= implode(' ', $errors) ?>
  </div>
  <?php endif; ?>

  <div class="checkout-card">

    <!-- Résumé article -->
    <div class="item-summary">
      <div class="item-summary-icon">
        <?php if ($type === 'villa'): ?>
          <i class="fas fa-home"></i>
        <?php else: ?>
          <i class="fas fa-compass"></i>
        <?php endif; ?>
      </div>
      <div class="item-summary-info">
        <h2><?= htmlspecialchars($type === 'villa' ? $item['titre'] : $item['nom_activite']) ?></h2>
        <p>
          <i class="fas fa-map-marker-alt" style="color:#1a3a2e;margin-right:6px;"></i>
          <?= htmlspecialchars($type === 'villa' ? $item['localisation'] : $item['lieu_depart']) ?>
        </p>
        <p style="font-weight:600;color:#d4af5a;margin-top:8px;">
          <?= number_format($prix_unitaire, 0, ',', ' ') ?> XOF <?= $label_prix ?>
        </p>
      </div>
    </div>

    <!-- Formulaire -->
    <form method="POST">

      <div class="form-grid">
        <div class="form-field">
          <label for="arrivee">Date d'arrivée *</label>
          <input type="date" id="arrivee" name="date_arrivee" required min="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-field">
          <label for="depart">Date de départ *</label>
          <input type="date" id="depart" name="date_depart" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
        </div>

        <?php if ($type === 'activite'): ?>
        <div class="form-field" style="grid-column:1;">
          <label for="qty">Nombre de personnes *</label>
          <input type="number" id="qty" name="quantite" min="1" max="20" value="1" required>
        </div>
        <?php endif; ?>
      </div>

      <!-- Résumé prix (JS) -->
      <div class="price-summary" id="priceSummary" style="display:none;">
        <div class="price-line">
          <span id="priceDesc"></span>
          <span id="priceBreak"></span>
        </div>
        <div class="price-line">
          <strong>Prix total</strong>
          <strong id="priceTotal">—</strong>
        </div>
      </div>

      <button type="submit" class="btn-paiement" id="submitBtn" disabled style="opacity:.5;cursor:not-allowed;">
        <i class="fas fa-lock"></i> Continuer vers le paiement
      </button>

    </form>

  </div>

</div>

<script>
const formArrivee = document.getElementById('arrivee');
const formDepart = document.getElementById('depart');
const formQty = document.getElementById('qty');
const priceSummary = document.getElementById('priceSummary');
const priceDesc = document.getElementById('priceDesc');
const priceBreak = document.getElementById('priceBreak');
const priceTotal = document.getElementById('priceTotal');
const submitBtn = document.getElementById('submitBtn');

const prixUnitaire = <?= $prix_unitaire ?>;
const type = '<?= $type ?>';

function updatePrice() {
  if (!formArrivee.value || !formDepart.value) {
    priceSummary.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '.5';
    submitBtn.style.cursor = 'not-allowed';
    return;
  }

  const d1 = new Date(formArrivee.value);
  const d2 = new Date(formDepart.value);

  if (d2 <= d1) {
    priceSummary.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '.5';
    return;
  }

  const nuits = Math.floor((d2 - d1) / (1000 * 60 * 60 * 24));
  let total = 0;
  let desc = '';

  if (type === 'villa') {
    total = nuits * prixUnitaire;
    desc = `${nuits} nuit${nuits > 1 ? 's' : ''} × ${prixUnitaire.toLocaleString('fr-FR')} XOF`;
  } else {
    const qty = parseInt(formQty.value) || 1;
    total = qty * prixUnitaire;
    desc = `${qty} personne${qty > 1 ? 's' : ''} × ${prixUnitaire.toLocaleString('fr-FR')} XOF`;
  }

  priceDesc.textContent = desc;
  priceBreak.textContent = (total / (type === 'villa' ? nuits : (parseInt(formQty.value) || 1))).toLocaleString('fr-FR', {minimumFractionDigits:0}) + ' XOF';
  priceTotal.textContent = total.toLocaleString('fr-FR') + ' XOF';

  priceSummary.style.display = 'block';
  submitBtn.disabled = false;
  submitBtn.style.opacity = '1';
  submitBtn.style.cursor = 'pointer';
}

formArrivee.addEventListener('change', updatePrice);
formDepart.addEventListener('change', updatePrice);
if (formQty) formQty.addEventListener('change', updatePrice);
</script>

<?php include 'includes/footer.php'; ?>
