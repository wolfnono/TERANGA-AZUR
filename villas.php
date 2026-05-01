<?php
session_start();
require_once 'config/db.php';

$page_title = "Nos Villas";
$page_desc  = "Découvrez notre sélection de villas de luxe au Sénégal.";
$extra_css  = "villas.css";

$filtre_piscine   = isset($_GET['piscine']) ? (int)$_GET['piscine'] : null;
$filtre_chambres  = isset($_GET['chambres']) ? (int)$_GET['chambres'] : null;
$filtre_prix_max  = isset($_GET['prix_max']) && $_GET['prix_max'] !== '' ? (int)$_GET['prix_max'] : null;
$filtre_voyageurs = isset($_GET['voyageurs']) && $_GET['voyageurs'] !== '' ? (int)$_GET['voyageurs'] : null;
$search           = isset($_GET['q']) ? trim($_GET['q']) : '';

$where = ['1=1'];
$params = [];

if ($filtre_piscine === 1) { $where[] = 'piscine = 1'; }
if ($filtre_chambres) { $where[] = 'chambres >= :chambres'; $params[':chambres'] = $filtre_chambres; }
if ($filtre_prix_max) { $where[] = 'prix_par_nuit <= :prix_max'; $params[':prix_max'] = $filtre_prix_max; }
if ($filtre_voyageurs) { $where[] = 'capacite_max >= :voyageurs'; $params[':voyageurs'] = $filtre_voyageurs; }
if ($search) { $where[] = '(titre LIKE :q OR localisation LIKE :q2)'; $params[':q'] = "%$search%"; $params[':q2'] = "%$search%"; }

$sql = "SELECT v.*, MIN(img.url) AS image_principale
FROM villas v
LEFT JOIN images_villas img ON v.id = img.villa_id
WHERE " . implode(' AND ', $where) . "
GROUP BY v.id
ORDER BY v.prix_par_nuit DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$villas = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero" style="background: var(--gradient-hero), url('images/Piscine-Villa2.png') center/cover;">
  <div class="page-hero-content">
    <span class="section-label" style="color:var(--or-sable);">Hébergements</span>
    <h1 class="animate-fadeInUp delay-1">Nos Villas d'Exception</h1>
    <p class="animate-fadeInUp delay-2">Des résidences de luxe face à l'Atlantique, choisies pour leur caractère unique</p>
  </div>
</div>

<!-- Filtres -->
<div class="filters-bar">
  <form method="GET" action="villas.php" style="display:contents;">
    <div class="filter-group">
      <label class="filter-label">Rechercher</label>
      <input type="text" name="q" class="filter-input" placeholder="Lieu, nom..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="filter-group">
      <label class="filter-label">Voyageurs min.</label>
      <select name="voyageurs" class="filter-select">
        <option value="">Tous</option>
        <?php for ($i = 2; $i <= 20; $i += 2): ?>
        <option value="<?= $i ?>" <?= $filtre_voyageurs == $i ? 'selected' : '' ?>><?= $i ?>+</option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="filter-group">
      <label class="filter-label">Chambres min.</label>
      <select name="chambres" class="filter-select">
        <option value="">Toutes</option>
        <?php for ($i = 1; $i <= 8; $i++): ?>
        <option value="<?= $i ?>" <?= $filtre_chambres == $i ? 'selected' : '' ?>><?= $i ?>+</option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="filter-group">
      <label class="filter-label">Prix max/nuit (XOF)</label>
      <select name="prix_max" class="filter-select">
        <option value="">Sans limite</option>
        <option value="100000" <?= $filtre_prix_max == 100000 ? 'selected' : '' ?>>100 000</option>
        <option value="250000" <?= $filtre_prix_max == 250000 ? 'selected' : '' ?>>250 000</option>
        <option value="500000" <?= $filtre_prix_max == 500000 ? 'selected' : '' ?>>500 000</option>
        <option value="1000000" <?= $filtre_prix_max == 1000000 ? 'selected' : '' ?>>1 000 000</option>
      </select>
    </div>
    <div class="filter-group">
      <label class="filter-label">Piscine</label>
      <select name="piscine" class="filter-select">
        <option value="">Peu importe</option>
        <option value="1" <?= $filtre_piscine === 1 ? 'selected' : '' ?>>Avec piscine</option>
      </select>
    </div>
    <div style="display:flex;align-items:flex-end;gap:10px;">
      <button type="submit" class="btn btn-primary" style="padding:12px 24px;white-space:nowrap;">
        <i class="fas fa-filter"></i> Filtrer
      </button>
      <a href="villas.php" class="btn btn-outline" style="padding:12px 18px;color:var(--texte-sombre);border-color:#ddd;">
        <i class="fas fa-times"></i>
      </a>
    </div>
  </form>
