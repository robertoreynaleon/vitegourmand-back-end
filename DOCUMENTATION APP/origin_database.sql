-- Script SQL pour la création et l'initialisation de la base de données Vite Gourmand
CREATE DATABASE IF NOT EXISTS vitegourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE vitegourmand;

-- Tables et données pour Vite Gourmand

-- Table de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    CONSTRAINT uq_role_name UNIQUE(name)
) ENGINE=InnoDB;

-- Insertion des rôles
INSERT INTO roles (id, name, description) VALUES
(1, 'admin', 'Administrateur système'),
(2, 'staff_member', 'Membre de Vite&Gourmand'),
(3, 'client', 'Client commandant des menus');

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address VARCHAR(100),
    city VARCHAR(50),
    postal_code VARCHAR(10),
    role_id INT NOT NULL DEFAULT 3,
    CONSTRAINT pk_user PRIMARY KEY(id),
    CONSTRAINT uq_email UNIQUE(email),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table des régimes alimentaires
CREATE TABLE regimes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL,
    CONSTRAINT uq_regime_label UNIQUE(label)
) ENGINE=InnoDB;

-- Insertion des régimes
INSERT INTO regimes (id, label) VALUES
(1, 'Classique'),
(2, 'Végétarien'),
(3, 'Vegan'),
(4, 'Sans gluten');

-- Table des allergènes
CREATE TABLE allergens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(100) NOT NULL,
    CONSTRAINT uq_allergen_label UNIQUE(label)
) ENGINE=InnoDB;

-- Insertion des allergènes courants
INSERT INTO allergens (label) VALUES
('Gluten'),
('Crustacés'),
('Oeufs'),
('Poisson'),
('Arachides'),
('Soja'),
('Lait'),
('Fruits à coque'),
('Céleri'),
('Moutarde'),
('Sésame'),
('Sulfites'),
('Lupin'),
('Mollusques'),
('Noix de cajou'),
('Gingembre');

-- Table des plats
CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    CONSTRAINT uq_dish_title UNIQUE(title)
) ENGINE=InnoDB;

