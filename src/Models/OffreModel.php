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

    public function findAll(int $nombreParPage = 10, int $debutListe = 0): array {
        $stmt = $this->pdo->prepare('
            SELECT o.*, e.nom AS nom_entreprise
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE o.active = TRUE
            ORDER BY o.date_offre DESC
            LIMIT :nombreParPage OFFSET :debutListe
        ');
        $stmt->bindValue(':nombreParPage', $nombreParPage, \PDO::PARAM_INT);
        $stmt->bindValue(':debutListe', $debutListe, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM OFFRE WHERE active = TRUE');
        return (int) $stmt->fetchColumn();
    }

    public function search(string $motCle, int $nombreParPage = 10, int $debutListe = 0): array {
        $stmt = $this->pdo->prepare('
            SELECT o.*, e.nom AS nom_entreprise
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE o.active = TRUE
              AND (o.titre LIKE :motCle OR e.nom LIKE :motCle)
            ORDER BY o.date_offre DESC
            LIMIT :nombreParPage OFFSET :debutListe
        ');
        $stmt->bindValue(':motCle', '%' . $motCle . '%');
        $stmt->bindValue(':nombreParPage', $nombreParPage, \PDO::PARAM_INT);
        $stmt->bindValue(':debutListe', $debutListe, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countSearch(string $motCle): int {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*)
            FROM OFFRE o
            JOIN ENTREPRISE e ON o.id_entreprise = e.id_entreprise
            WHERE o.active = TRUE
              AND (o.titre LIKE :motCle OR e.nom LIKE :motCle)
        ');
        $stmt->bindValue(':motCle', '%' . $motCle . '%');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function findByEntreprise(int $idEntreprise): array {
        $stmt = $this->pdo->prepare('
            SELECT o.*
            FROM OFFRE o
            WHERE o.id_entreprise = :idEntreprise AND o.active = TRUE
            ORDER BY o.date_offre DESC
        ');
        $stmt->execute([':idEntreprise' => $idEntreprise]);
        return $stmt->fetchAll();
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

    public function findCompetencesByOffre(int $id): array {
        $stmt = $this->pdo->prepare('
            SELECT c.libelle
            FROM offre_competence oc
            JOIN COMPETENCE c ON oc.id_competence = c.id_competence
            WHERE oc.id_offre = :id
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}