<?php

use PHPUnit\Framework\TestCase;
use App\Models\EntrepriseModel;

class TestEntrepriseModel extends TestCase
{
    public function testFindAll() // Retourne les entreprises actives
    {
        $donneesAttendues = [
            [
                'id_entreprise' => 1,
                'nom' => 'Apple',
                'active' => true
            ],
            [
                'id_entreprise' => 2,
                'nom' => 'Microsoft',
                'active' => true
            ]
        ];

        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();

        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($donneesAttendues);

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['query'])
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($statementMock);

        $model = new EntrepriseModel($pdoMock);

        $resultat = $model->findAll();

        $this->assertIsArray($resultat);
        $this->assertCount(2, $resultat);
        $this->assertEquals($donneesAttendues, $resultat);
    }

    public function testFindById() // Retourne une entreprise existante
    {
        $idEntreprise = 5;

        $donneesAttendues = [
            'id_entreprise' => 5,
            'nom' => 'Capgemini',
            'active' => true
        ];

        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', 'fetch'])
            ->getMock();

        $statementMock->expects($this->once())
            ->method('execute')
            ->with([':id' => $idEntreprise]);

        $statementMock->expects($this->once())
            ->method('fetch')
            ->willReturn($donneesAttendues);

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepare'])
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($statementMock);

        $model = new EntrepriseModel($pdoMock);

        $resultat = $model->findById($idEntreprise);

        $this->assertIsArray($resultat);
        $this->assertEquals($donneesAttendues, $resultat);
    }

    public function testFindByIdNotFound() // Retourne false si introuvable
    {
        $idEntreprise = 999;

        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', 'fetch'])
            ->getMock();

        $statementMock->expects($this->once())
            ->method('execute')
            ->with([':id' => $idEntreprise]);

        $statementMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepare'])
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($statementMock);

        $model = new EntrepriseModel($pdoMock);

        $resultat = $model->findById($idEntreprise);

        $this->assertFalse($resultat);
    }
}