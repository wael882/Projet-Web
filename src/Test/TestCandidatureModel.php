<?php

use PHPUnit\Framework\TestCase;
use App\Models\CandidatureModel;

class FakeStatement
{
    private $resultats;
    private $executedParams;

    public function __construct(array $resultats = array())
    {
        $this->resultats = $resultats;
        $this->executedParams = array();
    }

    public function execute(array $params = array()): bool
    {
        $this->executedParams = $params;
        return true;
    }

    public function fetchAll(): array
    {
        return $this->resultats;
    }

    public function getExecutedParams(): array
    {
        return $this->executedParams;
    }
}

class FakePdo
{
    private $resultats;
    private $lastStatement;
    private $lastQuery;

    public function __construct(array $resultats = array())
    {
        $this->resultats = $resultats;
        $this->lastStatement = null;
        $this->lastQuery = '';
    }

    public function prepare(string $query)
    {
        $this->lastQuery = $query;
        $this->lastStatement = new FakeStatement($this->resultats);
        return $this->lastStatement;
    }

    public function getLastStatement()
    {
        return $this->lastStatement;
    }

    public function getLastQuery(): string
    {
        return $this->lastQuery;
    }
}

class TestCandidatureModel extends TestCase
{
    public function testFindByEtudiantUtiliseLaBaseOuUnObjetVirtuel(): void
    {
        $resultats = array();

        try {
            $modelReel = new CandidatureModel();
            $resultats = $modelReel->findByEtudiant(1);
        } catch (\Throwable $e) {
            $resultats = array();
        }

        if (!empty($resultats)) {
            $this->assertIsArray($resultats);
            $this->assertArrayHasKey('titre_offre', $resultats[0]);
            $this->assertArrayHasKey('nom_entreprise', $resultats[0]);
            return;
        }

        $donneesVirtuelles = array(
            array(
                'id_candidature'   => 1,
                'id_etudiant'      => 1,
                'id_offre'         => 2,
                'lettre_motivation'=> 'Je suis très motivé',
                'cv_fichier'       => 'cv_test.pdf',
                'date_candidature' => '2026-03-17',
                'titre_offre'      => 'Développeur PHP',
                'nom_entreprise'   => 'OpenAI SARL'
            )
        );

        $fakePdo = new FakePdo($donneesVirtuelles);
        $model = new CandidatureModel($fakePdo);

        $resultat = $model->findByEtudiant(1);

        $this->assertIsArray($resultat);
        $this->assertCount(1, $resultat);
        $this->assertEquals('Développeur PHP', $resultat[0]['titre_offre']);
        $this->assertEquals('OpenAI SARL', $resultat[0]['nom_entreprise']);
        $this->assertEquals(1, $resultat[0]['id_etudiant']);
    }

    public function testCreateAvecObjetVirtuelEtCv(): void
    {
        $fakePdo = new FakePdo();
        $model = new CandidatureModel($fakePdo);

        $model->create(3, 7, 'Lettre de motivation test', 'cv_test.pdf');

        $stmt = $fakePdo->getLastStatement();

        $this->assertStringContainsString('INSERT INTO CANDIDATURE', $fakePdo->getLastQuery());
        $this->assertEquals(array(
            ':etudiant' => 3,
            ':offre'    => 7,
            ':lettre'   => 'Lettre de motivation test',
            ':cv'       => 'cv_test.pdf',
        ), $stmt->getExecutedParams());
    }

    public function testCreateAvecObjetVirtuelSansCv(): void
    {
        $fakePdo = new FakePdo();
        $model = new CandidatureModel($fakePdo);

        $model->create(4, 9, 'Autre lettre', null);

        $stmt = $fakePdo->getLastStatement();

        $this->assertEquals(array(
            ':etudiant' => 4,
            ':offre'    => 9,
            ':lettre'   => 'Autre lettre',
            ':cv'       => null,
        ), $stmt->getExecutedParams());
    }
}