-- Table des menus
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    regime_id INT NOT NULL,
    price_per_person DECIMAL(6,2) NOT NULL,
    min_people INT NOT NULL DEFAULT 6,
    remaining_quantity INT DEFAULT NULL,
    advance_order_days INT NOT NULL DEFAULT 2,
    CONSTRAINT uq_menu_title UNIQUE(title),
    FOREIGN KEY (regime_id) REFERENCES regimes(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table de liaison entre plats et allergènes (plusieurs allergènes par plat)
CREATE TABLE dish_allergens (
    dish_id INT NOT NULL,
    allergen_id INT NOT NULL,
    PRIMARY KEY (dish_id, allergen_id),
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergens(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table de liaison entre menus et plats (un menu contient plusieurs plats)
CREATE TABLE menu_dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    dish_id INT NOT NULL,
    dish_type VARCHAR(255) NOT NULL,
    CONSTRAINT unique_menu_dish UNIQUE (menu_id, dish_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table des images de menus
CREATE TABLE menu_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Table des commandes
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    delivery_date DATE NOT NULL,
    delivery_time TIME NOT NULL,
    delivery_address LONGTEXT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    equipment_loan BOOLEAN DEFAULT FALSE,
    equipment_returned BOOLEAN DEFAULT FALSE,
    status VARCHAR(255) NOT NULL DEFAULT 'en attente',
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_person DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id)
) ENGINE=InnoDB;

-- Table des tokens de réinitialisation de mot de passe
-- Ajoutée pendant le développement pour la fonctionnalité "mot de passe oublié"
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT uq_reset_token UNIQUE (token),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Insertion des plats
-- ENTRÉES
INSERT INTO dishes (title) VALUES ('Tartare de bœuf aux échalotes confites');

INSERT INTO dishes (title) VALUES ('Douzaine d''huîtres du Bassin d''Arcachon');

INSERT INTO dishes (title) VALUES ('Velouté de potimarron aux châtaignes');

INSERT INTO dishes (title) VALUES ('Gaspacho de tomates anciennes, basilic');

INSERT INTO dishes (title) VALUES ('Foie gras mi-cuit maison, chutney de figues');

INSERT INTO dishes (title) VALUES ('Asperges blanches des Landes, vinaigrette à l''orange');

INSERT INTO dishes (title) VALUES ('Salade landaise (gésiers, magret fumé, pignons)');


-- PLATS PRINCIPAUX
INSERT INTO dishes (title) VALUES ('Entrecôte bordelaise sauce aux cèpes');

INSERT INTO dishes (title) VALUES ('Pavé de bar de ligne, risotto crémeux au safran');

INSERT INTO dishes (title) VALUES ('Gratin de légumes rôtis, polenta crémeuse au parmesan');

INSERT INTO dishes (title) VALUES ('Buddha bowl quinoa, falafels, houmous de betterave');

INSERT INTO dishes (title) VALUES ('Chapon farci aux marrons, jus au vin de Pomerol');

INSERT INTO dishes (title) VALUES ('Gigot d''agneau rôti aux herbes, tian de légumes');

INSERT INTO dishes (title) VALUES ('Brochettes de poulet mariné, taboulé aux herbes fraîches');

INSERT INTO dishes (title) VALUES ('Magret de canard au miel, écrasé de pommes de terre');

INSERT INTO dishes (title) VALUES ('Blanquette de veau à l''ancienne, riz pilaf');


-- DESSERTS
INSERT INTO dishes (title) VALUES ('Cannelés de Bordeaux');

INSERT INTO dishes (title) VALUES ('Tarte fine aux poires et amandes');

INSERT INTO dishes (title) VALUES ('Fondant au chocolat, cœur coulant');

INSERT INTO dishes (title) VALUES ('Mousse au chocolat à l''aquafaba');

INSERT INTO dishes (title) VALUES ('Bûche pâtissière praliné-noisette');


-- -- Insertion des menus
-- 1. MENU CLASSIQUE BORDELAIS
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Classique Bordelais', 'Saveurs authentiques du terroir bordelais. Viande à conserver entre 0-4°C. Service sous 2h après livraison.', 1, 35.00, 8, 50, 3);

-- 2. MENU OCÉAN D'ARCACHON
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Océan d''Arcachon', 'Produits frais de la marée. Livraison le jour-même obligatoire. Conservation 4h max. Saison : septembre à avril.', 1, 42.00, 10, 30, 4);

-- 3. MENU VÉGÉTARIEN GOURMAND
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Végétarien Gourmand', 'Légumes de saison. Réchauffage four 15min à 180°C. Conservation 48h réfrigéré.', 2, 28.00, 6, 60, 2);

-- 4. MENU VEGAN NATURE
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Vegan Nature', 'Fraîcheur végétale. À consommer dans les 24h. Conservation 4°C. Idéal printemps/été.', 3, 26.00, 6, 40, 3);

-- 5. MENU FÊTES DE FIN D'ANNÉE - NOËL
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Fêtes du réveillon', 'Menu festif exceptionnel. Commande avant le 10 décembre impératif. Livraison 24-25 déc. Réchauffage plat principal 45min four.', 1, 58.00, 12, 20, 15);

-- 6. MENU PÂQUES PRINTANIER
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Pâques Printanier', 'Célébration du printemps. Livraison weekend de Pâques. Gigot à réchauffer 20min. Saison : mars-avril.', 1, 38.00, 8, 35, 7);

-- 7. MENU ÉTÉ TERRASSE
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Été Terrasse', 'Idéal pour événements extérieurs. Plats servis froids/tièdes. Conservation 4°C. Saison : juin à septembre.', 1, 32.00, 10, 45, 2);

-- 8. MENU VENDANGES - AUTOMNE
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Vendanges - Automne', 'Menu festif pour célébrations vigneronnes. Produits du terroir. Saison : septembre-octobre.', 1, 45.00, 15, 25, 10);

-- 9. MENU PETIT BUDGET - BISTROT
INSERT INTO menus (title, description, regime_id, price_per_person, min_people, remaining_quantity, advance_order_days)
VALUES ('Petit Budget - Bistrot', 'Rapport qualité/prix exceptionnel. Idéal événements associatifs/professionnels. Livraison standard.', 1, 22.00, 6, 80, 2);


-- Insertion des plats dans les menus
-- MENU 1 : Classique Bordelais (Tartare, Entrecôte, Cannelés)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (1, 1, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (1, 8, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (1, 17, 'dessert');

-- MENU 2 : Océan d'Arcachon (Huîtres, Bar risotto, Tarte poires)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (2, 2, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (2, 9, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (2, 18, 'dessert');

-- MENU 3 : Végétarien Gourmand (Velouté, Gratin légumes, Fondant chocolat)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (3, 3, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (3, 10, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (3, 19, 'dessert');

-- MENU 4 : Vegan Nature (Gaspacho, Buddha bowl, Mousse chocolat)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (4, 4, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (4, 11, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (4, 20, 'dessert');

-- MENU 5 : Noël (Foie gras, Chapon, Bûche)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (5, 5, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (5, 12, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (5, 21, 'dessert');

-- MENU 6 : Pâques (Asperges, Gigot, Fondant chocolat)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (6, 6, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (6, 13, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (6, 19, 'dessert');

-- MENU 7 : Été Terrasse (Salade landaise, Brochettes poulet, Tarte poires)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (7, 7, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (7, 14, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (7, 18, 'dessert');

-- MENU 8 : Vendanges (Tartare, Magret canard, Cannelés)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (8, 1, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (8, 15, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (8, 17, 'dessert');

-- MENU 9 : Bistrot (Velouté, Blanquette veau, Fondant chocolat)
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (9, 3, 'entrée');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (9, 16, 'plat_principal');
INSERT INTO menu_dishes (menu_id, dish_id, dish_type) VALUES (9, 19, 'dessert');


-- Insertion des allergènes pour chaque plat
-- Plat 1 : Tartare de bœuf (Moutarde, Œufs, Gluten)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (1, 10);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (1, 3);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (1, 1);

-- Plat 2 : Huîtres (Mollusques, Crustacés)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (2, 14);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (2, 2);

-- Plat 3 : Velouté potimarron (Fruits à coque, Lait)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (3, 8);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (3, 7);

-- Plat 4 : Gaspacho (Céleri)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (4, 9);

-- Plat 5 : Foie gras (Sulfites, Fruits à coque)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (5, 12);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (5, 8);

-- Plat 6 : Asperges (Moutarde, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (6, 10);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (6, 12);

-- Plat 7 : Salade landaise (Fruits à coque, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (7, 8);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (7, 12);

-- Plat 8 : Entrecôte bordelaise (Lait, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (8, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (8, 12);

-- Plat 9 : Bar risotto (Poisson, Lait, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (9, 4);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (9, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (9, 12);

-- Plat 10 : Gratin légumes (Lait, Céleri)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (10, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (10, 9);

-- Plat 11 : Buddha bowl (Sésame, Gluten)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (11, 11);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (11, 1);

-- Plat 12 : Chapon (Gluten, Œufs, Fruits à coque, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (12, 1);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (12, 3);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (12, 8);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (12, 12);

-- Plat 13 : Gigot agneau (Aucun allergène)
-- Pas d'insertion pour ce plat

-- Plat 14 : Brochettes poulet (Gluten, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (14, 1);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (14, 12);

-- Plat 15 : Magret canard (Lait)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (15, 7);

-- Plat 16 : Blanquette veau (Lait, Céleri, Gluten)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (16, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (16, 9);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (16, 1);

-- Plat 17 : Cannelés (Lait, Œufs, Gluten)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (17, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (17, 3);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (17, 1);

-- Plat 18 : Tarte poires (Fruits à coque, Gluten, Lait, Œufs)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (18, 8);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (18, 1);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (18, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (18, 3);

-- Plat 19 : Fondant chocolat (Lait, Œufs, Gluten, Soja)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (19, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (19, 3);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (19, 1);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (19, 6);

-- Plat 20 : Mousse chocolat aquafaba (Soja, Sulfites)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (20, 6);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (20, 12);

-- Plat 21 : Bûche (Lait, Œufs, Gluten, Fruits à coque)
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (21, 7);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (21, 3);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (21, 1);
INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (21, 8);
