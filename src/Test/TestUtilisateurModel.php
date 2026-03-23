<?php

use PHPUnit\Framework\TestCase;
use App\Models\UtilisateurModel;

class TestUtilisateurModel extends TestCase
{
    public function testFindByEmail() // Retourne un utilisateur existant
    {
        $email = "test@mail.com";

        $donneesAttendues = [
            'id_utilisateur' => 1,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => $email,
            'role' => 'admin'
        ];

        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', 'fetch'])
            ->getMock();

        $statementMock->expects($this->once())
            ->method('execute')
            ->with([':email' => $email]);

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

        $model = new UtilisateurModel($pdoMock);

        $resultat = $model->findByEmail($email);

        $this->assertIsArray($resultat);
        $this->assertEquals($donneesAttendues, $resultat);
    }

    public function testFindByEmailNotFound() // Retourne false si utilisateur introuvable
    {
        $email = "inexistant@mail.com";

        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', 'fetch'])
            ->getMock();

        $statementMock->expects($this->once())
            ->method('execute')
            ->with([':email' => $email]);

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

        $model = new UtilisateurModel($pdoMock);

        $resultat = $model->findByEmail($email);

        $this->assertFalse($resultat);
    }

    public function testCreate() // Retourne l'id du nouvel utilisateur
    {
        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();

        $statementMock->expects($this->once())
            ->method('execute');

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepare', 'lastInsertId'])
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($statementMock);

        $pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn("10");

        $model = new UtilisateurModel($pdoMock);

        $resultat = $model->create("Dupont", "Jean", "test@mail.com", "hash123", 1);

        $this->assertIsInt($resultat);
        $this->assertEquals(10, $resultat);
    }
}