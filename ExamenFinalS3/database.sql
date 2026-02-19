

CREATE DATABASE IF NOT EXISTS bngrc_dons CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bngrc_dons;


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
(1, 1, 500, '2026-02-01 08:00:00'),
(1, 2, 80, '2026-02-01 08:30:00'),
(1, 6, 100, '2026-02-01 09:00:00'),
(1, 11, 5000000, '2026-02-01 09:30:00'),
(2, 1, 300, '2026-02-02 10:00:00'),
(2, 3, 50, '2026-02-02 10:30:00'),
(2, 7, 40, '2026-02-02 11:00:00'),
(3, 1, 200, '2026-02-03 11:00:00'),
(3, 6, 60, '2026-02-03 11:30:00'),
(3, 9, 30, '2026-02-03 12:00:00'),
(4, 1, 150, '2026-02-04 09:00:00'),
(4, 6, 50, '2026-02-04 09:30:00'),
(5, 1, 400, '2026-02-05 08:00:00'),
(5, 2, 60, '2026-02-05 08:30:00'),
(5, 8, 100, '2026-02-05 09:00:00'),
(5, 11, 3000000, '2026-02-05 09:30:00'),
(6, 1, 250, '2026-02-06 10:00:00'),
(6, 4, 500, '2026-02-06 10:30:00'),
(7, 1, 350, '2026-02-07 08:00:00'),
(7, 6, 80, '2026-02-07 08:30:00'),
(7, 11, 2000000, '2026-02-07 09:00:00');

INSERT INTO don (donateur, description, date_don) VALUES
('Croix-Rouge Madagascar', 'Don alimentaire d\'urgence', '2026-02-08 10:00:00'),
('UNICEF', 'Matériaux de reconstruction', '2026-02-09 14:00:00'),
('Fondation TELMA', 'Contribution financière', '2026-02-10 09:00:00'),
('Communauté locale', 'Collecte alimentaire', '2026-02-11 11:00:00');

INSERT INTO don_detail (don_id, type_besoin_id, quantite) VALUES
(1, 1, 800),
(1, 2, 120),
(1, 3, 100),
(2, 6, 200),
(2, 7, 80),
(2, 9, 40),
(3, 11, 8000000),
(4, 1, 500),
(4, 4, 300);
