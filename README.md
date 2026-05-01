#  Teranga Azur - Système de Location de Villas & Activités

Plateforme de réservation de villas de luxe et d'activités touristiques au Sénégal.

##  Table des matières
- Vue d'ensemble
- Installation
- Structure du projet
- Fonctionnalités principales
- Guide d'utilisation

##  Vue d'ensemble

Teranga Azur est une application web permettant :
- **Clients** : Consulter, filtrer et réserver des villas et activités
- **Administrateurs** : Gérer le catalogue avec système de gestion d'images intégré

**Technologies :** PHP 7.4+, MySQL 5.7+, HTML5, CSS3, JavaScript

##  Fonctionnalités principales

###  Gestion des Villas
- Création/édition/suppression de villas
- Upload multiple de photos
- Affichage galerie sur page détail
- Filtres avancés (prix, chambres, piscine, capacité)
- Réservation avec calcul auto du prix

###  Gestion des Activités
- Création/édition/suppression d'activités
- Upload multiple de photos
- Affichage galerie sur page détail
- Filtres par prix et durée
- Réservation avec calcul du prix par personne

###  Système d'images
- Upload simple drag & drop
- Support JPG, PNG, WebP, GIF (5 Mo max)
- Stockage en base de données
- Affichage automatique
- Suppression avec nettoyage fichiers

###  Gestion des clients
- Inscription/connexion sécurisée
- Historique des réservations
- Profil utilisateur
- Admin : Liste clients avec résumé réservations

##  Guide d'utilisation

**Réserver une villa :**
1. Allez à "Nos Villas"
2. Filtrez par vos critères
3. Cliquez "Voir les détails"
4. Consultez la galerie
5. Sélectionnez vos dates et réservez

**Pour les administrateurs :**
1. Allez à Admin → Villas (ou Activités)
2. Créez/Editez un élément
3. Uploadez vos photos
4. Les images s'affichent automatiquement

##  Base de données

Tables principales :
- **villas** - Informations des villas
- **images_villas** - Photos des villas
- **activites** - Informations des activités
- **images_activites** - Photos des activités
- **clients** - Comptes utilisateurs
- **reservations** - Enregistrement des réservations

##  URLs principales

| Page | URL |
|------|-----|
| Accueil | `/` |
| Villas | `/villas.php` |
| Activités | `/activites.php` |
| Admin | `/admin/dashboard.php` |
| Connexion | `/connexion.php` |
| Mes réservations | `/mes-reservations.php` |

---

Pour plus d'informations techniques, consultez **DOCUMENTATION-CODE.md**
