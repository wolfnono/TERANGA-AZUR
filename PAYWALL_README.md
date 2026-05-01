# Système de Paiement Fictif - Teranga Azur

##  Vue d'ensemble

Le système de paiement fictif de Teranga Azur permet aux utilisateurs de :
- Sélectionner des dates pour une villa ou une activité
- Voir le calcul automatique du prix basé sur la durée du séjour
- Passer par une page de paiement fictif
- Recevoir une confirmation de réservation

##  Installation

### Prérequis
- XAMPP avec PHP 8.2+ et MySQL
- Base de données `teranga_azur_db` créée
- Site accessible sur `http://localhost:8080/Teranga_azur/`

### Étapes d'installation

1. **Accédez à la page d'installation** :
   ```
   http://localhost:8080/Teranga_azur/install-payment-system.php
   ```

2. **Vérifiez le statut des composants** :
   - ✓ Table `reservations` (créée automatiquement si manquante)
   - ✓ Fichier `checkout.php`
   - ✓ Fichier `paiement.php`
   - ✓ Fichier `confirmation.php`
   - ✓ Mises à jour `villas.php` et `activites.php`

3. **La page d'installation créera automatiquement la table `reservations`** si elle n'existe pas.

##  Flux de réservation

### 1. Sélection de la villa/activité
```
http://localhost:8080/Teranga_azur/villas.php
  ↓
Cliquer sur bouton "Réserver" d'une villa
```

### 2. Sélection des dates (checkout.php)
```
GET /checkout.php?type=villa&id=1
GET /checkout.php?type=activite&id=2
```

**Fonctionnalités** :
- Sélection de la date d'arrivée (minimale : aujourd'hui)
- Sélection de la date de départ (minimale : demain)
- Calcul en temps réel du prix basé sur :
  - Pour les villas : nombre de nuits × prix par nuit
  - Pour les activités : nombre de personnes × prix par personne
- Bouton de soumission désactivé tant que les dates ne sont pas valides

**Données stockées en session** :
```php
$_SESSION['checkout'] = [
    'type'          => 'villa|activite',
    'item_id'       => 1,
    'item_nom'      => 'Villa Océane',
    'date_arrivee'  => '2024-05-01',
    'date_depart'   => '2024-05-05',
    'nuits'         => 4,
    'quantite'      => 1,
    'prix_unitaire' => 180000,
    'prix_total'    => 720000,
    'label_prix'    => 'par nuit'
];
```

### 3. Paiement fictif (paiement.php)
```
POST /paiement.php
```

**Formulaire de paiement** :
- Numéro de carte (16 chiffres) - Exemple : `4111111111111111`
- Nom du titulaire
- Date d'expiration (MM/YY) - Exemple : `12/28`
- CVC (3 chiffres) - Exemple : `123`

**Validations** :
- ✓ Numéro de carte : 16 chiffres
- ✓ Titulaire : au moins 3 caractères
- ✓ Expiration : format MM/YY valide
- ✓ CVC : 3 chiffres

**Données sauvegardées en base** :
```sql
INSERT INTO reservations (
    type, item_id, client_id, date_arrivee, date_depart,
    nombre_nuits, nombre_personnes, prix_total, statut
) VALUES (...)
```

### 4. Confirmation (confirmation.php)
```
GET /confirmation.php?id=123
```

