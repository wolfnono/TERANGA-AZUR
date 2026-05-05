<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db.php';

$page_title = 'Carte Interactive';
$page_desc  = 'Découvrez la position de nos villas et activités sur la Petite Côte du Sénégal.';

$villas = $pdo->query(
    "SELECT id, titre, localisation, prix_par_nuit, chambres, capacite_max, latitude, longitude
     FROM villas WHERE latitude IS NOT NULL AND longitude IS NOT NULL ORDER BY titre"
)->fetchAll();

$activites = $pdo->query(
    "SELECT id, nom_activite, lieu_depart, prix_par_personne, duree_heures, latitude, longitude
     FROM activites WHERE latitude IS NOT NULL AND longitude IS NOT NULL ORDER BY nom_activite"
)->fetchAll();

include 'includes/header.php';
?>

<link rel="stylesheet" href="css/leaflet.css">

<style>
.carte-hero {
  background: linear-gradient(135deg, #1a3a2e 0%, #2d5a4f 100%);
  padding: 64px 5% 44px;
  text-align: center;
  color: #fff;
}
.carte-hero .section-label { margin-bottom: 16px; }
.carte-hero h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(1.9rem,4.5vw,2.9rem);
  font-weight: 300;
  margin: 0 0 12px;
  color: #fff;
}
.carte-hero p {
  font-size: 0.97rem;
  opacity: .8;
  max-width: 540px;
  margin: 0 auto;
  line-height: 1.7;
}

