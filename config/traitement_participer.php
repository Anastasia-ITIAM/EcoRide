<?php
session_start();

require_once __DIR__ . '/db.php';

// Vérifier que les données POST sont bien présentes et valides
if (
    !isset($_POST['confirm'], $_POST['covoiturage_id']) ||
    $_POST['confirm'] !== 'yes' ||
    !is_numeric($_POST['covoiturage_id']) ||
    !isset($_SESSION['user_id'])
) {
    $_SESSION['error_message'] = "Requête invalide.";
    header("Location: ../pages/recherche.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$covoiturageId = (int) $_POST['covoiturage_id'];

try {
    // Vérifier si l'utilisateur a déjà réservé ce trajet
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE utilisateur_id = :userId AND covoiturage_id = :covoiturageId");
    $stmt->execute(['userId' => $userId, 'covoiturageId' => $covoiturageId]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error_message'] = "Vous avez déjà réservé une place pour ce trajet.";
        header("Location: ../pages/mes_trajets.php");
        exit;
    }

    // Récupérer les infos du trajet
    $stmt = $pdo->prepare("SELECT * FROM covoiturages WHERE id = :id FOR UPDATE"); // lock pour éviter race conditions
    $stmt->execute(['id' => $covoiturageId]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trajet) {
        $_SESSION['error_message'] = "Trajet introuvable.";
        header("Location: ../pages/recherche.php");
        exit;
    }

    // Vérifier s'il reste des places
    if ($trajet['places_disponibles'] <= 0) {
        $_SESSION['error_message'] = "Il n'y a plus de places disponibles.";
        header("Location: ../pages/detail.php?id=" . $covoiturageId);
        exit;
    }

    // Vérifier les crédits de l'utilisateur
    $stmt = $pdo->prepare("SELECT credits FROM utilisateurs WHERE id = :id FOR UPDATE"); // lock pour éviter race conditions
    $stmt->execute(['id' => $userId]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utilisateur) {
        $_SESSION['error_message'] = "Utilisateur introuvable.";
        header("Location: ../pages/recherche.php");
        exit;
    }

    if ($utilisateur['credits'] < $trajet['prix']) {
        $_SESSION['error_message'] = "Crédits insuffisants.";
        header("Location: ../pages/detail.php?id=" . $covoiturageId);
        exit;
    }

    // Transaction pour éviter les conflits
    $pdo->beginTransaction();

    // Déduire les crédits
    $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits - :prix WHERE id = :id");
    $stmt->execute([
        'prix' => $trajet['prix'],
        'id' => $userId
    ]);

    // Réduire les places disponibles
    $stmt = $pdo->prepare("UPDATE covoiturages SET places_disponibles = places_disponibles - 1 WHERE id = :id");
    $stmt->execute(['id' => $covoiturageId]);

    // Enregistrer la participation
    $stmt = $pdo->prepare("
        INSERT INTO participations (utilisateur_id, covoiturage_id, date_participation)
        VALUES (:user_id, :covoiturage_id, NOW())
    ");
    $stmt->execute([
        'user_id' => $userId,
        'covoiturage_id' => $covoiturageId
    ]);

    $pdo->commit();

    // Mettre à jour la session côté client pour les crédits
    $_SESSION['user_credits'] = $utilisateur['credits'] - $trajet['prix'];
    $_SESSION['success_message'] = "Vous avez bien réservé ce trajet.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur participation trajet : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors de la réservation. Veuillez réessayer.";
}

// Redirection vers la page Mes trajets
header("Location: ../pages/mes_trajets.php");
exit;
