-- ============================================================
-- TERANGA AZUR DATABASE — Script complet
-- ============================================================

CREATE DATABASE IF NOT EXISTS teranga_azur_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE teranga_azur_db;

-- ---- CLIENTS ----
CREATE TABLE IF NOT EXISTS clients (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100)  NOT NULL,
    prenom     VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    telephone  VARCHAR(50),
    pays       VARCHAR(100),
    role       ENUM('client','admin') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- VILLAS ----
CREATE TABLE IF NOT EXISTS villas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(255)   NOT NULL,
    description     TEXT,
    prix_par_nuit   DECIMAL(10,2)  NOT NULL,
    capacite_max    INT            NOT NULL,
    chambres        INT            NOT NULL,
    piscine         TINYINT(1)     DEFAULT 0,
    localisation    VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- IMAGES VILLAS ----
CREATE TABLE IF NOT EXISTS images_villas (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    villa_id INT NOT NULL,
    url      VARCHAR(255) NOT NULL,
    ordre    INT DEFAULT 0,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

-- ---- SERVICES OPTIONNELS ----
CREATE TABLE IF NOT EXISTS services_optionnels (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom_service     VARCHAR(150) NOT NULL,
    description     TEXT,
    prix_journalier DECIMAL(10,2) NOT NULL
);

-- ---- RÉSERVATIONS VILLAS ----
CREATE TABLE IF NOT EXISTS reservations_villas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    client_id           INT NOT NULL,
    villa_id            INT NOT NULL,
    date_arrivee        DATE NOT NULL,
    date_depart         DATE NOT NULL,
    nb_voyageurs        INT NOT NULL,
    prix_total_sejour   DECIMAL(12,2) NOT NULL,
    statut              VARCHAR(50) DEFAULT 'En attente',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (villa_id)  REFERENCES villas(id)  ON DELETE CASCADE
);

-- ---- OPTIONS CHOISIES ----
CREATE TABLE IF NOT EXISTS options_choisies (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    reservation_villa_id  INT NOT NULL,
    service_id            INT NOT NULL,
    quantite              INT DEFAULT 1,
    nb_jours              INT DEFAULT 1,
    FOREIGN KEY (reservation_villa_id) REFERENCES reservations_villas(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id)           REFERENCES services_optionnels(id)  ON DELETE CASCADE
);

-- ---- ACTIVITÉS ----
CREATE TABLE IF NOT EXISTS activites (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nom_activite      VARCHAR(255) NOT NULL,
    description       TEXT,
    duree_heures      INT NOT NULL,
    prix_par_personne DECIMAL(10,2) NOT NULL,
    lieu_depart       VARCHAR(255),
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- RÉSERVATIONS ACTIVITÉS ----
CREATE TABLE IF NOT EXISTS reservations_activites (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    client_id           INT NOT NULL,
    activite_id         INT NOT NULL,
    date_activite       DATE NOT NULL,
    nb_participants     INT NOT NULL,
    prix_total_activite DECIMAL(10,2) NOT NULL,
    statut              VARCHAR(50) DEFAULT 'En attente',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id)   REFERENCES clients(id)   ON DELETE CASCADE,
    FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE
);

-- ============================================================
-- DONNÉES DE DÉMONSTRATION
-- ============================================================

-- Villas
INSERT INTO villas (titre, description, prix_par_nuit, capacite_max, chambres, piscine, localisation) VALUES
('Villa Océane Prestige', 'Magnifique villa de luxe face à l\'Atlantique, avec piscine à débordement et vue panoramique sur l\'océan. Décorations africaines contemporaines, salon ouvert sur la terrasse et cuisine entièrement équipée.', 350000, 12, 5, 1, 'Saly Portudal, Mbour'),
('Villa Teranga Royale', 'Grande villa familiale au cœur de la Petite Côte, entourée d\'un jardin tropical luxuriant. Piscine privée, barbecue extérieur et accès direct à la plage de sable blanc.', 280000, 16, 7, 1, 'Ngaparou, Mbour'),
('Villa Sahel Sunset', 'Havre de paix au design épuré, idéal pour les couples et petits groupes. Vue imprenable sur le coucher de soleil atlantique depuis la terrasse en bois massif.', 180000, 6, 3, 0, 'Popenguine'),
('Villa Baobab Lodge', 'Résidence authentique de style africain moderne, nichée dans un jardin de baobabs centenaires. Atmosphère hors du temps, calme absolu.', 220000, 10, 4, 1, 'Toubab Dialaw'),
('Villa Bleu Azur', 'Villa contemporaine aux tons bleus et blancs, à 50 mètres de la plage. Piscine chauffée, salle de sport et service de conciergerie 24h/24.', 420000, 14, 6, 1, 'Saly Portudal, Mbour'),
('Villa Les Casuarinas', 'Charmante villa entourée de casuarinas, parfaite pour un séjour romantique ou en famille. Terrasse couverte avec vue jardin.', 150000, 8, 4, 0, 'Joal-Fadiouth');

