<?php
/**
 * paiement.php — Formulaire de paiement fictif
 * Traite la réservation et la sauvegarde en base de données
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db.php';

// Vérifier que checkout a bien été effectué
if (empty($_SESSION['checkout'])) {
    header('Location: index.php');
    exit;
}

$checkout = $_SESSION['checkout'];

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $carte = trim($_POST['numero_carte'] ?? '');
    $titulaire = trim($_POST['titulaire'] ?? '');
    $expiration = trim($_POST['expiration'] ?? '');
    $cvc = trim($_POST['cvc'] ?? '');

    // Validation simple
    if (!$carte || strlen($carte) !== 16 || !ctype_digit($carte)) {
        $errors[] = "Numéro de carte invalide (16 chiffres).";
    }
    if (!$titulaire || strlen($titulaire) < 3) {
        $errors[] = "Nom du titulaire requis.";
    }
    if (!$expiration || !preg_match('/^\d{2}\/\d{2}$/', $expiration)) {
        $errors[] = "Date d'expiration invalide (MM/YY).";
    }
    if (!$cvc || !preg_match('/^\d{3}$/', $cvc)) {
        $errors[] = "CVC invalide (3 chiffres).";
    }

    if (empty($errors)) {
        // Sauvegarder la réservation en base de données
        $client_id = $_SESSION['client_id'] ?? null;
        $type = $checkout['type'];
        $item_id = $checkout['item_id'];
        $date_arrivee = $checkout['date_arrivee'];
        $date_depart = $checkout['date_depart'];
        $nuits = $checkout['nuits'] ?? 0;
        $quantite = $checkout['quantite'] ?? 1;
        $prix_total = $checkout['prix_total'];

        $stmt = $pdo->prepare("
            INSERT INTO reservations (type, item_id, client_id, date_arrivee, date_depart, nombre_nuits, nombre_personnes, prix_total, statut, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmée', NOW())
        ");
        $stmt->execute([
            $type,
            $item_id,
            $client_id,
            $date_arrivee,
            $date_depart,
            $nuits,
            $quantite,
            $prix_total
        ]);

        $reservation_id = (int)$pdo->lastInsertId();

        // Nettoyer la session
        unset($_SESSION['checkout']);

        // Rediriger vers confirmation
        header("Location: confirmation.php?id=$reservation_id");
        exit;
    }
}

$page_title = 'Paiement — Réservation';
include 'includes/header.php';
?>

<style>
.payment-hero {
  background: linear-gradient(135deg, #1a3a2e 0%, #2d5a4f 100%);
  padding: 60px 5% 40px;
  color: #fff;
  text-align: center;
}
.payment-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: 2.4rem; margin: 0; }
.payment-hero p { opacity: .8; margin: 10px 0 0; max-width: 540px; margin-left: auto; margin-right: auto; }

.payment-container {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 5%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

@media (max-width: 768px) {
  .payment-container {
    grid-template-columns: 1fr;
  }
}

.payment-card {
  background: #fff;
  border: 1px solid #e8e2d8;
  border-radius: 14px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(26,58,46,.12);
}

.payment-summary h2 {
  font-size: 1.3rem;
  color: #1a3a2e;
  margin: 0 0 24px;
  font-weight: 600;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 14px 0;
  border-bottom: 1px solid #e8e2d8;
  font-size: 0.95rem;
}

.summary-item:last-child {
  border-bottom: none;
}

.summary-item label {
  color: #546474;
}

.summary-item strong {
  color: #1a3a2e;
  font-weight: 600;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  padding: 16px 0;
  margin-top: 16px;
  border-top: 2px solid #d4af5a;
  font-size: 1.3rem;
  font-weight: 700;
}

.summary-total strong {
  color: #d4af5a;
}

.form-field {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

.form-field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a3a2e;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.form-field input {
  padding: 12px 14px;
  border: 1px solid #e8e2d8;
  border-radius: 9px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: border-color .2s, box-shadow .2s;
}

.form-field input:focus {
  outline: none;
  border-color: #d4af5a;
  box-shadow: 0 0 0 3px rgba(212,175,90,.15);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}

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
  margin-top: 8px;
}

.btn-paiement:hover {
  box-shadow: 0 4px 20px rgba(45,90,79,.55);
  transform: translateY(-1px);
}

.btn-retour {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  background: #f0f0f0;
  color: #1a3a2e;
  border: 1px solid #e8e2d8;
  border-radius: 9px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
  text-align: center;
  width: 100%;
  margin-bottom: 20px;
}

.btn-retour:hover {
  background: #e8e2d8;
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

.card-info {
  background: #f8f5f0;
  border: 1px solid #e8e2d8;
  border-radius: 10px;
  padding: 14px;
  margin-bottom: 20px;
  font-size: 0.85rem;
  color: #546474;
}

.card-info i {
  margin-right: 6px;
  color: #d4af5a;
}
</style>

<div class="payment-hero">
  <h1>Finaliser le paiement</h1>
  <p>Vérifiez votre réservation et complétez le paiement fictif</p>
</div>

<div class="payment-container">

  <!-- Formulaire de paiement -->
  <div class="payment-card">
    <h2>Informations de paiement</h2>

    <?php if (!empty($errors)): ?>
    <div class="error-msg">
      <strong>Erreur :</strong> <?= implode(' ', $errors) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <a href="checkout.php?type=<?= htmlspecialchars($checkout['type']) ?>&id=<?= htmlspecialchars($checkout['item_id']) ?>" class="btn-retour">
        <i class="fas fa-arrow-left"></i> Retour au panier
      </a>

      <div class="card-info">
        <i class="fas fa-info-circle"></i>
        Ceci est un système de paiement fictif à des fins de démonstration.
      </div>

      <div class="form-field">
        <label for="numero">Numéro de carte *</label>
        <input type="text" id="numero" name="numero_carte" placeholder="1234 5678 9012 3456"
               maxlength="16" inputmode="numeric" required
               value="<?= htmlspecialchars($_POST['numero_carte'] ?? '') ?>">
      </div>

      <div class="form-field">
        <label for="titulaire">Nom du titulaire *</label>
        <input type="text" id="titulaire" name="titulaire" placeholder="JOHN DOE" required
               value="<?= htmlspecialchars($_POST['titulaire'] ?? '') ?>">
      </div>

      <div class="form-grid">
        <div class="form-field">
          <label for="expiration">Expiration (MM/YY) *</label>
          <input type="text" id="expiration" name="expiration" placeholder="12/28"
                 maxlength="5" required
                 value="<?= htmlspecialchars($_POST['expiration'] ?? '') ?>">
        </div>
        <div class="form-field">
          <label for="cvc">CVC *</label>
          <input type="text" id="cvc" name="cvc" placeholder="123" maxlength="3"
                 inputmode="numeric" required
                 value="<?= htmlspecialchars($_POST['cvc'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn-paiement">
        <i class="fas fa-lock"></i> Confirmer le paiement
      </button>
    </form>
  </div>

  <!-- Résumé de la réservation -->
  <div class="payment-card payment-summary">
    <h2>Résumé de votre réservation</h2>

    <div class="summary-item">
      <label>Type</label>
      <strong><?= $checkout['type'] === 'villa' ? 'Villa' : 'Activité' ?></strong>
    </div>

    <div class="summary-item">
      <label>Nom</label>
      <strong><?= htmlspecialchars($checkout['item_nom']) ?></strong>
    </div>

    <div class="summary-item">
      <label>Arrivée</label>
      <strong><?= date('d/m/Y', strtotime($checkout['date_arrivee'])) ?></strong>
    </div>

    <div class="summary-item">
      <label>Départ</label>
      <strong><?= date('d/m/Y', strtotime($checkout['date_depart'])) ?></strong>
    </div>

    <?php if ($checkout['type'] === 'villa'): ?>
    <div class="summary-item">
      <label>Nuits</label>
      <strong><?= $checkout['nuits'] ?> nuit<?= $checkout['nuits'] > 1 ? 's' : '' ?></strong>
    </div>
    <?php else: ?>
    <div class="summary-item">
      <label>Personnes</label>
      <strong><?= $checkout['quantite'] ?> personne<?= $checkout['quantite'] > 1 ? 's' : '' ?></strong>
    </div>
    <?php endif; ?>

    <div class="summary-item">
      <label>Prix unitaire</label>
      <strong><?= number_format($checkout['prix_unitaire'], 0, ',', ' ') ?> XOF</strong>
    </div>

    <div class="summary-total">
      <label>Total</label>
      <strong><?= number_format($checkout['prix_total'], 0, ',', ' ') ?> XOF</strong>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
