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
                SELECT * FROM ENTREPRISE
                WHERE active = TRUE
                  AND (nom LIKE :search OR description LIKE :search OR email_contact LIKE :search)
                ORDER BY nom ASC LIMIT :limite OFFSET :offset
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
            $stmt = $this->pdo->prepare('
                SELECT COUNT(*) FROM ENTREPRISE
                WHERE active = TRUE
                  AND (nom LIKE :search OR description LIKE :search OR email_contact LIKE :search)
            ');
            $stmt->execute([':search' => '%' . $search . '%']);
        } else {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM ENTREPRISE WHERE active = TRUE');
        }
        return (int) $stmt->fetchColumn();
    }

    public function getEvaluations(int $idEntreprise): array {
        $stmt = $this->pdo->prepare('
            SELECT ev.id_evaluation, ev.id_etudiant, ev.note, ev.commentaire, ev.date_evaluation, u.prenom, u.nom
            FROM EVALUATION_ENTREPRISE ev
            JOIN ETUDIANT e ON ev.id_etudiant = e.id_etudiant
            JOIN UTILISATEUR u ON e.id_utilisateur = u.id_utilisateur
            WHERE ev.id_entreprise = :id
            ORDER BY ev.date_evaluation DESC
        ');
        $stmt->execute([':id' => $idEntreprise]);
        return $stmt->fetchAll();
    }

    public function modifierEvaluation(int $idEvaluation, int $idEtudiant, int $note, string $commentaire): void {
        $stmt = $this->pdo->prepare('
            UPDATE EVALUATION_ENTREPRISE
            SET note = :note, commentaire = :commentaire
            WHERE id_evaluation = :id AND id_etudiant = :etudiant
        ');
        $stmt->execute([
            ':note'        => $note,
            ':commentaire' => $commentaire,
            ':id'          => $idEvaluation,
            ':etudiant'    => $idEtudiant,
        ]);
    }

    public function supprimerEvaluation(int $idEvaluation, int $idEtudiant): void {
        $stmt = $this->pdo->prepare('
            DELETE FROM EVALUATION_ENTREPRISE
            WHERE id_evaluation = :id AND id_etudiant = :etudiant
        ');
        $stmt->execute([':id' => $idEvaluation, ':etudiant' => $idEtudiant]);
    }

    public function dejaEvalue(int $idEtudiant, int $idEntreprise): bool {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM EVALUATION_ENTREPRISE
            WHERE id_etudiant = :etudiant AND id_entreprise = :entreprise
        ');
        $stmt->execute([':etudiant' => $idEtudiant, ':entreprise' => $idEntreprise]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function evaluer(int $idEtudiant, int $idEntreprise, int $note, string $commentaire): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO EVALUATION_ENTREPRISE (id_etudiant, id_entreprise, note, commentaire)
            VALUES (:etudiant, :entreprise, :note, :commentaire)
        ');
        $stmt->execute([
            ':etudiant'   => $idEtudiant,
            ':entreprise' => $idEntreprise,
            ':note'       => $note,
            ':commentaire'=> $commentaire,
        ]);
    }

    public function demanderCreation(string $nom, string $description, string $email, string $telephone, string $ville, string $siteWeb): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO ENTREPRISE (nom, description, email_contact, telephone_contact, ville, site_web, active, statut)
            VALUES (:nom, :description, :email, :telephone, :ville, :site_web, FALSE, "en_attente")
        ');
        $stmt->execute([
            ':nom'         => $nom,
            ':description' => $description,
            ':email'       => $email,
            ':telephone'   => $telephone,
            ':ville'       => $ville,
            ':site_web'    => $siteWeb,
        ]);
    }

    public function getDemandesEnAttente(): array {
        $stmt = $this->pdo->query('
            SELECT * FROM ENTREPRISE WHERE statut = "en_attente" ORDER BY date_creation DESC
        ');
        return $stmt->fetchAll();
    }

    public function approuver(int $id): void {
        $this->pdo->prepare('
            UPDATE ENTREPRISE SET statut = "approuvee", active = TRUE WHERE id_entreprise = :id
        ')->execute([':id' => $id]);
    }

    public function rejeter(int $id): void {
        $this->pdo->prepare('
            UPDATE ENTREPRISE SET statut = "rejetee", active = FALSE WHERE id_entreprise = :id
        ')->execute([':id' => $id]);
    }

    public function findById(int $id): array|false {
        $stmt = $this->pdo->prepare('
            SELECT e.*,
                COUNT(DISTINCT c.id_utilisateur) AS nb_stagiaires,
                ROUND(AVG(ev.note), 1)           AS moyenne_evaluation,
                COUNT(DISTINCT ev.id_evaluation) AS nb_evaluations
            FROM ENTREPRISE e
            LEFT JOIN OFFRE o              ON o.id_entreprise  = e.id_entreprise
            LEFT JOIN CANDIDATURE c        ON c.id_offre       = o.id_offre
            LEFT JOIN EVALUATION_ENTREPRISE ev ON ev.id_entreprise = e.id_entreprise
            WHERE e.id_entreprise = :id
            GROUP BY e.id_entreprise
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
