<?php

namespace App\Controllers;
use App\Models\UtilisateurModel;
use App\Models\OffreModel;
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
        echo $this->twig->render('inscription.twig',[
        'error' => $_SESSION['error'] ?? null]);
        unset($_SESSION['error']);
    }

    public function identification(){
        echo $this->twig->render('identification.twig',[
        'success' => $_SESSION['success'] ?? null,
        'error' => $_SESSION['error'] ?? null]);
        unset($_SESSION['success']);
        unset($_SESSION['error']);
    }

    public function profil(){
        echo $this->twig->render('profil.twig');
    
    }

    public function favoris(){
        echo $this->twig->render('favoris.twig');
    
    }

    public function offre(){
        $model = new OffreModel();
        $offre = $model->findById($_GET['id']);
        if($offre){
            echo $this->twig->render('offre.twig',['offre' => $offre]);
        }else{
            $_SESSION['error'] = "Un probleme est survenu au niveau de l'affichage de l'offre";
            echo $this->twig->render('rechercher.twig',['offre' => $offre]);
        }
    }

    public function rechercher(){
        $model = new OffreModel();
        $recherche = $model->findAll();
        if ($recherche){
            echo $this->twig->render('rechercher.twig',['rechercher' => $recherche]);
        }else{
            $_SESSION['error'] = "Un probleme est survenu au niveau de l'affichage des offres";
            echo $this->twig->render('rechercher.twig',['rechercher' => $recherche]);

        }
    
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

    
    public function login() {

    $model = new  UtilisateurModel();
    $utilisateur = $model->findByEmail($_POST['email']);

    if (password_verify($_POST['password'], $utilisateur['mot_de_passe_hash'])) {
        $_SESSION['user'] = $utilisateur;
        $_SESSION['tentative'] = 0;
        header("location:/acceuil");
        exit;
    } else {
        echo $this->twig->render('identification.twig',['erreur'=> 'Email ou mot de passe incorect']);
        $_SESSION['tentative'] += 1;
        if($_SESSION['tentative'] == 3){
            header("location:/oubliMdp");
            exit;
        }else {
            return;
        }

    }

    }

    public function inscriptionPost() {
        $model = new UtilisateurModel();
        $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
        $existe = $model->findByEmail($_POST['email']);
        if(!$existe){
        $utilisateur = $model->create($_POST['nom'],$_POST['prenom'],$_POST['email'],$password,3,$_POST['ecole']);

        if($utilisateur){
            $_SESSION['success'] = 'Inscription réussite, connectez vous';
            header("location:/identification");
            exit;
        }else{
        echo $this->twig->render('inscription.twig',['erreur'=> 'Erreur lors de la création du compte']);
            
        }
        }else{
            $_SESSION['error'] = 'Un compte est deja associé a cette adress email';
            header("location:/identification");
            exit;
        }
    }

}

