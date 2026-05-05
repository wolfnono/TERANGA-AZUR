<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db.php';

$reservation_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$reservation_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header('Location: index.php');
    exit;
}

$item = null;
$item_type_label = $reservation['type'] === 'villa' ? 'Villa' : 'Activité';

if ($reservation['type'] === 'villa') {
    $stmt = $pdo->prepare("SELECT titre, localisation FROM villas WHERE id = ?");
    $stmt->execute([$reservation['item_id']]);
    $item = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT nom_activite, lieu_depart FROM activites WHERE id = ?");
    $stmt->execute([$reservation['item_id']]);
    $item = $stmt->fetch();
}

$page_title = 'Réservation confirmée — Merci!';
include 'includes/header.php';
?>

<style>
.confirmation-hero {
  background: linear-gradient(135deg, #1a3a2e 0%, #2d5a4f 100%);
  padding: 60px 5% 40px;
  color: #fff;
  text-align: center;
}

.confirmation-hero h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 2.4rem;
  margin: 0;
}

.confirmation-hero p {
  opacity: .8;
  margin: 10px 0 0;
  max-width: 540px;
  margin-left: auto;
  margin-right: auto;
}

.confirmation-container {
  max-width: 700px;
  margin: 40px auto;
  padding: 0 5%;
}

.confirmation-success {
  background: #f0fdf4;
  border: 2px solid #86efac;
  border-radius: 14px;
  padding: 40px;
  text-align: center;
  margin-bottom: 24px;
}

.confirmation-success i {
  font-size: 3rem;
  color: #22c55e;
  margin-bottom: 16px;
}

.confirmation-success h2 {
  color: #15803d;
  font-size: 1.8rem;
  margin: 0 0 8px;
}

.confirmation-success p {
  color: #4b5563;
  margin: 0;
}

.confirmation-number {
  background: #fff;
  border: 2px solid #d4af5a;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  text-align: center;
}

.confirmation-number .label {
  font-size: 0.85rem;
  color: #546474;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

.confirmation-number .number {
  font-family: 'Courier New', monospace;
  font-size: 2rem;
  font-weight: 700;
  color: #1a3a2e;
  word-break: break-all;
}

.confirmation-card {
  background: #fff;
  border: 1px solid #e8e2d8;
  border-radius: 14px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(26,58,46,.12);
  margin-bottom: 24px;
}

.confirmation-card h3 {
  font-size: 1.1rem;
  color: #1a3a2e;
  margin: 0 0 20px;
  font-weight: 600;
}

.confirmation-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #e8e2d8;
  font-size: 0.95rem;
}

.confirmation-item:last-child {
  border-bottom: none;
}

.confirmation-item label {
  color: #546474;
}

.confirmation-item strong {
  color: #1a3a2e;
  font-weight: 600;
}

.confirmation-total {
  display: flex;
  justify-content: space-between;
  padding: 16px 0;
  margin-top: 16px;
  border-top: 2px solid #d4af5a;
  font-size: 1.3rem;
  font-weight: 700;
}

.confirmation-total strong {
  color: #d4af5a;
}

.confirmation-actions {
  display: flex;
  gap: 14px;
  margin-top: 24px;
}

@media (max-width: 600px) {
  .confirmation-actions {
    flex-direction: column;
  }
}

.btn-action {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 24px;
  border: none;
  border-radius: 9px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all .2s;
}

.btn-primary {
  background: linear-gradient(135deg, #2d5a4f 0%, #4a8c7d 100%);
  color: #fff;
  box-shadow: 0 2px 12px rgba(45,90,79,.4);
}

.btn-primary:hover {
  box-shadow: 0 4px 20px rgba(45,90,79,.55);
  transform: translateY(-1px);
}

.btn-secondary {
  background: #f0f0f0;
  color: #1a3a2e;
  border: 1px solid #e8e2d8;
}

.btn-secondary:hover {
  background: #e8e2d8;
}

.confirmation-info {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  padding: 14px;
  margin-bottom: 20px;
  font-size: 0.85rem;
  color: #1e40af;
}

.confirmation-info i {
  margin-right: 6px;
}
</style>

<div class="confirmation-hero">
  <h1>Réservation confirmée !</h1>
  <p>Merci d'avoir choisi Teranga Azur</p>
</div>

<div class="confirmation-container">

  <div class="confirmation-success">
    <i class="fas fa-check-circle"></i>
    <h2>Succès</h2>
    <p>Votre paiement a été traité avec succès</p>
  </div>

  <div class="confirmation-number">
    <div class="label">Numéro de réservation</div>
    <div class="number">#<?= str_pad($reservation['id'], 6, '0', STR_PAD_LEFT) ?></div>
  </div>

  <div class="confirmation-info">
    <i class="fas fa-info-circle"></i>
    Vous recevrez un email de confirmation dans quelques minutes avec tous les détails de votre réservation.
  </div>

  <div class="confirmation-card">
    <h3>Détails de la réservation</h3>

    <div class="confirmation-item">
      <label>Type</label>
      <strong><?= $item_type_label ?></strong>
    </div>

    <div class="confirmation-item">
      <label>Nom</label>
      <strong>
        <?php if ($reservation['type'] === 'villa' && $item): ?>
          <?= htmlspecialchars($item['titre']) ?>
        <?php elseif ($item): ?>
          <?= htmlspecialchars($item['nom_activite']) ?>
        <?php endif; ?>
      </strong>
    </div>

    <div class="confirmation-item">
      <label>Localisation</label>
      <strong>
        <?php if ($reservation['type'] === 'villa' && $item): ?>
          <?= htmlspecialchars($item['localisation']) ?>
        <?php elseif ($item): ?>
          <?= htmlspecialchars($item['lieu_depart']) ?>
        <?php endif; ?>
      </strong>
    </div>

    <div class="confirmation-item">
      <label>Date d'arrivée</label>
      <strong><?= date('d/m/Y', strtotime($reservation['date_arrivee'])) ?></strong>
    </div>

    <div class="confirmation-item">
      <label>Date de départ</label>
      <strong><?= date('d/m/Y', strtotime($reservation['date_depart'])) ?></strong>
    </div>

    <?php if ($reservation['type'] === 'villa'): ?>
    <div class="confirmation-item">
      <label>Durée</label>
      <strong><?= $reservation['nombre_nuits'] ?> nuit<?= $reservation['nombre_nuits'] > 1 ? 's' : '' ?></strong>
    </div>
    <?php else: ?>
    <div class="confirmation-item">
      <label>Nombre de personnes</label>
      <strong><?= $reservation['nombre_personnes'] ?> personne<?= $reservation['nombre_personnes'] > 1 ? 's' : '' ?></strong>
    </div>
    <?php endif; ?>

    <div class="confirmation-total">
      <label>Montant</label>
      <strong><?= number_format($reservation['prix_total'], 0, ',', ' ') ?> XOF</strong>
    </div>
  </div>

  <div class="confirmation-actions">
    <a href="index.php" class="btn-action btn-primary">
      <i class="fas fa-home"></i> Retour à l'accueil
    </a>
    <a href="<?= $reservation['type'] === 'villa' ? 'villas.php' : 'activites.php' ?>" class="btn-action btn-secondary">
      <i class="fas fa-<?= $reservation['type'] === 'villa' ? 'home' : 'compass' ?>"></i>
      Voir d'autres <?= $reservation['type'] === 'villa' ? 'villas' : 'activités' ?>
    </a>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