.carte-legende {
  background: #fff;
  border-bottom: 1px solid #e8e2d8;
  padding: 13px 5%;
  display: flex;
  align-items: center;
  gap: 24px;
  flex-wrap: wrap;
}
.legende-item {
  display: flex; align-items: center; gap: 8px;
  font-size: 0.86rem; font-weight: 500; color: #1a3a2e;
}
.legende-dot {
  width: 13px; height: 13px; border-radius: 50%;
  border: 2px solid rgba(0,0,0,.12); flex-shrink: 0;
}
.legende-dot.blue { background: #2563eb; }
.legende-dot.gold { background: #d4af5a; }
.legende-note { margin-left: auto; font-size: 0.75rem; color: #5a6a7a; font-style: italic; }

.carte-wrap {
  display: flex;
  height: 580px;
  overflow: hidden;
  border-bottom: 1px solid #e8e2d8;
}

#map {
  flex: 1 1 auto;
  height: 580px;      /* hauteur fixe explicite */
  min-width: 0;
  z-index: 1;
}

.carte-sidebar {
  width: 310px;
  flex: 0 0 310px;
  height: 580px;
  border-left: 1px solid #e8e2d8;
  background: #fff;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

@media (max-width: 800px) {
  .carte-wrap { flex-direction: column; height: auto; }
  #map { flex: none; height: 360px; width: 100%; }
  .carte-sidebar { width: 100%; flex: none; height: 260px; border-left: none; border-top: 1px solid #e8e2d8; }
}

.csb-tabs {
  display: flex;
  border-bottom: 1px solid #e8e2d8;
  flex-shrink: 0;
}
.csb-tab {
  flex: 1; padding: 12px 6px; text-align: center;
  font-size: 0.81rem; font-weight: 600; cursor: pointer;
  border: none; background: none;
  border-bottom: 3px solid transparent; color: #5a6a7a;
  transition: all .15s;
}
.csb-tab.active { color: #1a3a2e; border-bottom-color: #d4af5a; }
.csb-tab i { margin-right: 4px; }

.csb-list { overflow-y: auto; flex: 1; padding: 8px; display: none; }
.csb-list.active { display: block; }

.csb-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 8px; border-radius: 8px; cursor: pointer;
  transition: background .12s; margin-bottom: 2px;
}
.csb-item:hover { background: #f1f5f9; }
.csb-item.hl    { background: #eff6ff; }

.csb-ico {
  width: 34px; height: 34px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; flex-shrink: 0;
}
.csb-ico.v { background: #dbeafe; color: #2563eb; }
.csb-ico.a { background: #fef3c7; color: #d97706; }

.csb-info { flex: 1; min-width: 0; }
.csb-name {
  font-size: 0.84rem; font-weight: 600; color: #1a3a2e;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.csb-sub { font-size: 0.72rem; color: #5a6a7a; margin-top: 1px; }
.csb-price { font-size: 0.76rem; font-weight: 700; color: #d4af5a; white-space: nowrap; flex-shrink: 0; }

.leaflet-popup-content-wrapper {
  border-radius: 12px !important;
  box-shadow: 0 8px 28px rgba(0,0,0,.16) !important;
  padding: 0 !important; overflow: hidden;
}
.leaflet-popup-content { margin: 0 !important; min-width: 210px; }
.pp-head { padding: 11px 13px 9px; border-bottom: 1px solid #f1f5f9; }
.pp-type { font-size: 0.62rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 3px; }
.pp-type.v { color: #2563eb; }
.pp-type.a { color: #d97706; }
.pp-name { font-size: 0.9rem; font-weight: 700; color: #1a3a2e; line-height: 1.3; }
.pp-body { padding: 9px 13px 11px; }
.pp-meta { font-size: 0.76rem; color: #5a6a7a; display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
.pp-meta i { color: #94a3b8; width: 13px; }
.pp-prix { font-size: 0.92rem; font-weight: 700; color: #d97706; margin-top: 6px; }
.pp-btn {
  display: block; margin-top: 10px; background: #1a3a2e; color: #fff;
  text-align: center; text-decoration: none; padding: 7px 12px;
  border-radius: 7px; font-size: 0.78rem; font-weight: 600;
}
.pp-btn:hover { background: #2d5a4f; }
</style>

<section class="carte-hero">
  <div class="section-label"><i class="fas fa-map-marked-alt"></i>&nbsp; Petite Côte, Sénégal</div>
  <h1>Carte Interactive</h1>
  <p>Retrouvez toutes nos villas et activités. Cliquez sur une épingle pour en savoir plus.</p>
</section>

<div class="carte-legende">
  <div class="legende-item"><div class="legende-dot blue"></div> Villas (<?= count($villas) ?>)</div>
  <div class="legende-item"><div class="legende-dot gold"></div> Activités (<?= count($activites) ?>)</div>
  <div class="legende-note"><i class="fas fa-info-circle"></i>&nbsp; Coordonnées fictives — projet académique</div>
</div>

<div class="carte-wrap">
  <div id="map"></div>

  <aside class="carte-sidebar">
    <div class="csb-tabs">
      <button class="csb-tab active" onclick="switchTab('v',this)"><i class="fas fa-home"></i> Villas</button>
      <button class="csb-tab"        onclick="switchTab('a',this)"><i class="fas fa-compass"></i> Activités</button>
    </div>

    <div class="csb-list active" id="list-v">
      <?php foreach ($villas as $v): ?>
      <div class="csb-item" onclick="zoomTo('v',<?= $v['id'] ?>)" id="li-v-<?= $v['id'] ?>">
        <div class="csb-ico v"><i class="fas fa-home"></i></div>
        <div class="csb-info">
          <div class="csb-name"><?= htmlspecialchars($v['titre']) ?></div>
          <div class="csb-sub"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($v['localisation']) ?></div>
        </div>
        <div class="csb-price"><?= number_format($v['prix_par_nuit'],0,',',' ') ?> XOF</div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($villas)): ?>
        <p style="padding:20px;text-align:center;color:#5a6a7a;font-size:.84rem;">Aucune villa géolocalisée.</p>
      <?php endif; ?>
    </div>

    <div class="csb-list" id="list-a">
      <?php foreach ($activites as $a): ?>
      <div class="csb-item" onclick="zoomTo('a',<?= $a['id'] ?>)" id="li-a-<?= $a['id'] ?>">
        <div class="csb-ico a"><i class="fas fa-compass"></i></div>
        <div class="csb-info">
          <div class="csb-name"><?= htmlspecialchars($a['nom_activite']) ?></div>
          <div class="csb-sub"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($a['lieu_depart']) ?></div>
        </div>
        <div class="csb-price"><?= number_format($a['prix_par_personne'],0,',',' ') ?> XOF</div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($activites)): ?>
        <p style="padding:20px;text-align:center;color:#5a6a7a;font-size:.84rem;">Aucune activité géolocalisée.</p>
      <?php endif; ?>
    </div>
  </aside>
</div>

<script src="js/leaflet.js"></script>
<script>
const VD = <?= json_encode(array_map(fn($v)=>[
  'id'=>(int)$v['id'],'titre'=>$v['titre'],'loc'=>$v['localisation'],
  'prix'=>(int)$v['prix_par_nuit'],'ch'=>(int)$v['chambres'],'cap'=>(int)$v['capacite_max'],
  'lat'=>(float)$v['latitude'],'lng'=>(float)$v['longitude'],
],$villas),JSON_UNESCAPED_UNICODE) ?>;

const AD = <?= json_encode(array_map(fn($a)=>[
  'id'=>(int)$a['id'],'nom'=>$a['nom_activite'],'dep'=>$a['lieu_depart'],
  'prix'=>(int)$a['prix_par_personne'],'dur'=>(float)$a['duree_heures'],
  'lat'=>(float)$a['latitude'],'lng'=>(float)$a['longitude'],
],$activites),JSON_UNESCAPED_UNICODE) ?>;

const map = L.map('map',{center:[14.45,-17.0],zoom:10});

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{
  maxZoom:19,
  attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

function svgIcon(color,letter){
  return L.divIcon({
    className:'',
    iconSize:[34,44], iconAnchor:[17,44], popupAnchor:[0,-46],
    html:`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34 44" width="34" height="44">
      <path d="M17 0C7.6 0 0 7.6 0 17c0 12.7 17 27 17 27S34 29.7 34 17C34 7.6 26.4 0 17 0z"
            fill="${color}" stroke="rgba(0,0,0,.18)" stroke-width="1.2"/>
      <circle cx="17" cy="17" r="9.5" fill="rgba(255,255,255,.93)"/>
      <text x="17" y="22" text-anchor="middle" font-size="11" font-weight="700"
            font-family="Arial,sans-serif" fill="${color}">${letter}</text>
    </svg>`
  });
}

const iV = svgIcon('#2563eb','V');
const iA = svgIcon('#d97706','A');
const MK = {v:{},a:{}};

VD.forEach(v=>{
  const pop=`<div class="pp-head"><div class="pp-type v">Villa</div><div class="pp-name">${v.titre}</div></div>
    <div class="pp-body">
      <div class="pp-meta"><i class="fas fa-map-marker-alt"></i>${v.loc}</div>
      <div class="pp-meta"><i class="fas fa-bed"></i>${v.ch} chambre${v.ch>1?'s':''} · ${v.cap} pers. max</div>
      <div class="pp-prix">${v.prix.toLocaleString('fr-FR')} XOF <span style="font-size:.7rem;font-weight:400;color:#94a3b8">/ nuit</span></div>
      <a href="villa-detail.php?id=${v.id}" class="pp-btn">Voir la villa →</a>
    </div>`;
  const m=L.marker([v.lat,v.lng],{icon:iV}).addTo(map).bindPopup(pop,{maxWidth:250});
  m.on('click',()=>hlItem('v',v.id));
  MK.v[v.id]=m;
});

AD.forEach(a=>{
  const pop=`<div class="pp-head"><div class="pp-type a">Activité</div><div class="pp-name">${a.nom}</div></div>
    <div class="pp-body">
      <div class="pp-meta"><i class="fas fa-map-marker-alt"></i>${a.dep}</div>
      <div class="pp-meta"><i class="fas fa-clock"></i>${a.dur}h de durée</div>
      <div class="pp-prix">${a.prix.toLocaleString('fr-FR')} XOF <span style="font-size:.7rem;font-weight:400;color:#94a3b8">/ pers.</span></div>
      <a href="activites.php" class="pp-btn">Voir les activités →</a>
    </div>`;
  const m=L.marker([a.lat,a.lng],{icon:iA}).addTo(map).bindPopup(pop,{maxWidth:250});
  m.on('click',()=>hlItem('a',a.id));
  MK.a[a.id]=m;
});

function zoomTo(t,id){
  const m=MK[t][id]; if(!m) return;
  map.flyTo(m.getLatLng(),14,{duration:0.7});
  setTimeout(()=>m.openPopup(),750);
  hlItem(t,id);
}

function hlItem(t,id){
  document.querySelectorAll('.csb-item.hl').forEach(e=>e.classList.remove('hl'));
  const el=document.getElementById(`li-${t}-${id}`);
  if(!el) return;
  el.classList.add('hl');
  el.scrollIntoView({block:'nearest',behavior:'smooth'});
  const listEl=el.closest('.csb-list');
  if(listEl && !listEl.classList.contains('active')){
    const tabId=listEl.id==='list-v'?'v':'a';
    const btn=document.querySelector(`.csb-tab[onclick*="'${tabId}'"]`);
    if(btn) switchTab(tabId,btn);
  }
}

function switchTab(id,btn){
  document.querySelectorAll('.csb-list').forEach(e=>e.classList.remove('active'));
  document.querySelectorAll('.csb-tab').forEach(e=>e.classList.remove('active'));
  document.getElementById(`list-${id}`).classList.add('active');
  btn.classList.add('active');
}

const pts=[...VD.map(v=>[v.lat,v.lng]),...AD.map(a=>[a.lat,a.lng])];
if(pts.length>1){
  map.fitBounds(L.latLngBounds(pts).pad(0.15),{maxZoom:13});
} else if(pts.length===1){
  map.setView(pts[0],13);
}

setTimeout(()=>map.invalidateSize(),300);
</script>

<?php include 'includes/footer.php'; ?>
