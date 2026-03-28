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

    private function requireRoles(array $roles): void
    {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
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
        $evaluations      = $entrepriseModel->getEvaluations((int) $_GET['id']);
        $dejaEvalue       = false;
        if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'etudiant') {
            $dejaEvalue = $entrepriseModel->dejaEvalue(
                (int) $_SESSION['user']['id_etudiant'],
                (int) $_GET['id']
            );
        }

        echo $this->twig->render('entreprise.twig', [
            'entreprise'      => $entreprise,
            'offresEntreprise'=> $offresEntreprise,
            'evaluations'     => $evaluations,
            'dejaEvalue'      => $dejaEvalue,
            'success'         => $_SESSION['success'] ?? null,
            'error'           => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function entrepriseEvaluer()
    {
        $this->requireRole('etudiant');
        $idEntreprise = (int) ($_POST['id_entreprise'] ?? 0);
        $note         = (int) ($_POST['note'] ?? 0);
        $commentaire  = trim($_POST['commentaire'] ?? '');

        if (!$idEntreprise || $note < 1 || $note > 5) {
            $_SESSION['error'] = 'Données invalides.';
            header('Location: /entreprise?id=' . $idEntreprise);
            exit;
        }

        $model      = new EntrepriseModel();
        $idEtudiant = (int) ($_SESSION['user']['id_etudiant'] ?? 0);

        if ($model->dejaEvalue($idEtudiant, $idEntreprise)) {
            $_SESSION['error'] = 'Vous avez déjà évalué cette entreprise.';
            header('Location: /entreprise?id=' . $idEntreprise);
            exit;
        }

        $model->evaluer($idEtudiant, $idEntreprise, $note, $commentaire);
        $_SESSION['success'] = 'Évaluation envoyée, merci !';
        header('Location: /entreprise?id=' . $idEntreprise);
        exit;
    }

    public function entrepriseEvaluerModifier()
    {
        $this->requireRole('etudiant');
        $idEvaluation = (int) ($_POST['id_evaluation'] ?? 0);
        $idEntreprise = (int) ($_POST['id_entreprise'] ?? 0);
        $note         = (int) ($_POST['note'] ?? 0);
        $commentaire  = trim($_POST['commentaire'] ?? '');
        $idEtudiant   = (int) ($_SESSION['user']['id_etudiant'] ?? 0);

        if (!$idEvaluation || !$idEntreprise || $note < 1 || $note > 5) {
            $_SESSION['error'] = 'Données invalides.';
            header('Location: /entreprise?id=' . $idEntreprise);
            exit;
        }

        (new EntrepriseModel())->modifierEvaluation($idEvaluation, $idEtudiant, $note, $commentaire);
        $_SESSION['success'] = 'Évaluation modifiée.';
        header('Location: /entreprise?id=' . $idEntreprise);
        exit;
    }

    public function entrepriseEvaluerSupprimer()
    {
        $this->requireRole('etudiant');
        $idEvaluation = (int) ($_POST['id_evaluation'] ?? 0);
        $idEntreprise = (int) ($_POST['id_entreprise'] ?? 0);
        $idEtudiant   = (int) ($_SESSION['user']['id_etudiant'] ?? 0);

        if ($idEvaluation && $idEntreprise) {
            (new EntrepriseModel())->supprimerEvaluation($idEvaluation, $idEtudiant);
            $_SESSION['success'] = 'Évaluation supprimée.';
        }
        header('Location: /entreprise?id=' . $idEntreprise);
        exit;
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
        $this->requireAuth();
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
            $nbCandidatures = $candidature->compterParOffre((int) $offre['id_offre']);
            echo $this->twig->render('offre.twig', [
                'offre'          => $offre,
                'favorisIds'     => $favorisIds,
                'dejaPostule'    => $dejaPostule,
                'competences'    => $competences,
                'nbCandidatures' => $nbCandidatures,
                'success'        => $_SESSION['success'] ?? null,
                'error'          => $_SESSION['error'] ?? null,
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
        $modeleOffre = new OffreModel();
        $modeleWishlist = new WishlistModel();
        $modeleEntreprise = new EntrepriseModel();
        $pageCourante = (int) ($_GET['page'] ?? 1);
        $nombreParPage = 10;
        $debut = ($pageCourante - 1) * $nombreParPage;

        $filtres = [
            'motCle'           => trim($_GET['motCle'] ?? ''),
            'titre'            => trim($_GET['titre'] ?? ''),
            'entreprise'       => trim($_GET['entreprise'] ?? ''),
            'competence'       => trim($_GET['competence'] ?? ''),
            'remuneration_min' => trim($_GET['remuneration_min'] ?? ''),
            'remuneration_max' => trim($_GET['remuneration_max'] ?? ''),
        ];

        $aucunFiltre = array_filter($filtres) === [];

        if ($aucunFiltre) {
            $offres = $modeleOffre->findAll($nombreParPage, $debut);
            $totalOffres = $modeleOffre->count();
        } else {
            $offres = $modeleOffre->rechercheAvancee($filtres, $nombreParPage, $debut);
            $totalOffres = $modeleOffre->compterRechercheAvancee($filtres);
        }

        $totalPages = ceil($totalOffres / $nombreParPage);
        $favorisIds = isset($_SESSION['user']) ? $modeleWishlist->getIdOffres((int) $_SESSION['user']['id_utilisateur']) : [];

        echo $this->twig->render('rechercher.twig', [
            'offres'       => $offres,
            'pageCourante' => $pageCourante,
            'totalPages'   => $totalPages,
            'favorisIds'   => $favorisIds,
            'filtres'      => $filtres,
            'entreprises'  => $modeleEntreprise->listerToutesActives(),
            'competences'  => $modeleOffre->listerCompetences(),
            'titres'       => $modeleOffre->listerTitres(),
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

    public function entrepriseInscription(): void {
        $this->requireRoles(['admin', 'pilote']);
        echo $this->twig->render('entreprise-inscription.twig', [
            'error'   => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
        ]);
        unset($_SESSION['error'], $_SESSION['success']);
    }

    public function entrepriseInscriptionPost(): void {
        $this->requireRoles(['admin', 'pilote']);
        $nom         = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email_contact'] ?? '');
        $telephone   = trim($_POST['telephone_contact'] ?? '');
        $ville       = trim($_POST['ville'] ?? '');
        $siteWeb     = trim($_POST['site_web'] ?? '');

        if (!$nom) {
            $_SESSION['error'] = 'Le nom de l\'entreprise est obligatoire.';
            header('Location: /entreprise/inscription');
            exit;
        }

        (new EntrepriseModel())->demanderCreation($nom, $description, $email, $telephone, $ville, $siteWeb);
        $_SESSION['success'] = 'Votre demande a bien été envoyée. Un administrateur va l\'examiner.';
        header('Location: /entreprise/inscription');
        exit;
    }

    public function entrepriseModifier(): void {
        $this->requireRole('admin');
        $id         = (int) ($_GET['id'] ?? 0);
        $model      = new EntrepriseModel();
        $entreprise = $model->findById($id);

        if (!$entreprise) {
            header('Location: /entreprises');
            exit;
        }

        echo $this->twig->render('entreprise-modifier.twig', [
            'entreprise' => $entreprise,
            'error'      => $_SESSION['error'] ?? null,
            'success'    => $_SESSION['success'] ?? null,
        ]);
        unset($_SESSION['error'], $_SESSION['success']);
    }

    public function entrepriseModifierPost(): void {
        $this->requireRole('admin');
        $id          = (int) ($_POST['id_entreprise'] ?? 0);
        $nom         = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email_contact'] ?? '');
        $telephone   = trim($_POST['telephone_contact'] ?? '');
        $ville       = trim($_POST['ville'] ?? '');
        $siteWeb     = trim($_POST['site_web'] ?? '');
        $idUser      = (int) $_SESSION['user']['id_utilisateur'];

        $model      = new EntrepriseModel();
        $entreprise = $model->findById($id);

        if (!$entreprise) {
            header('Location: /entreprises');
            exit;
        }

        if (!$nom) {
            $_SESSION['error'] = 'Le nom est obligatoire.';
            header('Location: /entreprise/modifier?id=' . $id);
            exit;
        }

        $model->demanderModification($id, $idUser, $nom, $description, $email, $telephone, $ville, $siteWeb);
        $_SESSION['success'] = 'Votre demande de modification a été envoyée. Un administrateur va l\'examiner.';
        header('Location: /entreprise?id=' . $id);
        exit;
    }

    public function offreCreer(): void {
        $this->requireRoles(['admin', 'pilote']);
        $modeleEntreprise = new EntrepriseModel();
        $entreprises      = $modeleEntreprise->listerToutesActives();

        if (empty($entreprises)) {
            $_SESSION['error'] = 'Aucune entreprise approuvée disponible pour créer une offre.';
            header('Location: /entreprises');
            exit;
        }

        $modeleOffre = new OffreModel();
        echo $this->twig->render('offre-creer.twig', [
            'entreprises' => $entreprises,
            'competences' => $modeleOffre->listerCompetences(),
            'error'       => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function offreCreerPost(): void {
        $this->requireRoles(['admin', 'pilote']);
        $modeleEntreprise    = new EntrepriseModel();
        $entreprisesActives  = $modeleEntreprise->listerToutesActives();

        $idEntreprise        = (int) ($_POST['id_entreprise'] ?? 0);
        $titre               = trim($_POST['titre'] ?? '');
        $description         = trim($_POST['description'] ?? '');
        $remunerationBase    = ($_POST['remuneration_base'] ?? '') !== '' ? (float) $_POST['remuneration_base'] : null;
        $dateOffre           = ($_POST['date_offre'] ?? '') !== '' ? $_POST['date_offre'] : null;
        $competencesChoisies = array_map('intval', $_POST['competences'] ?? []);

        // Vérifie que l'entreprise choisie est bien une entreprise active approuvée
        $idEntreprisesAutorisees = array_column($entreprisesActives, 'id_entreprise');
        if (!$titre || !$description || !in_array($idEntreprise, $idEntreprisesAutorisees)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            header('Location: /offre/creer');
            exit;
        }

        $modeleOffre = new OffreModel();
        $idOffre     = $modeleOffre->creer($idEntreprise, $titre, $description, $remunerationBase, $dateOffre);

        // Compétences existantes cochées
        foreach ($competencesChoisies as $idCompetence) {
            $modeleOffre->ajouterCompetence($idOffre, $idCompetence);
        }

        // Nouvelles compétences saisies manuellement
        $nouvellesCompetences = $_POST['nouvelles_competences'] ?? [];
        foreach ($nouvellesCompetences as $libelle) {
            $libelle = trim($libelle);
            if ($libelle === '') continue;
            $idCompetence = $modeleOffre->creerOuTrouverCompetence($libelle);
            $modeleOffre->ajouterCompetence($idOffre, $idCompetence);
        }

        $_SESSION['success'] = 'L\'offre a bien été créée.';
        header('Location: /offre?id=' . $idOffre);
        exit;
    }

    public function mesEntreprises(): void {
        header('Location: /entreprises');
        exit;
    }

    public function entrepriseDemanderSuppression(): void {
        $this->requireRoles(['admin', 'pilote']);
        $id = (int) ($_POST['id_entreprise'] ?? 0);
        $ok = (new EntrepriseModel())->demanderSuppression($id);
        $_SESSION['success'] = $ok ? 'Demande de suppression envoyée.' : 'Impossible d\'effectuer cette action.';
        header('Location: /entreprise?id=' . $id);
        exit;
    }

    public function adminSuppressions(): void {
        $this->requireRole('admin');
        $model = new EntrepriseModel();
        echo $this->twig->render('admin/suppressions.twig', [
            'demandes' => $model->getSuppressionsDemandees(),
            'success'  => $_SESSION['success'] ?? null,
            'error'    => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminSuppressionApprouver(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_entreprise'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->supprimer($id);
            $_SESSION['success'] = 'Entreprise supprimée.';
        }
        header('Location: /admin/suppressions');
        exit;
    }

    public function adminSuppressionRejeter(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_entreprise'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->rejeterSuppression($id);
            $_SESSION['success'] = 'Demande de suppression rejetée.';
        }
        header('Location: /admin/suppressions');
        exit;
    }

    public function adminEntrepriseModifierDirect(): void {
        $this->requireRole('admin');
        $id = (int) ($_GET['id'] ?? 0);
        $entreprise = (new EntrepriseModel())->findById($id);
        if (!$entreprise) { header('Location: /admin/entreprises'); exit; }
        echo $this->twig->render('admin/entreprise-modifier.twig', [
            'entreprise' => $entreprise,
            'success'    => $_SESSION['success'] ?? null,
            'error'      => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminEntrepriseModifierDirectPost(): void {
        $this->requireRole('admin');
        $id          = (int) ($_POST['id_entreprise'] ?? 0);
        $nom         = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email_contact'] ?? '');
        $telephone   = trim($_POST['telephone_contact'] ?? '');
        $ville       = trim($_POST['ville'] ?? '');
        $siteWeb     = trim($_POST['site_web'] ?? '');

        if (!$nom) {
            $_SESSION['error'] = 'Le nom est obligatoire.';
            header('Location: /admin/entreprise/modifier?id=' . $id);
            exit;
        }

        (new EntrepriseModel())->adminModifierDirect($id, $nom, $description, $email, $telephone, $ville, $siteWeb);
        $_SESSION['success'] = 'Entreprise modifiée.';
        header('Location: /entreprise?id=' . $id);
        exit;
    }

    public function adminEntrepriseSupprimer(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_entreprise'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->supprimer($id);
            $_SESSION['success'] = 'Entreprise supprimée.';
        }
        header('Location: /admin/entreprises');
        exit;
    }

    public function adminModifications(): void {
        $this->requireRole('admin');
        $model = new EntrepriseModel();
        echo $this->twig->render('admin/modifications.twig', [
            'demandes' => $model->getModificationsEnAttente(),
            'success'  => $_SESSION['success'] ?? null,
            'error'    => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminModificationApprouver(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_demande'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->approuverModification($id);
            $_SESSION['success'] = 'Modification approuvée.';
        }
        header('Location: /admin/modifications');
        exit;
    }

    public function adminModificationRejeter(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_demande'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->rejeterModification($id);
            $_SESSION['success'] = 'Modification rejetée.';
        }
        header('Location: /admin/modifications');
        exit;
    }

    public function adminDashboard(): void {
        $this->requireRole('admin');
        $db = \App\Database::getInstance()->getPdo();

        $stats = [
            'nb_entreprises_en_attente'  => count((new EntrepriseModel())->getDemandesEnAttente()),
            'nb_modifications_en_attente'=> count((new EntrepriseModel())->getModificationsEnAttente()),
            'nb_suppressions_en_attente' => count((new EntrepriseModel())->getSuppressionsDemandees()),
            'nb_offres'                  => (int) $db->query('SELECT COUNT(*) FROM OFFRE WHERE active = TRUE')->fetchColumn(),
            'nb_entreprises'             => (int) $db->query('SELECT COUNT(*) FROM ENTREPRISE WHERE statut = "approuvee" AND active = TRUE')->fetchColumn(),
            'nb_etudiants'               => (int) $db->query('SELECT COUNT(*) FROM ETUDIANT')->fetchColumn(),
            'nb_pilotes'                 => (int) $db->query('SELECT COUNT(*) FROM PILOTE')->fetchColumn(),
            'nb_candidatures'            => (int) $db->query('SELECT COUNT(*) FROM CANDIDATURE')->fetchColumn(),
        ];

        echo $this->twig->render('admin/dashboard.twig', [
            'stats'   => $stats,
            'success' => $_SESSION['success'] ?? null,
            'error'   => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminEntreprises(): void {
        $this->requireRole('admin');
        $model    = new EntrepriseModel();
        $demandes = $model->getDemandesEnAttente();
        echo $this->twig->render('admin/entreprises.twig', [
            'demandes' => $demandes,
            'success'  => $_SESSION['success'] ?? null,
            'error'    => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminEntrepriseApprouver(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_entreprise'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->approuver($id);
            $_SESSION['success'] = 'Entreprise approuvée.';
        }
        header('Location: /admin/entreprises');
        exit;
    }

    public function adminEntrepriseRejeter(): void {
        $this->requireRole('admin');
        $id = (int) ($_POST['id_entreprise'] ?? 0);
        if ($id) {
            (new EntrepriseModel())->rejeter($id);
            $_SESSION['success'] = 'Entreprise rejetée.';
        }
        header('Location: /admin/entreprises');
        exit;
    }

    public function entreprises(): void {
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