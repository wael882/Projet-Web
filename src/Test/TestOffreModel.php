<?php

require_once __DIR__ . '/../Models/OffreModel.php';
require_once __DIR__ . '/../Database.php';

use PHPUnit\Framework\TestCase;
use App\Models\OffreModel;

class FakeStatementOffre
{
    private $resultats;
    private $resultatUnique;
    private $executedParams;

    public function __construct(array $resultats = array(), $resultatUnique = false)
    {
        $this->resultats = $resultats;
        $this->resultatUnique = $resultatUnique;
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

    public function fetch()
    {
        return $this->resultatUnique;
    }

    public function getExecutedParams(): array
    {
        return $this->executedParams;
    }
}

class FakePdoOffre
{
    private $resultatsQuery;
    private $resultatPrepare;
    private $lastStatement;
    private $lastQuery;
    private $lastPreparedQuery;

    public function __construct(array $resultatsQuery = array(), $resultatPrepare = false)
    {
        $this->resultatsQuery = $resultatsQuery;
        $this->resultatPrepare = $resultatPrepare;
        $this->lastStatement = null;
        $this->lastQuery = '';
        $this->lastPreparedQuery = '';
    }

    public function query(string $query)
    {
        $this->lastQuery = $query;
        $this->lastStatement = new FakeStatementOffre($this->resultatsQuery, false);
        return $this->lastStatement;
    }

    public function prepare(string $query)
    {
        $this->lastPreparedQuery = $query;
        $this->lastStatement = new FakeStatementOffre(array(), $this->resultatPrepare);
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

    public function getLastPreparedQuery(): string
    {
        return $this->lastPreparedQuery;
    }
}

class TestOffreModel extends TestCase
{
    public function testFindAllAvecObjetVirtuel(): void
    {
        $donneesVirtuelles = array(
            array(
                'id_offre' => 1,
                'id_entreprise' => 2,
                'titre' => 'Stage Développeur Web',
                'description' => 'Développement PHP MVC',
                'active' => 1,
                'date_offre' => '2026-03-17',
                'nom_entreprise' => 'TechCorp'
            ),
            array(
                'id_offre' => 2,
                'id_entreprise' => 3,
                'titre' => 'Alternance Backend',
                'description' => 'API et base de données',
                'active' => 1,
                'date_offre' => '2026-03-16',
                'nom_entreprise' => 'DataSoft'
            )
        );

        $fakePdo = new FakePdoOffre($donneesVirtuelles);
        $model = new OffreModel($fakePdo);

        $resultat = $model->findAll();

        $this->assertIsArray($resultat);
        $this->assertCount(2, $resultat);
        $this->assertEquals('Stage Développeur Web', $resultat[0]['titre']);
        $this->assertEquals('TechCorp', $resultat[0]['nom_entreprise']);
        $this->assertStringContainsString('SELECT o.*, e.nom AS nom_entreprise', $fakePdo->getLastQuery());
    }

    public function testFindByIdAvecObjetVirtuel(): void
    {
        $offreVirtuelle = array(
            'id_offre' => 5,
            'id_entreprise' => 8,
            'titre' => 'Développeur PHP',
            'description' => 'Mission backend',
            'active' => 1,
            'date_offre' => '2026-03-17',
            'nom_entreprise' => 'OpenAI SARL',
            'email_contact' => 'contact@openai.test',
            'telephone_contact' => '0102030405'
        );

        $fakePdo = new FakePdoOffre(array(), $offreVirtuelle);
        $model = new OffreModel($fakePdo);

        $resultat = $model->findById(5);
        $stmt = $fakePdo->getLastStatement();

        $this->assertIsArray($resultat);
        $this->assertEquals(5, $resultat['id_offre']);
        $this->assertEquals('Développeur PHP', $resultat['titre']);
        $this->assertEquals('OpenAI SARL', $resultat['nom_entreprise']);
        $this->assertEquals('contact@openai.test', $resultat['email_contact']);
        $this->assertEquals(array(':id' => 5), $stmt->getExecutedParams());
        $this->assertStringContainsString('WHERE o.id_offre = :id', $fakePdo->getLastPreparedQuery());
    }

    public function testFindByIdRetourneFalseSiOffreIntrouvable(): void
    {
        $fakePdo = new FakePdoOffre(array(), false);
        $model = new OffreModel($fakePdo);

        $resultat = $model->findById(999);

        $this->assertFalse($resultat);
    }
}
