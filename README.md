# 🌊 TERANGA AZUR — Guide d'installation

**À toi ton toit de rêve**

---

## Structure du projet

```
teranga-azur/
├── config/
│   ├── db.php                   # Connexion PDO MySQL
│   └── teranga_azur_db.sql      # Script SQL complet avec données démo
├── css/
│   ├── style.css                # Styles globaux (navbar, footer, boutons...)
│   ├── home.css                 # Styles page d'accueil
│   ├── villas.css               # Styles liste villas
│   └── villa-detail.css         # Styles page villa détail
├── images/                      # ← AJOUTER VOS IMAGES ICI
│   ├── logo.png                 # Logo Teranga Azur (déjà fourni)
│   ├── hero-main.jpg            # Image hero accueil
│   ├── hero-villas.jpg          # Image hero page villas
│   ├── hero-activites.jpg       # Image hero page activités
│   ├── cta-bg.jpg               # Image section CTA
│   ├── villa-placeholder.jpg    # Image par défaut villas
│   ├── activite-placeholder.jpg # Image par défaut activités
│   ├── villas/
│   │   ├── villa-1-1.jpg        # Photos villa ID 1 (vue 1)
│   │   ├── villa-1-2.jpg        # Photos villa ID 1 (vue 2)
│   │   └── ...
│   └── activites/
│       ├── activite-1.jpg       # Photo activité ID 1
│       └── ...
├── includes/
│   ├── header.php               # Navigation commune
│   └── footer.php               # Footer commun
├── js/
│   └── main.js                  # JavaScript global
├── index.php                    # Page d'accueil
├── villas.php                   # Liste des villas (avec filtres)
├── villa-detail.php             # Détail d'une villa + réservation
├── activites.php                # Liste des activités (avec filtres)
├── activite-detail.php          # Détail d'une activité + réservation
├── mes-reservations.php         # Espace client : mes réservations
├── inscription.php              # Formulaire d'inscription
├── connexion.php                # Formulaire de connexion
└── logout.php                   # Déconnexion
```

---

## Installation

### 1. Prérequis
- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Serveur web (Apache/Nginx) ou WAMP/XAMPP/Laragon

### 2. Base de données
```sql
-- Dans phpMyAdmin ou MySQL CLI :
SOURCE /chemin/vers/config/teranga_azur_db.sql;
```

### 3. Configuration DB
Éditer `config/db.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'teranga_azur_db');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
```

### 4. Images
Copier le logo dans `images/logo.png`, puis ajouter :
- `images/hero-main.jpg` — Grande photo de villa/plage (1920×1080 min)
- `images/hero-villas.jpg` — Vue de villa avec piscine
- `images/hero-activites.jpg` — Photo d'activité (plage, excursion...)
- `images/cta-bg.jpg` — Photo d'ambiance coucher de soleil
- `images/villas/villa-{ID}-{NUM}.jpg` — Photos de chaque villa
- `images/activites/activite-{ID}.jpg` — Photos de chaque activité

### 5. Authentification (Production)
Dans `inscription.php`, remplacer la ligne commentée par :
```php
$ins->execute([$nom, $prenom, $email, $hash, $telephone, $pays]);
```
Et dans `connexion.php` :
```php
if ($client && password_verify($password, $client['password'])) {
```

---

## Pages disponibles

| Page | URL | Description |
|------|-----|-------------|
| Accueil | `/index.php` | Hero, villas vedettes, activités, stats |
| Nos Villas | `/villas.php` | Liste filtrée de toutes les villas |
| Villa Détail | `/villa-detail.php?id=X` | Galerie, caractéristiques, réservation |
| Activités | `/activites.php` | Liste filtrée de toutes les activités |
| Activité Détail | `/activite-detail.php?id=X` | Description, réservation |
| Mes Réservations | `/mes-reservations.php` | Espace client connecté |
| S'inscrire | `/inscription.php` | Création de compte |
| Se connecter | `/connexion.php` | Connexion |

---

## Palette de couleurs (thème logo)

| Variable | Valeur | Usage |
|----------|--------|-------|
| `--bleu-profond` | `#1a3a5c` | Fond navbar, titres |
| `--bleu-moyen` | `#2a5f8f` | Icônes, liens actifs |
| `--or-sable` | `#c9a96e` | Accents, prix, tagline |
| `--creme` | `#f5f0e8` | Fonds de sections |

---

*Teranga Azur — © 2025*
