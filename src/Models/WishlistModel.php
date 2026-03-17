<?php

namespace App\Models;

use App\Database;

class WishlistModel
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

    public function findByEtudiant(int $idEtudiant): array
    {
        $stmt = $this->pdo->prepare('
            SELECT w.*, o.titre AS titre_offre, e.nom AS nom_entreprise
            FROM WISHLIST w
            JOIN OFFRE o ON w.id_offre = o.id_offre
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE w.id_etudiant = :id
            ORDER BY w.date_ajout DESC
        ');
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    public function add(int $idEtudiant, int $idOffre): void
    {
        $stmt = $this->pdo->prepare('
            INSERT IGNORE INTO WISHLIST (id_etudiant, id_offre)
            VALUES (:etudiant, :offre)
        ');
        $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':offre' => $idOffre
        ]);
    }

    public function remove(int $idEtudiant, int $idOffre): void
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM WISHLIST
            WHERE id_etudiant = :etudiant AND id_offre = :offre
        ');
        $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':offre' => $idOffre
        ]);
    }
}
