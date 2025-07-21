<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$trajetId = $_GET['id'] ?? null;

if (!$trajetId || !is_numeric($trajetId)) {
    die("ID trajet invalide.");
}

// Vérifie s’il existe déjà une validation
$stmtCheck = $pdo->prepare("
    SELECT v.id AS validation_id, v.statut, v.commentaire, v.note, p.id AS participation_id
    FROM validation_trajet v
    JOIN participations p ON v.covoiturage_id = p.covoiturage_id AND v.utilisateur_id = p.utilisateur_id
    WHERE v.covoiturage_id = :trajetId AND v.utilisateur_id = :userId
");
$stmtCheck->execute(['trajetId' => $trajetId, 'userId' => $userId]);
$validation = $stmtCheck->fetch();

if (!$validation) {
    //  Vérifie que l'utilisateur est bien passager sur un trajet terminé
    $stmtParticipation = $pdo->prepare("
        SELECT p.id AS participation_id
        FROM participations p
        JOIN covoiturages c ON p.covoiturage_id = c.id
        WHERE p.utilisateur_id = :userId
          AND p.covoiturage_id = :trajetId
          AND c.statut = 'termine'
    ");
    $stmtParticipation->execute(['userId' => $userId, 'trajetId' => $trajetId]);
    $participation = $stmtParticipation->fetch();

    if (!$participation) {
        die("Ce trajet n'existe pas, n'est pas terminé ou vous n'êtes pas autorisé.");
    }

    $participationId = $participation['participation_id'];
} else {
    // Déjà validé ?
    if ($validation['statut'] === 'valide') {
        $_SESSION['message'] = "Vous avez déjà validé ce trajet.";
        header("Location: mes_trajets.php");
        exit;
    }

    $participationId = $validation['participation_id'];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentaire = trim($_POST['commentaire'] ?? '');
    $note = isset($_POST['note']) && is_numeric($_POST['note']) ? intval($_POST['note']) : null;

    if ($note < 1 || $note > 5) {
        $note = null; // sécurité
    }

    if ($validation) {
        // Mise à jour d'une validation existante
        $stmtUpdate = $pdo->prepare("
            UPDATE validation_trajet 
            SET statut = 'valide', commentaire = :commentaire, note = :note, date_validation = NOW()
            WHERE id = :id
        ");
        $stmtUpdate->execute([
            'commentaire' => $commentaire,
            'note' => $note,
            'id' => $validation['validation_id']
        ]);
    } else {
        // Insertion d'une nouvelle validation
        $stmtInsert = $pdo->prepare("
            INSERT INTO validation_trajet 
            (covoiturage_id, utilisateur_id, statut, commentaire, note, date_validation) 
            VALUES (:trajetId, :userId, 'valide', :commentaire, :note, NOW())
        ");
        $stmtInsert->execute([
            'trajetId' => $trajetId,
            'userId' => $userId,
            'commentaire' => $commentaire,
            'note' => $note
        ]);
    }

    //  Mise à jour participation
    $stmtUpdateParticipation = $pdo->prepare("
        UPDATE participations 
        SET est_valide = 1 
        WHERE id = :participationId
    ");
    $stmtUpdateParticipation->execute(['participationId' => $participationId]);

    $_SESSION['message'] = "Trajet validé avec succès ! Merci pour votre retour.";
    header("Location: mes_trajets.php");
    exit;
}

require_once '../templates/header.php';
?>

<!-- Formulaire de validation -->
<div class="container my-4">
    <h2 class="text-center mb-4">Valider le trajet ✅</h2>
    <p class="lead text-center">Merci d’avoir voyagé avec EcoRide 🌱. Vous pouvez confirmer que le trajet s’est bien déroulé et laisser un avis.</p>

    <form method="post">
    <input type="hidden" name="covoiturage_id" value="<?= htmlspecialchars($trajetId) ?>">
    <div class="mb-3">
        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
        <textarea name="commentaire" id="commentaire" class="form-control" rows="4"><?= htmlspecialchars($validation['commentaire'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label for="note" class="form-label">Note sur 5</label>
        <input type="number" name="note" id="note" min="1" max="5" class="form-control" value="<?= htmlspecialchars($validation['note'] ?? '') ?>">
    </div>
    <button type="submit" class="btn custom-btn">Valider le trajet</button>
    <a href="mes_trajets.php" class="btn btn-danger">Annuler</a>
</form>
</div>

<?php require_once '../templates/footer.php'; ?>
