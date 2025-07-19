<?php
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

    // Met à jour le statut du trajet
    $stmtUpdate = $pdo->prepare("UPDATE covoiturages SET statut = :statut WHERE id = :id");
    $stmtUpdate->execute(['statut' => $nouveauStatut, 'id' => $trajetId]);

    // Si le trajet est terminé, notifier tous les passagers par email via PHPMailer
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
            envoyerEmailParticipant($passager['email'], $passager['pseudo']);
        }
    }

    // Redirection vers la page détail du trajet
    header("Location: ../pages/detail.php?id=$trajetId");
    exit;
}
