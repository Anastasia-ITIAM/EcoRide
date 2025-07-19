<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db.php';

// Récupération des données du formulaire
$pseudo = trim($_POST['pseudo'] ?? '');
$email = trim($_POST['email'] ?? '');
$motdepasse = $_POST['motdepasse'] ?? '';
$confirmer = $_POST['confirmer_motdepasse'] ?? '';
$conditions = isset($_POST['conditions']);

$erreurs = [];

// Sauvegarde temporaire des anciennes données pour réafficher en cas d'erreur
$_SESSION['old'] = [
    'pseudo' => $pseudo,
    'email' => $email
];

// Vérifications de base
if (!$conditions) {
    $erreurs[] = "Vous devez accepter les conditions d'utilisation.";
}
if (empty($pseudo) || empty($email) || empty($motdepasse) || empty($confirmer)) {
    $erreurs[] = "Tous les champs sont obligatoires.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Adresse email invalide.";
}
if ($motdepasse !== $confirmer) {
    $erreurs[] = "Les mots de passe ne correspondent pas.";
}
if (strlen($motdepasse) < 6) {
    $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
}

// Vérifier si l'email ou pseudo est déjà utilisé
if (empty($erreurs)) {
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR pseudo = ?");
    $stmt->execute([$email, $pseudo]);
    if ($stmt->fetch()) {
        $erreurs[] = "Cet email ou pseudo est déjà utilisé.";
    }
}

// Si pas d'erreurs, insertion dans la base
if (empty($erreurs)) {
    $motdepasse_hash = password_hash($motdepasse, PASSWORD_DEFAULT);

    // Insertion dans la BDD avec 20 crédits par défaut
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe, credits) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pseudo, $email, $motdepasse_hash, 20]);

    // Connexion automatique après inscription
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_pseudo'] = $pseudo;
    $_SESSION['user_credits'] = 20;

    $_SESSION['success_message'] = "Bienvenue $pseudo ! Vous faites désormais partie de la famille EcoRide 💚 20 crédits vous ont été offerts 🎁";

    // Nettoyage
    unset($_SESSION['old']);

    // Redirection personnalisée après inscription
    $redirect = $_SESSION['redirect_after_register'] ?? '../pages/recherche.php?inscription=success';
    unset($_SESSION['redirect_after_register']);
    header("Location: $redirect");
    exit;
} else {
    // Sauvegarde des erreurs en session et retour au formulaire
    $_SESSION['form_erreurs'] = $erreurs;
    header("Location: ../pages/inscription.php");
    exit;
}
