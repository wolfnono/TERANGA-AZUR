<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['client_id'])) {
  header('Location: connexion.php?redirect=mes-reservations.php');
  exit;
}

$page_title = "Mes Réservations";
$client_id  = $_SESSION['client_id'];

$stmt = $pdo->prepare("
  SELECT rv.*, v.titre, v.localisation, v.prix_par_nuit, v.chambres
  FROM reservations_villas rv
  JOIN villas v ON rv.villa_id = v.id
  WHERE rv.client_id = ?
  ORDER BY rv.date_arrivee DESC
");
$stmt->execute([$client_id]);
$reservations_villas = $stmt->fetchAll();

$stmt2 = $pdo->prepare("
  SELECT ra.*, a.nom_activite, a.lieu_depart, a.duree_heures
  FROM reservations_activites ra
  JOIN activites a ON ra.activite_id = a.id
  WHERE ra.client_id = ?
  ORDER BY ra.date_activite DESC
");
$stmt2->execute([$client_id]);
$reservations_activites = $stmt2->fetchAll();

$stmt3 = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt3->execute([$client_id]);
$client = $stmt3->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['annuler_villa'])) {
    $res_id = (int)$_POST['res_id'];
    $pdo->prepare("UPDATE reservations_villas SET statut = 'Annulée' WHERE id = ? AND client_id = ?")->execute([$res_id, $client_id]);
    header('Location: mes-reservations.php?msg=annulee');
    exit;
  }
  if (isset($_POST['annuler_activite'])) {
    $res_id = (int)$_POST['res_id'];
    $pdo->prepare("UPDATE reservations_activites SET statut = 'Annulée' WHERE id = ? AND client_id = ?")->execute([$res_id, $client_id]);
    header('Location: mes-reservations.php?msg=annulee');
    exit;
  }
}

$msg = $_GET['msg'] ?? '';

include 'includes/header.php';
?>

<div class="page-hero" style="background:linear-gradient(135deg,rgba(26,58,46,0.95) 0%,rgba(45,90,79,0.85) 100%);min-height:260px;">
  <div class="page-hero-content">
    <span class="section-label" style="color:var(--or-sable);">Mon espace</span>
    <h1 style="color:var(--blanc);">Mes Réservations</h1>
    <p style="color:rgba(255,255,255,0.8);">Bonjour <?= htmlspecialchars($client['prenom'] ?? '') ?> 👋</p>
  </div>
</div>

