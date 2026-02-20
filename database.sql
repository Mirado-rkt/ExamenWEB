

CREATE DATABASE IF NOT EXISTS bngrc_dons CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bngrc_dons;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS achat;
DROP TABLE IF EXISTS dispatch;
DROP TABLE IF EXISTS don_detail;
DROP TABLE IF EXISTS don;
DROP TABLE IF EXISTS besoin;
DROP TABLE IF EXISTS type_besoin;
DROP TABLE IF EXISTS ville;
DROP TABLE IF EXISTS region;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE region (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB;


CREATE TABLE ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region_id INT NOT NULL,
    FOREIGN KEY (region_id) REFERENCES region(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    categorie ENUM('nature', 'materiau', 'argent') NOT NULL,
    prix_unitaire DECIMAL(15,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_besoin_id INT NOT NULL,
    quantite DECIMAL(15,2) NOT NULL,
    ordre INT NOT NULL DEFAULT 0,
    date_saisie DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE,
    FOREIGN KEY (type_besoin_id) REFERENCES type_besoin(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donateur VARCHAR(200) DEFAULT 'Anonyme',
    description TEXT,
    date_don DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE don_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    type_besoin_id INT NOT NULL,
    quantite DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (don_id) REFERENCES don(id) ON DELETE CASCADE,
    FOREIGN KEY (type_besoin_id) REFERENCES type_besoin(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dispatch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_detail_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite DECIMAL(15,2) NOT NULL,
    date_dispatch DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (don_detail_id) REFERENCES don_detail(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des achats (achat de besoins en nature/matériaux avec les dons en argent)
CREATE TABLE achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    besoin_id INT NOT NULL,
    don_detail_id INT NOT NULL,
    quantite DECIMAL(15,2) NOT NULL,
    prix_unitaire DECIMAL(15,2) NOT NULL,
    frais_pourcent DECIMAL(5,2) NOT NULL DEFAULT 0,
    montant_total DECIMAL(15,2) NOT NULL,
    date_achat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (besoin_id) REFERENCES besoin(id) ON DELETE CASCADE,
    FOREIGN KEY (don_detail_id) REFERENCES don_detail(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Régions de Madagascar
INSERT INTO region (nom) VALUES 
('Atsinanana'),      -- 1: Toamasina
('Vatovavy'),        -- 2: Mananjary
('Atsimo-Atsinanana'), -- 3: Farafangana
('Diana'),           -- 4: Nosy Be
('Menabe');          -- 5: Morondava

-- Villes
INSERT INTO ville (nom, region_id) VALUES 
('Toamasina', 1),    -- id: 1
('Mananjary', 2),    -- id: 2
('Farafangana', 3),  -- id: 3
('Nosy Be', 4),      -- id: 4
('Morondava', 5);    -- id: 5

-- Types de besoins
INSERT INTO type_besoin (nom, categorie, prix_unitaire) VALUES
('Riz (kg)', 'nature', 3000.00),        -- id: 1
('Eau (L)', 'nature', 1000.00),         -- id: 2
('Huile (L)', 'nature', 6000.00),       -- id: 3
('Haricots', 'nature', 4000.00),        -- id: 4
('Tôle', 'materiau', 25000.00),         -- id: 5
('Bâche', 'materiau', 15000.00),        -- id: 6
('Clous (kg)', 'materiau', 8000.00),    -- id: 7
('Bois', 'materiau', 10000.00),         -- id: 8
('groupe', 'materiau', 6750000.00),     -- id: 9
('Argent', 'argent', 1.00);             -- id: 10

-- Besoins des villes avec ordre de priorité pour le dispatch
-- ville_id: 1=Toamasina, 2=Mananjary, 3=Farafangana, 4=Nosy Be, 5=Morondava
-- type_besoin_id: 1=Riz, 2=Eau, 3=Huile, 4=Haricots, 5=Tôle, 6=Bâche, 7=Clous, 8=Bois, 9=groupe, 10=Argent
INSERT INTO besoin (ville_id, type_besoin_id, quantite, ordre, date_saisie) VALUES
-- Toamasina
(1, 1, 800, 17, '2026-02-16 08:00:00'),      -- Riz (kg)
(1, 2, 1500, 4, '2026-02-15 08:00:00'),      -- Eau (L)
(1, 5, 120, 23, '2026-02-16 08:00:00'),      -- Tôle
(1, 6, 200, 1, '2026-02-15 08:00:00'),       -- Bâche
(1, 10, 12000000, 12, '2026-02-16 08:00:00'), -- Argent
(1, 9, 3, 16, '2026-02-15 08:00:00'),        -- groupe
-- Mananjary
(2, 1, 500, 9, '2026-02-15 08:00:00'),       -- Riz (kg)
(2, 3, 120, 25, '2026-02-16 08:00:00'),      -- Huile (L)
(2, 5, 80, 6, '2026-02-15 08:00:00'),        -- Tôle
(2, 7, 60, 19, '2026-02-16 08:00:00'),       -- Clous (kg)
(2, 10, 6000000, 3, '2026-02-15 08:00:00'),  -- Argent
-- Farafangana
(3, 1, 600, 21, '2026-02-16 08:00:00'),      -- Riz (kg)
(3, 2, 1000, 14, '2026-02-15 08:00:00'),     -- Eau (L)
(3, 6, 150, 8, '2026-02-16 08:00:00'),       -- Bâche
(3, 8, 100, 26, '2026-02-15 08:00:00'),      -- Bois
(3, 10, 8000000, 10, '2026-02-16 08:00:00'), -- Argent
-- Nosy Be
(4, 1, 300, 5, '2026-02-15 08:00:00'),       -- Riz (kg)
(4, 4, 200, 18, '2026-02-16 08:00:00'),      -- Haricots
(4, 5, 40, 2, '2026-02-15 08:00:00'),        -- Tôle
(4, 7, 30, 24, '2026-02-16 08:00:00'),       -- Clous (kg)
(4, 10, 4000000, 7, '2026-02-15 08:00:00'),  -- Argent
-- Morondava
(5, 1, 700, 11, '2026-02-16 08:00:00'),      -- Riz (kg)
(5, 2, 1200, 20, '2026-02-15 08:00:00'),     -- Eau (L)
(5, 6, 180, 15, '2026-02-16 08:00:00'),      -- Bâche
(5, 8, 150, 22, '2026-02-15 08:00:00'),      -- Bois
(5, 10, 10000000, 13, '2026-02-16 08:00:00'); -- Argent

-- Dons
INSERT INTO don (donateur, description, date_don) VALUES
('Anonyme', 'Don argent', '2026-02-16 08:00:00'),      -- id: 1
('Anonyme', 'Don argent', '2026-02-16 09:00:00'),      -- id: 2
('Anonyme', 'Don argent', '2026-02-17 08:00:00'),      -- id: 3
('Anonyme', 'Don argent', '2026-02-17 09:00:00'),      -- id: 4
('Anonyme', 'Don argent', '2026-02-17 10:00:00'),      -- id: 5
('Anonyme', 'Don nature', '2026-02-16 10:00:00'),      -- id: 6
('Anonyme', 'Don nature', '2026-02-16 11:00:00'),      -- id: 7
('Anonyme', 'Don materiel', '2026-02-17 11:00:00'),    -- id: 8
('Anonyme', 'Don materiel', '2026-02-17 12:00:00'),    -- id: 9
('Anonyme', 'Don nature', '2026-02-17 13:00:00'),      -- id: 10
('Anonyme', 'Don nature', '2026-02-18 08:00:00'),      -- id: 11
('Anonyme', 'Don materiel', '2026-02-18 09:00:00'),    -- id: 12
('Anonyme', 'Don nature', '2026-02-18 10:00:00'),      -- id: 13
('Anonyme', 'Don argent', '2026-02-19 08:00:00'),      -- id: 14
('Anonyme', 'Don materiel', '2026-02-19 09:00:00'),    -- id: 15
('Anonyme', 'Don nature', '2026-02-17 14:00:00');      -- id: 16

-- Détails des dons
INSERT INTO don_detail (don_id, type_besoin_id, quantite) VALUES
(1, 10, 5000000),    -- 2026-02-16, Argent, 5000000
(2, 10, 3000000),    -- 2026-02-16, Argent, 3000000
(3, 10, 4000000),    -- 2026-02-17, Argent, 4000000
(4, 10, 1500000),    -- 2026-02-17, Argent, 1500000
(5, 10, 6000000),    -- 2026-02-17, Argent, 6000000
(6, 1, 400),         -- 2026-02-16, Riz (kg), 400
(7, 2, 600),         -- 2026-02-16, Eau (L), 600
(8, 5, 50),          -- 2026-02-17, Tôle, 50
(9, 6, 70),          -- 2026-02-17, Bâche, 70
(10, 4, 100),        -- 2026-02-17, Haricots, 100
(11, 1, 2000),       -- 2026-02-18, Riz (kg), 2000
(12, 5, 300),        -- 2026-02-18, Tôle, 300
(13, 2, 5000),       -- 2026-02-18, Eau (L), 5000
(14, 10, 20000000),  -- 2026-02-19, Argent, 20000000
(15, 6, 500),        -- 2026-02-19, Bâche, 500
(16, 4, 88);         -- 2026-02-17, Haricots, 88