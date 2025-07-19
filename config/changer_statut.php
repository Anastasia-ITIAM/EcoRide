<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/mailer.php';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trajetId = $_POST['trajet_id'] ?? null;
    $nouveauStatut = $_POST['nouveau_statut'] ?? null;

    if (!$trajetId || !$nouveauStatut) {
        die("Paramètres manquants.");
    }

    // Vérifie que le chauffeur est bien connecté
    $conducteurId = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare("SELECT * FROM covoiturages WHERE id = :id AND conducteur_id = :cid");
    $stmt->execute(['id' => $trajetId, 'cid' => $conducteurId]);
    $trajet = $stmt->fetch();

    if (!$trajet) {
        die("Trajet introuvable ou non autorisé.");
    }

    // Met à jour le statut
    $stmtUpdate = $pdo->prepare("UPDATE covoiturages SET statut = :statut WHERE id = :id");
    $stmtUpdate->execute(['statut' => $nouveauStatut, 'id' => $trajetId]);

    // Si le trajet est terminé, notifier les passagers
    if ($nouveauStatut === 'termine') {
        $stmtPassagers = $pdo->prepare("
            SELECT u.email, u.pseudo 
            FROM participations p 
            JOIN utilisateurs u ON p.utilisateur_id = u.id 
            WHERE p.covoiturage_id = :id
        ");
        $stmtPassagers->execute(['id' => $trajetId]);
        $passagers = $stmtPassagers->fetchAll();

        foreach ($passagers as $passager) {
            // Ici tu peux utiliser mail() ou une lib comme PHPMailer
            mail(
                $passager['email'],
                "Merci pour votre trajet",
                "Bonjour {$passager['pseudo']},\nMerci pour votre participation.\nMerci de vous connecter à votre espace pour valider le bon déroulement du trajet."
            );
        }
    }

    header("Location: ../pages/detail.php?id=$trajetId");
    exit;
}
