<?php
session_start();
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: activites.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM activites WHERE id = ?");
$stmt->execute([$id]);
$activite = $stmt->fetch();
if (!$activite) { header('Location: activites.php'); exit; }

$page_title = $activite['nom_activite'];
$page_desc  = substr($activite['description'] ?? '', 0, 160);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
  if (!isset($_SESSION['client_id'])) {
    header('Location: connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
  }
  $client_id     = $_SESSION['client_id'];
  $date_activite = $_POST['date_activite'] ?? '';
  $nb_participants = (int)($_POST['nb_participants'] ?? 0);

  if ($date_activite && $nb_participants > 0) {
    $prix_total = $nb_participants * $activite['prix_par_personne'];
    try {
      $ins = $pdo->prepare("INSERT INTO reservations_activites (client_id, activite_id, date_activite, nb_participants, prix_total_activite) VALUES (?, ?, ?, ?, ?)");
      $ins->execute([$client_id, $id, $date_activite, $nb_participants, $prix_total]);
      $message = "Votre réservation pour « " . htmlspecialchars($activite['nom_activite']) . " » a bien été enregistrée !";
      $message_type = 'success';
    } catch (Exception $e) {
      $message = "Erreur lors de la réservation. Veuillez réessayer.";
      $message_type = 'error';
    }
  } else {
    $message = "Veuillez remplir tous les champs.";
    $message_type = 'error';
  }
}

include 'includes/header.php';
?>

<!-- Hero activité -->
<div class="page-hero" style="background: var(--gradient-hero), url('images/activites/activite-<?= $id ?>.jpg') center/cover; background-size: cover; min-height:450px; margin-top:80px;">
  <div class="page-hero-content">
    <span class="section-label" style="color:var(--or-sable);">Activité</span>
    <h1 style="color:var(--blanc);font-size:clamp(2rem,5vw,4rem);"><?= htmlspecialchars($activite['nom_activite']) ?></h1>
    <div style="display:flex;gap:20px;justify-content:center;margin-top:16px;flex-wrap:wrap;">
      <span style="color:rgba(255,255,255,0.85);display:flex;align-items:center;gap:6px;">
        <i class="fas fa-clock" style="color:var(--or-sable);"></i> <?= $activite['duree_heures'] ?> heure(s)
      </span>
      <span style="color:rgba(255,255,255,0.85);display:flex;align-items:center;gap:6px;">
        <i class="fas fa-map-marker-alt" style="color:var(--or-sable);"></i> <?= htmlspecialchars($activite['lieu_depart']) ?>
      </span>
      <span style="color:rgba(255,255,255,0.85);display:flex;align-items:center;gap:6px;">
        <i class="fas fa-tag" style="color:var(--or-sable);"></i> <?= number_format($activite['prix_par_personne'], 0, ',', ' ') ?> XOF/pers.
      </span>
    </div>
  </div>
</div>

<section class="section">
  <div style="display:grid;grid-template-columns:1fr 380px;gap:50px;align-items:start;" class="detail-grid">
    <!-- Description -->
    <div>
      <div style="margin-bottom:16px;">
        <a href="activites.php" style="color:var(--bleu-moyen);text-decoration:none;font-size:0.88rem;">
          <i class="fas fa-arrow-left"></i> Retour aux activités
        </a>
      </div>
      <h2 style="color:var(--bleu-profond);margin-bottom:20px;">Description</h2>
      <p style="color:var(--texte-gris);line-height:1.9;font-size:1rem;">
        <?= nl2br(htmlspecialchars($activite['description'] ?? 'Description en cours de rédaction.')) ?>
      </p>

      <div style="margin-top:40px;display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
        <div style="background:var(--creme);padding:24px;border-radius:12px;text-align:center;">
          <i class="fas fa-clock" style="font-size:1.5rem;color:var(--bleu-moyen);margin-bottom:10px;display:block;"></i>
          <strong style="color:var(--bleu-profond);"><?= $activite['duree_heures'] ?> h</strong>
          <p style="font-size:0.82rem;color:var(--texte-gris);margin-top:4px;">Durée</p>
        </div>
        <div style="background:var(--creme);padding:24px;border-radius:12px;text-align:center;">
          <i class="fas fa-map-marker-alt" style="font-size:1.5rem;color:var(--bleu-moyen);margin-bottom:10px;display:block;"></i>
          <strong style="color:var(--bleu-profond);"><?= htmlspecialchars($activite['lieu_depart']) ?></strong>
          <p style="font-size:0.82rem;color:var(--texte-gris);margin-top:4px;">Départ</p>
        </div>
        <div style="background:var(--creme);padding:24px;border-radius:12px;text-align:center;">
          <i class="fas fa-tag" style="font-size:1.5rem;color:var(--or-sable);margin-bottom:10px;display:block;"></i>
          <strong style="color:var(--bleu-profond);"><?= number_format($activite['prix_par_personne'], 0, ',', ' ') ?> XOF</strong>
          <p style="font-size:0.82rem;color:var(--texte-gris);margin-top:4px;">Par personne</p>
        </div>
      </div>
    </div>

    <!-- Réservation -->
    <div class="booking-card" style="background:var(--blanc);border-radius:16px;padding:32px;box-shadow:0 10px 40px rgba(26,58,92,0.1);position:sticky;top:100px;">
      <h3 style="color:var(--bleu-profond);margin-bottom:24px;font-size:1.5rem;">Réserver cette activité</h3>

      <?php if ($message): ?>
      <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
      </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Date souhaitée</label>
          <input type="date" name="date_activite" class="form-input" min="<?= date('Y-m-d') ?>" required id="dateAct">
        </div>
        <div class="form-group">
          <label class="form-label">Nombre de participants</label>
          <select name="nb_participants" class="form-select" required id="nbParticipants">
            <option value="">Choisir...</option>
            <?php for ($i = 1; $i <= 20; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?> personne<?= $i > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div id="priceSummaryAct" style="background:var(--creme);padding:16px;border-radius:10px;margin-bottom:20px;display:none;">
          <div style="display:flex;justify-content:space-between;font-size:0.88rem;color:var(--texte-gris);">
            <span id="priceActLabel"></span>
            <span id="priceActTotal"></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-weight:600;color:var(--bleu-profond);margin-top:8px;border-top:1px solid #ddd;padding-top:8px;">
            <span>Total</span>
            <span id="priceActGrand"></span>
          </div>
        </div>

        <?php if (isset($_SESSION['client_id'])): ?>
        <button type="submit" name="reserver" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;">
          <i class="fas fa-check"></i> Confirmer la réservation
        </button>
        <?php else: ?>
        <a href="connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;">
          <i class="fas fa-lock"></i> Se connecter pour réserver
        </a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
const prixPers = <?= $activite['prix_par_personne'] ?>;
const nbSel = document.getElementById('nbParticipants');
const summaryAct = document.getElementById('priceSummaryAct');

nbSel.addEventListener('change', function() {
  const nb = parseInt(this.value);
  if (!nb) { summaryAct.style.display = 'none'; return; }
  const total = nb * prixPers;
  document.getElementById('priceActLabel').textContent = `${nb} pers. × ${prixPers.toLocaleString('fr-FR')} XOF`;
  document.getElementById('priceActTotal').textContent = total.toLocaleString('fr-FR') + ' XOF';
  document.getElementById('priceActGrand').textContent = total.toLocaleString('fr-FR') + ' XOF';
  summaryAct.style.display = 'block';
});
</script>
