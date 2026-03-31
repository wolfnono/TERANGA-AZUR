<?php
session_start();
require_once 'config/db.php';

$page_title = "Accueil";
$page_desc  = "Teranga Azur — Location de villas de luxe au Sénégal. Séjours d'exception face à l'océan.";
$extra_css  = "home.css";

$stmt = $pdo->query("SELECT * FROM villas ORDER BY prix_par_nuit DESC LIMIT 3");
$villas_vedettes = $stmt->fetchAll();

$stmt2 = $pdo->query("SELECT * FROM activites ORDER BY prix_par_personne ASC LIMIT 3");
$activites_vedettes = $stmt2->fetchAll();

include 'includes/header.php';
?>


<section class="hero" id="hero">
  <div class="hero-bg">
    <div class="hero-overlay"></div>
    <img src="images/VillaA.png" alt="Villa Teranga Azur" class="hero-img" id="heroImg">
  </div>

  <div class="hero-content">
    <div class="hero-badge animate-fadeInUp delay-1">
      <i class="fas fa-star"></i> Villas d'exception au Sénégal
    </div>
    <h1 class="hero-title animate-fadeInUp delay-2">
      À toi ton<br><em>toit de rêve</em>
    </h1>
    <p class="hero-subtitle animate-fadeInUp delay-3">
      Des villas luxueuses face à l'Atlantique, une hospitalité africaine authentique,<br>
      des souvenirs gravés pour l'éternité.
    </p>
    <div class="hero-actions animate-fadeInUp delay-4">
      <a href="villas.php" class="btn btn-primary">
        <i class="fas fa-home"></i> Découvrir les villas
      </a>
      <a href="activites.php" class="btn btn-outline">
        <i class="fas fa-compass"></i> Nos activités
      </a>
    </div>
  </div>

   <div class="hero-search animate-fadeInUp delay-4">
    <form action="villas.php" method="GET" class="search-form">
      <div class="search-field">
        <label><i class="fas fa-calendar-alt"></i> Arrivée</label>
        <input type="date" name="date_arrivee" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="search-divider"></div>
      <div class="search-field">
        <label><i class="fas fa-calendar-alt"></i> Départ</label>
        <input type="date" name="date_depart" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="search-divider"></div>
      <div class="search-field">
        <label><i class="fas fa-users"></i> Voyageurs</label>
        <select name="voyageurs">
          <option value="">Combien ?</option>
          <?php for ($i = 1; $i <= 20; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?> personne<?= $i > 1 ? 's' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary search-btn">
        <i class="fas fa-search"></i> Rechercher
      </button>
    </form>
  </div>

  <div class="hero-scroll">
    <span>Découvrir</span>
    <div class="scroll-line"></div>
  </div>
</section>

 <section class="stats-section">
  <div class="stats-grid">
    <div class="stat-item">
      <span class="stat-number" data-target="<?= count($villas_vedettes) ?>+">0</span>
      <span class="stat-label">Villas de luxe</span>
    </div>
    <div class="stat-item">
      <span class="stat-number" data-target="500">0</span>
      <span class="stat-label">Clients satisfaits</span>
    </div>
    <div class="stat-item">
      <span class="stat-number" data-target="15">0</span>
      <span class="stat-label">Activités disponibles</span>
    </div>
    <div class="stat-item">
      <span class="stat-number" data-target="5">0</span>
      <span class="stat-label">Années d'expérience</span>
    </div>
  </div>
</section>

 <section class="section" id="villas-vedettes">
  <div class="section-header">
    <span class="section-label">Nos Hébergements</span>
    <h2 class="section-title">Villas d'exception</h2>
    <div class="divider-or"></div>
    <p class="section-subtitle">
      Chaque villa a été sélectionnée pour son caractère unique, son confort absolu et sa vue imprenable sur l'océan.
    </p>
  </div>

  <div class="grid-3">
    <?php foreach ($villas_vedettes as $villa): ?>
    <div class="card villa-card">
      <div class="card-img-wrapper">
        <?php $v_imgs = [1=>'VillaA.png', 2=>'VillaB.png', 3=>'VillaC.png']; $v_img = $v_imgs[$villa['id']] ?? 'Vue-Balcon.villa2.png'; ?>
        <img src="images/<?= $v_img ?>"
             onerror="this.src='images/Logo.png'"
             alt="<?= htmlspecialchars($villa['titre']) ?>"
             class="card-img">
        <?php if ($villa['piscine']): ?>
        <span class="card-badge"><i class="fas fa-swimming-pool"></i> Piscine</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="card-price"><?= number_format($villa['prix_par_nuit'], 0, ',', ' ') ?> XOF <span style="font-weight:300;font-size:0.85rem;color:var(--texte-gris)">/ nuit</span></div>
        <h3 class="card-title"><?= htmlspecialchars($villa['titre']) ?></h3>
        <p class="card-desc"><?= htmlspecialchars(substr($villa['description'] ?? '', 0, 120)) ?>...</p>
        <div class="card-features">
          <span class="feature-tag"><i class="fas fa-users"></i> <?= $villa['capacite_max'] ?> pers.</span>
          <span class="feature-tag"><i class="fas fa-door-open"></i> <?= $villa['chambres'] ?> ch.</span>
          <span class="feature-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($villa['localisation']) ?></span>
        </div>
        <a href="villa-detail.php?id=<?= $villa['id'] ?>" class="btn btn-dark" style="width:100%;justify-content:center;">
          Voir la villa <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="text-align:center;margin-top:50px;">
    <a href="villas.php" class="btn btn-primary">
      Voir toutes nos villas <i class="fas fa-arrow-right"></i>
    </a>
  </div>
</section>

 <section class="section why-section" style="background:var(--creme);">
  <div class="section-header">
    <span class="section-label">Notre Engagement</span>
    <h2 class="section-title">Pourquoi choisir Teranga Azur ?</h2>
    <div class="divider-or"></div>
  </div>

  <div class="why-grid">
    <div class="why-item">
      <div class="why-icon"><i class="fas fa-gem"></i></div>
      <h3>Luxe & Authenticité</h3>
      <p>Des villas d'exception qui allient confort moderne et charme africain pour une expérience unique.</p>
    </div>
    <div class="why-item">
      <div class="why-icon"><i class="fas fa-shield-alt"></i></div>
      <h3>Réservation Sécurisée</h3>
      <p>Votre tranquillité d'esprit est notre priorité. Réservez en toute confiance avec nos garanties.</p>
    </div>
    <div class="why-item">
      <div class="why-icon"><i class="fas fa-concierge-bell"></i></div>
      <h3>Services Personnalisés</h3>
      <p>Chef cuisinier, transferts, activités... Nous créons votre séjour sur mesure.</p>
    </div>
    <div class="why-item">
      <div class="why-icon"><i class="fas fa-heart"></i></div>
      <h3>La Teranga</h3>
      <p>L'hospitalité sénégalaise dans toute sa splendeur. Chaque client est traité comme un roi.</p>
    </div>
  </div>
</section>

 <section class="section" id="activites-vedettes">
  <div class="section-header">
    <span class="section-label">Explorez</span>
    <h2 class="section-title">Activités inoubliables</h2>
    <div class="divider-or"></div>
    <p class="section-subtitle">Enrichissez votre séjour avec nos expériences uniques, conçues pour vous faire découvrir le Sénégal sous son meilleur jour.</p>
  </div>

  <div class="grid-3">
    <?php foreach ($activites_vedettes as $activite): ?>
    <div class="card activite-card">
      <div class="card-img-wrapper">
        <?php $a_imgs = [1=>'Piscine1.png', 2=>'Piscine2.png']; $a_img = $a_imgs[$activite['id']] ?? 'Salon.png'; ?>
        <img src="images/<?= $a_img ?>"
             onerror="this.src='images/Logo.png'"
             alt="<?= htmlspecialchars($activite['nom_activite']) ?>"
             class="card-img">
        <span class="card-badge"><?= $activite['duree_heures'] ?>h</span>
      </div>
      <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($activite['nom_activite']) ?></h3>
        <p class="card-desc"><?= htmlspecialchars(substr($activite['description'] ?? '', 0, 110)) ?>...</p>
        <div class="card-features">
          <span class="feature-tag"><i class="fas fa-clock"></i> <?= $activite['duree_heures'] ?> heure(s)</span>
          <span class="feature-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($activite['lieu_depart']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
          <span class="card-price"><?= number_format($activite['prix_par_personne'], 0, ',', ' ') ?> XOF<span style="font-weight:300;font-size:0.8rem;color:var(--texte-gris)"> /pers.</span></span>
          <a href="activite-detail.php?id=<?= $activite['id'] ?>" class="btn btn-dark" style="padding:10px 20px;">Réserver</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="text-align:center;margin-top:50px;">
    <a href="activites.php" class="btn btn-primary">
      Toutes les activités <i class="fas fa-arrow-right"></i>
    </a>
  </div>
</section>

 <section class="cta-section">
  <div class="cta-overlay"></div>
  <img src="images/Terasse.png" alt="" class="cta-bg">
  <div class="cta-content">
    <span class="section-label" style="color:var(--or-sable);">Votre séjour de rêve vous attend</span>
    <h2 style="color:var(--blanc);font-size:clamp(2rem,5vw,3.5rem);margin:16px 0;">
      Prêt à vivre l'expérience<br><em>Teranga Azur ?</em>
    </h2>
    <p style="color:rgba(255,255,255,0.8);margin-bottom:36px;font-size:1.05rem;">
      Inscrivez-vous et réservez votre villa dès aujourd'hui.
    </p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
      <a href="villas.php" class="btn btn-primary">Réserver une villa</a>
      <a href="inscription.php" class="btn btn-outline">Créer un compte</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
