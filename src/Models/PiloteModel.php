<?php

namespace App\Models;

use App\Database;

class PiloteModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function findByUtilisateur(int $idUtilisateur): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM PILOTE WHERE id_utilisateur = :id
        ');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetch();
    }

    public function getEtudiants(int $idPilote): array
    {
        $stmt = $this->pdo->prepare('
            SELECT e.*, u.nom, u.prenom, u.email,
                   COUNT(c.id_candidature) AS nb_candidatures
            FROM ETUDIANT e
            JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
            LEFT JOIN CANDIDATURE c ON c.id_utilisateur = e.id_utilisateur
            WHERE e.id_pilote = :id_pilote
            GROUP BY e.id_etudiant
            ORDER BY u.nom, u.prenom
        ');
        $stmt->execute([':id_pilote' => $idPilote]);
        return $stmt->fetchAll();
    }

    public function getEtudiant(int $idEtudiant, int $idPilote): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT e.*, u.nom, u.prenom, u.email
            FROM ETUDIANT e
            JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
            WHERE e.id_etudiant = :id AND e.id_pilote = :id_pilote
        ');
        $stmt->execute([':id' => $idEtudiant, ':id_pilote' => $idPilote]);
        return $stmt->fetch();
    }

    public function modifierEtudiant(int $idEtudiant, int $idPilote, string $nom, string $prenom, string $email, string $ecole, string $promotion, ?string $motDePasseHash = null): bool
    {
        $etudiant = $this->getEtudiant($idEtudiant, $idPilote);
        if (!$etudiant) return false;

        if ($motDePasseHash !== null) {
            $stmt = $this->pdo->prepare('
                UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email,
                       ecole = :ecole, mot_de_passe_hash = :hash
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':email'  => $email,
                ':ecole'  => $ecole,
                ':hash'   => $motDePasseHash,
                ':id'     => $etudiant['id_utilisateur'],
            ]);
        } else {
            $stmt = $this->pdo->prepare('
                UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email, ecole = :ecole
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':email'  => $email,
                ':ecole'  => $ecole,
                ':id'     => $etudiant['id_utilisateur'],
            ]);
        }

        $stmt = $this->pdo->prepare('
            UPDATE ETUDIANT SET promotion = :promotion WHERE id_etudiant = :id
        ');
        $stmt->execute([':promotion' => $promotion ?: null, ':id' => $idEtudiant]);

        return true;
    }

    public function supprimerEtudiant(int $idEtudiant, int $idPilote): bool
    {
        // Vérifie que l'étudiant appartient bien à ce pilote
        $etudiant = $this->getEtudiant($idEtudiant, $idPilote);
        if (!$etudiant) return false;

        $stmt = $this->pdo->prepare('DELETE FROM ETUDIANT WHERE id_etudiant = :id');
        $stmt->execute([':id' => $idEtudiant]);

        $stmt = $this->pdo->prepare('DELETE FROM UTILISATEUR WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $etudiant['id_utilisateur']]);

        return true;
    }

    public function creerEtudiant(int $idPilote, string $nom, string $prenom, string $email, string $motDePasseHash, string $ecole): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe_hash, id_role, ecole)
            VALUES (:nom, :prenom, :email, :hash, 3, :ecole)
        ');
        $stmt->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':hash'   => $motDePasseHash,
            ':ecole'  => $ecole,
        ]);
        $idUtilisateur = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare('
            INSERT INTO ETUDIANT (id_utilisateur, id_pilote) VALUES (:id_utilisateur, :id_pilote)
        ');
        $stmt->execute([':id_utilisateur' => $idUtilisateur, ':id_pilote' => $idPilote]);
        return true;
    }

    public function supprimer(int $idPilote): bool
    {
        $pilote = $this->findById($idPilote);
        if (!$pilote) return false;

        // La suppression de l'utilisateur cascade sur PILOTE
        // et met id_pilote à NULL dans ETUDIANT (ON DELETE SET NULL)
        $stmt = $this->pdo->prepare('DELETE FROM UTILISATEUR WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $pilote['id_utilisateur']]);
        return true;
    }

    public function modifier(int $idPilote, string $nom, string $prenom, string $email, string $ecole, ?string $motDePasseHash = null): bool
    {
        $pilote = $this->findById($idPilote);
        if (!$pilote) return false;

        if ($motDePasseHash !== null) {
            $stmt = $this->pdo->prepare('
                UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email,
                       ecole = :ecole, mot_de_passe_hash = :hash
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':email'  => $email,
                ':ecole'  => $ecole,
                ':hash'   => $motDePasseHash,
                ':id'     => $pilote['id_utilisateur'],
            ]);
        } else {
            $stmt = $this->pdo->prepare('
                UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email, ecole = :ecole
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':email'  => $email,
                ':ecole'  => $ecole,
                ':id'     => $pilote['id_utilisateur'],
            ]);
        }
        return true;
    }

    public function creer(string $nom, string $prenom, string $email, string $motDePasseHash, string $ecole): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe_hash, id_role, ecole)
            VALUES (:nom, :prenom, :email, :hash, 2, :ecole)
        ');
        $stmt->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':hash'   => $motDePasseHash,
            ':ecole'  => $ecole,
        ]);
        $idUtilisateur = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare('INSERT INTO PILOTE (id_utilisateur) VALUES (:id)');
        $stmt->execute([':id' => $idUtilisateur]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getTous(string $recherche = ''): array
    {
        if ($recherche !== '') {
            $stmt = $this->pdo->prepare('
                SELECT p.id_pilote, p.id_utilisateur,
                       u.nom, u.prenom, u.email, u.ecole, u.date_creation,
                       COUNT(e.id_etudiant) AS nb_etudiants
                FROM PILOTE p
                JOIN UTILISATEUR u ON p.id_utilisateur = u.id_utilisateur
                LEFT JOIN ETUDIANT e ON e.id_pilote = p.id_pilote
                WHERE u.nom LIKE :recherche OR u.prenom LIKE :recherche OR u.email LIKE :recherche
                GROUP BY p.id_pilote
                ORDER BY u.nom, u.prenom
            ');
            $stmt->execute([':recherche' => '%' . $recherche . '%']);
        } else {
            $stmt = $this->pdo->query('
                SELECT p.id_pilote, p.id_utilisateur,
                       u.nom, u.prenom, u.email, u.ecole, u.date_creation,
                       COUNT(e.id_etudiant) AS nb_etudiants
                FROM PILOTE p
                JOIN UTILISATEUR u ON p.id_utilisateur = u.id_utilisateur
                LEFT JOIN ETUDIANT e ON e.id_pilote = p.id_pilote
                GROUP BY p.id_pilote
                ORDER BY u.nom, u.prenom
            ');
        }
        return $stmt->fetchAll();
    }

    public function findById(int $idPilote): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT p.id_pilote, p.id_utilisateur,
                   u.nom, u.prenom, u.email, u.ecole, u.date_creation,
                   COUNT(e.id_etudiant) AS nb_etudiants
            FROM PILOTE p
            JOIN UTILISATEUR u ON p.id_utilisateur = u.id_utilisateur
            LEFT JOIN ETUDIANT e ON e.id_pilote = p.id_pilote
            WHERE p.id_pilote = :id
            GROUP BY p.id_pilote
        ');
        $stmt->execute([':id' => $idPilote]);
        return $stmt->fetch();
    }

    public function getCandidaturesEtudiant(int $idUtilisateur): array
    {
        $stmt = $this->pdo->prepare('
            SELECT c.*, o.titre AS titre_offre, e.nom AS nom_entreprise
            FROM CANDIDATURE c
            JOIN OFFRE o ON c.id_offre = o.id_offre
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE c.id_utilisateur = :id
            ORDER BY c.date_candidature DESC
        ');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    // ── Méthodes réservées à l'admin (sans restriction de pilote) ──────────────

    public function getTousEtudiants(string $recherche = ''): array
    {
        if ($recherche !== '') {
            $stmt = $this->pdo->prepare('
                SELECT e.id_etudiant, e.id_utilisateur, e.promotion, e.statut_recherche_stage, e.id_pilote,
                       u.nom, u.prenom, u.email, u.ecole, u.date_creation,
                       COUNT(c.id_candidature) AS nb_candidatures,
                       p_u.nom AS pilote_nom, p_u.prenom AS pilote_prenom
                FROM ETUDIANT e
                JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
                LEFT JOIN CANDIDATURE c ON c.id_utilisateur = e.id_utilisateur
                LEFT JOIN PILOTE p ON e.id_pilote = p.id_pilote
                LEFT JOIN UTILISATEUR p_u ON p.id_utilisateur = p_u.id_utilisateur
                WHERE u.nom LIKE :recherche OR u.prenom LIKE :recherche OR u.email LIKE :recherche
                GROUP BY e.id_etudiant
                ORDER BY u.nom, u.prenom
            ');
            $stmt->execute([':recherche' => '%' . $recherche . '%']);
        } else {
            $stmt = $this->pdo->query('
                SELECT e.id_etudiant, e.id_utilisateur, e.promotion, e.statut_recherche_stage, e.id_pilote,
                       u.nom, u.prenom, u.email, u.ecole, u.date_creation,
                       COUNT(c.id_candidature) AS nb_candidatures,
                       p_u.nom AS pilote_nom, p_u.prenom AS pilote_prenom
                FROM ETUDIANT e
                JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
                LEFT JOIN CANDIDATURE c ON c.id_utilisateur = e.id_utilisateur
                LEFT JOIN PILOTE p ON e.id_pilote = p.id_pilote
                LEFT JOIN UTILISATEUR p_u ON p.id_utilisateur = p_u.id_utilisateur
                GROUP BY e.id_etudiant
                ORDER BY u.nom, u.prenom
            ');
        }
        return $stmt->fetchAll();
    }

    public function getEtudiantAdmin(int $idEtudiant): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT e.id_etudiant, e.id_utilisateur, e.promotion, e.statut_recherche_stage, e.id_pilote,
                   u.nom, u.prenom, u.email, u.ecole, u.date_creation,
                   COUNT(c.id_candidature) AS nb_candidatures,
                   p_u.nom AS pilote_nom, p_u.prenom AS pilote_prenom
            FROM ETUDIANT e
            JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
            LEFT JOIN CANDIDATURE c ON c.id_utilisateur = e.id_utilisateur
            LEFT JOIN PILOTE p ON e.id_pilote = p.id_pilote
            LEFT JOIN UTILISATEUR p_u ON p.id_utilisateur = p_u.id_utilisateur
            WHERE e.id_etudiant = :id
            GROUP BY e.id_etudiant
        ');
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetch();
    }

    public function modifierEtudiantAdmin(int $idEtudiant, string $nom, string $prenom, string $email, string $ecole, string $promotion, ?int $idPilote, ?string $motDePasseHash = null): bool
    {
        $etudiant = $this->getEtudiantAdmin($idEtudiant);
        if (!$etudiant) return false;

        if ($motDePasseHash !== null) {
            $stmt = $this->pdo->prepare('
                UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email,
                       ecole = :ecole, mot_de_passe_hash = :hash
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':email'  => $email,
                ':ecole'  => $ecole,
                ':hash'   => $motDePasseHash,
                ':id'     => $etudiant['id_utilisateur'],
            ]);
        } else {
            $stmt = $this->pdo->prepare('
                UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email, ecole = :ecole
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([
                ':nom'    => $nom,
                ':prenom' => $prenom,
                ':email'  => $email,
                ':ecole'  => $ecole,
                ':id'     => $etudiant['id_utilisateur'],
            ]);
        }

        $stmt = $this->pdo->prepare('
            UPDATE ETUDIANT SET promotion = :promotion, id_pilote = :id_pilote WHERE id_etudiant = :id
        ');
        $stmt->execute([
            ':promotion' => $promotion ?: null,
            ':id_pilote' => $idPilote,
            ':id'        => $idEtudiant,
        ]);

        return true;
    }

    public function creerEtudiantAdmin(?int $idPilote, string $nom, string $prenom, string $email, string $motDePasseHash, string $ecole): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe_hash, id_role, ecole)
            VALUES (:nom, :prenom, :email, :hash, 3, :ecole)
        ');
        $stmt->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':hash'   => $motDePasseHash,
            ':ecole'  => $ecole,
        ]);
        $idUtilisateur = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare('
            INSERT INTO ETUDIANT (id_utilisateur, id_pilote) VALUES (:id_utilisateur, :id_pilote)
        ');
        $stmt->execute([':id_utilisateur' => $idUtilisateur, ':id_pilote' => $idPilote]);
        return true;
    }

    public function supprimerEtudiantAdmin(int $idEtudiant): bool
    {
        $etudiant = $this->getEtudiantAdmin($idEtudiant);
        if (!$etudiant) return false;

        $stmt = $this->pdo->prepare('DELETE FROM ETUDIANT WHERE id_etudiant = :id');
        $stmt->execute([':id' => $idEtudiant]);

        $stmt = $this->pdo->prepare('DELETE FROM UTILISATEUR WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $etudiant['id_utilisateur']]);

        return true;
    }
}
