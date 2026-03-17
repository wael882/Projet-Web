<?php

require_once __DIR__ . '/../Models/WishlistModel.php';

use PHPUnit\Framework\TestCase;
use App\Models\WishlistModel;

class FakeStatementWishlist
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

class FakePdoWishlist
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
        $this->lastStatement = new FakeStatementWishlist($this->resultats);
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

class TestWishlistModel extends TestCase
{
    public function testFindByEtudiantAvecObjetVirtuel(): void
    {
        $donneesVirtuelles = array(
            array(
                'id_etudiant' => 1,
                'id_offre' => 5,
                'date_ajout' => '2026-03-17',
                'titre_offre' => 'Stage PHP',
                'nom_entreprise' => 'TechCorp'
            )
        );

        $fakePdo = new FakePdoWishlist($donneesVirtuelles);
        $model = new WishlistModel($fakePdo);

        $resultat = $model->findByEtudiant(1);

        $this->assertIsArray($resultat);
        $this->assertCount(1, $resultat);
        $this->assertEquals('Stage PHP', $resultat[0]['titre_offre']);
        $this->assertEquals('TechCorp', $resultat[0]['nom_entreprise']);
    }

    public function testAddWishlist(): void
    {
        $fakePdo = new FakePdoWishlist();
        $model = new WishlistModel($fakePdo);

        $model->add(3, 7);

        $stmt = $fakePdo->getLastStatement();

        $this->assertStringContainsString('INSERT IGNORE INTO WISHLIST', $fakePdo->getLastQuery());
        $this->assertEquals(array(
            ':etudiant' => 3,
            ':offre' => 7
        ), $stmt->getExecutedParams());
    }

    public function testRemoveWishlist(): void
    {
        $fakePdo = new FakePdoWishlist();
        $model = new WishlistModel($fakePdo);

        $model->remove(3, 7);

        $stmt = $fakePdo->getLastStatement();

        $this->assertStringContainsString('DELETE FROM WISHLIST', $fakePdo->getLastQuery());
        $this->assertEquals(array(
            ':etudiant' => 3,
            ':offre' => 7
        ), $stmt->getExecutedParams());
    }
}
