<?php

namespace App\Models;

use App\Database;

class EntrepriseModel {

    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = Database::getInstance()->getPdo();
        }
    }

    public function findAll(): array {
        $stmt = $this->pdo->query('
            SELECT * FROM ENTREPRISE WHERE active = TRUE ORDER BY nom ASC
        ');
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->pdo->prepare('SELECT * FROM ENTREPRISE WHERE id_entreprise = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
