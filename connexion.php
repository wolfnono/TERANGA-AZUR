<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['client_id'])) {
  // Déjà connecté: rediriger selon le rôle
  if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin/dashboard.php');
  } else {
    header('Location: mes-reservations.php');
  }
  exit;
}

$page_title = "Se connecter";
$page_desc  = "Connectez-vous à votre espace Teranga Azur.";

$redirect = $_GET['redirect'] ?? 'mes-reservations.php';
$message  = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $email_val = $email;

  if ($email && $password) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
    $stmt->execute([$email]);
    $client = $stmt->fetch();

    if ($client && password_verify($password, $client['password'])) {
      $_SESSION['client_id']     = $client['id'];
      $_SESSION['client_nom']    = $client['nom'];
      $_SESSION['client_prenom'] = $client['prenom'];
      $_SESSION['role']          = $client['role'] ?? 'client';

      if (($client['role'] ?? 'client') === 'admin') {
        header('Location: admin/dashboard.php');
      } else {
        header('Location: ' . htmlspecialchars($redirect));
      }
      exit;
    } else {
      $message = "Email ou mot de passe incorrect.";
    }
  } else {
    $message = "Veuillez remplir tous les champs.";
  }
}

include 'includes/header.php';
?>

<div style="min-height:100vh;background:var(--creme);display:flex;align-items:center;justify-content:center;padding:100px 20px 60px;">
  <div style="width:100%;max-width:460px;">

    <!-- Logo -->
    <div style="text-align:center;margin-bottom:36px;">
      <a href="index.php">
        <img src="images/Logo.png" alt="Teranga Azur" style="height:60px;margin-bottom:16px;">
      </a>
      <h1 style="font-size:2rem;color:var(--bleu-profond);">Bon retour !</h1>
      <p class="tagline" style="margin-top:6px;">Connectez-vous à votre espace</p>
    </div>

    <!-- Card -->
    <div style="background:var(--blanc);border-radius:20px;padding:40px;box-shadow:0 10px 50px rgba(26,58,46,0.1);">

      <?php if ($message): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($message) ?>
      </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="form-group">
          <label class="form-label">Adresse email</label>
          <input type="email" name="email" class="form-input" placeholder="votre@email.com"
                 value="<?= htmlspecialchars($email_val) ?>" required autofocus>
        </div>

        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <label class="form-label" style="margin:0;">Mot de passe</label>
            <a href="#" style="font-size:0.8rem;color:var(--bleu-moyen);text-decoration:none;">Mot de passe oublié ?</a>
          </div>
          <div style="position:relative;">
            <input type="password" name="password" class="form-input" placeholder="••••••••" id="pwd" required>
            <button type="button" onclick="togglePwd()" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--texte-gris);">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:24px;">
          <input type="checkbox" name="remember" id="remember">
          <label for="remember" style="font-size:0.85rem;color:var(--texte-gris);cursor:pointer;">
            Se souvenir de moi
          </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;font-size:0.95rem;">
          <i class="fas fa-sign-in-alt"></i> Se connecter
        </button>
      </form>

      <!-- Séparateur -->
      <div style="display:flex;align-items:center;gap:16px;margin:28px 0;">
        <div style="flex:1;height:1px;background:#eee;"></div>
        <span style="font-size:0.8rem;color:var(--texte-gris);">ou</span>
        <div style="flex:1;height:1px;background:#eee;"></div>
      </div>

      <p style="text-align:center;color:var(--texte-gris);font-size:0.9rem;">
        Pas encore de compte ?
        <a href="inscription.php" style="color:var(--bleu-moyen);font-weight:600;text-decoration:none;">S'inscrire gratuitement</a>
      </p>
    </div>

    <!-- Retour accueil -->
    <p style="text-align:center;margin-top:20px;">
      <a href="index.php" style="color:var(--texte-gris);font-size:0.85rem;text-decoration:none;">
        <i class="fas fa-arrow-left"></i> Retour à l'accueil
      </a>
    </p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function togglePwd() {
  const pwd = document.getElementById('pwd');
  const eye = document.getElementById('eyeIcon');
  const isHidden = pwd.type === 'password';
  pwd.type = isHidden ? 'text' : 'password';
  eye.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