<section class="section" style="padding-top:60px;">

  <?php if ($msg === 'annulee'): ?>
  <div class="alert alert-info" style="max-width:600px;margin:0 auto 30px;">
    <i class="fas fa-info-circle"></i> Votre réservation a été annulée avec succès.
  </div>
  <?php endif; ?>

  <div style="margin-bottom:60px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
      <h2 style="color:var(--bleu-profond);font-size:1.8rem;">
        <i class="fas fa-home" style="color:var(--or-sable);margin-right:10px;"></i>
        Villas réservées
      </h2>
      <a href="villas.php" class="btn btn-primary" style="padding:10px 22px;font-size:0.82rem;">
        <i class="fas fa-plus"></i> Réserver une villa
      </a>
    </div>

    <?php if (empty($reservations_villas)): ?>
    <div style="background:var(--creme);border-radius:16px;padding:50px;text-align:center;">
      <i class="fas fa-home" style="font-size:2.5rem;color:#ccc;margin-bottom:16px;display:block;"></i>
      <p style="color:var(--texte-gris);margin-bottom:20px;">Vous n'avez pas encore réservé de villa.</p>
      <a href="villas.php" class="btn btn-primary">Explorer nos villas</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:20px;">
      <?php foreach ($reservations_villas as $res): ?>
      <div class="resa-card" style="background:var(--blanc);border-radius:16px;padding:28px 32px;box-shadow:0 4px 20px var(--ombre);display:grid;grid-template-columns:auto 1fr auto;gap:24px;align-items:center;">
        <div class="resa-img-wrap">
          <?php $v_imgs = [1=>'VillaA.png', 2=>'VillaB.png', 3=>'VillaC.png']; $v_img = $v_imgs[$res['villa_id'] ?? 1] ?? 'Vue-Balcon.villa2.png'; ?>
          <img src="images/<?= $v_img ?>"
               onerror="this.src='images/Logo.png'"
               alt="" style="width:100px;height:80px;object-fit:cover;border-radius:10px;">
        </div>
        <div>
          <h3 style="color:var(--bleu-profond);font-size:1.3rem;margin-bottom:6px;"><?= htmlspecialchars($res['titre']) ?></h3>
          <p style="color:var(--texte-gris);font-size:0.88rem;display:flex;gap:16px;flex-wrap:wrap;">
            <span><i class="fas fa-map-marker-alt" style="color:var(--bleu-moyen);"></i> <?= htmlspecialchars($res['localisation']) ?></span>
            <span><i class="fas fa-calendar-alt" style="color:var(--bleu-moyen);"></i>
              <?= date('d/m/Y', strtotime($res['date_arrivee'])) ?> → <?= date('d/m/Y', strtotime($res['date_depart'])) ?>
            </span>
            <span><i class="fas fa-users" style="color:var(--bleu-moyen);"></i> <?= $res['nb_voyageurs'] ?> voyageurs</span>
          </p>
          <p style="color:var(--or-sable);font-weight:600;margin-top:8px;font-size:1rem;">
            <?= number_format($res['prix_total_sejour'], 0, ',', ' ') ?> XOF
          </p>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;">
          <?php
          $statut = $res['statut'] ?? 'En attente';
          $badge_map = ['En attente' => 'attente', 'Confirmée' => 'confirmee', 'Annulée' => 'annulee', 'Terminée' => 'terminee'];
          $badge_class = $badge_map[$statut] ?? 'attente';
          ?>
          <span class="badge badge-<?= $badge_class ?>"><?= $statut ?></span>
          <?php if ($statut === 'En attente'): ?>
          <form method="POST" onsubmit="return confirm('Confirmer l\'annulation ?');">
            <input type="hidden" name="res_id" value="<?= $res['id'] ?>">
            <button type="submit" name="annuler_villa" class="btn" style="padding:8px 16px;font-size:0.78rem;background:rgba(220,53,69,0.1);color:#842029;border:1px solid rgba(220,53,69,0.2);">
              <i class="fas fa-times"></i> Annuler
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
      <h2 style="color:var(--bleu-profond);font-size:1.8rem;">
        <i class="fas fa-compass" style="color:var(--or-sable);margin-right:10px;"></i>
        Activités réservées
      </h2>
      <a href="activites.php" class="btn btn-primary" style="padding:10px 22px;font-size:0.82rem;">
        <i class="fas fa-plus"></i> Réserver une activité
      </a>
    </div>

    <?php if (empty($reservations_activites)): ?>
    <div style="background:var(--creme);border-radius:16px;padding:50px;text-align:center;">
      <i class="fas fa-compass" style="font-size:2.5rem;color:#ccc;margin-bottom:16px;display:block;"></i>
      <p style="color:var(--texte-gris);margin-bottom:20px;">Vous n'avez pas encore réservé d'activité.</p>
      <a href="activites.php" class="btn btn-primary">Explorer les activités</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:20px;">
      <?php foreach ($reservations_activites as $res): ?>
      <div class="resa-card" style="background:var(--blanc);border-radius:16px;padding:28px 32px;box-shadow:0 4px 20px var(--ombre);display:grid;grid-template-columns:auto 1fr auto;gap:24px;align-items:center;">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--bleu-profond),var(--bleu-moyen));border-radius:12px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-compass" style="font-size:2rem;color:var(--or-sable);"></i>
        </div>
        <div>
          <h3 style="color:var(--bleu-profond);font-size:1.3rem;margin-bottom:6px;"><?= htmlspecialchars($res['nom_activite']) ?></h3>
          <p style="color:var(--texte-gris);font-size:0.88rem;display:flex;gap:16px;flex-wrap:wrap;">
            <span><i class="fas fa-calendar" style="color:var(--bleu-moyen);"></i> <?= date('d/m/Y', strtotime($res['date_activite'])) ?></span>
            <span><i class="fas fa-users" style="color:var(--bleu-moyen);"></i> <?= $res['nb_participants'] ?> participant(s)</span>
            <span><i class="fas fa-map-marker-alt" style="color:var(--bleu-moyen);"></i> <?= htmlspecialchars($res['lieu_depart']) ?></span>
          </p>
          <p style="color:var(--or-sable);font-weight:600;margin-top:8px;font-size:1rem;">
            <?= number_format($res['prix_total_activite'], 0, ',', ' ') ?> XOF
          </p>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;">
          <?php
          $statut = $res['statut'] ?? 'En attente';
          $badge_map = ['En attente' => 'attente', 'Confirmée' => 'confirmee', 'Annulée' => 'annulee', 'Terminée' => 'terminee'];
          $badge_class = $badge_map[$statut] ?? 'attente';
          ?>
          <span class="badge badge-<?= $badge_class ?>"><?= $statut ?></span>
          <?php if ($statut === 'En attente'): ?>
          <form method="POST" onsubmit="return confirm('Confirmer l\'annulation ?');">
            <input type="hidden" name="res_id" value="<?= $res['id'] ?>">
            <button type="submit" name="annuler_activite" class="btn" style="padding:8px 16px;font-size:0.78rem;background:rgba(220,53,69,0.1);color:#842029;border:1px solid rgba(220,53,69,0.2);">
              <i class="fas fa-times"></i> Annuler
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</section>

<?php include 'includes/footer.php'; ?>

<style>
@media (max-width: 768px) {
  .resa-card { grid-template-columns: 1fr !important; }
  .resa-img-wrap { display: none; }
}
</style>
