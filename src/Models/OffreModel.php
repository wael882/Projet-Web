<?php

namespace App\Models;

use App\Database;

class OffreModel {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function findAll(int $limite = 10, int $offset = 0): array {
        $stmt = $this->pdo->prepare('
            SELECT o.*, e.nom AS nom_entreprise
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE o.active = TRUE
            ORDER BY o.date_offre DESC
            LIMIT :limite OFFSET :offset
        ');
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM OFFRE WHERE active = TRUE');
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): array|false {
        $stmt = $this->pdo->prepare('
            SELECT o.*, e.nom AS nom_entreprise, e.email_contact, e.telephone_contact
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE o.id_offre = :id
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
