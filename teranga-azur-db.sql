-- Active: 1761651316714@@127.0.0.1@3306
CREATE DATABASE IF NOT EXISTS teranga_azur_db, 
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(150),
    telephone VARCHAR(50),
    pays VARCHAR(100)
);

CREATE TABLE villas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255),
    description TEXT,
    prix_par_nuit DECIMAL(10,2),
    capacite_max INT,
    chambres INT,
    piscine TINYINT(1),
    localisation VARCHAR(255)
);

CREATE TABLE images_villas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    villa_id INT,
    url VARCHAR(255),
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

CREATE TABLE reservations_villas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    villa_id INT,
    date_arrivee DATE,
    date_depart DATE,
    nb_voyageurs INT,
    prix_total_sejour DECIMAL(12,2),
    statut VARCHAR(50) DEFAULT 'En attente',
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

CREATE TABLE services_optionnels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_service VARCHAR(150),
    description TEXT,
    prix_journalier DECIMAL(10,2)
);

CREATE TABLE options_choisies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_villa_id INT,
    service_id INT,
    quantite INT,
    nb_jours INT,
    FOREIGN KEY (reservation_villa_id) REFERENCES reservations_villas(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services_optionnels(id) ON DELETE CASCADE
);

CREATE TABLE activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_activite VARCHAR(255),
    description TEXT,
    duree_heures INT,
    prix_par_personne DECIMAL(10,2),
    lieu_depart VARCHAR(255)
);


CREATE TABLE reservations_activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    activite_id INT,
    date_activite DATE,
    nb_participants INT,
    prix_total_activite DECIMAL(10,2),
    statut VARCHAR(50) DEFAULT 'En attente',
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE
);