</div>

<!-- Résultats -->
<section class="section" style="padding-top:50px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:36px;flex-wrap:wrap;gap:12px;">
    <h2 style="color:var(--bleu-profond);font-size:1.4rem;">
      <?= count($villas) ?> villa<?= count($villas) > 1 ? 's' : '' ?> disponible<?= count($villas) > 1 ? 's' : '' ?>
    </h2>
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:0.82rem;color:var(--texte-gris);">Trier par :</span>
      <select class="filter-select" style="min-width:160px;" id="sortSelect">
        <option value="prix_desc">Prix (croissant)</option>
        <option value="prix_asc">Prix (décroissant)</option>
        <option value="capacite">Capacité</option>
      </select>
    </div>
  </div>

  <?php if (empty($villas)): ?>
  <div style="text-align:center;padding:80px 20px;">
    <i class="fas fa-search" style="font-size:3rem;color:var(--creme-fonce);margin-bottom:20px;display:block;"></i>
    <h3 style="color:var(--texte-gris);margin-bottom:12px;">Aucune villa trouvée</h3>
    <p style="color:var(--texte-gris);">Essayez d'ajuster vos filtres.</p>
    <a href="villas.php" class="btn btn-primary" style="margin-top:24px;">Voir toutes les villas</a>
  </div>
  <?php else: ?>
  <div class="villas-grid" id="villasGrid">
    <?php foreach ($villas as $villa): ?>
    <div class="card villa-card">
      <div class="card-img-wrapper">
        <?php $v_img = $villa['image_principale'] ?? 'images/logo.png'; ?>
        <img src="<?= htmlspecialchars($v_img) ?>"
             onerror="this.src='images/logo.png'"
             alt="<?= htmlspecialchars($villa['titre']) ?>"
             class="card-img">
        <?php if ($villa['piscine']): ?>
        <span class="card-badge"><i class="fas fa-swimming-pool"></i> Piscine</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="card-price"><?= number_format($villa['prix_par_nuit'], 0, ',', ' ') ?> XOF <span style="font-weight:300;font-size:0.85rem;color:var(--texte-gris)">/nuit</span></div>
        <h3 class="card-title"><?= htmlspecialchars($villa['titre']) ?></h3>
        <p class="card-desc"><?= htmlspecialchars(substr($villa['description'] ?? 'Description non disponible.', 0, 120)) ?>...</p>
        <div class="card-features">
          <span class="feature-tag"><i class="fas fa-users"></i> <?= $villa['capacite_max'] ?> pers.</span>
          <span class="feature-tag"><i class="fas fa-door-open"></i> <?= $villa['chambres'] ?> chambres</span>
          <span class="feature-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($villa['localisation']) ?></span>
        </div>
        <div style="display:flex;gap:10px;">
          <a href="villa-detail.php?id=<?= $villa['id'] ?>" class="btn btn-outline" style="flex:1;justify-content:center;">
            Voir les détails <i class="fas fa-arrow-right"></i>
          </a>
          <a href="checkout.php?type=villa&id=<?= $villa['id'] ?>" class="btn btn-dark" style="flex:1;justify-content:center;">
            <i class="fas fa-calendar-alt"></i> Réserver
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
