# Dictionnaire de Données — Projet CESI Stage

## Entités et attributs

---

### UTILISATEUR
Table de base pour tous les comptes utilisateurs.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_utilisateur | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(100) | NOT NULL | Nom de famille |
| prenom | VARCHAR(100) | NOT NULL | Prénom |
| email | VARCHAR(150) | NOT NULL, UNIQUE | Adresse email (identifiant de connexion) |
| mot_de_passe | VARCHAR(255) | NOT NULL | Mot de passe hashé (bcrypt) |
| role | ENUM('etudiant','pilote','admin') | NOT NULL | Rôle de l'utilisateur |
| date_creation | DATETIME | DEFAULT NOW() | Date de création du compte |

---

### PROMOTION
Classe/groupe d'étudiants géré par un pilote.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_promotion | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(100) | NOT NULL | Nom de la promotion (ex: BTS SIO 2024) |
| annee | INT | NOT NULL | Année scolaire |

---

### ETUDIANT
Sous-type d'Utilisateur avec informations spécifiques aux étudiants.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_etudiant | INT | PK, AUTO_INCREMENT | Identifiant unique |
| id_utilisateur | INT | FK → Utilisateur, NOT NULL | Référence au compte utilisateur |
| id_promotion | INT | FK → Promotion, NOT NULL | Promotion de l'étudiant |
| cv_path | VARCHAR(255) | NULL | Chemin vers le fichier CV |

---

### PILOTE
Sous-type d'Utilisateur — encadrant pédagogique.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_pilote | INT | PK, AUTO_INCREMENT | Identifiant unique |
| id_utilisateur | INT | FK → Utilisateur, NOT NULL | Référence au compte utilisateur |

---

### ENTREPRISE
Société qui publie des offres de stage.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_entreprise | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(150) | NOT NULL | Nom de l'entreprise |
| secteur | VARCHAR(100) | NULL | Secteur d'activité |
| ville | VARCHAR(100) | NULL | Ville du siège |
| description | TEXT | NULL | Description de l'entreprise |
| email_contact | VARCHAR(150) | NULL | Email de contact |

---

### OFFRE
Offre de stage publiée par une entreprise.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_offre | INT | PK, AUTO_INCREMENT | Identifiant unique |
| id_entreprise | INT | FK → Entreprise, NOT NULL | Entreprise qui publie l'offre |
| titre | VARCHAR(200) | NOT NULL | Titre de l'offre |
| description | TEXT | NOT NULL | Détail du poste |
| remuneration | DECIMAL(8,2) | NULL | Rémunération mensuelle |
| duree | INT | NULL | Durée du stage (en semaines) |
| date_debut | DATETIME | NULL | Date de début souhaitée |
| date_fin | DATETIME | NULL | Date de fin souhaitée |
| statut | ENUM('active','inactive') | DEFAULT 'active' | Statut de l'offre |
| date_publication | DATETIME | DEFAULT NOW() | Date de mise en ligne |

---

### COMPETENCE
Tag / compétence associée aux offres.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_competence | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(100) | NOT NULL, UNIQUE | Nom de la compétence (ex: PHP, SQL) |

---

### OFFRE_COMPETENCE *(table pivot)*
Association Many-to-Many entre Offre et Competence.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_offre | INT | FK → Offre, PK | Référence à l'offre |
| id_competence | INT | FK → Competence, PK | Référence à la compétence |

---

### CANDIDATURE *(table pivot)*
Association Many-to-Many entre Etudiant et Offre — représente une candidature.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_candidature | INT | PK, AUTO_INCREMENT | Identifiant unique |
| id_etudiant | INT | FK → Etudiant, NOT NULL | Etudiant qui postule |
| id_offre | INT | FK → Offre, NOT NULL | Offre visée |
| date_candidature | DATETIME | DEFAULT NOW() | Date d'envoi |
| statut | ENUM('envoyee','vue','acceptee','refusee') | DEFAULT 'envoyee' | Statut de la candidature |
| lettre_motivation | TEXT | NULL | Lettre de motivation |

---

### WISHLIST *(table pivot)*
Association Many-to-Many entre Etudiant et Offre — représente les favoris.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_wishlist | INT | PK, AUTO_INCREMENT | Identifiant unique |
| id_etudiant | INT | FK → Etudiant, NOT NULL | Etudiant propriétaire |
| id_offre | INT | FK → Offre, NOT NULL | Offre mise en favori |
| date_ajout | DATETIME | DEFAULT NOW() | Date d'ajout au favori |

---

### EVALUATION
Note laissée par un étudiant sur une entreprise.

| Attribut | Type | Contrainte | Description |
|---|---|---|---|
| id_evaluation | INT | PK, AUTO_INCREMENT | Identifiant unique |
| id_etudiant | INT | FK → Etudiant, NOT NULL | Etudiant qui évalue |
| id_entreprise | INT | FK → Entreprise, NOT NULL | Entreprise évaluée |
| note | INT | NOT NULL, CHECK(1-5) | Note de 1 à 5 |
| commentaire | TEXT | NULL | Commentaire libre |
| date_evaluation | DATETIME | DEFAULT NOW() | Date de l'évaluation |

---

## Récapitulatif des associations

| Association | Cardinalité | Réalisation |
|---|---|---|
| Entreprise → Offre | 1,N | Clé étrangère id_entreprise dans Offre |
| Offre ↔ Competence | N,N | Table pivot OFFRE_COMPETENCE |
| Etudiant ↔ Offre | N,N | Table pivot CANDIDATURE |
| Etudiant ↔ Offre | N,N | Table pivot WISHLIST |
| Etudiant ↔ Entreprise | N,N | Table pivot EVALUATION |
| Pilote → Etudiant | via Promotion | Etudiant.id_promotion → Promotion |
| Promotion → Etudiant | 1,N | Clé étrangère id_promotion dans Etudiant |
| Utilisateur → Etudiant | 1,1 | Clé étrangère id_utilisateur dans Etudiant |
| Utilisateur → Pilote | 1,1 | Clé étrangère id_utilisateur dans Pilote |
