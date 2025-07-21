<?php
session_start();
require_once '../config/db.php';
require_once '../includes/mailer.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$trajetId = $_POST['trajet_id'] ?? null;
$role = $_POST['role'] ?? null;

if (!$trajetId || !$role || !in_array($role, ['chauffeur', 'passager'])) {
    $_SESSION['error'] = "Paramètres invalides.";
    header("Location: ../pages/mes_trajets.php");
    exit;
}

try {
    if ($role === 'chauffeur') {
        // Vérifier que l'utilisateur est bien le conducteur du trajet
        $stmt = $pdo->prepare("SELECT id FROM covoiturages WHERE id = :id AND conducteur_id = :uid");
        $stmt->execute(['id' => $trajetId, 'uid' => $userId]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à annuler ce trajet.";
            header("Location: ../pages/mes_trajets.php");
            exit;
        }

        $pdo->beginTransaction();

        // Récupération des participants
        $stmt = $pdo->prepare("
            SELECT u.id AS utilisateur_id, u.email, u.pseudo, c.prix
            FROM participations p
            JOIN utilisateurs u ON u.id = p.utilisateur_id
            JOIN covoiturages c ON c.id = p.covoiturage_id
            WHERE p.covoiturage_id = :trajetId
        ");
        $stmt->execute(['trajetId' => $trajetId]);
        $participants = $stmt->fetchAll();

        // Supprimer les participations et le covoiturage
        $pdo->prepare("DELETE FROM participations WHERE covoiturage_id = :id")->execute(['id' => $trajetId]);
        $pdo->prepare("DELETE FROM covoiturages WHERE id = :id")->execute(['id' => $trajetId]);

        // Rembourser les participants + envoyer mail
        $stmtCredit = $pdo->prepare("UPDATE utilisateurs SET credits = credits + :prix WHERE id = :id");

        foreach ($participants as $p) {
            $stmtCredit->execute(['prix' => $p['prix'], 'id' => $p['utilisateur_id']]);

            $email = $p['email'];
            $pseudo = $p['pseudo'];
            $sujet = "❌ Covoiturage annulé - EcoRide";
            $message = "Bonjour $pseudo,

Le covoiturage auquel vous étiez inscrit a été annulé par le conducteur.

Vous avez été remboursé de {$p['prix']} crédits.

Merci de votre compréhension 🌱

L’équipe EcoRide";

            envoyerEmailParticipant($email, $pseudo, $sujet, $message);
        }

        $pdo->commit();

        $_SESSION['message'] = "Trajet annulé. Tous les participants ont été remboursés et informés.";
        header("Location: ../pages/mes_trajets.php");
        exit;

    } elseif ($role === 'passager') {
        // Vérifier la participation et récupérer le prix
        $stmt = $pdo->prepare("
            SELECT c.prix
            FROM participations p
            JOIN covoiturages c ON c.id = p.covoiturage_id
            WHERE p.covoiturage_id = :cid AND p.utilisateur_id = :uid
        ");
        $stmt->execute(['cid' => $trajetId, 'uid' => $userId]);
        $prix = $stmt->fetchColumn();

        if ($prix === false) {
            $_SESSION['error'] = "Participation introuvable.";
            header("Location: ../pages/mes_trajets.php");
            exit;
        }

        $pdo->beginTransaction();

        $pdo->prepare("DELETE FROM participations WHERE covoiturage_id = :cid AND utilisateur_id = :uid")
            ->execute(['cid' => $trajetId, 'uid' => $userId]);

        $pdo->prepare("UPDATE utilisateurs SET credits = credits + :prix WHERE id = :id")
            ->execute(['prix' => $prix, 'id' => $userId]);

        $pdo->prepare("UPDATE covoiturages SET places_disponibles = places_disponibles + 1 WHERE id = :cid")
            ->execute(['cid' => $trajetId]);

        // Envoi email au passager
        $stmt = $pdo->prepare("SELECT email, pseudo FROM utilisateurs WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if ($user) {
            $sujet = "📤 Désinscription confirmée - EcoRide";
            $message = "Bonjour {$user['pseudo']},

Vous vous êtes désinscrit du covoiturage (ID : $trajetId).

Vous avez été remboursé de $prix crédits.

Merci pour votre confiance 💚

L’équipe EcoRide";

            envoyerEmailParticipant($user['email'], $user['pseudo'], $sujet, $message);
        }

        $pdo->commit();

        $_SESSION['message'] = "Vous vous êtes désinscrit. Crédit remboursé et email envoyé.";
        header("Location: ../pages/mes_trajets.php");
        exit;
    }

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'annulation : " . $e->getMessage();
    header("Location: ../pages/mes_trajets.php");
    exit;
}
