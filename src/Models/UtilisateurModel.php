<?php

namespace App\Models;

use App\Database;

class UtilisateurModel {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getPdo();
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
}
