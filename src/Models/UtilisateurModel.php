<?php

namespace App\Models;

use App\Database;

class UtilisateurModel {

    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = Database::getInstance()->getPdo();
        }
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->pdo->prepare('
            SELECT u.*, r.libelle AS role, e.id_etudiant
            FROM UTILISATEUR u
            JOIN ROLE r ON u.id_role = r.id_role
            LEFT JOIN ETUDIANT e ON e.id_utilisateur = u.id_utilisateur
            WHERE u.email = :email AND u.actif = TRUE
        ');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function create(string $nom, string $prenom, string $email, string $motDePasseHash, int $idRole, string $ecole): int {
        $stmt = $this->pdo->prepare('
            INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe_hash, id_role, ecole)
            VALUES (:nom, :prenom, :email, :hash, :id_role, :ecole)
        ');
        $stmt->execute([
            ':nom'     => $nom,
            ':prenom'  => $prenom,
            ':email'   => $email,
            ':hash'    => $motDePasseHash,
            ':id_role' => $idRole,
            ':ecole'   =>$ecole,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $nom, string $prenom, string $email): bool {
        $stmt = $this->pdo->prepare('
            UPDATE UTILISATEUR SET nom = :nom, prenom = :prenom, email = :email
            WHERE id_utilisateur = :id
        ');
        $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function saveResetToken(string $email, string $token, string $expiry): bool {
        $stmt = $this->pdo->prepare('
            UPDATE UTILISATEUR
            SET reset_token = :token, reset_token_expiry = :expiry
            WHERE email = :email AND actif = TRUE
        ');
        $stmt->execute([':token' => $token, ':expiry' => $expiry, ':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    public function findByResetToken(string $token): array|false {
        $stmt = $this->pdo->prepare('
            SELECT id_utilisateur, email, reset_token_expiry
            FROM UTILISATEUR
            WHERE reset_token = :token AND actif = TRUE
        ');
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function updatePassword(int $idUtilisateur, string $motDePasseHash): void {
        $stmt = $this->pdo->prepare('
            UPDATE UTILISATEUR
            SET mot_de_passe_hash = :hash, reset_token = NULL, reset_token_expiry = NULL
            WHERE id_utilisateur = :id
        ');
        $stmt->execute([':hash' => $motDePasseHash, ':id' => $idUtilisateur]);
    }
}
