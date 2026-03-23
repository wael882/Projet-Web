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
}