**Affichage** :
- Numéro de réservation (formaté : #000123)
- Détails complets de la réservation
- Boutons pour revenir à l'accueil ou voir d'autres offres

##  Structure de la table reservations

```sql
CREATE TABLE reservations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  type VARCHAR(50) NOT NULL,              -- 'villa' ou 'activite'
  item_id INT NOT NULL,                   -- ID de la villa/activité
  client_id INT,                          -- ID du client (NULL si non connecté)
  date_arrivee DATE NOT NULL,            -- YYYY-MM-DD
  date_depart DATE NOT NULL,             -- YYYY-MM-DD
  nombre_nuits INT DEFAULT 0,            -- Pour les villas
  nombre_personnes INT DEFAULT 1,        -- Pour les activités
  prix_total DECIMAL(10,2) NOT NULL,    -- Montant total en XOF
  statut VARCHAR(50) DEFAULT 'en_attente', -- 'confirmée', 'en_attente', etc.
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

##  Test complet du système

### Scénario 1 : Réserver une villa

1. Allez sur https://localhost:8080/Teranga_azur/villas.php
2. Cliquez sur "Réserver" pour une villa
3. Sélectionnez dates : 
   - Arrivée : 2024-05-15
   - Départ : 2024-05-20 (5 nuits)
4. Vérifiez le calcul : si prix/nuit = 180 000 XOF → total = 900 000 XOF
5. Cliquez sur "Continuer vers le paiement"
6. Entrez infos fictives :
   - Numéro : 4111111111111111
   - Titulaire : Jean Dupont
   - Expiration : 12/28
   - CVC : 123
7. Cliquez "Confirmer le paiement"
8. Vérifiez la page de confirmation avec le numéro de réservation

### Scénario 2 : Réserver une activité

1. Allez sur https://localhost:8080/Teranga_azur/activites.php
2. Cliquez sur "Réserver" pour une activité
3. Sélectionnez dates et nombre de personnes
4. Vérifiez le calcul : nombre de personnes × prix/personne
5. Suivez le même processus de paiement

### Scénario 3 : Validation des erreurs

Essayez les cas d'erreur pour vérifier les validations :

**checkout.php** :
- Dates invalides (départ < arrivée)
- Dates dans le passé
- Pas de dates sélectionnées

**paiement.php** :
- Numéro de carte avec moins de 16 chiffres
- Titulaire vide ou trop court
- Expiration au mauvais format
- CVC qui n'est pas 3 chiffres

##  Fichiers du système

### Fichiers créés/modifiés

| Fichier | Type | Description |
|---------|------|-------------|
| `checkout.php` | Créé | Page de sélection des dates |
| `paiement.php` | Créé | Formulaire de paiement fictif |
| `confirmation.php` | Créé | Page de confirmation |
| `install-payment-system.php` | Créé | Page d'installation et test |
| `villas.php` | Modifié | Ajout bouton "Réserver" |
| `activites.php` | Modifié | Mise à jour bouton "Réserver" |

##  Style et UX

Tous les fichiers de paiement utilisent la palette de couleurs du site :
- **Bleu principal** : #0c2a4a
- **Bleu moyen** : #1a508c
- **Bleu clair** : #2d80cc
- **Or** : #d4af5a
- **Police** : Inter (Jost pour les titres)

Les pages sont **responsives** et fonctionnent sur mobile/tablette/desktop.

##  Notes de sécurité

 **Ce système est FICTIF à des fins de démonstration** :

- Les numéros de carte ne sont jamais validés ou stockés
- Aucun appel à une vraie API de paiement
- Les données de paiement ne sont pas chiffrées
- **À NE JAMAIS utiliser en production** sans intégration d'une vraie solution de paiement (Stripe, PayPal, etc.)

Pour une vraie intégration, il faudrait :
- Stocker les données de paiement de manière sécurisée
- Utiliser une API de paiement certifiée
- Implémenter le chiffrement SSL/TLS
- Respecter les normes PCI DSS

##  Dépannage

### La table `reservations` ne se crée pas
→ Vérifiez la connexion MySQL et les permissions de l'utilisateur `root`

### Les réservations ne sont pas sauvegardées
→ Vérifiez les logs d'erreur : `/logs/php_error.log`

### Les boutons "Réserver" ne mènent nulle part
→ Vérifiez que `villas.php` et `activites.php` ont été mis à jour correctement
→ Cherchez les liens `checkout.php?type=...`

### Les dates calculées sont incorrectes
→ Vérifiez le JavaScript dans `checkout.php` fonction `updatePrice()`

##  Support

Pour toute question, vérifiez d'abord la page d'installation :
```
http://localhost:8080/Teranga_azur/install-payment-system.php
```

Elle diagnostiquera automatiquement tout problème.

---

**Version** : 1.0  
**Date** : Avril 2024  
**Système** : Teranga Azur - Fictitious Payment System
