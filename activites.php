<?php
session_start();
require_once 'config/db.php';

$page_title = "Activités";
$page_desc  = "Découvrez nos activités et excursions au Sénégal.";

$search      = isset($_GET['q']) ? trim($_GET['q']) : '';
$prix_max    = isset($_GET['prix_max']) && $_GET['prix_max'] !== '' ? (int)$_GET['prix_max'] : null;
$duree_max   = isset($_GET['duree']) && $_GET['duree'] !== '' ? (int)$_GET['duree'] : null;

$where = ['1=1'];
$params = [];
if ($search) {
  $where[] = '(nom_activite LIKE :q OR lieu_depart LIKE :q2)';
  $params[':q'] = "%$search%";
  $params[':q2'] = "%$search%";
}
if ($prix_max) { $where[] = 'prix_par_personne <= :prix_max'; $params[':prix_max'] = $prix_max; }
if ($duree_max) { $where[] = 'duree_heures <= :duree'; $params[':duree'] = $duree_max; }

$sql = "SELECT a.*, MIN(img.url) AS image_principale
FROM activites a
LEFT JOIN images_activites img ON a.id = img.activite_id
WHERE " . implode(' AND ', $where) . "
GROUP BY a.id
ORDER BY a.prix_par_personne ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activites = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-hero" style="background: var(--gradient-hero), url('images/Piscine1.png') center/cover;">
  <div class="page-hero-content">
    <span class="section-label" style="color:var(--or-sable);">Explorer</span>
    <h1 class="animate-fadeInUp delay-1">Activités & Excursions</h1>
    <p class="animate-fadeInUp delay-2">Des expériences authentiques pour découvrir le meilleur du Sénégal</p>
  </div>
</div>

<div class="filters-bar">
  <form method="GET" action="activites.php" style="display:contents;">
    <div class="filter-group">
      <label class="filter-label">Rechercher</label>
      <input type="text" name="q" class="filter-input" placeholder="Activité, lieu..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="filter-group">
      <label class="filter-label">Prix max / pers. (XOF)</label>
      <select name="prix_max" class="filter-select">
        <option value="">Tous les prix</option>
        <option value="10000" <?= $prix_max == 10000 ? 'selected' : '' ?>>10 000</option>
        <option value="25000" <?= $prix_max == 25000 ? 'selected' : '' ?>>25 000</option>
        <option value="50000" <?= $prix_max == 50000 ? 'selected' : '' ?>>50 000</option>
        <option value="100000" <?= $prix_max == 100000 ? 'selected' : '' ?>>100 000</option>
      </select>
    </div>
    <div class="filter-group">
      <label class="filter-label">Durée max (h)</label>
      <select name="duree" class="filter-select">
        <option value="">Toutes durées</option>
        <option value="2" <?= $duree_max == 2 ? 'selected' : '' ?>>≤ 2h</option>
        <option value="4" <?= $duree_max == 4 ? 'selected' : '' ?>>≤ 4h</option>
        <option value="8" <?= $duree_max == 8 ? 'selected' : '' ?>>≤ 8h</option>
        <option value="24" <?= $duree_max == 24 ? 'selected' : '' ?>>Journée</option>
      </select>
    </div>
    <div style="display:flex;align-items:flex-end;gap:10px;">
      <button type="submit" class="btn btn-primary" style="padding:12px 24px;">
        <i class="fas fa-filter"></i> Filtrer
      </button>
      <a href="activites.php" class="btn btn-outline" style="padding:12px 18px;color:var(--texte-sombre);border-color:#ddd;">
        <i class="fas fa-times"></i>
      </a>
    </div>
  </form>
</div>

