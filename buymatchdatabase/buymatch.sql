CREATE DATABASE buymatch;
USE buymatch;

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom_user VARCHAR(100) NOT NULL,
    prenom_user VARCHAR(100) NOT NULL,
    email_user VARCHAR(150) NOT NULL UNIQUE,
    phone_user VARCHAR(20),
    photo_user VARCHAR(255),
    password_user VARCHAR(255) NOT NULL,
    role_user ENUM('acheteur','organisateur','admin') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE matchs (
    id_match INT AUTO_INCREMENT PRIMARY KEY,
    organisateur_id INT NOT NULL,
    equipe1_nom VARCHAR(120) NOT NULL,
    equipe1_logo VARCHAR(255),
    equipe2_nom VARCHAR(120) NOT NULL,
    equipe2_logo VARCHAR(255),
    date_match DATE NOT NULL,
    heure_match TIME NOT NULL,
    lieu_match VARCHAR(150) NOT NULL,
    total_places INT NOT NULL DEFAULT 2000,
    statut_match ENUM('en_attente','publie','refuse') DEFAULT 'en_attente',
    FOREIGN KEY (organisateur_id) REFERENCES users(id_user)
);

CREATE TABLE categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT,
    nom_categorie VARCHAR(80) NOT NULL,
    prix_categorie DECIMAL(10,2) NOT NULL,
    places_max INT NOT NULL,
    FOREIGN KEY (match_id) REFERENCES matchs(id_match)
);

CREATE TABLE tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    acheteur_id INT NOT NULL,
    match_id INT NOT NULL,
    categorie_id INT NOT NULL,
    place_numero INT NOT NULL,
    prix_ticket DECIMAL(10,2) NOT NULL,
    code_ticket VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (acheteur_id) REFERENCES users(id_user),
    FOREIGN KEY (match_id) REFERENCES matchs(id_match),
    FOREIGN KEY (categorie_id) REFERENCES categories(id_categorie),
    UNIQUE KEY uniq_place_match (match_id, place_numero)
);

CREATE TABLE comments (
    id_comment INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    user_id INT NOT NULL,
    note INT,
    contenu TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matchs(id_match),
    FOREIGN KEY (user_id) REFERENCES users(id_user)
);

CREATE VIEW v_match_sales AS
SELECT m.id_match,m.equipe1_nom,m.equipe2_nom,
    m.date_match,m.lieu_match,
    COUNT(t.id_ticket) AS billets_vendus,
    IFNULL(SUM(t.prix_ticket), 0) AS chiffre_affaires
FROM matchs m
LEFT JOIN tickets t ON t.match_id = m.id_match
GROUP BY m.id_match, m.equipe1_nom, m.equipe2_nom, m.date_match, m.lieu_match;


DELIMITER $$

CREATE PROCEDURE sp_total_ventes_match(IN p_match_id INT)
BEGIN
    SELECT 
        m.id_match,
        m.equipe1_nom,
        m.equipe2_nom,
        COUNT(t.id_ticket) AS billets_vendus,
        IFNULL(SUM(t.prix_ticket), 0) AS chiffre_affaires
    FROM matchs m
    LEFT JOIN tickets t ON t.match_id = m.id_match
    WHERE m.id_match = p_match_id
    GROUP BY m.id_match, m.equipe1_nom, m.equipe2_nom;
END$$

DELIMITER ;


INSERT INTO users(nom_user, prenom_user, email_user, phone_user, password_user, role_user)
VALUES ("Hammouch", "Abdellah", "hammouchabdellah4529@gmail.com", "0645291000", "abha11228899", "admin");
