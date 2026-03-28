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
        $requete = $this->pdo->prepare('
            SELECT OFFRE.*, ENTREPRISE.nom AS nom_entreprise
            FROM OFFRE
            JOIN ENTREPRISE ON OFFRE.id_entreprise = ENTREPRISE.id_entreprise
            WHERE OFFRE.active = TRUE
            ORDER BY OFFRE.date_offre DESC
            LIMIT :nombreParPage OFFSET :debutListe
        ');
        $requete->bindValue(':nombreParPage', $nombreParPage, \PDO::PARAM_INT);
        $requete->bindValue(':debutListe', $debutListe, \PDO::PARAM_INT);
        $requete->execute();
        return $requete->fetchAll();
    }

    public function count(): int {
        $requete = $this->pdo->query('SELECT COUNT(*) FROM OFFRE WHERE active = TRUE');
        return (int) $requete->fetchColumn();
    }

    public function search(string $motCle, int $nombreParPage = 10, int $debutListe = 0): array {
        $requete = $this->pdo->prepare('
            SELECT OFFRE.*, ENTREPRISE.nom AS nom_entreprise
            FROM OFFRE
            JOIN ENTREPRISE ON OFFRE.id_entreprise = ENTREPRISE.id_entreprise
            WHERE OFFRE.active = TRUE
              AND (OFFRE.titre LIKE :motCle OR ENTREPRISE.nom LIKE :motCle)
            ORDER BY OFFRE.date_offre DESC
            LIMIT :nombreParPage OFFSET :debutListe
        ');
        $requete->bindValue(':motCle', '%' . $motCle . '%');
        $requete->bindValue(':nombreParPage', $nombreParPage, \PDO::PARAM_INT);
        $requete->bindValue(':debutListe', $debutListe, \PDO::PARAM_INT);
        $requete->execute();
        return $requete->fetchAll();
    }

    public function countSearch(string $motCle): int {
        $requete = $this->pdo->prepare('
            SELECT COUNT(*)
            FROM OFFRE
            JOIN ENTREPRISE ON OFFRE.id_entreprise = ENTREPRISE.id_entreprise
            WHERE OFFRE.active = TRUE
              AND (OFFRE.titre LIKE :motCle OR ENTREPRISE.nom LIKE :motCle)
        ');
        $requete->bindValue(':motCle', '%' . $motCle . '%');
        $requete->execute();
        return (int) $requete->fetchColumn();
    }

    public function findByEntreprise(int $idEntreprise): array {
        $requete = $this->pdo->prepare('
            SELECT OFFRE.*
            FROM OFFRE
            WHERE OFFRE.id_entreprise = :idEntreprise AND OFFRE.active = TRUE
            ORDER BY OFFRE.date_offre DESC
        ');
        $requete->execute([':idEntreprise' => $idEntreprise]);
        return $requete->fetchAll();
    }

    public function findById(int $id): array|false {
        $requete = $this->pdo->prepare('
            SELECT OFFRE.*, ENTREPRISE.nom AS nom_entreprise, ENTREPRISE.email_contact, ENTREPRISE.telephone_contact, ENTREPRISE.logo AS logo_entreprise
            FROM OFFRE
            JOIN ENTREPRISE ON OFFRE.id_entreprise = ENTREPRISE.id_entreprise
            WHERE OFFRE.id_offre = :id
        ');
        $requete->execute([':id' => $id]);
        return $requete->fetch();
    }

    public function creer(int $idEntreprise, string $titre, string $description, ?float $remunerationBase, ?string $dateOffre, ?string $photo = null): int {
        $requete = $this->pdo->prepare('
            INSERT INTO OFFRE (titre, description, remuneration_base, date_offre, photo, id_entreprise)
            VALUES (:titre, :description, :remunerationBase, :dateOffre, :photo, :idEntreprise)
        ');
        $requete->execute([
            ':titre'            => $titre,
            ':description'      => $description,
            ':remunerationBase' => $remunerationBase,
            ':dateOffre'        => $dateOffre,
            ':photo'            => $photo,
            ':idEntreprise'     => $idEntreprise,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function ajouterCompetence(int $idOffre, int $idCompetence): void {
        $requete = $this->pdo->prepare('
            INSERT IGNORE INTO offre_competence (id_offre, id_competence) VALUES (:idOffre, :idCompetence)
        ');
        $requete->execute([':idOffre' => $idOffre, ':idCompetence' => $idCompetence]);
    }

    public function listerCompetences(): array {
        $requete = $this->pdo->query('SELECT id_competence, libelle FROM COMPETENCE ORDER BY libelle ASC');
        return $requete->fetchAll();
    }

    public function creerOuTrouverCompetence(string $libelle): int {
        $stmt = $this->pdo->prepare('SELECT id_competence FROM COMPETENCE WHERE libelle = :libelle');
        $stmt->execute([':libelle' => $libelle]);
        $idExistant = $stmt->fetchColumn();
        if ($idExistant !== false) {
            return (int) $idExistant;
        }
        $stmt = $this->pdo->prepare('INSERT INTO COMPETENCE (libelle) VALUES (:libelle)');
        $stmt->execute([':libelle' => $libelle]);
        return (int) $this->pdo->lastInsertId();
    }

    public function listerTitres(): array {
        $requete = $this->pdo->query('SELECT DISTINCT titre FROM OFFRE WHERE active = TRUE ORDER BY titre ASC');
        return $requete->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function rechercheAvancee(array $filtres, int $nombreParPage = 10, int $debut = 0): array {
        $conditions = ['OFFRE.active = TRUE'];
        $parametres = [];

        if (!empty($filtres['motCle'])) {
            $conditions[] = '(OFFRE.titre LIKE :motCle OR ENTREPRISE.nom LIKE :motCle OR OFFRE.description LIKE :motCle)';
            $parametres[':motCle'] = '%' . $filtres['motCle'] . '%';
        }
        if (!empty($filtres['titre'])) {
            $conditions[] = 'OFFRE.titre LIKE :titre';
            $parametres[':titre'] = '%' . $filtres['titre'] . '%';
        }
        if (!empty($filtres['entreprise'])) {
            $conditions[] = 'ENTREPRISE.nom LIKE :entreprise';
            $parametres[':entreprise'] = '%' . $filtres['entreprise'] . '%';
        }
        if (!empty($filtres['competence'])) {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM offre_competence
                JOIN COMPETENCE ON offre_competence.id_competence = COMPETENCE.id_competence
                WHERE offre_competence.id_offre = OFFRE.id_offre
                  AND COMPETENCE.libelle = :competence
            )';
            $parametres[':competence'] = $filtres['competence'];
        }
        if (isset($filtres['remuneration_min']) && $filtres['remuneration_min'] !== '') {
            $conditions[] = 'OFFRE.remuneration_base >= :remuneration_min';
            $parametres[':remuneration_min'] = (float) $filtres['remuneration_min'];
        }
        if (isset($filtres['remuneration_max']) && $filtres['remuneration_max'] !== '') {
            $conditions[] = 'OFFRE.remuneration_base <= :remuneration_max';
            $parametres[':remuneration_max'] = (float) $filtres['remuneration_max'];
        }

        $clause = implode(' AND ', $conditions);
        $sql = "
            SELECT OFFRE.*, ENTREPRISE.nom AS nom_entreprise
            FROM OFFRE
            JOIN ENTREPRISE ON OFFRE.id_entreprise = ENTREPRISE.id_entreprise
            WHERE $clause
            ORDER BY OFFRE.date_offre DESC
            LIMIT :nombreParPage OFFSET :debut
        ";

        $requete = $this->pdo->prepare($sql);
        foreach ($parametres as $cle => $valeur) {
            $requete->bindValue($cle, $valeur);
        }
        $requete->bindValue(':nombreParPage', $nombreParPage, \PDO::PARAM_INT);
        $requete->bindValue(':debut', $debut, \PDO::PARAM_INT);
        $requete->execute();
        return $requete->fetchAll();
    }

    public function compterRechercheAvancee(array $filtres): int {
        $conditions = ['OFFRE.active = TRUE'];
        $parametres = [];

        if (!empty($filtres['motCle'])) {
            $conditions[] = '(OFFRE.titre LIKE :motCle OR ENTREPRISE.nom LIKE :motCle OR OFFRE.description LIKE :motCle)';
            $parametres[':motCle'] = '%' . $filtres['motCle'] . '%';
        }
        if (!empty($filtres['titre'])) {
            $conditions[] = 'OFFRE.titre LIKE :titre';
            $parametres[':titre'] = '%' . $filtres['titre'] . '%';
        }
        if (!empty($filtres['entreprise'])) {
            $conditions[] = 'ENTREPRISE.nom LIKE :entreprise';
            $parametres[':entreprise'] = '%' . $filtres['entreprise'] . '%';
        }
        if (!empty($filtres['competence'])) {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM offre_competence
                JOIN COMPETENCE ON offre_competence.id_competence = COMPETENCE.id_competence
                WHERE offre_competence.id_offre = OFFRE.id_offre
                  AND COMPETENCE.libelle = :competence
            )';
            $parametres[':competence'] = $filtres['competence'];
        }
        if (isset($filtres['remuneration_min']) && $filtres['remuneration_min'] !== '') {
            $conditions[] = 'OFFRE.remuneration_base >= :remuneration_min';
            $parametres[':remuneration_min'] = (float) $filtres['remuneration_min'];
        }
        if (isset($filtres['remuneration_max']) && $filtres['remuneration_max'] !== '') {
            $conditions[] = 'OFFRE.remuneration_base <= :remuneration_max';
            $parametres[':remuneration_max'] = (float) $filtres['remuneration_max'];
        }

        $clause = implode(' AND ', $conditions);
        $sql = "
            SELECT COUNT(*)
            FROM OFFRE
            JOIN ENTREPRISE ON OFFRE.id_entreprise = ENTREPRISE.id_entreprise
            WHERE $clause
        ";

        $requete = $this->pdo->prepare($sql);
        foreach ($parametres as $cle => $valeur) {
            $requete->bindValue($cle, $valeur);
        }
        $requete->execute();
        return (int) $requete->fetchColumn();
    }

    public function findCompetencesByOffre(int $id): array {
        $requete = $this->pdo->prepare('
            SELECT COMPETENCE.libelle
            FROM offre_competence
            JOIN COMPETENCE ON offre_competence.id_competence = COMPETENCE.id_competence
            WHERE offre_competence.id_offre = :id
        ');
        $requete->execute([':id' => $id]);
        return $requete->fetchAll(\PDO::FETCH_COLUMN);
    }
}
