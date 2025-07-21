<?php
session_start();
require_once '../config/db.php';

// Vérification du rôle employé
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'employe') {
    header("Location: ../connexion.php");
    exit;
}

// Traitement de la validation ou du refus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avis_id'], $_POST['action'])) {
    $stmt = $pdo->prepare("UPDATE validation_trajet SET validation_employe = :statut, date_validation = NOW() WHERE id = :id");
    $stmt->execute([
        'statut' => $_POST['action'] === 'valider' ? 'valide' : 'refuse',
        'id' => $_POST['avis_id']
    ]);
    header("Location: moderation_avis.php");
    exit;
}

// Récupération des avis en attente
$stmtAvis = $pdo->query("
    SELECT vt.id, vt.note, vt.commentaire, vt.date_validation,
           u1.pseudo AS auteur, u1.email AS email_auteur,
           u2.pseudo AS conducteur_pseudo, u2.email AS conducteur_email,
           c.id AS trajet_id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.heure_arrivee
    FROM validation_trajet vt
    JOIN utilisateurs u1 ON vt.utilisateur_id = u1.id
    JOIN covoiturages c ON vt.covoiturage_id = c.id
    JOIN utilisateurs u2 ON c.conducteur_id = u2.id
    WHERE vt.validation_employe = 'en_attente'
    ORDER BY vt.date_validation DESC
");

$avis_en_attente = $stmtAvis->fetchAll();

// Trajets signalés ou notés 1 ou 2
$stmtProblemes = $pdo->query("
    SELECT 
        c.id AS trajet_id,
        c.date_depart,
        CONCAT(c.date_depart, ' ', c.heure_arrivee) AS date_arrivee,
        c.adresse_depart AS lieu_depart,
        c.adresse_arrivee AS lieu_arrivee,
        u1.pseudo AS conducteur_pseudo,
        u1.email AS conducteur_email,
        u2.pseudo AS passager_pseudo,
        u2.email AS passager_email
    FROM covoiturages c
    JOIN utilisateurs u1 ON c.conducteur_id = u1.id
    JOIN participations p ON c.id = p.covoiturage_id
    JOIN utilisateurs u2 ON p.utilisateur_id = u2.id
    WHERE c.id IN (
           SELECT covoiturage_id FROM validation_trajet 
           WHERE note <= 2 AND statut = 'valide'
       )
");


$trajets_problemes = $stmtProblemes->fetchAll();
?>

<?php require_once '../templates/header.php'; ?>

<div class="container mt-4">
    <h2 class="text-center">🛠️ Espace Employé – Modération des Avis</h2>

    <h4 class="mt-5">📝 Avis en attente</h4>
    <?php if ($avis_en_attente): ?>
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Auteur</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Trajet</th>
                        <th>Date</th>
                        <th>Conducteur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avis_en_attente as $avis): ?>
                        <tr>
                            <td><?= htmlspecialchars($avis['auteur']) ?></td>
                            <td><?= (int)$avis['note'] ?>/5</td>
                            <td><?= nl2br(htmlspecialchars($avis['commentaire'])) ?></td>
                            <td>#<?= $avis['trajet_id'] ?><br>
                                <?= htmlspecialchars($avis['adresse_depart']) ?> → <?= htmlspecialchars($avis['adresse_arrivee']) ?><br>
                                <?= htmlspecialchars($avis['date_depart']) ?> à <?= htmlspecialchars($avis['heure_depart']) ?>
                            </td>
                            <td><?= $avis['date_validation'] ?? '—' ?></td>
                            <td>
                                <?= htmlspecialchars($avis['conducteur_pseudo']) ?><br>
                                <small><?= htmlspecialchars($avis['conducteur_email']) ?></small>
                            </td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="avis_id" value="<?= $avis['id'] ?>">
                                    <button type="submit" name="action" value="valider" class="btn btn-success btn-sm">Valider</button>
                                    <button type="submit" name="action" value="refuser" class="btn btn-danger btn-sm">Refuser</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Aucun avis à modérer pour le moment.</p>
    <?php endif; ?>

    <h4 class="mt-5">🚨 Trajets signalés ou problématiques</h4>
    <?php if ($trajets_problemes): ?>
        <div class="table-responsive">
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>#Trajet</th>
                        <th>Date Départ</th>
                        <th>Date Arrivée</th>
                        <th>Lieu Départ</th>
                        <th>Lieu Arrivée</th>
                        <th>Conducteur</th>
                        <th>Passager</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trajets_problemes as $t): ?>
                        <tr>
                            <td><?= $t['trajet_id'] ?></td>
                            <td><?= $t['date_depart'] ?></td>
                            <td><?= $t['date_arrivee'] ?></td>
                            <td><?= htmlspecialchars($t['lieu_depart']) ?></td>
                            <td><?= htmlspecialchars($t['lieu_arrivee']) ?></td>
                            <td>
                                <?= htmlspecialchars($t['conducteur_pseudo']) ?><br>
                                <small><?= htmlspecialchars($t['conducteur_email']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['passager_pseudo']) ?><br>
                                <small><?= htmlspecialchars($t['passager_email']) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Aucun trajet signalé ou problématique pour l’instant.</p>
    <?php endif; ?>
</div>

<?php require_once '../templates/footer.php'; ?>
