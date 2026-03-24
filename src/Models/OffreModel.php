<?php

namespace App\Models;

use App\Database;

class OffreModel
{
    private $pdo;

    public function __construct($pdo = null)
    {
        if ($pdo === null) {
            $this->pdo = Database::getInstance()->getPdo();
        } else {
            $this->pdo = $pdo;
        }
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT o.*, e.nom AS nom_entreprise
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE o.active = TRUE
            ORDER BY o.date_offre DESC
        ');
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
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