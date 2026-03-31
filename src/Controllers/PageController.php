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

        $nombreParPage  = 10;
        $pageCourante   = max(1, (int) ($_GET['page'] ?? 1));
        $recherche      = trim($_GET['recherche'] ?? '');
        $totalEtudiants = $model->compterEtudiants((int) $pilote['id_pilote'], $recherche);
        $totalPages     = (int) ceil($totalEtudiants / $nombreParPage);
        $decalage       = ($pageCourante - 1) * $nombreParPage;

        $etudiants = $model->getEtudiants((int) $pilote['id_pilote'], $nombreParPage, $decalage, $recherche);
        echo $this->twig->render('pilote/dashboard.twig', [
            'etudiants'      => $etudiants,
            'totalEtudiants' => $totalEtudiants,
            'pageCourante'   => $pageCourante,
            'totalPages'     => $totalPages,
            'recherche'      => $recherche,
            'success'        => $_SESSION['success'] ?? null,
            'error'          => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function piloteEntreprises(): void {
        $this->requireRole('pilote');
        $idUtilisateur = (int) $_SESSION['user']['id_utilisateur'];
        $entreprises   = (new EntrepriseModel())->getEntreprisesParCreateur($idUtilisateur);
        echo $this->twig->render('pilote/entreprises.twig', [
            'entreprises' => $entreprises,
            'success'     => $_SESSION['success'] ?? null,
            'error'       => $_SESSION['error'] ?? null,
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

        $nom       = trim($_POST['nom'] ?? '');
        $prenom    = trim($_POST['prenom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $ecole     = trim($_POST['ecole'] ?? '');
        $promotion = trim($_POST['promotion'] ?? '');
        $mdp       = $_POST['password'] ?? '';

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
        $model->creerEtudiant((int) $pilote['id_pilote'], $nom, $prenom, $email, password_hash($mdp, PASSWORD_DEFAULT), $ecole, $promotion);

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

    public function piloteModifierEtudiant()
    {
        $this->requireRole('pilote');
        $model      = new PiloteModel();
        $pilote     = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        $idEtudiant = (int) ($_GET['id'] ?? 0);
        $etudiant   = $model->getEtudiant($idEtudiant, (int) $pilote['id_pilote']);

        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /pilote');
            exit;
        }

        echo $this->twig->render('pilote/etudiant-modifier.twig', [
            'etudiant' => $etudiant,
            'error'    => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function piloteModifierEtudiantPost()
    {
        $this->requireRole('pilote');
        $model      = new PiloteModel();
        $pilote     = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        $idEtudiant = (int) ($_POST['id_etudiant'] ?? 0);
        $nom        = trim($_POST['nom'] ?? '');
        $prenom     = trim($_POST['prenom'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $ecole      = trim($_POST['ecole'] ?? '');
        $promotion  = trim($_POST['promotion'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');

        if (!$nom || !$prenom || !$email) {
            $_SESSION['error'] = 'Nom, prénom et email sont obligatoires.';
            header('Location: /pilote/modifier-etudiant?id=' . $idEtudiant);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Adresse email invalide.';
            header('Location: /pilote/modifier-etudiant?id=' . $idEtudiant);
            exit;
        }

        $etudiantActuel = $model->getEtudiant($idEtudiant, (int) $pilote['id_pilote']);
        if (!$etudiantActuel) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /pilote');
            exit;
        }

        if ($email !== $etudiantActuel['email'] && (new UtilisateurModel())->findByEmail($email)) {
            $_SESSION['error'] = 'Cette adresse email est déjà utilisée.';
            header('Location: /pilote/modifier-etudiant?id=' . $idEtudiant);
            exit;
        }

        $hash = $motDePasse !== '' ? password_hash($motDePasse, PASSWORD_DEFAULT) : null;
        $model->modifierEtudiant($idEtudiant, (int) $pilote['id_pilote'], $nom, $prenom, $email, $ecole, $promotion, $hash);

        $_SESSION['success'] = 'Compte étudiant modifié avec succès.';
        header('Location: /pilote/etudiant?id=' . $idEtudiant);
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
            $modelePilote  = new PiloteModel();
            $pilote        = $modelePilote->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);

            if (!$pilote) {
                header('Location: /identification?error=pilote_introuvable');
                exit;
            }

            $tousEtudiants         = $modelePilote->getEtudiants((int) $pilote['id_pilote']);
            $nbEtudiants           = count($tousEtudiants);
            $nbAvecStage           = count(array_filter($tousEtudiants, fn($e) => $e['statut_recherche_stage'] === 'Stage trouvé'));
            $nbCandidaturesTotales = (int) array_sum(array_column($tousEtudiants, 'nb_candidatures'));
            $derniersEtudiants     = array_slice($tousEtudiants, 0, 5);

            echo $this->twig->render('pilote/acceuil.twig', [
                'nbEtudiants'           => $nbEtudiants,
                'nbAvecStage'           => $nbAvecStage,
                'nbCandidaturesTotales' => $nbCandidaturesTotales,
                'derniersEtudiants'     => $derniersEtudiants,
            ]);
            return;
        }
        if ($role === 'admin') {
            header('Location: /admin');
            exit;
        }

        $candidatureModel = new CandidatureModel();
        $wishlistModel    = new WishlistModel();
        $modeleOffre      = new OffreModel();

        $candidatures = $candidatureModel->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        $nbFavoris    = count($wishlistModel->getIdOffres((int) $_SESSION['user']['id_utilisateur']));

        $cartesStatistiques = [
            [
                'titre'  => 'Offres disponibles',
                'type'   => 'chiffre',
                'valeur' => $modeleOffre->count(),
                'unite'  => 'offres actives en base',
            ],
            [
                'titre'  => 'Candidatures moyennes',
                'type'   => 'chiffre',
                'valeur' => $modeleOffre->moyenneCandidaturesParOffre(),
                'unite'  => 'candidatures par offre en moyenne',
            ],
            [
                'titre'  => 'Top des offres en favoris',
                'type'   => 'liste',
                'items'  => $modeleOffre->topOffresWishlist(5),
            ],
            [
                'titre'  => 'Répartition par durée de stage',
                'type'   => 'repartition',
                'items'  => $modeleOffre->repartitionParDureeStage(),
            ],
        ];

        echo $this->twig->render('acceuil.twig', [
            'candidatures'          => $candidatures,
            'nbCandidatures'        => count($candidatures),
            'nbFavoris'             => $nbFavoris,
            'dernieresCandidatures' => array_slice($candidatures, 0, 3),
            'cartesStatistiques'    => $cartesStatistiques,
        ]);
    }

    public function candidature()
    {
        $this->requireRole('etudiant');
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
        $roleUtilisateur  = $_SESSION['user']['role'] ?? '';
        if (!empty($_SESSION['user']) && in_array($roleUtilisateur, ['pilote', 'admin'], true)) {
            $dejaEvalue = $entrepriseModel->dejaEvalue(
                (int) $_SESSION['user']['id_utilisateur'],
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
        $this->requireRoles(['pilote', 'admin']);
        $idEntreprise  = (int) ($_POST['id_entreprise'] ?? 0);
        $note          = (int) ($_POST['note'] ?? 0);
        $commentaire   = trim($_POST['commentaire'] ?? '');

        if (!$idEntreprise || $note < 1 || $note > 5) {
            $_SESSION['error'] = 'Données invalides.';
            header('Location: /entreprise?id=' . $idEntreprise);
            exit;
        }

        $model         = new EntrepriseModel();
        $idUtilisateur = (int) ($_SESSION['user']['id_utilisateur'] ?? 0);

        if ($model->dejaEvalue($idUtilisateur, $idEntreprise)) {
            $_SESSION['error'] = 'Vous avez déjà évalué cette entreprise.';
            header('Location: /entreprise?id=' . $idEntreprise);
            exit;
        }

        $model->evaluer($idUtilisateur, $idEntreprise, $note, $commentaire);
        $_SESSION['success'] = 'Évaluation envoyée, merci !';
        header('Location: /entreprise?id=' . $idEntreprise);
        exit;
    }

    public function entrepriseEvaluerModifier()
    {
        $this->requireRoles(['pilote', 'admin']);
        $idEvaluation  = (int) ($_POST['id_evaluation'] ?? 0);
        $idEntreprise  = (int) ($_POST['id_entreprise'] ?? 0);
        $note          = (int) ($_POST['note'] ?? 0);
        $commentaire   = trim($_POST['commentaire'] ?? '');
        $idUtilisateur = (int) ($_SESSION['user']['id_utilisateur'] ?? 0);

        if (!$idEvaluation || !$idEntreprise || $note < 1 || $note > 5) {
            $_SESSION['error'] = 'Données invalides.';
            header('Location: /entreprise?id=' . $idEntreprise);
            exit;
        }

        (new EntrepriseModel())->modifierEvaluation($idEvaluation, $idUtilisateur, $note, $commentaire);
        $_SESSION['success'] = 'Évaluation modifiée.';
        header('Location: /entreprise?id=' . $idEntreprise);
        exit;
    }

    public function entrepriseEvaluerSupprimer()
    {
        $this->requireRoles(['pilote', 'admin']);
        $idEvaluation  = (int) ($_POST['id_evaluation'] ?? 0);
        $idEntreprise  = (int) ($_POST['id_entreprise'] ?? 0);
        $idUtilisateur = (int) ($_SESSION['user']['id_utilisateur'] ?? 0);

        if ($idEvaluation && $idEntreprise) {
            (new EntrepriseModel())->supprimerEvaluation($idEvaluation, $idUtilisateur);
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
        $this->requireRole('etudiant');
        $model = new WishlistModel();
        $favoris = $model->findByUtilisateur((int) $_SESSION['user']['id_utilisateur']);
        echo $this->twig->render('favoris.twig',['favoris' => $favoris]);
    }

    public function favorisPost(){
        $this->requireRole('etudiant');
        $model = new WishlistModel();
        $model->add((int) $_SESSION['user']['id_utilisateur'], (int) $_POST['id_offre']);
        header("location:" . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function favorisDelete(){
        $this->requireRole('etudiant');
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
        $this->requireRole('etudiant');

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
        $this->requireRole('etudiant');

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

    public function telechargerCv()
    {
        $this->requireAuth();

        $idCandidature = (int) ($_GET['id'] ?? 0);
        if (!$idCandidature) {
            http_response_code(400);
            exit('Requête invalide.');
        }

        $model       = new CandidatureModel();
        $candidature = $model->findById($idCandidature);

        if (!$candidature || empty($candidature['cv_fichier'])) {
            http_response_code(404);
            exit('CV introuvable.');
        }

        $utilisateur = $_SESSION['user'];
        $acces       = false;

        // L'étudiant peut télécharger son propre CV
        if ($utilisateur['role'] === 'etudiant' &&
            (int) $candidature['id_utilisateur'] === (int) $utilisateur['id_utilisateur']) {
            $acces = true;
        }

        // Le pilote peut télécharger les CV de ses étudiants
        if ($utilisateur['role'] === 'pilote') {
            $modelePilote = new PiloteModel();
            $pilote       = $modelePilote->findByUtilisateur((int) $utilisateur['id_utilisateur']);
            if ($pilote) {
                $etudiant = $modelePilote->getEtudiant(
                    0, // non utilisé directement, on cherche par id_utilisateur
                    (int) $pilote['id_pilote']
                );
                // Vérifie que l'étudiant propriétaire de la candidature appartient à ce pilote
                $etudiants = $modelePilote->getEtudiants((int) $pilote['id_pilote']);
                foreach ($etudiants as $e) {
                    if ((int) $e['id_utilisateur'] === (int) $candidature['id_utilisateur']) {
                        $acces = true;
                        break;
                    }
                }
            }
        }

        // L'admin a accès à tout
        if ($utilisateur['role'] === 'admin') {
            $acces = true;
        }

        if (!$acces) {
            http_response_code(403);
            exit('Accès refusé.');
        }

        $cheminFichier = UPLOAD_PATH . '/cv/' . basename($candidature['cv_fichier']);
        if (!file_exists($cheminFichier)) {
            http_response_code(404);
            exit('Fichier introuvable.');
        }

        $nomTelechargement = 'CV_' . $candidature['id_utilisateur'] . '_' . $candidature['id_offre'] . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nomTelechargement . '"');
        header('Content-Length: ' . filesize($cheminFichier));
        readfile($cheminFichier);
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

        $cheminLogo = null;
        if (!empty($_FILES['logo']['name'])) {
            $fichier       = $_FILES['logo'];
            $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo         = new \finfo(FILEINFO_MIME_TYPE);
            $mimeReel      = $finfo->file($fichier['tmp_name']);

            if ($fichier['error'] === UPLOAD_ERR_OK && $fichier['size'] <= 2 * 1024 * 1024 && in_array($mimeReel, $typesAutorises)) {
                $ext         = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeReel];
                $nomFichier  = 'logo_' . uniqid() . '.' . $ext;
                $destination = UPLOAD_PATH . '/logos/' . $nomFichier;
                if (move_uploaded_file($fichier['tmp_name'], $destination)) {
                    $cheminLogo = 'uploads/logos/' . $nomFichier;
                }
            } else {
                $_SESSION['error'] = 'Logo invalide (JPG/PNG/WEBP, 2 Mo max).';
                header('Location: /entreprise/inscription');
                exit;
            }
        }

        $idCreateur = (int) ($_SESSION['user']['id_utilisateur'] ?? 0) ?: null;
        (new EntrepriseModel())->demanderCreation($nom, $description, $email, $telephone, $ville, $siteWeb, $cheminLogo, $idCreateur);
        $_SESSION['success'] = 'Votre demande a bien été envoyée. Un administrateur va l\'examiner.';
        $redirection = ($_SESSION['user']['role'] ?? '') === 'pilote' ? '/pilote/entreprises' : '/entreprise/inscription';
        header('Location: ' . $redirection);
        exit;
    }

    public function entrepriseModifier(): void {
        $this->requireRoles(['admin', 'pilote']);
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
        $this->requireRoles(['admin', 'pilote']);
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

    public function offreModifier(): void {
        $this->requireRoles(['admin', 'pilote']);

        $idOffre     = (int) ($_GET['id'] ?? 0);
        $modeleOffre = new OffreModel();
        $offre       = $modeleOffre->findById($idOffre);

        if (!$offre) {
            $_SESSION['error'] = 'Offre introuvable.';
            header('Location: /rechercher');
            exit;
        }

        $modeleEntreprise = new EntrepriseModel();
        $entreprises      = $modeleEntreprise->listerToutesActives();
        $competences      = $modeleOffre->findCompetencesByOffre($idOffre);

        echo $this->twig->render('offre-modifier.twig', [
            'offre'       => $offre,
            'entreprises' => $entreprises,
            'competences' => $competences,
            'error'       => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function offreModifierPost(): void {
        $this->requireRoles(['admin', 'pilote']);

        $idOffre     = (int) ($_POST['id_offre'] ?? 0);
        $modeleOffre = new OffreModel();
        $offre       = $modeleOffre->findById($idOffre);

        if (!$offre) {
            $_SESSION['error'] = 'Offre introuvable.';
            header('Location: /rechercher');
            exit;
        }

        $modeleEntreprise   = new EntrepriseModel();
        $entreprisesActives = $modeleEntreprise->listerToutesActives();

        $idEntreprise     = (int) ($_POST['id_entreprise'] ?? 0);
        $titre            = trim($_POST['titre'] ?? '');
        $description      = trim($_POST['description'] ?? '');
        $remunerationBase = ($_POST['remuneration_base'] ?? '') !== '' ? (float) $_POST['remuneration_base'] : null;
        $dateOffre        = ($_POST['date_offre'] ?? '') !== '' ? $_POST['date_offre'] : null;

        $idEntreprisesAutorisees = array_column($entreprisesActives, 'id_entreprise');
        if (!$titre || !$description || !in_array($idEntreprise, $idEntreprisesAutorisees)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            header('Location: /offre/modifier?id=' . $idOffre);
            exit;
        }

        $modeleOffre->modifier($idOffre, $idEntreprise, $titre, $description, $remunerationBase, $dateOffre);

        // Réinitialise et recrée les compétences
        $modeleOffre->supprimerToutesCompetences($idOffre);

        $competencesExistantes = array_map('intval', $_POST['competences_existantes'] ?? []);
        foreach ($competencesExistantes as $idCompetence) {
            $modeleOffre->ajouterCompetence($idOffre, $idCompetence);
        }

        $nouvellesCompetences = $_POST['nouvelles_competences'] ?? [];
        foreach ($nouvellesCompetences as $libelle) {
            $libelle = trim($libelle);
            if ($libelle === '') continue;
            $idCompetence = $modeleOffre->creerOuTrouverCompetence($libelle);
            $modeleOffre->ajouterCompetence($idOffre, $idCompetence);
        }

        $_SESSION['success'] = 'L\'offre a bien été modifiée.';
        header('Location: /offre?id=' . $idOffre);
        exit;
    }

    public function offreStatistiques(): void {
        // SFx11 : accessible à tous (admin, pilote, étudiant, anonyme)

        $modeleOffre = new OffreModel();

        $cartes = [
            [
                'titre'  => 'Offres disponibles',
                'type'   => 'chiffre',
                'valeur' => $modeleOffre->count(),
                'unite'  => 'offres actives en base',
            ],
            [
                'titre'  => 'Candidatures moyennes',
                'type'   => 'chiffre',
                'valeur' => $modeleOffre->moyenneCandidaturesParOffre(),
                'unite'  => 'candidatures par offre en moyenne',
            ],
            [
                'titre'  => 'Top des offres en favoris',
                'type'   => 'liste',
                'items'  => $modeleOffre->topOffresWishlist(5),
            ],
            [
                'titre'  => 'Répartition par durée de stage',
                'type'   => 'repartition',
                'items'  => $modeleOffre->repartitionParDureeStage(),
            ],
        ];

        echo $this->twig->render('offre-statistiques.twig', [
            'cartes' => $cartes,
        ]);
    }

    public function offreSupprimerPost(): void {
        $this->requireRoles(['admin', 'pilote']);

        $idOffre     = (int) ($_POST['id_offre'] ?? 0);
        $modeleOffre = new OffreModel();
        $offre       = $modeleOffre->findById($idOffre);

        if (!$offre) {
            $_SESSION['error'] = 'Offre introuvable.';
            header('Location: /rechercher');
            exit;
        }

        $modeleOffre->supprimer($idOffre);

        $_SESSION['success'] = 'L\'offre a bien été supprimée.';
        header('Location: /rechercher');
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

        $modeleEntreprise  = new EntrepriseModel();
        $entrepriseActuelle = $modeleEntreprise->findById($id);
        $supprimerLogo     = !empty($_POST['supprimer_logo']);

        $cheminLogo = null;
        if (!empty($_FILES['logo']['name'])) {
            $fichier        = $_FILES['logo'];
            $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo          = new \finfo(FILEINFO_MIME_TYPE);
            $mimeReel       = $finfo->file($fichier['tmp_name']);

            if ($fichier['error'] === UPLOAD_ERR_OK && $fichier['size'] <= 2 * 1024 * 1024 && in_array($mimeReel, $typesAutorises)) {
                $ext        = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeReel];
                $nomFichier = 'logo_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($fichier['tmp_name'], UPLOAD_PATH . '/logos/' . $nomFichier)) {
                    // Supprime l'ancien fichier si présent
                    if (!empty($entrepriseActuelle['logo'])) {
                        @unlink(dirname(UPLOAD_PATH) . '/public/' . $entrepriseActuelle['logo']);
                    }
                    $cheminLogo = 'uploads/logos/' . $nomFichier;
                }
            } else {
                $_SESSION['error'] = 'Logo invalide (JPG/PNG/WEBP, 2 Mo max).';
                header('Location: /admin/entreprise/modifier?id=' . $id);
                exit;
            }
        } elseif ($supprimerLogo && !empty($entrepriseActuelle['logo'])) {
            @unlink(UPLOAD_PATH . '/logos/' . basename($entrepriseActuelle['logo']));
            $cheminLogo = '';  // chaîne vide pour mettre NULL en base
        }

        $modeleEntreprise->adminModifierDirect($id, $nom, $description, $email, $telephone, $ville, $siteWeb, $cheminLogo);
        $_SESSION['success'] = 'Entreprise modifiée.';
        header('Location: /entreprise?id=' . $id);
        exit;
    }

    public function adminEntrepriseSupprimer(): void {
        $this->requireRole('admin');
        $id     = (int) ($_POST['id_entreprise'] ?? 0);
        $retour = $_POST['retour'] ?? '/entreprises';
        // Sécurité : on n'accepte que les URLs internes
        if (!str_starts_with($retour, '/')) {
            $retour = '/entreprises';
        }
        if ($id) {
            (new EntrepriseModel())->supprimer($id);
            $_SESSION['success'] = 'Entreprise supprimée.';
        }
        header('Location: ' . $retour);
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

    public function adminEntreprisesGerer(): void {
        $this->requireRole('admin');
        // La page de gestion est désormais fusionnée avec /entreprises
        $search = trim($_GET['search'] ?? '');
        $redirectUrl = '/entreprises' . ($search ? '?search=' . urlencode($search) : '');
        header('Location: ' . $redirectUrl);
        exit;
    }

    public function adminEntrepriseCreerPost(): void {
        $this->requireRole('admin');
        $nom         = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email_contact'] ?? '');
        $telephone   = trim($_POST['telephone_contact'] ?? '');
        $ville       = trim($_POST['ville'] ?? '');
        $siteWeb     = trim($_POST['site_web'] ?? '');

        if (!$nom) {
            $_SESSION['error'] = 'Le nom est obligatoire.';
            header('Location: /entreprises');
            exit;
        }

        $cheminLogo = null;
        if (!empty($_FILES['logo']['name'])) {
            $fichier        = $_FILES['logo'];
            $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo          = new \finfo(FILEINFO_MIME_TYPE);
            $mimeReel       = $finfo->file($fichier['tmp_name']);
            if ($fichier['error'] === UPLOAD_ERR_OK && $fichier['size'] <= 2 * 1024 * 1024 && in_array($mimeReel, $typesAutorises)) {
                $ext         = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeReel];
                $nomFichier  = 'logo_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($fichier['tmp_name'], UPLOAD_PATH . '/logos/' . $nomFichier)) {
                    $cheminLogo = 'uploads/logos/' . $nomFichier;
                }
            } else {
                $_SESSION['error'] = 'Logo invalide (JPG/PNG/WEBP, 2 Mo max).';
                header('Location: /entreprises');
                exit;
            }
        }

        $id = (new EntrepriseModel())->creerDirect($nom, $description, $email, $telephone, $ville, $siteWeb, $cheminLogo);
        $_SESSION['success'] = 'Entreprise créée et approuvée.';
        header('Location: /entreprise?id=' . $id);
        exit;
    }

    public function adminEntreprises(): void {
        $this->requireRole('admin');
        $nombreParPage = 10;
        $pageCourante  = max(1, (int) ($_GET['page'] ?? 1));
        $modele        = new EntrepriseModel();
        $totalDemandes = $modele->compterDemandesEnAttente();
        $totalPages    = (int) ceil($totalDemandes / $nombreParPage);
        $decalage      = ($pageCourante - 1) * $nombreParPage;
        $demandes      = $modele->getDemandesEnAttente($nombreParPage, $decalage);
        echo $this->twig->render('admin/entreprises.twig', [
            'demandes'      => $demandes,
            'totalDemandes' => $totalDemandes,
            'pageCourante'  => $pageCourante,
            'totalPages'    => $totalPages,
            'success'       => $_SESSION['success'] ?? null,
            'error'         => $_SESSION['error'] ?? null,
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
        $model      = new EntrepriseModel();
        $search     = trim($_GET['search'] ?? '');
        $estAdmin   = ($_SESSION['user']['role'] ?? '') === 'admin';

        if ($estAdmin) {
            // L'admin voit toutes les entreprises (tous statuts, sans pagination)
            $entreprises = $model->findAllAdmin($search);
            echo $this->twig->render('entreprises.twig', [
                'entreprises' => $entreprises,
                'search'      => $search,
                'success'     => $_SESSION['success'] ?? null,
                'error'       => $_SESSION['error'] ?? null,
            ]);
            unset($_SESSION['success'], $_SESSION['error']);
        } else {
            // Les autres voient uniquement les entreprises approuvées avec pagination
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

    public function adminPiloteSupprimerPost(): void
    {
        $this->requireRole('admin');
        $idPilote = (int) ($_POST['id_pilote'] ?? 0);
        $piloteModel = new PiloteModel();
        $pilote = $piloteModel->findById($idPilote);
        if (!$pilote) {
            $_SESSION['error'] = 'Pilote introuvable.';
            header('Location: /admin/pilotes');
            exit;
        }
        $piloteModel->supprimer($idPilote);
        $_SESSION['success'] = 'Le compte de ' . $pilote['prenom'] . ' ' . $pilote['nom'] . ' a été supprimé.';
        header('Location: /admin/pilotes');
        exit;
    }

    public function adminPiloteModifier(): void
    {
        $this->requireRole('admin');
        $idPilote = (int) ($_GET['id'] ?? 0);
        $pilote   = (new PiloteModel())->findById($idPilote);
        if (!$pilote) {
            $_SESSION['error'] = 'Pilote introuvable.';
            header('Location: /admin/pilotes');
            exit;
        }
        echo $this->twig->render('admin/pilote-modifier.twig', [
            'pilote'  => $pilote,
            'error'   => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
        ]);
        unset($_SESSION['error'], $_SESSION['success']);
    }

    public function adminPiloteModifierPost(): void
    {
        $this->requireRole('admin');
        $idPilote   = (int) ($_POST['id_pilote'] ?? 0);
        $nom        = trim($_POST['nom'] ?? '');
        $prenom     = trim($_POST['prenom'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $ecole      = trim($_POST['ecole'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');

        if (!$nom || !$prenom || !$email) {
            $_SESSION['error'] = 'Nom, prénom et email sont obligatoires.';
            header('Location: /admin/pilote/modifier?id=' . $idPilote);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Adresse email invalide.';
            header('Location: /admin/pilote/modifier?id=' . $idPilote);
            exit;
        }

        $piloteModel  = new PiloteModel();
        $piloteActuel = $piloteModel->findById($idPilote);
        if (!$piloteActuel) {
            $_SESSION['error'] = 'Pilote introuvable.';
            header('Location: /admin/pilotes');
            exit;
        }

        // Vérifie l'unicité de l'email si changé
        if ($email !== $piloteActuel['email']) {
            $existant = (new UtilisateurModel())->findByEmail($email);
            if ($existant) {
                $_SESSION['error'] = 'Cette adresse email est déjà utilisée.';
                header('Location: /admin/pilote/modifier?id=' . $idPilote);
                exit;
            }
        }

        $hash = $motDePasse !== '' ? password_hash($motDePasse, PASSWORD_DEFAULT) : null;
        $piloteModel->modifier($idPilote, $nom, $prenom, $email, $ecole, $hash);

        $_SESSION['success'] = 'Compte pilote modifié avec succès.';
        header('Location: /admin/pilote?id=' . $idPilote);
        exit;
    }

    public function adminPiloteCreer(): void
    {
        $this->requireRole('admin');
        echo $this->twig->render('admin/pilote-creer.twig', [
            'error' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function adminPiloteCreerPost(): void
    {
        $this->requireRole('admin');
        $nom    = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');
        $ecole  = trim($_POST['ecole'] ?? '');

        if (!$nom || !$prenom || !$email || !$motDePasse) {
            $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: /admin/pilote/creer');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Adresse email invalide.';
            header('Location: /admin/pilote/creer');
            exit;
        }
        if ((new UtilisateurModel())->findByEmail($email)) {
            $_SESSION['error'] = 'Cette adresse email est déjà utilisée.';
            header('Location: /admin/pilote/creer');
            exit;
        }

        $idPilote = (new PiloteModel())->creer($nom, $prenom, $email, password_hash($motDePasse, PASSWORD_DEFAULT), $ecole);
        $_SESSION['success'] = 'Compte pilote créé avec succès.';
        header('Location: /admin/pilote?id=' . $idPilote);
        exit;
    }

    public function adminEtudiants(): void
    {
        $this->requireRoles(['admin', 'pilote']);
        $recherche     = trim($_GET['search'] ?? '');
        $nombreParPage = 10;
        $pageCourante  = max(1, (int) ($_GET['page'] ?? 1));
        $modele        = new PiloteModel();
        $totalEtudiants = $modele->compterTousEtudiants($recherche);
        $totalPages    = (int) ceil($totalEtudiants / $nombreParPage);
        $decalage      = ($pageCourante - 1) * $nombreParPage;
        $etudiants     = $modele->getTousEtudiants($recherche, $nombreParPage, $decalage);
        echo $this->twig->render('admin/etudiants.twig', [
            'etudiants'      => $etudiants,
            'recherche'      => $recherche,
            'totalEtudiants' => $totalEtudiants,
            'pageCourante'   => $pageCourante,
            'totalPages'     => $totalPages,
            'success'        => $_SESSION['success'] ?? null,
            'error'          => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminEtudiant(): void
    {
        $this->requireRoles(['admin', 'pilote']);
        $idEtudiant   = (int) ($_GET['id'] ?? 0);
        $piloteModel  = new PiloteModel();
        $etudiant     = $piloteModel->getEtudiantAdmin($idEtudiant);
        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /admin/etudiants');
            exit;
        }
        $candidatures = $piloteModel->getCandidaturesEtudiant($etudiant['id_utilisateur']);
        echo $this->twig->render('admin/etudiant.twig', [
            'etudiant'    => $etudiant,
            'candidatures' => $candidatures,
            'success'     => $_SESSION['success'] ?? null,
            'error'       => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminEtudiantCreer(): void
    {
        $this->requireRole('admin');
        $pilotes = (new PiloteModel())->getTous();
        echo $this->twig->render('admin/etudiant-creer.twig', [
            'pilotes' => $pilotes,
            'error'   => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function adminEtudiantCreerPost(): void
    {
        $this->requireRole('admin');
        $nom        = trim($_POST['nom'] ?? '');
        $prenom     = trim($_POST['prenom'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');
        $ecole      = trim($_POST['ecole'] ?? '');
        $idPilote   = ($_POST['id_pilote'] ?? '') !== '' ? (int) $_POST['id_pilote'] : null;

        if (!$nom || !$prenom || !$email || !$motDePasse) {
            $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: /admin/etudiant/creer');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Adresse email invalide.';
            header('Location: /admin/etudiant/creer');
            exit;
        }
        if ((new UtilisateurModel())->findByEmail($email)) {
            $_SESSION['error'] = 'Cette adresse email est déjà utilisée.';
            header('Location: /admin/etudiant/creer');
            exit;
        }

        (new PiloteModel())->creerEtudiantAdmin($idPilote, $nom, $prenom, $email, password_hash($motDePasse, PASSWORD_DEFAULT), $ecole);
        $_SESSION['success'] = 'Compte étudiant créé avec succès.';
        header('Location: /admin/etudiants');
        exit;
    }

    public function adminEtudiantModifier(): void
    {
        $this->requireRole('admin');
        $idEtudiant = (int) ($_GET['id'] ?? 0);
        $piloteModel = new PiloteModel();
        $etudiant   = $piloteModel->getEtudiantAdmin($idEtudiant);
        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /admin/etudiants');
            exit;
        }
        $pilotes = $piloteModel->getTous();
        echo $this->twig->render('admin/etudiant-modifier.twig', [
            'etudiant' => $etudiant,
            'pilotes'  => $pilotes,
            'error'    => $_SESSION['error'] ?? null,
            'success'  => $_SESSION['success'] ?? null,
        ]);
        unset($_SESSION['error'], $_SESSION['success']);
    }

    public function adminEtudiantModifierPost(): void
    {
        $this->requireRole('admin');
        $idEtudiant = (int) ($_POST['id_etudiant'] ?? 0);
        $nom        = trim($_POST['nom'] ?? '');
        $prenom     = trim($_POST['prenom'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $ecole      = trim($_POST['ecole'] ?? '');
        $promotion  = trim($_POST['promotion'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');
        $idPilote   = ($_POST['id_pilote'] ?? '') !== '' ? (int) $_POST['id_pilote'] : null;

        if (!$nom || !$prenom || !$email) {
            $_SESSION['error'] = 'Nom, prénom et email sont obligatoires.';
            header('Location: /admin/etudiant/modifier?id=' . $idEtudiant);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Adresse email invalide.';
            header('Location: /admin/etudiant/modifier?id=' . $idEtudiant);
            exit;
        }

        $piloteModel    = new PiloteModel();
        $etudiantActuel = $piloteModel->getEtudiantAdmin($idEtudiant);
        if (!$etudiantActuel) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /admin/etudiants');
            exit;
        }

        if ($email !== $etudiantActuel['email']) {
            $existant = (new UtilisateurModel())->findByEmail($email);
            if ($existant) {
                $_SESSION['error'] = 'Cette adresse email est déjà utilisée.';
                header('Location: /admin/etudiant/modifier?id=' . $idEtudiant);
                exit;
            }
        }

        $hash = $motDePasse !== '' ? password_hash($motDePasse, PASSWORD_DEFAULT) : null;
        $piloteModel->modifierEtudiantAdmin($idEtudiant, $nom, $prenom, $email, $ecole, $promotion, $idPilote, $hash);

        $_SESSION['success'] = 'Compte étudiant modifié avec succès.';
        header('Location: /admin/etudiant?id=' . $idEtudiant);
        exit;
    }

    public function adminEtudiantSupprimerPost(): void
    {
        $this->requireRole('admin');
        $idEtudiant  = (int) ($_POST['id_etudiant'] ?? 0);
        $piloteModel = new PiloteModel();
        $etudiant    = $piloteModel->getEtudiantAdmin($idEtudiant);
        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable.';
            header('Location: /admin/etudiants');
            exit;
        }
        $piloteModel->supprimerEtudiantAdmin($idEtudiant);
        $_SESSION['success'] = 'Le compte de ' . $etudiant['prenom'] . ' ' . $etudiant['nom'] . ' a été supprimé.';
        header('Location: /admin/etudiants');
        exit;
    }

    public function adminPilotes(): void
    {
        $this->requireRole('admin');
        $recherche     = trim($_GET['search'] ?? '');
        $nombreParPage = 10;
        $pageCourante  = max(1, (int) ($_GET['page'] ?? 1));
        $modele        = new PiloteModel();
        $totalPilotes  = $modele->compterTous($recherche);
        $totalPages    = (int) ceil($totalPilotes / $nombreParPage);
        $decalage      = ($pageCourante - 1) * $nombreParPage;
        $pilotes       = $modele->getTous($recherche, $nombreParPage, $decalage);
        echo $this->twig->render('admin/pilotes.twig', [
            'pilotes'      => $pilotes,
            'recherche'    => $recherche,
            'totalPilotes' => $totalPilotes,
            'pageCourante' => $pageCourante,
            'totalPages'   => $totalPages,
            'success'      => $_SESSION['success'] ?? null,
            'error'        => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function adminPilote(): void
    {
        $this->requireRole('admin');
        $idPilote    = (int) ($_GET['id'] ?? 0);
        $piloteModel = new PiloteModel();
        $pilote      = $piloteModel->findById($idPilote);
        if (!$pilote) {
            $_SESSION['error'] = 'Pilote introuvable.';
            header('Location: /admin/pilotes');
            exit;
        }
        $etudiants = $piloteModel->getEtudiants($idPilote);
        echo $this->twig->render('admin/pilote.twig', [
            'pilote'    => $pilote,
            'etudiants' => $etudiants,
            'success'   => $_SESSION['success'] ?? null,
            'error'     => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

}