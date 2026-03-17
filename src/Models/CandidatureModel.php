<?php

namespace App\Models;

use App\Database;

class CandidatureModel
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
            SELECT c.*, o.titre AS titre_offre, e.nom AS nom_entreprise
            FROM CANDIDATURE c
            JOIN OFFRE o ON c.id_offre = o.id_offre
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE c.id_etudiant = :id
            ORDER BY c.date_candidature DESC
        ');
        $stmt->execute([':id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    public function create(int $idEtudiant, int $idOffre, string $lettre, $cvFichier = null): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO CANDIDATURE (id_etudiant, id_offre, lettre_motivation, cv_fichier)
            VALUES (:etudiant, :offre, :lettre, :cv)
        ');
        $stmt->execute([
            ':etudiant' => $idEtudiant,
            ':offre'    => $idOffre,
            ':lettre'   => $lettre,
            ':cv'       => $cvFichier,
        ]);
    }
}