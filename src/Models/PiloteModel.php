<?php

namespace App\Models;

use App\Database;

class PiloteModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function findByUtilisateur(int $idUtilisateur): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM PILOTE WHERE id_utilisateur = :id
        ');
        $stmt->execute([':id' => $idUtilisateur]);
        return $stmt->fetch();
    }

    public function getEtudiants(int $idPilote): array
    {
        $stmt = $this->pdo->prepare('
            SELECT e.*, u.nom, u.prenom, u.email,
                   COUNT(c.id_candidature) AS nb_candidatures
            FROM ETUDIANT e
            JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
            LEFT JOIN CANDIDATURE c ON c.id_utilisateur = e.id_utilisateur
            WHERE e.id_pilote = :id_pilote
            GROUP BY e.id_etudiant
            ORDER BY u.nom, u.prenom
        ');
        $stmt->execute([':id_pilote' => $idPilote]);
        return $stmt->fetchAll();
    }

    public function getEtudiant(int $idEtudiant, int $idPilote): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT e.*, u.nom, u.prenom, u.email
            FROM ETUDIANT e
            JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
            WHERE e.id_etudiant = :id AND e.id_pilote = :id_pilote
        ');
        $stmt->execute([':id' => $idEtudiant, ':id_pilote' => $idPilote]);
        return $stmt->fetch();
    }

    public function supprimerEtudiant(int $idEtudiant, int $idPilote): bool
    {
        // Vérifie que l'étudiant appartient bien à ce pilote
        $etudiant = $this->getEtudiant($idEtudiant, $idPilote);
        if (!$etudiant) return false;

        $stmt = $this->pdo->prepare('DELETE FROM ETUDIANT WHERE id_etudiant = :id');
        $stmt->execute([':id' => $idEtudiant]);

        $stmt = $this->pdo->prepare('DELETE FROM UTILISATEUR WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $etudiant['id_utilisateur']]);

        return true;
    }

    public function creerEtudiant(int $idPilote, string $nom, string $prenom, string $email, string $motDePasseHash, string $ecole): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe_hash, id_role, ecole)
            VALUES (:nom, :prenom, :email, :hash, 3, :ecole)
        ');
        $stmt->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':hash'   => $motDePasseHash,
            ':ecole'  => $ecole,
        ]);
        $idUtilisateur = (int) $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare('
            INSERT INTO ETUDIANT (id_utilisateur, id_pilote) VALUES (:id_utilisateur, :id_pilote)
        ');
        $stmt->execute([':id_utilisateur' => $idUtilisateur, ':id_pilote' => $idPilote]);
        return true;
    }

    public function getCandidaturesEtudiant(int $idUtilisateur): array
    {
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
}
