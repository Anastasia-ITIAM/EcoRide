<?php
session_start();
require_once '../config/db.php'; // Connexion à la BDD
require_once '../templates/header.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$erreur = '';

// Enregistrer la page précédente si elle est fournie en GET
if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';

    if (!empty($pseudo) && !empty($motdepasse)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = :pseudo");
        $stmt->execute(['pseudo' => $pseudo]);
        $utilisateur = $stmt->fetch();
        var_dump($pseudo);
        var_dump($motdepasse);
        var_dump($utilisateur['mot_de_passe']);
        var_dump(password_verify($motdepasse, $utilisateur['mot_de_passe']));




        if ($utilisateur && password_verify($motdepasse, $utilisateur['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_pseudo'] = $utilisateur['pseudo'];
            $_SESSION['user_role'] = $utilisateur['role'];
            $_SESSION['user_credits'] = $utilisateur['credits'];

            // Redirection vers la page précédente ou accueil
            $redirect = $_SESSION['redirect_after_login'] ?? '/EcoRide/index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit();
        } else {
            $erreur = "Pseudo ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<div class="container my-5">
    <h2 class="text-center mb-4" style="color: var(--eco-text);">Reconnectez-vous à l’aventure verte 🌿</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="row g-3 col-md-6 mx-auto">
        <div class="col-12">
            <label for="pseudo" class="form-label">Pseudo</label>
            <input type="text" class="form-control" name="pseudo" id="pseudo" required />
        </div>

        <div class="col-12">
            <label for="motdepasse" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" name="motdepasse" id="motdepasse" required />
        </div>

        <div class="col-12 text-center">
            <button type="submit" class="btn custom-btn">Se connecter</button>
        </div>

        <div class="col-12 text-center">
            <p class="mt-3">Pas encore inscrit ?
                <a href="/EcoRide/pages/inscription.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" class="eco-link">
                    Créer un compte
                </a>
            </p>
        </div>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
