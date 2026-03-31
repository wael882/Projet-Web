-- Migration : transfert de la fonctionnalité d'évaluation de l'étudiant vers le pilote/admin
-- La colonne id_etudiant est remplacée par id_utilisateur dans EVALUATION_ENTREPRISE

ALTER TABLE EVALUATION_ENTREPRISE
    DROP FOREIGN KEY fk_eval_etudiant;

ALTER TABLE EVALUATION_ENTREPRISE
    CHANGE COLUMN id_etudiant id_utilisateur INT NOT NULL;

ALTER TABLE EVALUATION_ENTREPRISE
    ADD CONSTRAINT fk_eval_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
