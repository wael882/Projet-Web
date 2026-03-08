<?php

namespace App\Controllers;

class PageController {

private $twig;

public function __construct($twig) {
    $this->twig = $twig;
}


    public function acceuil() {
        echo $this->twig->render('acceuil.twig');
    
    }

    public function candidature(){
        echo $this->twig->render('candidature.twig');
    }

    public function entreprise(){
        echo $this->twig->render('entreprise.twig');
    }

    public function inscription(){
        echo $this->twig->render('inscription.twig');
    }

    public function identification(){
        echo $this->twig->render('identification.twig');
    }

    public function profil(){
        echo $this->twig->render('profil.twig');
    
    }

    public function favoris(){
        echo $this->twig->render('favoris.twig');
    
    }

    public function offre(){
        echo $this->twig->render('offre.twig');
    
    }

    public function rechercher(){
        echo $this->twig->render('rechercher.twig');
    
    }

    public function index() {
    echo $this->twig->render('index.twig');
    }

    public function offre_index() {
    echo $this->twig->render('offre-index.twig');
    }

    public function a_propos() {
    echo $this->twig->render('a-propos.twig');
    
}

}