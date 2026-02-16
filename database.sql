-- Base de données Sultana - Boutique de vêtements arabes
-- Création des tables pour la gestion complète du site e-commerce

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS sultana_boutique;
USE sultana_boutique;

-- Table des catégories de produits
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des produits
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    stock INT DEFAULT 0,
    categorie_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id)
);

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(50),
    pays VARCHAR(50),
    role ENUM('client', 'admin') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des commandes
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'confirmee', 'expediee', 'livree', 'annulee') DEFAULT 'en_attente',
    adresse_livraison TEXT,
    mode_paiement VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Table des détails de commande
CREATE TABLE commande_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    total_ligne DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

-- Table du panier (pour les paniers temporaires)
CREATE TABLE panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    produit_id INT,
    quantite INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id),
    UNIQUE KEY unique_panier (utilisateur_id, produit_id)
);

-- Insertion des catégories
INSERT INTO categories (nom, description) VALUES
('Caftan', 'Caftans traditionnels marocains de luxe'),
('Abaya', 'Abayas modernes et élégantes'),
('Djellaba', 'Djellabas traditionnelles et contemporaines'),
('Thobe', 'Thobes traditionnels pour hommes'),
('Accessoires', 'Accessoires et compléments de mode arabe');

-- Insertion des produits
INSERT INTO produits (nom, description, prix, image_url, stock, categorie_id) VALUES
('Caftan Royal', 'Caftan traditionnel marocain en soie avec broderies dorées', 2500.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Caftan+Royal', 10, 1),
('Caftan Princesse', 'Caftan moderne en velours avec pierres précieuses', 3200.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Caftan+Princesse', 8, 1),
('Abaya Élégante', 'Abaya noire élégante avec détails discrets', 1800.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Abaya+Élégante', 15, 2),
('Abaya Moderne', 'Abaya moderne avec coupe contemporaine', 2200.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Abaya+Moderne', 12, 2),
('Djellaba Luxe', 'Djellaba de luxe en laine fine', 1200.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Djellaba+Luxe', 20, 3),
('Djellaba Traditionnelle', 'Djellaba traditionnelle en coton', 800.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Djellaba+Traditionnelle', 25, 3),
('Thobe Traditionnel', 'Thobe blanc traditionnel pour homme', 950.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Thobe+Traditionnel', 30, 4),
('Thobe Moderne', 'Thobe moderne avec coupe ajustée', 1200.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Thobe+Moderne', 18, 4),
('Ceinture Orientale', 'Ceinture en métal doré pour caftan', 150.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Ceinture+Orientale', 50, 5),
('Bijoux Arabes', 'Ensemble de bijoux traditionnels arabes', 350.00, 'https://via.placeholder.com/300x400/1a1a1a/ffd700?text=Bijoux+Arabes', 40, 5);

-- Insertion des utilisateurs (avec mots de passe en clair comme demandé)
INSERT INTO utilisateurs (nom, email, mot_de_passe, telephone, adresse, ville, pays, role) VALUES
('Ahmed Benali', 'ahmed.benali@email.com', 'password123', '+212 6 12 34 56 78', '123 Rue Mohammed V, Casablanca', 'Casablanca', 'Maroc', 'client'),
('Fatima Alami', 'fatima.alami@email.com', 'azerty456', '+212 6 23 45 67 89', '456 Avenue Hassan II, Rabat', 'Rabat', 'Maroc', 'client'),
('Mohammed Karim', 'mohammed.karim@email.com', 'qwerty789', '+212 6 34 56 78 90', '789 Boulevard Moulay Youssef, Marrakech', 'Marrakech', 'Maroc', 'client'),
('Amina Said', 'amina.said@email.com', 'sultan123', '+212 6 45 67 89 01', '321 Rue Agdal, Fès', 'Fès', 'Maroc', 'client'),
('Youssef Mansour', 'youssef.mansour@email.com', 'luxury456', '+212 6 56 78 90 12', '654 Avenue Mohammed V, Tanger', 'Tanger', 'Maroc', 'client'),
('Admin Sultana', 'admin@sultana.com', 'admin123', '+212 6 99 88 77 66', '1 Siège Social, Casablanca', 'Casablanca', 'Maroc', 'admin');

-- Insertion de quelques commandes d'exemple
INSERT INTO commandes (utilisateur_id, total, statut, adresse_livraison, mode_paiement) VALUES
(1, 2500.00, 'livree', '123 Rue Mohammed V, Casablanca', 'Carte bancaire'),
(2, 3000.00, 'expediee', '456 Avenue Hassan II, Rabat', 'PayPal'),
(3, 1750.00, 'confirmee', '789 Boulevard Moulay Youssef, Marrakech', 'Virement bancaire');

-- Insertion des détails de commandes
INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, total_ligne) VALUES
(1, 1, 1, 2500.00, 2500.00),
(2, 2, 1, 3200.00, 3200.00),
(3, 6, 1, 800.00, 800.00),
(3, 9, 1, 150.00, 150.00),
(3, 10, 1, 350.00, 350.00);

-- Insertion de quelques articles dans le panier
INSERT INTO panier (utilisateur_id, produit_id, quantite) VALUES
(1, 3, 1),
(2, 5, 2),
(4, 7, 1);

-- Création des vues pour faciliter les requêtes
CREATE VIEW vue_produits_complets AS
SELECT 
    p.id,
    p.nom,
    p.description,
    p.prix,
    p.image_url,
    p.stock,
    c.nom AS categorie_nom,
    p.created_at
FROM produits p
JOIN categories c ON p.categorie_id = c.id;

CREATE VIEW vue_commandes_completes AS
SELECT 
    co.id,
    co.total,
    co.statut,
    co.adresse_livraison,
    co.mode_paiement,
    co.created_at,
    u.nom AS client_nom,
    u.email AS client_email
FROM commandes co
JOIN utilisateurs u ON co.utilisateur_id = u.id;

-- Création des procédures stockées utiles
DELIMITER //

-- Procédure pour ajouter un produit au panier
CREATE PROCEDURE ajouter_au_panier(
    IN p_utilisateur_id INT,
    IN p_produit_id INT,
    IN p_quantite INT
)
BEGIN
    INSERT INTO panier (utilisateur_id, produit_id, quantite)
    VALUES (p_utilisateur_id, p_produit_id, p_quantite)
    ON DUPLICATE KEY UPDATE quantite = quantite + p_quantite;
END //

-- Procédure pour créer une commande à partir du panier
CREATE PROCEDURE creer_commande_depuis_panier(
    IN p_utilisateur_id INT,
    IN p_adresse_livraison TEXT,
    IN p_mode_paiement VARCHAR(50)
)
BEGIN
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_commande_id INT;
    
    -- Calculer le total
    SELECT SUM(p.prix * pa.quantite) INTO v_total
    FROM panier pa
    JOIN produits p ON pa.produit_id = p.id
    WHERE pa.utilisateur_id = p_utilisateur_id;
    
    -- Créer la commande
    INSERT INTO commandes (utilisateur_id, total, adresse_livraison, mode_paiement)
    VALUES (p_utilisateur_id, v_total, p_adresse_livraison, p_mode_paiement);
    
    SET v_commande_id = LAST_INSERT_ID();
    
    -- Ajouter les détails de commande
    INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, total_ligne)
    SELECT v_commande_id, pa.produit_id, pa.quantite, p.prix, (p.prix * pa.quantite)
    FROM panier pa
    JOIN produits p ON pa.produit_id = p.id
    WHERE pa.utilisateur_id = p_utilisateur_id;
    
    -- Vider le panier
    DELETE FROM panier WHERE utilisateur_id = p_utilisateur_id;
    
    SELECT v_commande_id AS commande_id, v_total AS total;
END //

DELIMITER ;

-- Affichage des données pour vérification
SELECT '=== CATÉGORIES ===' as info;
SELECT * FROM categories;

SELECT '=== PRODUITS ===' as info;
SELECT * FROM vue_produits_complets;

SELECT '=== UTILISATEURS ===' as info;
SELECT id, nom, email, role FROM utilisateurs;

SELECT '=== COMMANDES ===' as info;
SELECT * FROM vue_commandes_completes;

SELECT '=== PANIERS ===' as info;
SELECT * FROM panier;
