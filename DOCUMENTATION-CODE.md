#  Documentation Technique - Teranga Azur

Guide détaillé du code PHP, des fonctions utilisées et du fonctionnement des cartes.

---

##  Architecture PHP

### Structure générale des fichiers

Chaque page PHP suit ce schéma :

```php
<?php
session_start();                    // Démarre la session utilisateur
require_once 'config/db.php';      // Connexion base de données

// TRAITEMENT DES DONNÉES
// - Récupération des paramètres
// - Requêtes à la base de données
// - Validations

include 'includes/header.php';     // En-tête HTML
?>

<!-- HTML & AFFICHAGE -->

<?php include 'includes/footer.php'; ?>
```

### La connexion PDO (config/db.php)

```php
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
```

**PDO = PHP Data Objects**
- Interface sécurisée pour accéder à la BDD
- Protège contre les injections SQL (prepared statements)

---

##  Les cartes (Cards) - Fonctionnement complet

### Qu'est-ce qu'une carte?

Une "carte" (card) est un conteneur qui affiche un produit avec :
-  Image
-  Titre et description  
-  Caractéristiques (prix, durée, lieu)
-  Boutons d'action (Voir, Réserver)

### Code HTML d'une carte activité

```html
<div class="card activite-card">
  <a href="activite-detail.php?id=<?= $activite['id'] ?>">
    <div class="card-img-wrapper">
      <img src="<?= htmlspecialchars($a_img) ?>" alt="...">
      <span class="card-badge">
        <i class="fas fa-clock"></i> <?= $activite['duree_heures'] ?>h
      </span>
    </div>
    <div class="card-body">
      <h3><?= htmlspecialchars($activite['nom_activite']) ?></h3>
      <p><?= htmlspecialchars(substr($activite['description'], 0, 120)) ?>...</p>
      <div class="card-features">
        <span><i class="fas fa-clock"></i> <?= $activite['duree_heures'] ?>h</span>
        <span><i class="fas fa-map"></i> <?= htmlspecialchars($activite['lieu_depart']) ?></span>
      </div>
    </div>
  </a>
  <div style="padding: 0 20px 20px;">
    <div style="margin-bottom: 12px;">
      <div class="card-price"><?= number_format($activite['prix_par_personne'], 0, ',', ' ') ?> XOF</div>
      <span>/pers.</span>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="activite-detail.php?id=<?= $activite['id'] ?>" class="btn btn-outline">
        <i class="fas fa-eye"></i> Voir
      </a>
      <a href="checkout.php?type=activite&id=<?= $activite['id'] ?>" class="btn btn-dark">
        <i class="fas fa-calendar-alt"></i> Réserver
      </a>
    </div>
  </div>
</div>
```

### Ligne par ligne - Explication

**Boucle PHP - Afficher plusieurs cartes**
```php
<?php foreach ($activites as $activite): ?>
  <!-- Code de la carte - répété pour chaque activité -->
<?php endforeach; ?>
```
- `$activites` = tableau de toutes les activités
- `$activite` = une seule activité dans la boucle
- Le code s'exécute autant de fois qu'il y a d'activités

**Affichage du titre - htmlspecialchars()**
```php
<h3><?= htmlspecialchars($activite['nom_activite']) ?></h3>
```
- `<?= ... ?>` = raccourci pour `<?php echo ... ?>`
- `htmlspecialchars()` = convertit les caractères spéciaux
  - `<` devient `&lt;`
  - Cela empêche l'injection de code malveillant

**Formatage du prix - number_format()**
```php
<?= number_format($activite['prix_par_personne'], 0, ',', ' ') ?> XOF
```
- Syntaxe : `number_format(valeur, décimales, séparateur_décimal, séparateur_milliers)`
- Exemple : `20001` devient `"20 001"`
- Rend le prix plus lisible

**Liens dynamiques**
```php
<a href="activite-detail.php?id=<?= $activite['id'] ?>">
```
- Crée un lien avec l'ID en paramètre GET
- `activite-detail.php?id=5` ouvre le détail de l'activité avec ID=5

**Images avec fallback**
```php
<?php $a_img = $activite['image_principale'] ?? 'images/Logo.png'; ?>
<img src="<?= htmlspecialchars($a_img) ?>" onerror="this.src='images/Logo.png'">
```
- `??` = opérateur nullsafe : si gauche est null, utilise la droite
- `onerror` = si l'image ne charge pas, affiche le logo de secours

---

##  Système d'images

### Comment les images s'affichent

**Étape 1 : Récupération en BDD (activites.php)**

```php
$sql = "SELECT a.*, MIN(img.url) AS image_principale
        FROM activites a
        LEFT JOIN images_activites img ON a.id = img.activite_id
        WHERE ... 
        GROUP BY a.id";
```

**Décomposition :**
- `SELECT a.*` = tous les champs de la table activites
- `MIN(img.url) AS image_principale` = première image (MIN par ordre croissant)
- `LEFT JOIN images_activites img ON a.id = img.activite_id` = jointure
  - `LEFT JOIN` = inclut aussi les activités sans images
  - `ON a.id = img.activite_id` = condition de jointure
- `GROUP BY a.id` = groupe par activité

**Résultat :** chaque activité a un champ `image_principale` avec l'URL de la première photo

**Étape 2 : Affichage**
```php
<?php $a_img = $activite['image_principale'] ?? 'images/Logo.png'; ?>
<img src="<?= htmlspecialchars($a_img) ?>" onerror="this.src='images/Logo.png'">
```

