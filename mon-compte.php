<?php
/**
 * mon-compte.php — Gestion du compte utilisateur (profil + mot de passe)
 */
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['client_id'])) {
    header('Location: connexion.php?redirect=mon-compte.php');
    exit;
}

$page_title = "Mon Compte";
$page_desc  = "Gérez vos informations personnelles et votre mot de passe.";
$client_id  = $_SESSION['client_id'];

// Charger le client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

$msg      = '';
$msg_type = '';

// -------------------------------------------------------
// Traitement du formulaire
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- MISE À JOUR PROFIL ---
    if (isset($_POST['update_profile'])) {
        $nom       = trim($_POST['nom'] ?? '');
        $prenom    = trim($_POST['prenom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $pays      = trim($_POST['pays'] ?? '');
        $errors    = [];

        if (!$nom)    $errors[] = "Le nom est requis.";
        if (!$prenom) $errors[] = "Le prénom est requis.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

        // Vérifier email dupliqué
        if (empty($errors)) {
            $check = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
            $check->execute([$email, $client_id]);
            if ($check->fetch()) $errors[] = "Cet email est déjà utilisé par un autre compte.";
        }

        if (empty($errors)) {
            $pdo->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, pays=? WHERE id=?")
                ->execute([$nom, $prenom, $email, $telephone, $pays, $client_id]);
            // Mettre à jour la session
            $_SESSION['client_nom']    = $nom;
            $_SESSION['client_prenom'] = $prenom;
            // Recharger les données
            $stmt->execute([$client_id]);
            $client = $stmt->fetch();
            $msg      = "Vos informations ont été mises à jour avec succès.";
            $msg_type = 'success';
        } else {
            $msg      = implode(' ', $errors);
            $msg_type = 'error';
        }
    }

    // --- CHANGEMENT DE MOT DE PASSE ---
    if (isset($_POST['update_password'])) {
        $old_pwd  = $_POST['old_password'] ?? '';
        $new_pwd  = $_POST['new_password'] ?? '';
        $new_pwd2 = $_POST['new_password2'] ?? '';
        $errors   = [];

        if (!password_verify($old_pwd, $client['password'])) {
            $errors[] = "Mot de passe actuel incorrect.";
        }
        if (strlen($new_pwd) < 8) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        }
        if ($new_pwd !== $new_pwd2) {
            $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
        }

        if (empty($errors)) {
            $hash = password_hash($new_pwd, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE clients SET password=? WHERE id=?")->execute([$hash, $client_id]);
            $msg      = "Mot de passe modifié avec succès.";
            $msg_type = 'success';
            // Recharger le client pour avoir le nouveau hash
            $stmt->execute([$client_id]);
            $client = $stmt->fetch();
        } else {
            $msg      = implode(' ', $errors);
            $msg_type = 'error';
        }
    }
}

// Stats rapides
$nb_villas  = $pdo->prepare("SELECT COUNT(*) FROM reservations_villas WHERE client_id = ?");
$nb_villas->execute([$client_id]);
$nb_v = $nb_villas->fetchColumn();

$nb_acts = $pdo->prepare("SELECT COUNT(*) FROM reservations_activites WHERE client_id = ?");
$nb_acts->execute([$client_id]);
$nb_a = $nb_acts->fetchColumn();

$membre_depuis = date('F Y', strtotime($client['created_at']));

include 'includes/header.php';
?>

<!-- HERO -->
<div class="page-hero" style="background:linear-gradient(135deg,rgba(26,58,92,0.95) 0%,rgba(42,95,143,0.85) 100%);min-height:280px;">
  <div class="page-hero-content">
    <span class="section-label" style="color:var(--or-sable);">Mon espace</span>
    <h1 style="color:var(--blanc);">Mon Compte</h1>
    <p style="color:rgba(255,255,255,0.8);">
      Bonjour <?= htmlspecialchars($client['prenom']) ?> 👋 — Membre depuis <?= $membre_depuis ?>
    </p>
  </div>
</div>

<section class="section" style="padding-top:60px;padding-bottom:80px;">

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msg_type ?>" style="max-width:900px;margin:0 auto 30px;">
    <i class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($msg) ?>
  </div>
  <?php endif; ?>

  <div style="max-width:900px;margin:0 auto;">

    <!-- STATS RAPIDES -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:48px;">
      <div style="background:var(--creme);border-radius:16px;padding:28px 24px;text-align:center;">
        <div style="font-size:2.2rem;font-weight:700;color:var(--bleu-profond);font-family:'Cormorant Garamond',serif;"><?= $nb_v ?></div>
        <div style="font-size:0.82rem;color:var(--texte-gris);text-transform:uppercase;letter-spacing:0.1em;margin-top:4px;">
          <i class="fas fa-home" style="color:var(--bleu-moyen);margin-right:5px;"></i>Villa<?= $nb_v > 1 ? 's' : '' ?> réservée<?= $nb_v > 1 ? 's' : '' ?>
        </div>
      </div>
      <div style="background:var(--creme);border-radius:16px;padding:28px 24px;text-align:center;">
        <div style="font-size:2.2rem;font-weight:700;color:var(--bleu-profond);font-family:'Cormorant Garamond',serif;"><?= $nb_a ?></div>
        <div style="font-size:0.82rem;color:var(--texte-gris);text-transform:uppercase;letter-spacing:0.1em;margin-top:4px;">
          <i class="fas fa-compass" style="color:var(--bleu-moyen);margin-right:5px;"></i>Activité<?= $nb_a > 1 ? 's' : '' ?> réservée<?= $nb_a > 1 ? 's' : '' ?>
        </div>
      </div>
      <div style="background:linear-gradient(135deg,var(--bleu-profond),var(--bleu-moyen));border-radius:16px;padding:28px 24px;text-align:center;">
        <div style="font-size:1.5rem;font-weight:600;color:var(--or-sable);font-family:'Cormorant Garamond',serif;">
          <?= htmlspecialchars($client['pays'] ?: 'N/A') ?>
        </div>
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.1em;margin-top:4px;">
          <i class="fas fa-globe" style="color:var(--or-sable);margin-right:5px;"></i>Pays
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;">

      <!-- ===== FORMULAIRE PROFIL ===== -->
      <div style="background:var(--blanc);border-radius:20px;padding:36px;box-shadow:0 8px 40px var(--ombre);">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--creme-fonce);">
          <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--bleu-profond),var(--bleu-moyen));border-radius:12px;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-user" style="color:#fff;font-size:1.1rem;"></i>
          </div>
          <div>
            <h2 style="font-size:1.4rem;color:var(--bleu-profond);margin-bottom:2px;">Mes informations</h2>
            <p style="font-size:0.82rem;color:var(--texte-gris);">Nom, email, contact</p>
          </div>
        </div>

        <form method="POST" novalidate>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label class="form-label">Prénom *</label>
              <input type="text" name="prenom" class="form-input"
                     value="<?= htmlspecialchars($client['prenom']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nom *</label>
              <input type="text" name="nom" class="form-input"
                     value="<?= htmlspecialchars($client['nom']) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Adresse email *</label>
            <input type="email" name="email" class="form-input"
                   value="<?= htmlspecialchars($client['email']) ?>" required>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label class="form-label">Téléphone</label>
              <input type="tel" name="telephone" class="form-input"
                     placeholder="+221 77 000 00 00"
                     value="<?= htmlspecialchars($client['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Pays</label>
              <select name="pays" class="form-select">
                <option value="">Choisir...</option>
                <?php
                $pays_list = ['Sénégal','France','Belgique','Suisse','Canada','Maroc',"Côte d'Ivoire",'Autre'];
                foreach ($pays_list as $p):
                ?>
                <option value="<?= $p ?>" <?= $client['pays'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit" name="update_profile" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
            <i class="fas fa-save"></i> Enregistrer les modifications
          </button>
        </form>
      </div>

      <!-- ===== FORMULAIRE MOT DE PASSE ===== -->
      <div>
        <!-- Changer le mot de passe -->
        <div style="background:var(--blanc);border-radius:20px;padding:36px;box-shadow:0 8px 40px var(--ombre);margin-bottom:24px;">
          <div style="display:flex;align-items:center;gap:14px;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--creme-fonce);">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#c9a96e,#d4af5a);border-radius:12px;display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-lock" style="color:var(--bleu-profond);font-size:1.1rem;"></i>
            </div>
            <div>
              <h2 style="font-size:1.4rem;color:var(--bleu-profond);margin-bottom:2px;">Mot de passe</h2>
              <p style="font-size:0.82rem;color:var(--texte-gris);">Modifier votre mot de passe</p>
            </div>
          </div>

          <form method="POST" novalidate>
            <div class="form-group">
              <label class="form-label">Mot de passe actuel *</label>
              <div style="position:relative;">
                <input type="password" name="old_password" class="form-input" id="pwd_old" placeholder="••••••••" required>
                <button type="button" onclick="togglePwd('pwd_old',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--texte-gris);">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Nouveau mot de passe * (min. 8 car.)</label>
              <div style="position:relative;">
                <input type="password" name="new_password" class="form-input" id="pwd_new" placeholder="••••••••" required>
                <button type="button" onclick="togglePwd('pwd_new',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--texte-gris);">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Confirmer le nouveau mot de passe *</label>
              <div style="position:relative;">
                <input type="password" name="new_password2" class="form-input" id="pwd_new2" placeholder="••••••••" required>
                <button type="button" onclick="togglePwd('pwd_new2',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--texte-gris);">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <button type="submit" name="update_password" class="btn btn-dark" style="width:100%;justify-content:center;">
              <i class="fas fa-key"></i> Changer le mot de passe
            </button>
          </form>
        </div>

        <!-- Liens rapides -->
        <div style="background:var(--creme);border-radius:20px;padding:28px;">
          <h3 style="font-size:1rem;color:var(--bleu-profond);margin-bottom:16px;font-family:'Jost',sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">
            <i class="fas fa-link" style="color:var(--or-sable);margin-right:8px;"></i>Accès rapides
          </h3>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="mes-reservations.php" style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--blanc);border-radius:12px;text-decoration:none;color:var(--bleu-profond);font-size:0.9rem;font-weight:500;transition:all 0.2s;box-shadow:0 2px 8px var(--ombre);"
               onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
              <i class="fas fa-calendar-check" style="color:var(--bleu-moyen);width:20px;text-align:center;"></i>
              Mes Réservations
              <i class="fas fa-chevron-right" style="margin-left:auto;color:var(--texte-gris);font-size:0.75rem;"></i>
            </a>
            <a href="villas.php" style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--blanc);border-radius:12px;text-decoration:none;color:var(--bleu-profond);font-size:0.9rem;font-weight:500;transition:all 0.2s;box-shadow:0 2px 8px var(--ombre);"
               onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
              <i class="fas fa-home" style="color:var(--bleu-moyen);width:20px;text-align:center;"></i>
              Explorer les villas
              <i class="fas fa-chevron-right" style="margin-left:auto;color:var(--texte-gris);font-size:0.75rem;"></i>
            </a>
            <a href="activites.php" style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--blanc);border-radius:12px;text-decoration:none;color:var(--bleu-profond);font-size:0.9rem;font-weight:500;transition:all 0.2s;box-shadow:0 2px 8px var(--ombre);"
               onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
              <i class="fas fa-compass" style="color:var(--bleu-moyen);width:20px;text-align:center;"></i>
              Explorer les activités
              <i class="fas fa-chevron-right" style="margin-left:auto;color:var(--texte-gris);font-size:0.75rem;"></i>
            </a>
            <a href="logout.php"
               onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')"
               style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:rgba(220,53,69,0.06);border-radius:12px;text-decoration:none;color:#842029;font-size:0.9rem;font-weight:500;transition:all 0.2s;border:1px solid rgba(220,53,69,0.15);"
               onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
              <i class="fas fa-sign-out-alt" style="width:20px;text-align:center;"></i>
              Se déconnecter
              <i class="fas fa-chevron-right" style="margin-left:auto;font-size:0.75rem;"></i>
            </a>
          </div>
        </div>

      </div><!-- /colonne droite -->
    </div><!-- /grid -->
  </div><!-- /max-width -->
</section>

<?php include 'includes/footer.php'; ?>

<script>
function togglePwd(id, btn) {
  const input = document.getElementById(id);
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  btn.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>

<style>
@media (max-width: 800px) {
  section > div > div[style*="grid-template-columns:1fr 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
@media (max-width: 600px) {
  section > div > div:first-child {
    grid-template-columns: 1fr !important;
  }
}
</style>
