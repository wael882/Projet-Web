-- ============================================================
-- Migration : WISHLIST utilise id_utilisateur au lieu de id_etudiant
-- Permet aux admin et pilotes d'utiliser les favoris
-- ============================================================

USE cesi_db;

-- Supprime la clé étrangère et la clé primaire existantes
ALTER TABLE WISHLIST DROP FOREIGN KEY fk_wl_etudiant;
ALTER TABLE WISHLIST DROP PRIMARY KEY;

-- Renomme la colonne
ALTER TABLE WISHLIST CHANGE id_etudiant id_utilisateur INT NOT NULL;

-- Migre les données : les id_etudiant stockés deviennent des id_utilisateur
UPDATE WISHLIST w
JOIN ETUDIANT e ON w.id_utilisateur = e.id_etudiant
SET w.id_utilisateur = e.id_utilisateur;

-- Recrée la clé primaire et la clé étrangère
ALTER TABLE WISHLIST ADD PRIMARY KEY (id_utilisateur, id_offre);
ALTER TABLE WISHLIST ADD CONSTRAINT fk_wl_utilisateur
    FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE;
