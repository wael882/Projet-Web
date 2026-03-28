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

    public function findByUtilisateur(int $idUtilisateur): array {
        $stmt = $this->pdo->prepare('
            SELECT c.*, o.titre AS titre_offre, e.nom AS nom_entreprise
            FROM CANDIDATURE c
            JOIN OFFRE o ON c.id_offre = o.id_offre
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE c.id_utilisateur = :id
            ORDER BY c.date_candidature DESC
        ');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    public function dejaPostule(int $idUtilisateur, int $idOffre): bool {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM CANDIDATURE
            WHERE id_utilisateur = :utilisateur AND id_offre = :offre
        ');
        $stmt->execute([':utilisateur' => $idUtilisateur, ':offre' => $idOffre]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function updateStatut(int $idCandidature, string $statut): void {
        $stmt = $this->pdo->prepare('
            UPDATE CANDIDATURE SET statut = :statut WHERE id_candidature = :id
        ');
        $stmt->execute([':statut' => $statut, ':id' => $idCandidature]);
    }

    public function compterParOffre(int $idOffre): int {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM CANDIDATURE WHERE id_offre = :idOffre');
        $requete->execute([':idOffre' => $idOffre]);
        return (int) $requete->fetchColumn();
    }

    public function create(int $idUtilisateur, int $idOffre, string $lettre, ?string $cvFichier = null): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO CANDIDATURE (id_utilisateur, id_offre, lettre_motivation, cv_fichier)
            VALUES (:utilisateur, :offre, :lettre, :cv)
        ');
        $stmt->execute([
            ':utilisateur' => $idUtilisateur,
            ':offre'       => $idOffre,
            ':lettre'      => $lettre,
            ':cv'          => $cvFichier,
        ]);
    }
}
