

CREATE DATABASE IF NOT EXISTS bngrc_dons CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bngrc_dons;

drop table region;
drop table ville;
drop table type_besoin;
drop table besoin;
drop table don;
drop table don_detail;
drop table dispatch;
drop table achat;
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


INSERT INTO region (nom) VALUES 
('Analamanga'), 
('Vakinankaratra'), 
('Atsinanana'),
('Boeny'),
('Atsimo-Andrefana');

INSERT INTO ville (nom, region_id) VALUES 
('Antananarivo', 1),
('Ambohidratrimo', 1),
('Antsirabe', 2),
('Ambatolampy', 2),
('Toamasina', 3),
('Mahajanga', 4),
('Toliara', 5);

INSERT INTO type_besoin (nom, categorie, prix_unitaire) VALUES
('Riz (kg)', 'nature', 2500.00),
('Huile (litre)', 'nature', 8000.00),
('Sucre (kg)', 'nature', 4000.00),
('Eau (litre)', 'nature', 1000.00),
('Lait en poudre (boîte)', 'nature', 12000.00),
('Tôle (unité)', 'materiau', 35000.00),
('Clou (kg)', 'materiau', 12000.00),
('Bois (unité)', 'materiau', 15000.00),
('Ciment (sac)', 'materiau', 45000.00),
('Bâche (unité)', 'materiau', 25000.00),
('Argent (Ar)', 'argent', 1.00);

INSERT INTO besoin (ville_id, type_besoin_id, quantite, date_saisie) VALUES
(1, 1, 10, '2026-02-01 08:00:00'),
(1, 2, 8, '2026-02-01 09:00:00'),
(3, 1, 30, '2026-02-02 08:00:00'),
(3, 6, 10, '2026-02-02 09:00:00'),
(5, 1, 20, '2026-02-03 08:00:00'),
(5, 2, 12, '2026-02-03 09:00:00'),
(6, 1, 15, '2026-02-04 08:00:00'),
(2, 3, 15, '2026-02-04 09:00:00'),
(4, 6, 6, '2026-02-05 08:00:00'),
(7, 1, 25, '2026-02-05 09:00:00');

INSERT INTO don (donateur, description, date_don) VALUES
('Croix-Rouge Madagascar', 'Don alimentaire d\'urgence', '2026-02-08 10:00:00'),
('UNICEF', 'Matériaux de reconstruction', '2026-02-09 14:00:00'),
('Communauté locale', 'Collecte alimentaire', '2026-02-10 11:00:00'),
('Gouvernement', 'Aide financière d\'urgence', '2026-02-11 09:00:00');

INSERT INTO don_detail (don_id, type_besoin_id, quantite) VALUES
(1, 1, 50),
(1, 2, 15),
(2, 6, 16),
(2, 3, 20),
(3, 1, 10),
(4, 11, 3000000);


-- Achats avec le don en argent (don_detail_id=6, Gouvernement, 3 000 000 Ar)
-- Achat de Riz pour Antananarivo (besoin_id=1): 5 kg à 2500 Ar + 10% frais = 13 750 Ar
-- Achat de Tôle pour Antsirabe (besoin_id=4): 3 unités à 35000 Ar + 10% frais = 115 500 Ar
-- Achat de Tôle pour Ambatolampy (besoin_id=9): 2 unités à 35000 Ar + 10% frais = 77 000 Ar
INSERT INTO achat (besoin_id, don_detail_id, quantite, prix_unitaire, frais_pourcent, montant_total, date_achat) VALUES
(1, 6, 5, 2500.00, 10.00, 13750.00, '2026-02-12 10:00:00'),
(4, 6, 3, 35000.00, 10.00, 115500.00, '2026-02-12 11:00:00'),
(9, 6, 2, 35000.00, 10.00, 77000.00, '2026-02-12 14:00:00');