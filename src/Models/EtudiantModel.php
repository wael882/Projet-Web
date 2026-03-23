<?php

namespace App\Models;

use App\Database;

class EtudiantModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function create(int $idUtilisateur): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO ETUDIANT (id_utilisateur) VALUES (:id_utilisateur)
        ');
        $stmt->execute([':id_utilisateur' => $idUtilisateur]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findByUtilisateur(int $idUtilisateur): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM ETUDIANT WHERE id_utilisateur = :id
        ');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetch();
    }

    public function update(int $idUtilisateur, string $promotion, string $statut, ?string $avatarPath = null): void
    {
        if ($avatarPath !== null) {
            $stmt = $this->pdo->prepare('
                UPDATE ETUDIANT SET promotion = :promotion, statut_recherche_stage = :statut, avatar = :avatar
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([':promotion' => $promotion, ':statut' => $statut, ':avatar' => $avatarPath, ':id' => $idUtilisateur]);
        } else {
            $stmt = $this->pdo->prepare('
                UPDATE ETUDIANT SET promotion = :promotion, statut_recherche_stage = :statut
                WHERE id_utilisateur = :id
            ');
            $stmt->execute([':promotion' => $promotion, ':statut' => $statut, ':id' => $idUtilisateur]);
        }
    }
}
