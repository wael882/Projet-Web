<?php

namespace App\Controllers;
use App\Models\UtilisateurModel;
use App\Models\OffreModel;

class PageController {

    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    // -------------------------------------------------------------------------
    // Helpers : sécurité
    // -------------------------------------------------------------------------

    /**
     * Redirige vers /identification si l'utilisateur n'est pas connecté.
     * Si $roles est fourni, vérifie aussi que le rôle de l'utilisateur est autorisé.
     */
    private function requireAuth(array $roles = []): void {
        if (empty($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /identification');
            exit;
        }
        if (!empty($roles) && !in_array($_SESSION['user']['role'], $roles)) {
            $_SESSION['error'] = 'Accès interdit : vous n\'avez pas les permissions nécessaires.';
            header('Location: /acceuil');
            exit;
        }
    }

    /**
     * Redirige vers /acceuil si l'utilisateur est déjà connecté (pour login/inscription).
     */
    private function redirectIfAuth(): void {
        if (!empty($_SESSION['user'])) {
            header('Location: /acceuil');
            exit;
        }
    }

    /**
     * Valide le token CSRF du formulaire POST.
     */
    private function validateCsrfToken(): bool {
        $token = $_POST['csrf_token'] ?? '';
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // -------------------------------------------------------------------------
    // Pages publiques
    // -------------------------------------------------------------------------

    public function index(): void {
        echo $this->twig->render('index.twig');
    }

    public function offre_index(): void {
        echo $this->twig->render('offre-index.twig');
    }

    public function a_propos(): void {
        echo $this->twig->render('a-propos.twig');
    }

    // -------------------------------------------------------------------------
    // Authentification
    // -------------------------------------------------------------------------

    public function identification(): void {
        $this->redirectIfAuth();
        echo $this->twig->render('identification.twig', [
            'success' => $_SESSION['success'] ?? null,
            'erreur'  => $_SESSION['error']   ?? null,
        ]);
        unset($_SESSION['success'], $_SESSION['error']);
    }

    public function login(): void {
        if (!$this->validateCsrfToken()) {
            $_SESSION['error'] = 'Requête invalide (CSRF). Veuillez réessayer.';
            header('Location: /identification');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo $this->twig->render('identification.twig', [
                'erreur' => 'Veuillez remplir tous les champs.',
            ]);
            return;
        }

        $model      = new UtilisateurModel();
        $utilisateur = $model->findByEmail($email);

        if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe_hash'])) {
            // Connexion réussie
            session_regenerate_id(true);
            $_SESSION['user']      = $utilisateur;
            $_SESSION['tentative'] = 0;
            header('Location: /acceuil');
            exit;
        }

        // Échec
        $_SESSION['tentative'] = ($_SESSION['tentative'] ?? 0) + 1;
        if ($_SESSION['tentative'] >= 3) {
            $_SESSION['tentative'] = 0;
            $_SESSION['error'] = 'Trop de tentatives. Veuillez réessayer plus tard.';
            header('Location: /identification');
            exit;
        }

        $restantes = 3 - $_SESSION['tentative'];
        echo $this->twig->render('identification.twig', [
            'erreur' => "Email ou mot de passe incorrect. ($restantes tentative(s) restante(s))",
        ]);
    }

    public function inscription(): void {
        $this->redirectIfAuth();
        echo $this->twig->render('inscription.twig', [
            'erreur' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['error']);
    }

    public function inscriptionPost(): void {
        if (!$this->validateCsrfToken()) {
            $_SESSION['error'] = 'Requête invalide (CSRF). Veuillez réessayer.';
            header('Location: /inscription');
            exit;
        }

        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $ecole  = trim($_POST['ecole']  ?? '');
        $password = $_POST['password']  ?? '';

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            header('Location: /inscription');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            header('Location: /inscription');
            exit;
        }

        $model  = new UtilisateurModel();
        $existe = $model->findByEmail($email);

        if ($existe) {
            $_SESSION['error'] = 'Un compte est déjà associé à cette adresse email.';
            header('Location: /inscription');
            exit;
        }

        $hash        = password_hash($password, PASSWORD_DEFAULT);
        $utilisateur = $model->create($nom, $prenom, $email, $hash, 3, $ecole);

        if ($utilisateur) {
            $_SESSION['success'] = 'Inscription réussie, connectez-vous.';
            header('Location: /identification');
            exit;
        }

        $_SESSION['error'] = 'Erreur lors de la création du compte. Veuillez réessayer.';
        header('Location: /inscription');
        exit;
    }

    public function deconnexion(): void {
        session_destroy();
        header('Location: /');
        exit;
    }

    // -------------------------------------------------------------------------
    // Pages authentifiées
    // -------------------------------------------------------------------------

    public function acceuil(): void {
        $this->requireAuth();
        echo $this->twig->render('acceuil.twig');
    }

    public function rechercher(): void {
        $this->requireAuth();
        $model     = new OffreModel();
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $limite    = 10;
        $offset    = ($page - 1) * $limite;
        $rechercher = $model->findAll($limite, $offset);
        $total      = $model->count();
        $totalPages = (int) ceil($total / $limite);
        echo $this->twig->render('rechercher.twig', [
            'rechercher' => $rechercher,
            'page'        => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    public function offre(): void {
        $this->requireAuth();
        $model = new OffreModel();
        $offre = $model->findById((int) ($_GET['id'] ?? 0));
        if ($offre) {
            echo $this->twig->render('offre.twig', ['offre' => $offre]);
        } else {
            $_SESSION['error'] = "L'offre demandée est introuvable.";
            header('Location: /rechercher');
            exit;
        }
    }

    public function entreprise(): void {
        $this->requireAuth();
        echo $this->twig->render('entreprise.twig');
    }

    public function profil(): void {
        $this->requireAuth();
        echo $this->twig->render('profil.twig');
    }

    // -------------------------------------------------------------------------
    // Pages réservées aux étudiants (+ admin)
    // -------------------------------------------------------------------------

    public function candidature(): void {
        $this->requireAuth(['etudiant', 'admin', 'pilote']);
        echo $this->twig->render('candidature.twig');
    }

    public function favoris(): void {
        $this->requireAuth(['etudiant', 'admin']);
        echo $this->twig->render('favoris.twig');
    }
}
