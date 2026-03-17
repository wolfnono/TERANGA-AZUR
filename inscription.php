<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['client_id'])) {
  header('Location: mes-reservations.php');
  exit;
}

$page_title = "S'inscrire";
$page_desc  = "Créez votre compte Teranga Azur pour réserver vos villas et activités.";

$message = '';
$message_type = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom       = trim($_POST['nom'] ?? '');
  $prenom    = trim($_POST['prenom'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $telephone = trim($_POST['telephone'] ?? '');
  $pays      = trim($_POST['pays'] ?? '');
  $password  = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';

  $form_data = compact('nom', 'prenom', 'email', 'telephone', 'pays');
  $errors = [];

  if (!$nom)       $errors[] = "Le nom est requis.";
  if (!$prenom)    $errors[] = "Le prénom est requis.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
  if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
  if ($password !== $password2) $errors[] = "Les mots de passe ne correspondent pas.";

  if (empty($errors)) {
     $check = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
      $errors[] = "Cet email est déjà utilisé.";
    }
  }

  if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins  = $pdo->prepare("INSERT INTO clients (nom, prenom, email, telephone, pays) VALUES (?, ?, ?, ?, ?)");
     $ins->execute([$nom, $prenom, $email, $telephone, $pays]);
    $client_id = $pdo->lastInsertId();
    $_SESSION['client_id']   = $client_id;
    $_SESSION['client_nom']  = $nom;
    $_SESSION['client_prenom'] = $prenom;
    header('Location: mes-reservations.php');
    exit;
  } else {
    $message = implode('<br>', $errors);
    $message_type = 'error';
  }
}

include 'includes/header.php';
?>

<div style="min-height:100vh;background:var(--creme);display:flex;align-items:center;justify-content:center;padding:100px 20px 60px;">
  <div style="width:100%;max-width:560px;">

     <div style="text-align:center;margin-bottom:36px;">
      <a href="index.php">
        <img src="images/Logo.png" alt="Teranga Azur" style="height:60px;margin-bottom:16px;">
      </a>
      <h1 style="font-size:2rem;color:var(--bleu-profond);">Créer un compte</h1>
      <p class="tagline" style="margin-top:6px;">Rejoignez la famille Teranga Azur</p>
    </div>

     <div style="background:var(--blanc);border-radius:20px;padding:40px;box-shadow:0 10px 50px rgba(26,58,92,0.1);">

      <?php if ($message): ?>
      <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-exclamation-circle"></i>
        <?= $message ?>
      </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="form-group">
            <label class="form-label">Prénom *</label>
            <input type="text" name="prenom" class="form-input" placeholder="Votre prénom"
                   value="<?= htmlspecialchars($form_data['prenom'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nom *</label>
            <input type="text" name="nom" class="form-input" placeholder="Votre nom"
                   value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Adresse email *</label>
          <input type="email" name="email" class="form-input" placeholder="votre@email.com"
                 value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="tel" name="telephone" class="form-input" placeholder="+221 77 000 00 00"
                   value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Pays</label>
            <select name="pays" class="form-select">
              <option value="">Choisir...</option>
              <option value="Sénégal" <?= ($form_data['pays'] ?? '') === 'Sénégal' ? 'selected' : '' ?>>Sénégal</option>
              <option value="France" <?= ($form_data['pays'] ?? '') === 'France' ? 'selected' : '' ?>>France</option>
              <option value="Belgique" <?= ($form_data['pays'] ?? '') === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
              <option value="Suisse" <?= ($form_data['pays'] ?? '') === 'Suisse' ? 'selected' : '' ?>>Suisse</option>
              <option value="Maroc" <?= ($form_data['pays'] ?? '') === 'Maroc' ? 'selected' : '' ?>>Maroc</option>
              <option value="Côte d'Ivoire" <?= ($form_data['pays'] ?? '') === "Côte d'Ivoire" ? 'selected' : '' ?>>Côte d'Ivoire</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mot de passe * (min. 8 caractères)</label>
          <div style="position:relative;">
            <input type="password" name="password" class="form-input" placeholder="••••••••" id="pwd1" required>
            <button type="button" onclick="togglePwd('pwd1',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--texte-gris);">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Confirmer le mot de passe *</label>
          <div style="position:relative;">
            <input type="password" name="password2" class="form-input" placeholder="••••••••" id="pwd2" required>
            <button type="button" onclick="togglePwd('pwd2',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--texte-gris);">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div style="margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;">
          <input type="checkbox" name="cgv" id="cgv" required style="margin-top:4px;">
          <label for="cgv" style="font-size:0.85rem;color:var(--texte-gris);cursor:pointer;">
            J'accepte les <a href="#" style="color:var(--bleu-moyen);">conditions générales d'utilisation</a> et la <a href="#" style="color:var(--bleu-moyen);">politique de confidentialité</a>.
          </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;font-size:0.95rem;">
          <i class="fas fa-user-plus"></i> Créer mon compte
        </button>
      </form>

      <p style="text-align:center;margin-top:24px;color:var(--texte-gris);font-size:0.9rem;">
        Vous avez déjà un compte ?
        <a href="connexion.php" style="color:var(--bleu-moyen);font-weight:500;text-decoration:none;">Se connecter</a>
      </p>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function togglePwd(id, btn) {
  const input = document.getElementById(id);
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  btn.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
