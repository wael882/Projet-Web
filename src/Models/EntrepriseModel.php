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

    public function findAll(string $search = '', int $limite = 10, int $offset = 0): array {
        if ($search !== '') {
            $stmt = $this->pdo->prepare('
                SELECT * FROM ENTREPRISE WHERE active = TRUE AND nom LIKE :search ORDER BY nom ASC LIMIT :limite OFFSET :offset
            ');
            $stmt->bindValue(':search', '%' . $search . '%');
        } else {
            $stmt = $this->pdo->prepare('
                SELECT * FROM ENTREPRISE WHERE active = TRUE ORDER BY nom ASC LIMIT :limite OFFSET :offset
            ');
        }
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(string $search = ''): int {
        if ($search !== '') {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ENTREPRISE WHERE active = TRUE AND nom LIKE :search');
            $stmt->execute([':search' => '%' . $search . '%']);
        } else {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM ENTREPRISE WHERE active = TRUE');
        }
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): array|false {
        $stmt = $this->pdo->prepare('SELECT * FROM ENTREPRISE WHERE id_entreprise = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
