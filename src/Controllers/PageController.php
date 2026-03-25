<?php

namespace App\Controllers;

use App\Models\UtilisateurModel;
use App\Models\OffreModel;
use App\Models\WishlistModel;
use App\Models\EtudiantModel;
use App\Models\EntrepriseModel;
use App\Models\CandidatureModel;
use App\Models\PiloteModel;

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

    private function requireRole(string $role): void
    {
        $this->requireAuth();
        if (($_SESSION['user']['role'] ?? '') !== $role) {
            $_SESSION['error'] = 'Accès refusé.';
            header('Location: /acceuil');
            exit;
        }
    }

    public function pilote()
    {
        $this->requireRole('pilote');
        $model  = new PiloteModel();
        $pilote = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);

        if (!$pilote) {
            session_destroy();
            header('Location: /identification?error=pilote_introuvable');
            exit;
        }

        $etudiants = $model->getEtudiants((int) $pilote['id_pilote']);
        echo $this->twig->render('pilote/dashboard.twig', [
            'etudiants' => $etudiants,
            'success'   => $_SESSION['success'] ?? null,
            'error'     => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function piloteEtudiant()
    {
        $this->requireRole('pilote');
        $model  = new PiloteModel();
        $pilote = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);

        $etudiant = $model->getEtudiant((int) ($_GET['id'] ?? 0), (int) $pilote['id_pilote']);
        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /pilote');
            exit;
        }

        $candidatures = $model->getCandidaturesEtudiant((int) $etudiant['id_utilisateur']);
        echo $this->twig->render('pilote/etudiant.twig', [
            'etudiant'     => $etudiant,
            'candidatures' => $candidatures,
            'success'      => $_SESSION['success'] ?? null,
            'error'        => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function piloteCreerEtudiant()
    {
        $this->requireRole('pilote');

        $nom    = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $ecole  = trim($_POST['ecole'] ?? '');
        $mdp    = $_POST['password'] ?? '';

        if (!$nom || !$prenom || !$email || !$ecole || !$mdp) {
            $_SESSION['error'] = 'Tous les champs sont obligatoires.';
            header('Location: /pilote');
            exit;
        }

        $utilisateurModel = new UtilisateurModel();
        if ($utilisateurModel->findByEmail($email)) {
            $_SESSION['error'] = 'Un compte existe déjà avec cet email.';
            header('Location: /pilote');
            exit;
        }

        $model  = new PiloteModel();
        $pilote = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        $model->creerEtudiant((int) $pilote['id_pilote'], $nom, $prenom, $email, password_hash($mdp, PASSWORD_DEFAULT), $ecole);

        $_SESSION['success'] = 'Compte étudiant créé avec succès.';
        header('Location: /pilote');
        exit;
    }

    public function piloteSupprimerEtudiant()
    {
        $this->requireRole('pilote');
        $idEtudiant = (int) ($_POST['id_etudiant'] ?? 0);

        $model  = new PiloteModel();
        $pilote = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);

        if (!$model->supprimerEtudiant($idEtudiant, (int) $pilote['id_pilote'])) {
            $_SESSION['error'] = 'Impossible de supprimer cet étudiant.';
        } else {
            $_SESSION['success'] = 'Compte étudiant supprimé.';
        }

        header('Location: /pilote');
        exit;
    }

    public function piloteUpdateStatut()
    {
        $this->requireRole('pilote');
        $statutsValides = ['envoyee', 'vue', 'acceptee', 'refusee'];
        $statut         = $_POST['statut'] ?? '';
        $idCandidature  = (int) ($_POST['id_candidature'] ?? 0);
        $idEtudiant     = (int) ($_POST['id_etudiant'] ?? 0);

        if (!in_array($statut, $statutsValides) || !$idCandidature) {
            $_SESSION['error'] = 'Données invalides.';
            header('Location: /pilote/etudiant?id=' . $idEtudiant);
            exit;
        }

        $model = new CandidatureModel();
        $model->updateStatut($idCandidature, $statut);
        $_SESSION['success'] = 'Statut mis à jour.';
        header('Location: /pilote/etudiant?id=' . $idEtudiant);
        exit;
    }
    public function acceuil()
    {
        $this->requireAuth();

        $role = $_SESSION['user']['role'] ?? '';
        if ($role === 'pilote') {
            header('Location: /pilote');
            exit;
        }
        if ($role === 'admin') {
            header('Location: /admin');
            exit;
        }

        $candidatureModel = new CandidatureModel();
        $wishlistModel    = new WishlistModel();

        $candidatures = $candidatureModel->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        $nbFavoris    = count($wishlistModel->getIdOffres((int) $_SESSION['user']['id_utilisateur']));

        echo $this->twig->render('acceuil.twig', [
            'candidatures'       => $candidatures,
            'nbCandidatures'     => count($candidatures),
            'nbFavoris'          => $nbFavoris,
            'dernieresCandidatures' => array_slice($candidatures, 0, 3),
        ]);
    }

    public function candidature()
    {
        $this->requireAuth();
        $model = new CandidatureModel();
        $candidatures = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        echo $this->twig->render('candidature.twig', ['candidatures' => $candidatures]);
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
        $etudiantModel = new EtudiantModel();
        $etudiant      = $etudiantModel->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        echo $this->twig->render('profil.twig', [
            'etudiant' => $etudiant,
            'success'  => $_SESSION['success'] ?? null,
            'error'    => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function profilPost()
    {
        $this->requireAuth();

        $nom    = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email'] ?? '');

        if (!$nom || !$prenom || !$email) {
            $_SESSION['error'] = 'Tous les champs sont obligatoires.';
            header('Location: /profil');
            exit;
        }

        $utilisateurModel = new UtilisateurModel();
        $existant = $utilisateurModel->findByEmail($email);
        if ($existant && (int) $existant['id_utilisateur'] !== (int) $_SESSION['user']['id_utilisateur']) {
            $_SESSION['error'] = 'Cet email est déjà utilisé par un autre compte.';
            header('Location: /profil');
            exit;
        }

        $utilisateurModel->update((int) $_SESSION['user']['id_utilisateur'], $nom, $prenom, $email);
        $_SESSION['user']['nom']    = $nom;
        $_SESSION['user']['prenom'] = $prenom;
        $_SESSION['user']['email']  = $email;

        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== ($_POST['password_confirm'] ?? '')) {
                $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
                header('Location: /profil');
                exit;
            }
            $utilisateurModel->updatePassword((int) $_SESSION['user']['id_utilisateur'], password_hash($_POST['password'], PASSWORD_DEFAULT));
        }

        // Infos étudiant
        $promotion = trim($_POST['promotion'] ?? '');
        $statut    = trim($_POST['statut_recherche_stage'] ?? '');
        $avatarPath = null;

        if (!empty($_FILES['avatar']['name'])) {
            $fichier = $_FILES['avatar'];
            $maxSize = 2 * 1024 * 1024;
            $typesAutorise = ['image/jpeg', 'image/png', 'image/webp'];

            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeReel = $finfo->file($fichier['tmp_name']);

            if ($fichier['error'] === UPLOAD_ERR_OK && $fichier['size'] <= $maxSize && in_array($mimeReel, $typesAutorise)) {
                $ext        = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeReel];
                $nomFichier = 'avatar_' . $_SESSION['user']['id_utilisateur'] . '.' . $ext;
                $destination = UPLOAD_PATH . '/avatars/' . $nomFichier;
                if (move_uploaded_file($fichier['tmp_name'], $destination)) {
                    $avatarPath = 'uploads/avatars/' . $nomFichier;
                }
            } else {
                $_SESSION['error'] = 'Photo invalide (JPG/PNG/WEBP, 2 Mo max).';
                header('Location: /profil');
                exit;
            }
        }

        $etudiantModel = new EtudiantModel();
        $etudiantModel->update((int) $_SESSION['user']['id_utilisateur'], $promotion, $statut, $avatarPath);

        $_SESSION['success'] = 'Profil mis à jour avec succès.';
        header('Location: /profil');
        exit;
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
        if ($offre) {
            $wishlist    = new WishlistModel();
            $candidature = new CandidatureModel();
            $favorisIds  = isset($_SESSION['user']) ? $wishlist->getIdOffres((int) $_SESSION['user']['id_utilisateur']) : [];
            $dejaPostule = isset($_SESSION['user']['id_utilisateur'])
                ? $candidature->dejaPostule((int) $_SESSION['user']['id_utilisateur'], (int) $offre['id_offre'])
                : false;
            $competences = $model->findCompetencesByOffre((int) $offre['id_offre']);
            echo $this->twig->render('offre.twig', [
                'offre'       => $offre,
                'favorisIds'  => $favorisIds,
                'dejaPostule' => $dejaPostule,
                'competences' => $competences,
                'success'     => $_SESSION['success'] ?? null,
                'error'       => $_SESSION['error'] ?? null,
            ]);
            unset($_SESSION['success'], $_SESSION['error']);
        } else {
            $_SESSION['error'] = "Un probleme est survenu au niveau de l'affichage de l'offre";
            echo $this->twig->render('rechercher.twig', ['offre' => $offre]);
        }
    }

    public function postuler()
    {
        $this->requireAuth();

        $model = new OffreModel();
        $offre = $model->findById((int) ($_GET['id'] ?? 0));

        if (!$offre) {
            $_SESSION['error'] = 'Offre introuvable.';
            header('Location: /rechercher');
            exit;
        }

        $candidature = new CandidatureModel();
        if ($candidature->dejaPostule((int) $_SESSION['user']['id_utilisateur'], (int) $offre['id_offre'])) {
            $_SESSION['error'] = 'Vous avez déjà postulé à cette offre.';
            header('Location: /offre?id=' . $offre['id_offre']);
            exit;
        }

        echo $this->twig->render('postuler.twig', [
            'offre' => $offre,
            'error' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function candidaturePost()
    {
        $this->requireAuth();

        $idUtilisateur = (int) ($_SESSION['user']['id_utilisateur'] ?? 0);
        $idOffre       = (int) ($_POST['id_offre'] ?? 0);
        $lettre        = trim($_POST['lettre_motivation'] ?? '');

        if (!$idUtilisateur || !$idOffre) {
            $_SESSION['error'] = 'Données invalides.';
            header('Location: /offre?id=' . $idOffre);
            exit;
        }

        $model = new CandidatureModel();

        if ($model->dejaPostule($idUtilisateur, $idOffre)) {
            $_SESSION['error'] = 'Vous avez déjà postulé à cette offre.';
            header('Location: /offre?id=' . $idOffre);
            exit;
        }

        // Gestion du CV
        $cvPath = null;
        if (!empty($_FILES['cv']['name'])) {
            $fichier = $_FILES['cv'];
            $maxSize = 2 * 1024 * 1024; // 2 Mo

            if ($fichier['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'Erreur lors de l\'envoi du fichier.';
                header('Location: /postuler?id=' . $idOffre);
                exit;
            }

            if ($fichier['size'] > $maxSize) {
                $_SESSION['error'] = 'Le CV ne doit pas dépasser 2 Mo.';
                header('Location: /postuler?id=' . $idOffre);
                exit;
            }

            $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeReel = $finfo->file($fichier['tmp_name']);

            if ($ext !== 'pdf' || $mimeReel !== 'application/pdf') {
                $_SESSION['error'] = 'Le CV doit être au format PDF.';
                header('Location: /postuler?id=' . $idOffre);
                exit;
            }

            $nomFichier = 'cv_' . $idUtilisateur . '_' . $idOffre . '_' . time() . '.pdf';
            $destination = UPLOAD_PATH . '/cv/' . $nomFichier;

            if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
                $_SESSION['error'] = 'Impossible de sauvegarder le fichier.';
                header('Location: /postuler?id=' . $idOffre);
                exit;
            }

            $cvPath = 'uploads/cv/' . $nomFichier;
        }

        $model->create($idUtilisateur, $idOffre, $lettre, $cvPath);
        $_SESSION['success'] = 'Votre candidature a bien été envoyée !';
        header('Location: /offre?id=' . $idOffre);
        exit;
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

        if ($utilisateur && password_verify($_POST['password'], $utilisateur['mot_de_passe_hash'])) {
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

    public function deconnexion(): void {
        session_destroy();
        header('Location: /');
        exit;
    }

    public function entreprises(): void {
        $this->requireAuth();
        $model       = new EntrepriseModel();
        $search      = trim($_GET['search'] ?? '');
        $page        = max(1, (int) ($_GET['page'] ?? 1));
        $limite      = 10;
        $offset      = ($page - 1) * $limite;
        $entreprises = $model->findAll($search, $limite, $offset);
        $total       = $model->count($search);
        $totalPages  = (int) ceil($total / $limite);
        echo $this->twig->render('entreprises.twig', [
            'entreprises' => $entreprises,
            'search'      => $search,
            'page'        => $page,
            'totalPages'  => $totalPages,
        ]);
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