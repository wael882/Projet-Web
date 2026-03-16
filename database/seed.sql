-- ============================================================
-- CESI Stage -- Jeu de données de test
-- Exécuter APRÈS schema.sql
-- ============================================================

USE cesi_db;

-- ------------------------------------------------------------
-- ROLE (1=admin, 2=pilote, 3=etudiant)
-- ------------------------------------------------------------
INSERT INTO ROLE (libelle) VALUES
('admin'),
('pilote'),
('etudiant');

-- ------------------------------------------------------------
-- UTILISATEUR
-- Mots de passe hashés avec password_hash('password123', PASSWORD_BCRYPT)
-- id 1=Admin, 2=Sophie(pilote), 3=Marc(pilote),
--    4=Lucas, 5=Emma, 6=Nathan, 7=Chloé, 8=Alexis (etudiants)
-- ------------------------------------------------------------
INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe_hash, actif, id_role) VALUES
('Admin',    'System',   'admin@cesi.fr',                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 1),
('Martin',   'Sophie',   'sophie.martin@cesi.fr',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 2),
('Dupont',   'Marc',     'marc.dupont@cesi.fr',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 2),
('Lefebvre', 'Lucas',    'lucas.lefebvre@etud.cesi.fr',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 3),
('Bernard',  'Emma',     'emma.bernard@etud.cesi.fr',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 3),
('Moreau',   'Nathan',   'nathan.moreau@etud.cesi.fr',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 3),
('Petit',    'Chloé',    'chloe.petit@etud.cesi.fr',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 3),
('Simon',    'Alexis',   'alexis.simon@etud.cesi.fr',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, 3);

-- ------------------------------------------------------------
-- ADMIN (correspond à id_utilisateur=1)
-- ------------------------------------------------------------
INSERT INTO ADMIN (id_utilisateur) VALUES (1);

-- ------------------------------------------------------------
-- PILOTE (id 1=Sophie, id 2=Marc)
-- ------------------------------------------------------------
INSERT INTO PILOTE (id_utilisateur) VALUES
(2),
(3);

-- ------------------------------------------------------------
-- ETUDIANT (id 1=Lucas, 2=Emma, 3=Nathan, 4=Chloé, 5=Alexis)
-- id_pilote: 1=Sophie, 2=Marc
-- ------------------------------------------------------------
INSERT INTO ETUDIANT (id_utilisateur, promotion, statut_recherche_stage, id_pilote) VALUES
(4, 'BTS SIO SLAM 2024', 'en_recherche',    1),
(5, 'BTS SIO SLAM 2024', 'en_recherche',    1),
(6, 'BTS SIO SISR 2024', 'en_recherche',    2),
(7, 'BTS SIO SISR 2024', 'non_disponible',  2),
(8, 'BTS SIO SLAM 2024', 'en_recherche',    1);

-- ------------------------------------------------------------
-- ENTREPRISE (5 entreprises)
-- ------------------------------------------------------------
INSERT INTO ENTREPRISE (nom, description, email_contact, telephone_contact, active) VALUES
('TechCorp',     'Entreprise spécialisée en développement web et mobile.',       'contact@techcorp.fr',      '0123456789', TRUE),
('DataSoft',     'Éditeur de logiciels de gestion de données.',                  'rh@datasoft.fr',           '0234567890', TRUE),
('WebAgency',    'Agence web créative, design et développement.',                'jobs@webagency.fr',        '0345678901', TRUE),
('NetSecure',    'Cybersécurité et infrastructure réseau.',                      'recrutement@netsecure.fr', '0456789012', TRUE),
('CloudSystems', 'Solutions cloud et DevOps pour entreprises.',                  'hr@cloudsystems.fr',       '0567890123', TRUE);

-- ------------------------------------------------------------
-- COMPETENCE (8 compétences)
-- id: 1=PHP, 2=JS, 3=SQL, 4=Python, 5=React, 6=Docker, 7=Réseau, 8=Cybersécurité
-- ------------------------------------------------------------
INSERT INTO COMPETENCE (libelle) VALUES
('PHP'),
('JavaScript'),
('SQL'),
('Python'),
('React'),
('Docker'),
('Réseau'),
('Cybersécurité');