<section class="section" style="padding-top:50px;">
  <div style="margin-bottom:28px;">
    <h2 style="color:var(--bleu-profond);font-size:1.4rem;">
      <?= count($activites) ?> activité<?= count($activites) > 1 ? 's' : '' ?> disponible<?= count($activites) > 1 ? 's' : '' ?>
    </h2>
  </div>

  <?php if (empty($activites)): ?>
  <div style="text-align:center;padding:80px 20px;">
    <i class="fas fa-compass" style="font-size:3rem;color:var(--creme-fonce);margin-bottom:20px;display:block;"></i>
    <h3 style="color:var(--texte-gris);">Aucune activité trouvée</h3>
    <a href="activites.php" class="btn btn-primary" style="margin-top:24px;">Voir toutes les activités</a>
  </div>
  <?php else: ?>
  <div class="grid-3">
    <?php foreach ($activites as $activite): ?>
    <div class="card activite-card">
      <a href="activite-detail.php?id=<?= $activite['id'] ?>" style="text-decoration:none;color:inherit;">
        <div class="card-img-wrapper" style="cursor:pointer;">
          <?php $a_img = $activite['image_principale'] ?? 'images/Logo.png'; ?>
          <img src="<?= htmlspecialchars($a_img) ?>"
               onerror="this.src='images/Logo.png'"
               alt="<?= htmlspecialchars($activite['nom_activite']) ?>"
               class="card-img">
          <span class="card-badge">
            <i class="fas fa-clock"></i> <?= $activite['duree_heures'] ?>h
          </span>
        </div>
        <div class="card-body">
          <h3 class="card-title"><?= htmlspecialchars($activite['nom_activite']) ?></h3>
          <p class="card-desc"><?= htmlspecialchars(substr($activite['description'] ?? '', 0, 120)) ?>...</p>
          <div class="card-features">
            <span class="feature-tag"><i class="fas fa-clock"></i> <?= $activite['duree_heures'] ?> heure(s)</span>
            <span class="feature-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($activite['lieu_depart']) ?></span>
          </div>
        </div>
      </a>
      <div style="padding:0 20px 20px;">
        <div style="margin-bottom:12px;">
          <div class="card-price" style="margin:0;margin-bottom:2px;"><?= number_format($activite['prix_par_personne'], 0, ',', ' ') ?> XOF</div>
          <span style="font-weight:300;font-size:0.75rem;color:var(--texte-gris);">/pers.</span>
        </div>
        <div style="display:flex;gap:8px;">
          <a href="activite-detail.php?id=<?= $activite['id'] ?>" class="btn btn-outline" style="flex:1;padding:10px 12px;font-size:0.8rem;justify-content:center;">
            <i class="fas fa-eye"></i> Voir
          </a>
          <a href="checkout.php?type=activite&id=<?= $activite['id'] ?>" class="btn btn-dark" style="flex:1;padding:10px 12px;font-size:0.8rem;justify-content:center;">
            <i class="fas fa-calendar-alt"></i> Réserver
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<section class="section" style="background:var(--creme);padding:80px 5%;">
  <div style="max-width:700px;margin:0 auto;text-align:center;">
    <span class="section-label">Notre promesse</span>
    <h2 class="section-title">Des expériences conçues pour vous</h2>
    <div class="divider-or"></div>
    <p style="color:var(--texte-gris);line-height:1.9;font-size:1rem;margin-bottom:36px;">
      Chaque activité est soigneusement sélectionnée pour vous offrir une découverte authentique du Sénégal : ses paysages, sa culture, sa gastronomie et ses habitants chaleureux.
    </p>
    <div style="display:flex;gap:30px;justify-content:center;flex-wrap:wrap;">
      <div style="text-align:center;">
        <i class="fas fa-shield-alt" style="font-size:1.8rem;color:var(--bleu-moyen);margin-bottom:10px;"></i>
        <p style="font-size:0.88rem;font-weight:500;color:var(--bleu-profond);">Guides certifiés</p>
      </div>
      <div style="text-align:center;">
        <i class="fas fa-users" style="font-size:1.8rem;color:var(--bleu-moyen);margin-bottom:10px;"></i>
        <p style="font-size:0.88rem;font-weight:500;color:var(--bleu-profond);">Petits groupes</p>
      </div>
      <div style="text-align:center;">
        <i class="fas fa-camera" style="font-size:1.8rem;color:var(--bleu-moyen);margin-bottom:10px;"></i>
        <p style="font-size:0.88rem;font-weight:500;color:var(--bleu-profond);">Photos incluses</p>
      </div>
      <div style="text-align:center;">
        <i class="fas fa-leaf" style="font-size:1.8rem;color:var(--bleu-moyen);margin-bottom:10px;"></i>
        <p style="font-size:0.88rem;font-weight:500;color:var(--bleu-profond);">Éco-responsable</p>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
