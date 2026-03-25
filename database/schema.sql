-- ============================================================
-- CESI Stage -- Script de création de la base de données
-- Base : cesi_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS cesi_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cesi_db;

-- ------------------------------------------------------------
-- TABLE : ROLE
-- ------------------------------------------------------------
CREATE TABLE ROLE (
    id_role     INT AUTO_INCREMENT PRIMARY KEY,
    libelle     VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : UTILISATEUR
-- ------------------------------------------------------------
CREATE TABLE UTILISATEUR (
    id_utilisateur      INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(100)    NOT NULL,
    prenom              VARCHAR(100)    NOT NULL,
    email               VARCHAR(150)    NOT NULL UNIQUE,
    mot_de_passe_hash   VARCHAR(255)    NOT NULL,
    ecole               VARCHAR(150)    NULL,
    actif               BOOLEAN         NOT NULL DEFAULT TRUE,
    date_creation       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_role             INT             NOT NULL,
    CONSTRAINT fk_utilisateur_role
        FOREIGN KEY (id_role) REFERENCES ROLE(id_role)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : ADMIN
-- ------------------------------------------------------------
CREATE TABLE ADMIN (
    id_admin        INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur  INT NOT NULL UNIQUE,
    CONSTRAINT fk_admin_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : PILOTE
-- ------------------------------------------------------------
CREATE TABLE PILOTE (
    id_pilote       INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur  INT NOT NULL UNIQUE,
    CONSTRAINT fk_pilote_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : ETUDIANT
-- ------------------------------------------------------------
CREATE TABLE ETUDIANT (
    id_etudiant             INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur          INT          NOT NULL UNIQUE,
    promotion               VARCHAR(100) NULL,
    statut_recherche_stage  VARCHAR(100) NULL,
    id_pilote               INT          NULL,
    CONSTRAINT fk_etudiant_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_etudiant_pilote
        FOREIGN KEY (id_pilote) REFERENCES PILOTE(id_pilote)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : ENTREPRISE
-- ------------------------------------------------------------
CREATE TABLE ENTREPRISE (
    id_entreprise       INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(150) NOT NULL,
    description         TEXT         NULL,
    email_contact       VARCHAR(150) NULL,
    telephone_contact   VARCHAR(20)  NULL,
    active              BOOLEAN      NOT NULL DEFAULT TRUE,
    date_creation       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : COMPETENCE
-- ------------------------------------------------------------
CREATE TABLE COMPETENCE (
    id_competence   INT AUTO_INCREMENT PRIMARY KEY,
    libelle         VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : OFFRE
-- ------------------------------------------------------------
CREATE TABLE OFFRE (
    id_offre            INT AUTO_INCREMENT PRIMARY KEY,
    titre               VARCHAR(200)    NOT NULL,
    description         TEXT            NOT NULL,
    remuneration_base   DECIMAL(8,2)    NULL,
    date_offre          DATE            NULL,
    duree_stage         INT             NULL COMMENT 'Durée en semaines',
    active              BOOLEAN         NOT NULL DEFAULT TRUE,
    id_entreprise       INT             NOT NULL,
    CONSTRAINT fk_offre_entreprise
        FOREIGN KEY (id_entreprise) REFERENCES ENTREPRISE(id_entreprise)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE PIVOT : offre_competence (est_associee)
-- ------------------------------------------------------------
CREATE TABLE offre_competence (
    id_offre        INT NOT NULL,
    id_competence   INT NOT NULL,
    PRIMARY KEY (id_offre, id_competence),
    CONSTRAINT fk_oc_offre
        FOREIGN KEY (id_offre) REFERENCES OFFRE(id_offre)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_oc_competence
        FOREIGN KEY (id_competence) REFERENCES COMPETENCE(id_competence)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE PIVOT : etudiant_competence (detient)
-- ------------------------------------------------------------
CREATE TABLE etudiant_competence (
    id_etudiant     INT NOT NULL,
    id_competence   INT NOT NULL,
    PRIMARY KEY (id_etudiant, id_competence),
    CONSTRAINT fk_ec_etudiant
        FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_etudiant)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_ec_competence
        FOREIGN KEY (id_competence) REFERENCES COMPETENCE(id_competence)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : EVALUATION_ENTREPRISE
-- ------------------------------------------------------------
CREATE TABLE EVALUATION_ENTREPRISE (
    id_evaluation   INT AUTO_INCREMENT PRIMARY KEY,
    note            INT      NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire     TEXT     NULL,
    date_evaluation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_etudiant     INT      NOT NULL,
    id_entreprise   INT      NOT NULL,
    CONSTRAINT fk_eval_etudiant
        FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_etudiant)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_eval_entreprise
        FOREIGN KEY (id_entreprise) REFERENCES ENTREPRISE(id_entreprise)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : CANDIDATURE
-- ------------------------------------------------------------
CREATE TABLE CANDIDATURE (
    id_candidature      INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant         INT          NOT NULL,
    id_offre            INT          NOT NULL,
    cv_fichier          VARCHAR(255) NULL,
    lettre_motivation   TEXT         NULL,
    date_candidature    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut              VARCHAR(50)  NOT NULL DEFAULT 'envoyee',
    CONSTRAINT fk_cand_etudiant
        FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_etudiant)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_cand_offre
        FOREIGN KEY (id_offre) REFERENCES OFFRE(id_offre)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLE : WISHLIST
-- ------------------------------------------------------------
CREATE TABLE WISHLIST (
    id_etudiant INT      NOT NULL,
    id_offre    INT      NOT NULL,
    date_ajout  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_etudiant, id_offre),
    CONSTRAINT fk_wl_etudiant
        FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_etudiant)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_wl_offre
        FOREIGN KEY (id_offre) REFERENCES OFFRE(id_offre)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
