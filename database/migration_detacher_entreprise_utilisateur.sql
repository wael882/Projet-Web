-- ============================================================
-- Migration : Détacher ENTREPRISE de UTILISATEUR
-- Supprime le lien de propriété entre une entreprise et son créateur
-- ============================================================

USE cesi_db;

-- Supprime la contrainte de clé étrangère si elle existe
SET @contrainte = (
    SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'cesi_db'
      AND TABLE_NAME   = 'ENTREPRISE'
      AND COLUMN_NAME  = 'id_utilisateur'
    LIMIT 1
);
SET @requete = IF(@contrainte IS NOT NULL,
    CONCAT('ALTER TABLE ENTREPRISE DROP FOREIGN KEY ', @contrainte),
    'SELECT 1'
);
PREPARE stmt FROM @requete;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Supprime la colonne id_utilisateur (si elle existe)
SET @colExiste = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'cesi_db'
      AND TABLE_NAME   = 'ENTREPRISE'
      AND COLUMN_NAME  = 'id_utilisateur'
);
SET @requeteDrop = IF(@colExiste > 0,
    'ALTER TABLE ENTREPRISE DROP COLUMN id_utilisateur',
    'SELECT 1'
);
PREPARE stmtDrop FROM @requeteDrop;
EXECUTE stmtDrop;
DEALLOCATE PREPARE stmtDrop;