-- ------------------------------------------------------------
-- OFFRE (10 offres)
-- ------------------------------------------------------------
INSERT INTO OFFRE (id_entreprise, titre, description, remuneration_base, date_offre, duree_stage, active) VALUES
(1, 'Développeur PHP / Symfony',    'Stage de développement backend sur une application SaaS.',      600.00, '2024-06-01', 8,  TRUE),
(1, 'Développeur React',            'Intégration de composants frontend en React.js.',                550.00, '2024-06-01', 8,  TRUE),
(2, 'Data Analyst junior',          'Analyse de données avec Python et Power BI.',                    620.00, '2024-07-01', 12, TRUE),
(2, 'Développeur SQL / ETL',        'Développement de pipelines de données sous SQL Server.',         580.00, '2024-07-01', 8,  TRUE),
(3, 'Intégrateur web',              'Intégration HTML/CSS/JS de maquettes Figma.',                    500.00, '2024-06-15', 6,  TRUE),
(3, 'Développeur WordPress',        'Création et personnalisation de thèmes WordPress.',              520.00, '2024-06-15', 8,  TRUE),
(4, 'Technicien réseau',            'Maintenance et supervision de l\'infrastructure réseau.',        560.00, '2024-09-01', 12, TRUE),
(4, 'Analyste sécurité',            'Audit de sécurité et tests de pénétration (pentest).',          650.00, '2024-09-01', 12, TRUE),
(5, 'DevOps / CI-CD',               'Mise en place de pipelines CI/CD avec GitLab et Docker.',       680.00, '2024-06-01', 10, TRUE),
(5, 'Administrateur cloud AWS',     'Gestion et optimisation d\'une infrastructure AWS.',             700.00, '2024-06-01', 12, TRUE);

-- ------------------------------------------------------------
-- OFFRE_COMPETENCE (est_associee)
-- ------------------------------------------------------------
INSERT INTO offre_competence (id_offre, id_competence) VALUES
(1, 1), (1, 3),     -- PHP Symfony → PHP, SQL
(2, 2), (2, 5),     -- React → JS, React
(3, 4), (3, 3),     -- Data Analyst → Python, SQL
(4, 3),             -- ETL → SQL
(5, 2),             -- Intégrateur → JS
(6, 2),             -- WordPress → JS
(7, 7),             -- Réseau → Réseau
(8, 8), (8, 7),     -- Sécurité → Cybersécurité, Réseau
(9, 6), (9, 2),     -- DevOps → Docker, JS
(10, 6);            -- AWS → Docker

-- ------------------------------------------------------------
-- ETUDIANT_COMPETENCE (detient)
-- id_etudiant: 1=Lucas, 2=Emma, 3=Nathan, 4=Chloé, 5=Alexis
-- ------------------------------------------------------------
INSERT INTO etudiant_competence (id_etudiant, id_competence) VALUES
(1, 1), (1, 3),     -- Lucas → PHP, SQL
(2, 1), (2, 2),     -- Emma → PHP, JS
(3, 7), (3, 3),     -- Nathan → Réseau, SQL
(4, 8), (4, 7),     -- Chloé → Cybersécurité, Réseau
(5, 6), (5, 2);     -- Alexis → Docker, JS

-- ------------------------------------------------------------
-- CANDIDATURE
-- id_etudiant: 1=Lucas, 2=Emma, 3=Nathan, 4=Chloé, 5=Alexis
-- ------------------------------------------------------------
INSERT INTO CANDIDATURE (id_etudiant, id_offre, lettre_motivation, statut) VALUES
(1, 1, 'Je suis passionné par le développement PHP et souhaite mettre en pratique mes compétences.', 'envoyee'),
(1, 3, 'Mon intérêt pour la data science m\'a poussé à postuler pour ce poste.', 'vue'),
(2, 1, 'Développeuse motivée, je souhaite intégrer une équipe dynamique.', 'acceptee'),
(2, 5, 'L\'intégration web est au cœur de ma formation.', 'envoyee'),
(3, 7, 'La gestion réseau est ma spécialité au sein de ma formation SISR.', 'envoyee'),
(4, 8, 'La cybersécurité m\'attire beaucoup, ce stage serait idéal.', 'refusee'),
(5, 9, 'Le DevOps est la voie que je souhaite emprunter pour ma carrière.', 'vue');

-- ------------------------------------------------------------
-- WISHLIST
-- ------------------------------------------------------------
INSERT INTO WISHLIST (id_etudiant, id_offre) VALUES
(1, 2),
(1, 9),
(2, 3),
(3, 8),
(4, 9),
(5, 10);

-- ------------------------------------------------------------
-- EVALUATION_ENTREPRISE
-- ------------------------------------------------------------
INSERT INTO EVALUATION_ENTREPRISE (id_etudiant, id_entreprise, note, commentaire) VALUES
(2, 1, 5, 'Excellente expérience, équipe très accueillante et projets stimulants.'),
(1, 2, 4, 'Bonne ambiance, missions intéressantes mais encadrement perfectible.'),
(3, 4, 3, 'Stage correct, mais peu de contact avec les équipes techniques.');
