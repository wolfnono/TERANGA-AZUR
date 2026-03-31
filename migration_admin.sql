-- ============================================================
-- MIGRATION — Ajout du système de rôles admin
-- À exécuter si la base de données existe déjà
-- ============================================================

USE teranga_azur_db;

-- 1. Ajouter la colonne role (si elle n'existe pas déjà)
ALTER TABLE clients
  ADD COLUMN IF NOT EXISTS role ENUM('client','admin') DEFAULT 'client';

-- 2. Créer le compte administrateur
-- Mot de passe : Admin1234!
INSERT INTO clients (nom, prenom, email, password, telephone, pays, role)
VALUES (
  'Admin',
  'Teranga',
  'admin@teranga-azur.sn',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  '+221 77 000 00 01',
  'Sénégal',
  'admin'
)
ON DUPLICATE KEY UPDATE role = 'admin';

-- Vérification
SELECT id, nom, prenom, email, role FROM clients;
