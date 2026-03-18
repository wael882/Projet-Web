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

    public function findByUtilisateur(int $idUtilisateur): array {
        $stmt = $this->pdo->prepare('
            SELECT w.*, o.titre AS titre_offre, e.nom AS nom_entreprise
            FROM WISHLIST w
            JOIN OFFRE o ON w.id_offre = o.id_offre
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE w.id_utilisateur = :id
            ORDER BY w.date_ajout DESC
        ');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    public function getIdOffres(int $idUtilisateur): array {
        $stmt = $this->pdo->prepare('SELECT id_offre FROM WISHLIST WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function add(int $idUtilisateur, int $idOffre): void {
        $stmt = $this->pdo->prepare('
            INSERT IGNORE INTO WISHLIST (id_utilisateur, id_offre) VALUES (:utilisateur, :offre)
        ');
        $stmt->execute([':utilisateur' => $idUtilisateur, ':offre' => $idOffre]);
    }

    public function remove(int $idUtilisateur, int $idOffre): void {
        $stmt = $this->pdo->prepare('
            DELETE FROM WISHLIST WHERE id_utilisateur = :utilisateur AND id_offre = :offre
        ');
        $stmt->execute([':utilisateur' => $idUtilisateur, ':offre' => $idOffre]);
    }
}
