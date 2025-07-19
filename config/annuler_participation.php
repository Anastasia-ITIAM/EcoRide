<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour annuler un trajet.";
    header('Location: ../pages/connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];

if (empty($_POST['covoiturage_id']) || !is_numeric($_POST['covoiturage_id'])) {
    $_SESSION['error_message'] = "Identifiant de trajet invalide.";
    header('Location: ../pages/mes_trajets.php');
    exit;
}

$covoiturageId = (int) $_POST['covoiturage_id'];

// Vérifier que la participation existe
$stmt = $pdo->prepare("SELECT * FROM participations WHERE utilisateur_id = :user_id AND covoiturage_id = :covoiturage_id");
$stmt->execute([
    'user_id' => $userId,
    'covoiturage_id' => $covoiturageId
]);
$participation = $stmt->fetch();

if (!$participation) {
    $_SESSION['error_message'] = "Vous n'avez pas réservé ce trajet.";
    header('Location: ../pages/mes_trajets.php');
    exit;
}

// Récupérer le prix du trajet
$stmt = $pdo->prepare("SELECT prix FROM covoiturages WHERE id = :id");
$stmt->execute(['id' => $covoiturageId]);
$trajet = $stmt->fetch();

if (!$trajet) {
    $_SESSION['error_message'] = "Trajet introuvable.";
    header('Location: ../pages/mes_trajets.php');
    exit;
}

$prix = $trajet['prix'];

try {
    $pdo->beginTransaction();

    // Supprimer la participation
    $stmt = $pdo->prepare("DELETE FROM participations WHERE utilisateur_id = :user_id AND covoiturage_id = :covoiturage_id");
    $stmt->execute([
        'user_id' => $userId,
        'covoiturage_id' => $covoiturageId
    ]);

    // Rendre la place disponible
    $stmt = $pdo->prepare("UPDATE covoiturages SET places_disponibles = places_disponibles + 1 WHERE id = :id");
    $stmt->execute(['id' => $covoiturageId]);

    // Rembourser les crédits
    $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits + :prix WHERE id = :id");
    $stmt->execute([
        'prix' => $prix,
        'id' => $userId
    ]);

    // Mettre à jour la session
    $_SESSION['user_credits'] += $prix;

    $pdo->commit();

    $_SESSION['success_message'] = "Votre participation a été annulée. $prix crédits vous ont été remboursés.";
    header('Location: ../pages/mes_trajets.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Une erreur est survenue lors de l'annulation.";
    header('Location: ../pages/mes_trajets.php');
    exit;
}
