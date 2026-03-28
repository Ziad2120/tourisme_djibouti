-- ============================================================
--  TOURISME LOCAL DJIBOUTI — Base de données
--  Créez cette base dans phpMyAdmin ou via MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS tourisme_djibouti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tourisme_djibouti;

-- ---- SITES TOURISTIQUES ----
CREATE TABLE sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    categorie ENUM('naturel','culturel','historique','gastronomique') NOT NULL,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    adresse VARCHAR(200),
    photo VARCHAR(255),
    patrimoine BOOLEAN DEFAULT FALSE,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----- Ajout de la colonne image_url si nécessaire (MySQL 5.x compatible) -----
SET @exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE table_schema = DATABASE()
    AND table_name = 'sites'
    AND column_name = 'image_url'
);

SET @sql = IF(
  @exists = 0,
  'ALTER TABLE sites ADD COLUMN image_url VARCHAR(500) DEFAULT NULL',
  'SELECT "column exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---- FICHES HISTORIQUES ----
CREATE TABLE fiches_historiques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    titre VARCHAR(200),
    contenu TEXT,
    langue ENUM('fr','ar','en','so') DEFAULT 'fr',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- ---- AUDIO GUIDES ----
CREATE TABLE audio_guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    titre VARCHAR(200),
    fichier_url VARCHAR(255),
    duree_sec INT,
    langue ENUM('fr','ar','en','so') DEFAULT 'fr',
    auteur VARCHAR(150),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- ---- REALITE AUGMENTEE ----
CREATE TABLE realite_augmentee (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    type_overlay ENUM('reconstitution','annotation','faune','panorama') NOT NULL,
    description TEXT,
    modele_url VARCHAR(255),
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- ---- PARCOURS ----
CREATE TABLE parcours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    theme ENUM('nature','culture','histoire','aventure','gastronomie') NOT NULL,
    difficulte ENUM('facile','moyen','difficile') DEFAULT 'facile',
    duree_estimee INT COMMENT 'En minutes',
    distance_km DECIMAL(5,2),
    photo VARCHAR(255),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- PARCOURS <-> SITES (table pivot) ----
CREATE TABLE parcours_sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parcours_id INT NOT NULL,
    site_id INT NOT NULL,
    ordre INT DEFAULT 1,
    FOREIGN KEY (parcours_id) REFERENCES parcours(id) ON DELETE CASCADE,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- ---- TOURISTES ----
CREATE TABLE touristes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    pays VARCHAR(100),
    langue ENUM('fr','ar','en','so') DEFAULT 'fr',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- AVIS ----
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    touriste_id INT,
    site_id INT NOT NULL,
    nom_visiteur VARCHAR(100),
    note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_avis TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valide BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (touriste_id) REFERENCES touristes(id) ON DELETE SET NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- ---- RÉSERVATIONS DE GUIDES TOURISTIQUES ----
CREATE TABLE IF NOT EXISTS reservations_guides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    nom_visiteur VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    nombre_personnes INT DEFAULT 1,
    date_visite DATE NOT NULL,
    heure_visite TIME,
    langue_guide ENUM('fr','ar','en','so') DEFAULT 'fr',
    type_guide ENUM('standard','premium','groupe') DEFAULT 'standard',
    commentaires TEXT,
    statut ENUM('en_attente','confirmee','realisee','annulee') DEFAULT 'en_attente',
    date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- ============================================================
--  DONNÉES DE TEST
-- ============================================================

INSERT INTO sites (nom, description, categorie, latitude, longitude, adresse, photo, patrimoine) VALUES
('Lac Assal', 'Le point le plus bas d\'Afrique (-155m) et le lac le plus salé du monde. Ses cristaux de sel forment un paysage lunaire saisissant, entouré de volcans noirs.', 'naturel', 11.6553, 42.4150, 'Région Tadjourah', 'lac_assal.jpg', TRUE),
('Lac Abbé', 'Paysage apocalyptique de cheminées calcaires baignées d\'une brume sulfureuse, habitat de flamants roses. Décor du film "La Planète des Singes".', 'naturel', 11.1500, 41.7833, 'Région Dikhil', 'lac_abbe.jpg', TRUE),
('Forêt du Day', 'Dernière forêt de genévriers de Djibouti, refuge du francolin de Djibouti, oiseau endémique. Altitude de 1500m, air frais et sentiers ombragés.', 'naturel', 11.7500, 42.8167, 'Massif du Goda', 'foret_day.jpg', FALSE),
('Mosquée Hamoudi', 'La plus ancienne mosquée de Djibouti-Ville (1906), construite par des artisans yéménites. Architecture ottomane avec minarets caractéristiques.', 'culturel', 11.5886, 43.1450, 'Djibouti-Ville, quartier européen', 'mosquee_hamoudi.jpg', TRUE),
('Marché central', 'Cœur vivant de Djibouti-Ville. Épices, tissus somalis, qat, encens et artisanat afar. Véritable immersion dans la culture locale.', 'gastronomique', 11.5900, 43.1444, 'Djibouti-Ville, centre', 'marche_central.jpg', FALSE),
('Arta Beach', 'Plage de sable blanc face au golfe de Tadjourah. Eaux cristallines idéales pour la plongée avec requins-baleines (novembre–février).', 'naturel', 11.5167, 43.0000, 'Région Arta', 'arta_beach.jpg', FALSE),
('Tadjourah', 'La ville blanche, plus ancienne ville de Djibouti. Médina historique, mosquées centenaires et marché traditionnel afar sur les rives du Golfe.', 'historique', 11.7878, 42.8803, 'Région Tadjourah', 'tadjourah.jpg', TRUE),
('Iles Moucha', 'Archipel de corail à 30 minutes de Djibouti-Ville. Snorkeling exceptionnel, tortues marines, raies mantas et dauphins.', 'naturel', 11.6833, 43.2167, 'Golfe de Tadjourah', 'moucha.jpg', FALSE);

INSERT INTO fiches_historiques (site_id, titre, contenu, langue) VALUES
(1, 'Géologie du Lac Assal', 'Le Lac Assal se situe dans la zone de rift afar, l\'une des rares régions au monde où trois plaques tectoniques s\'écartent simultanément. Formé il y a environ 1 million d\'années, il se trouve à 155 mètres sous le niveau de la mer, faisant de lui le point le plus bas du continent africain. Sa salinité atteint 34,8%, dix fois plus salée que l\'océan. Les gisements de sel exploités depuis des siècles ont été une source majeure de commerce caravanier dans toute la Corne de l\'Afrique.', 'fr'),
(2, 'Les Cheminées du Lac Abbé', 'Les cheminées calcaires du Lac Abbé sont des formations géothermiques uniques au monde, résultat de l\'activité volcanique intense de la région. Certaines atteignent 50 mètres de hauteur. Le lac lui-même est un lac alcalin peu profond, résidu d\'un ancien grand lac préhistorique. C\'est un site majeur pour l\'ornithologie : des milliers de flamants roses viennent s\'y reproduire. Stanley Kubrick s\'en inspira pour le tournage des premières scènes de "2001, Odyssée de l\'Espace".', 'fr'),
(4, 'La Mosquée Hamoudi — Joyau Ottoman', 'Construite en 1906 par Sheikh Hamoudi ibn Mohammed, cette mosquée est le monument islamique le plus emblématique de Djibouti. Les artisans venus du Yémen ont apporté avec eux les techniques de l\'architecture ottomane : arcs en ogive, minarets élancés, cour intérieure carrelée de faïences bleues. Elle peut accueillir jusqu\'à 1000 fidèles et reste le centre spirituel de la communauté musulmane djiboutienne.', 'fr'),
(7, 'Tadjourah — La Cité Blanche', 'Tadjourah est la plus ancienne cité de Djibouti, fondée au XIIe siècle par les sultans afars. Surnommée "la ville blanche" pour ses maisons chaulées de blanc, elle fut pendant des siècles le principal port de commerce de la région, exportant sel, ivoire et esclaves vers le monde arabe. La médina conserve son architecture traditionnelle afar avec ses ruelles sinueuses, ses mosquées et son marché hebdomadaire.', 'fr');

INSERT INTO audio_guides (site_id, titre, duree_sec, langue, auteur) VALUES
(1, 'Histoire géologique du Lac Assal', 240, 'fr', 'Dr. Ibrahim Said Elmi'),
(2, 'Les mystères du Lac Abbé', 180, 'fr', 'Prof. Amina Warsama'),
(4, 'Visite guidée de la Mosquée Hamoudi', 300, 'fr', 'Sheikh Omar Abdillahi'),
(7, 'Tadjourah, mémoire vivante', 210, 'fr', 'Dr. Hodan Mohamed');

INSERT INTO realite_augmentee (site_id, type_overlay, description) VALUES
(1, 'panorama', 'Vue panoramique 360° sur les cristaux de sel et les volcans environnants'),
(2, 'reconstitution', 'Reconstitution de l\'ancien lac préhistorique avec sa faune disparue'),
(3, 'faune', 'Identification des oiseaux endémiques visibles depuis les sentiers'),
(4, 'annotation', 'Annotations des éléments architecturaux ottomans et leur symbolique');

INSERT INTO parcours (titre, description, theme, difficulte, duree_estimee, distance_km, photo) VALUES
('Merveilles Naturelles de Djibouti', 'Un voyage au cœur des paysages volcaniques et lacustres les plus spectaculaires de la Corne de l\'Afrique.', 'nature', 'moyen', 480, 250.0, 'parcours_nature.jpg'),
('Djibouti-Ville Historique', 'Exploration du centre historique : mosquées, marchés et architecture coloniale du vieux Djibouti.', 'histoire', 'facile', 180, 5.0, 'parcours_ville.jpg'),
('Aventure Côtière', 'Plages, îles et fonds marins exceptionnels du Golfe de Tadjourah.', 'aventure', 'facile', 360, 80.0, 'parcours_cote.jpg');

INSERT INTO parcours_sites (parcours_id, site_id, ordre) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3),
(2, 4, 1), (2, 5, 2), (2, 7, 3),
(3, 6, 1), (3, 8, 2);

INSERT INTO avis (nom_visiteur, site_id, note, commentaire) VALUES
('Marie D.', 1, 5, 'Paysage absolument irréel, on se croirait sur une autre planète. Incontournable !'),
('Ahmed K.', 1, 5, 'Le lever de soleil sur le lac est un moment magique. Prévoir de l\'eau et protection solaire.'),
('Sophie L.', 2, 5, 'Les cheminées au coucher du soleil : une photo qui vaut le voyage. Spectaculaire.'),
('Jean-Paul M.', 4, 4, 'Belle architecture, guide très accueillant. Respecter les horaires de prière.'),
('Fatima H.', 5, 5, 'Le vrai Djibouti ! Épices, couleurs, odeurs. Un dépaysement total en plein cœur de la ville.'),
('Carlos R.', 6, 5, 'Eau cristalline, plage magnifique. Les requins-baleines en novembre — une expérience de vie.'),
('Nadia S.', 7, 4, 'Ville authentique et préservée. La médina vaut vraiment le détour. Habitants très chaleureux.');

-- ---- TABLE ADMIN (à ajouter si elle n'existe pas) ----
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin par défaut : login=admin / mot de passe=admin123
INSERT IGNORE INTO admins (login, mot_de_passe, nom)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur');
-- Note: le hash correspond à "password" (bcrypt). Changez-le en production.
-- Pour "admin123" générez avec: password_hash('admin123', PASSWORD_DEFAULT)

-- Colonne images dans sites (si pas déjà là) (MySQL 5.x compatible)
SET @exists_image = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE table_schema = DATABASE()
    AND table_name = 'sites'
    AND column_name = 'image_url'
);

SET @sql = IF(
  @exists_image = 0,
  'ALTER TABLE sites ADD COLUMN image_url VARCHAR(500) DEFAULT NULL',
  'SELECT "column image_url exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exists_order = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE table_schema = DATABASE()
    AND table_name = 'sites'
    AND column_name = 'ordre_affichage'
);

SET @sql = IF(
  @exists_order = 0,
  'ALTER TABLE sites ADD COLUMN ordre_affichage INT DEFAULT 0',
  'SELECT "column ordre_affichage exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