---

##  Les requêtes SQL

### SELECT simple
```php
$stmt = $pdo->prepare("SELECT * FROM activites WHERE id = ?");
$stmt->execute([$activite_id]);
$activite = $stmt->fetch();
```
- `?` = placeholder sécurisé
- `fetch()` = une ligne
- `fetchAll()` = toutes les lignes

### SELECT avec filtres
```php
$where = ['1=1'];
$params = [];
if ($search) {
    $where[] = '(nom_activite LIKE :q OR lieu_depart LIKE :q2)';
    $params[':q'] = "%$search%";
    $params[':q2'] = "%$search%";
}
$sql = "SELECT * FROM activites WHERE " . implode(' AND ', $where);
```
- `LIKE '%texte%'` = contient "texte"
- `implode(' AND ', $where)` = joint les conditions avec AND

### SELECT avec JOIN
```php
$sql = "SELECT a.*, MIN(img.url) AS image_principale
        FROM activites a
        LEFT JOIN images_activites img ON a.id = img.activite_id
        GROUP BY a.id";
```
- `JOIN` = combine deux tables
- `LEFT JOIN` = inclut les lignes sans correspondance
- `GROUP BY` = regroupe les résultats

### INSERT
```php
$stmt = $pdo->prepare("INSERT INTO reservations (type, item_id, client_id) VALUES (?, ?, ?)");
$stmt->execute([$type, $item_id, $client_id]);
$reservation_id = $pdo->lastInsertId();
```
- `lastInsertId()` = l'ID auto-généré de la ligne insérée

### UPDATE
```php
$stmt = $pdo->prepare("UPDATE activites SET nom_activite=?, description=? WHERE id=?");
$stmt->execute([$nom, $desc, $id]);
```

### DELETE
```php
$stmt = $pdo->prepare("DELETE FROM images_activites WHERE id=?");
$stmt->execute([$img_id]);
```

---

##  Fonctions PHP essentielles

### Gestion des sessions
```php
session_start();                      // Démarre la session
$_SESSION['client_id'] = $id;         // Enregistre une variable
isset($_SESSION['client_id'])         // Vérifie si connecté
```

### Gestion des données
```php
isset($var)                           // Variable existe?
empty($var)                           // Variable est vide?
trim($text)                           // Supprime espaces avant/après
htmlspecialchars($text)               // Échappe HTML (sécurité)
number_format(20001, 0, ',', ' ')    // Formate nombre → "20 001"
substr($text, 0, 120)                // Prend 120 caractères
```

### Redirection
```php
header('Location: index.php');        // Redirige
exit;                                 // Arrête le script
```

### Boucles
```php
foreach ($activites as $activite) {   // Pour chaque élément
    echo $activite['nom'];
}

for ($i = 1; $i <= 5; $i++) {        // Boucle 5 fois
    echo $i;
}
```

### Upload d'images
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    foreach ($_FILES['photos']['tmp_name'] as $k => $tmp) {
        $mime = mime_content_type($tmp);        // Type du fichier
        
        if (in_array($mime, $ALLOWED)) {        // Vérifier type autorisé
            $ext = ['image/jpeg' => 'jpg'][$ mime];
            $name = 'activite_' . uniqid() . '.' . $ext;
            
            move_uploaded_file($tmp, UPLOAD_DIR . $name);  // Déplacer fichier
            $pdo->prepare("INSERT INTO images_activites (activite_id, url) VALUES (?,?)")
                ->execute([$activite_id, UPLOAD_URL . $name]);  // Enregistrer en BDD
        }
    }
}
```

---

##  Flux complet de réservation

**1. Listing des activités (activites.php)**
- Récupère les activités avec images : `SELECT ... LEFT JOIN`
- Affiche les cartes
- Lien `activite-detail.php?id=X`

**2. Page détail (activite-detail.php)**
- Récupère l'activité : `SELECT WHERE id=?`
- Récupère les images : `SELECT FROM images_activites WHERE activite_id=?`
- Affiche la galerie complète

**3. Panier (checkout.php)**
- Enregistre en session : `$_SESSION['checkout'] = [...dates, prix...]`
- Affiche résumé avant paiement

**4. Paiement (paiement.php)**
- Récupère les infos de `$_SESSION['checkout']`
- Valide le formulaire fictif
- `INSERT INTO reservations` avec infos

**5. Confirmation (confirmation.php)**
- Récupère la réservation : `SELECT FROM reservations WHERE id=?`
- Affiche le numéro de réservation

---

##  Sécurité

** Prepared Statements :**
```php
$stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
$stmt->execute([$_POST['email']]);
```

** Échappement HTML :**
```php
<h1><?= htmlspecialchars($activite['nom']) ?></h1>
```

** Validations :**
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email invalide";
}
```

---

##  Résumé des concepts

| Concept | Utilité |
|---------|---------|
| **PDO** | Interface sécurisée à la BDD |
| **Prepared Statements** | Prévient les injections SQL |
| **htmlspecialchars()** | Prévient le XSS |
| **LEFT JOIN** | Affiche données même sans images |
| **GROUP BY** | Regroupe par entité |
| **session_start()** | Mémorise l'utilisateur |
| **foreach** | Boucle sur les données |
| **number_format()** | Formate les nombres |
| **uniqid()** | Génère ID unique |
| **move_uploaded_file()** | Enregistre fichier |

---

Consultez **README.md** pour une vue d'ensemble du projet.
