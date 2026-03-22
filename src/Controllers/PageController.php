<?php

namespace App\Controllers;

use App\Models\UtilisateurModel;
use App\Models\OffreModel;
use App\Models\WishlistModel;
use App\Models\EtudiantModel;
use App\Models\EntrepriseModel;

class PageController
{

    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    private function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /identification');
            exit;
        }
    }
    public function acceuil()
    {
        echo $this->twig->render('acceuil.twig');

    }

    public function candidature()
    {
        $this->requireAuth();
        echo $this->twig->render('candidature.twig');
    }

    public function entreprise()
    {
        $entrepriseModel = new EntrepriseModel();
        $offreModel = new OffreModel();

        $entreprise = $entrepriseModel->findById((int) $_GET['id']);

        if (!$entreprise) {
            $_SESSION['error'] = "Entreprise introuvable.";
            header('Location: /rechercher');
            exit;
        }

        $offresEntreprise = $offreModel->findByEntreprise((int) $_GET['id']);

        echo $this->twig->render('entreprise.twig', [
            'entreprise' => $entreprise,
            'offresEntreprise' => $offresEntreprise
        ]);
    }

    public function inscription()
    {
        echo $this->twig->render('inscription.twig', [
            'error' => $_SESSION['error'] ?? null]);
        unset($_SESSION['error']);
    }

    public function identification()
    {
        echo $this->twig->render('identification.twig', [
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null]);
        unset($_SESSION['success']);
        unset($_SESSION['error']);
    }

    public function profil()
    {
        $this->requireAuth();
        echo $this->twig->render('profil.twig');
    }

    public function favoris(){
        $this->requireAuth();
        $model = new WishlistModel();
        $favoris = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        echo $this->twig->render('favoris.twig',['favoris' => $favoris]);
    }

    public function favorisPost(){
        $this->requireAuth();
        $model = new WishlistModel();
        $model->add((int) $_SESSION['user']['id_utilisateur'], (int) $_POST['id_offre']);
        header("location:" . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function favorisDelete(){
        $this->requireAuth();
        $model = new WishlistModel();
        $model->remove((int) $_SESSION['user']['id_utilisateur'], (int) $_POST['id_offre']);
        header("location:" . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function offre()
    {
        $model = new OffreModel();
        $offre = $model->findById((int) $_GET['id']);
        if($offre){
            $wishlist = new WishlistModel();
            $favorisIds = isset($_SESSION['user']) ? $wishlist->getIdOffres((int) $_SESSION['user']['id_utilisateur']) : [];
            echo $this->twig->render('offre.twig', ['offre' => $offre, 'favorisIds' => $favorisIds]);
        }else{
            $_SESSION['error'] = "Un probleme est survenu au niveau de l'affichage de l'offre";
            echo $this->twig->render('rechercher.twig', ['offre' => $offre]);
        }
    }

    public function rechercher()
    {
        $model = new OffreModel();
        $wishlist = new WishlistModel();
        $pageCourante = $_GET['page'] ?? 1;
        $motCle = trim($_GET['motCle'] ?? '');
        $nombreParPage = 10;
        $debutListe = ($pageCourante - 1) * $nombreParPage;

        if ($motCle !== '') {
            $offres = $model->search($motCle, $nombreParPage, $debutListe);
            $totalOffres = $model->countSearch($motCle);
        } else {
            $offres = $model->findAll($nombreParPage, $debutListe);
            $totalOffres = $model->count();
        }

        $totalPages = ceil($totalOffres / $nombreParPage);
        $favorisIds = isset($_SESSION['user']) ? $wishlist->getIdOffres((int) $_SESSION['user']['id_utilisateur']) : [];
        echo $this->twig->render('rechercher.twig', [
            'offres' => $offres,
            'pageCourante' => $pageCourante,
            'totalPages' => $totalPages,
            'favorisIds' => $favorisIds,
            'motCle' => $motCle
        ]);
    }

    public function index()
    {
        echo $this->twig->render('index.twig');
    }

    public function offre_index()
    {
        $model = new OffreModel();
        $offres = $model->findAll();
        echo $this->twig->render('offre-index.twig', ['offres' => $offres]);
    }

    public function a_propos()
    {
        echo $this->twig->render('a-propos.twig');
    }


    public function login()
    {

        $model = new UtilisateurModel();
        $utilisateur = $model->findByEmail($_POST['email']);

        if (password_verify($_POST['password'], $utilisateur['mot_de_passe_hash'])) {
            $_SESSION['user'] = $utilisateur;
            $_SESSION['tentative'] = 0;
            header("location:/acceuil");
            exit;
        }
        else {
            echo $this->twig->render('identification.twig', ['erreur' => 'Email ou mot de passe incorect']);
            $_SESSION['tentative'] += 1;
            if ($_SESSION['tentative'] == 3) {
                header("location:/oubliMdp");
                exit;
            }
            else {
                return;
            }

        }

    }

    public function logout() {
        session_destroy();
        header("location:/");
        exit;
    }

    public function oubliMdp() {
        echo $this->twig->render('oubli-mdp.twig', [
            'success'    => $_SESSION['success'] ?? null,
            'error'      => $_SESSION['error'] ?? null,
            'reset_link' => $_SESSION['reset_link'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['reset_link']);
    }

    public function oubliMdpPost() {
        $model = new UtilisateurModel();
        $email = trim($_POST['email'] ?? '');

        $utilisateur = $model->findByEmail($email);

        // Message neutre même si l'email n'existe pas (sécurité)
        if ($utilisateur) {
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 heure
            $model->saveResetToken($email, $token, $expiry);

            // Pas de serveur mail en local : on affiche le lien directement
            $lien = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/reinitMdp?token=' . $token;
            $_SESSION['reset_link'] = $lien;
        }

        $_SESSION['success'] = 'Si cet email existe, un lien de réinitialisation a été généré.';
        header('Location: /oubliMdp');
        exit;
    }

    public function reinitMdp() {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            header('Location: /oubliMdp');
            exit;
        }

        $model       = new UtilisateurModel();
        $utilisateur = $model->findByResetToken($token);

        if (!$utilisateur || strtotime($utilisateur['reset_token_expiry']) < time()) {
            $_SESSION['error'] = 'Ce lien est invalide ou expiré.';
            header('Location: /oubliMdp');
            exit;
        }

        echo $this->twig->render('reinit-mdp.twig', [
            'token' => $token,
            'error' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function reinitMdpPost() {
        $token           = $_POST['token'] ?? '';
        $motDePasse      = $_POST['password'] ?? '';
        $confirmation    = $_POST['password_confirm'] ?? '';

        if ($motDePasse !== $confirmation) {
            $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
            header('Location: /reinitMdp?token=' . urlencode($token));
            exit;
        }

        $model       = new UtilisateurModel();
        $utilisateur = $model->findByResetToken($token);

        if (!$utilisateur || strtotime($utilisateur['reset_token_expiry']) < time()) {
            $_SESSION['error'] = 'Ce lien est invalide ou expiré.';
            header('Location: /oubliMdp');
            exit;
        }

        $model->updatePassword($utilisateur['id_utilisateur'], password_hash($motDePasse, PASSWORD_DEFAULT));
        $_SESSION['success'] = 'Mot de passe modifié avec succès. Connectez-vous.';
        header('Location: /identification');
        exit;
    }

    public function inscriptionPost() {
        $model = new UtilisateurModel();
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $existe = $model->findByEmail($_POST['email']);
        if (!$existe) {
            $utilisateur = $model->create($_POST['nom'], $_POST['prenom'], $_POST['email'], $password, 3, $_POST['ecole']);

            if ($utilisateur) {
                $etudiantModel = new EtudiantModel();
                $etudiantModel->create($utilisateur);
                $_SESSION['success'] = 'Inscription réussite, connectez vous';
                header("location:/identification");
                exit;
            }
            else {
                echo $this->twig->render('inscription.twig', ['erreur' => 'Erreur lors de la création du compte']);

            }
        }
        else {
            $_SESSION['error'] = 'Un compte est deja associé a cette adress email';
            header("location:/identification");
            exit;
        }
    }

}