-- Garantir la colonne 'ordre' même si la table existait sans elle
ALTER TABLE images_villas ADD COLUMN IF NOT EXISTS ordre INT DEFAULT 0;

-- Images villas
INSERT INTO images_villas (villa_id, url, ordre) VALUES
(1, 'images/villas/villa-1-1.jpg', 1),
(1, 'images/villas/villa-1-2.jpg', 2),
(2, 'images/villas/villa-2-1.jpg', 1),
(3, 'images/villas/villa-3-1.jpg', 1);

-- Services optionnels
INSERT INTO services_optionnels (nom_service, description, prix_journalier) VALUES
('Chef cuisinier privé', 'Un chef expérimenté prépare vos repas dans la villa, avec menus personnalisés.', 50000),
('Transfert aéroport', 'Navette privée depuis/vers l\'aéroport de Dakar. Véhicule climatisé.', 35000),
('Service de ménage quotidien', 'Nettoyage complet de la villa chaque matin par notre équipe.', 15000),
('Location de voiture avec chauffeur', 'Berline climatisée avec chauffeur bilingue pour vos déplacements.', 45000),
('Baby-sitting certifié', 'Garde d\'enfants par une nurse diplômée et expérimentée.', 20000);

-- Activités
INSERT INTO activites (nom_activite, description, duree_heures, prix_par_personne, lieu_depart) VALUES
('Sortie pêche en mer', 'Partez en mer avec nos pêcheurs locaux pour une matinée de pêche traditionnelle au large de Mbour. Prise garantie ou non, l\'expérience est inoubliable !', 4, 25000, 'Port de Mbour'),
('Tour en pirogue sur le Saloum', 'Explorez les mangroves du Delta du Saloum en pirogue traditionnelle. Observation d\'oiseaux rares et découverte des villages de pêcheurs.', 6, 30000, 'Foundiougne'),
('Visite de la Réserve de Bandia', 'Safari dans la réserve naturelle de Bandia : girafes, rhinocéros, buffles, et de nombreuses espèces endémiques vous attendent.', 5, 40000, 'Bandia, Mbour'),
('Coucher de soleil à cheval', 'Balade à cheval sur la plage au coucher du soleil, un moment magique et romantique face à l\'Atlantique.', 2, 20000, 'Saly Portudal'),
('Initiation à la cuisine sénégalaise', 'Apprenez à préparer le thiéboudienne, le yassa et autres plats emblématiques avec une cuisinière locale.', 3, 15000, 'Saly Portudal'),
('Excursion Ile de Gorée', 'Visite de l\'île historique de Gorée, classée au patrimoine mondial de l\'UNESCO. Transfert depuis Dakar inclus.', 8, 45000, 'Dakar'),
('Quad & buggy sur les plages', 'Aventure tout-terrain sur les plages et dunes de la Petite Côte en quad ou buggy.', 2, 35000, 'Saly Portudal'),
('Plongée sous-marine', 'Découvrez les fonds marins de la Petite Côte avec nos moniteurs PADI certifiés. Matériel inclus.', 3, 50000, 'Mbour');

-- Client de démo
INSERT INTO clients (nom, prenom, email, password, telephone, pays, role) VALUES
('Demo', 'Utilisateur', 'demo@teranga-azur.sn', '$2y$10$placeholder_hash', '+221 77 000 00 00', 'Sénégal', 'client');

-- Compte administrateur
-- Mot de passe: Admin1234!  (hash bcrypt généré avec password_hash)
INSERT INTO clients (nom, prenom, email, password, telephone, pays, role) VALUES
('Admin', 'Teranga', 'admin@teranga-azur.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+221 77 000 00 01', 'Sénégal', 'admin');
-- Note: Le hash ci-dessus correspond au mot de passe 'Admin1234!'
-- Pour regénérer: echo password_hash('Admin1234!', PASSWORD_DEFAULT);
