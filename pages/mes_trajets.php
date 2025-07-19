<?php
session_start();
require_once '../config/db.php';
require_once '../templates/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center my-4'>Vous devez être connecté pour voir vos trajets réservés.</div>";
    require_once '../templates/footer.php';
    exit;
}

$userId = $_SESSION['user_id'];

// Trajets à valider pour le passager : trajets terminés, non validés, où l'utilisateur est passager
$sqlValider = "
SELECT c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.heure_arrivee, c.prix,
       c.statut, 'passager' AS role, u.pseudo AS conducteur
FROM participations p
JOIN covoiturages c ON p.covoiturage_id = c.id
JOIN utilisateurs u ON c.conducteur_id = u.id
WHERE p.utilisateur_id = :user_id
  AND c.statut = 'termine'
  AND (p.est_valide IS NULL OR p.est_valide = 0)
  AND c.conducteur_id != :user_id
ORDER BY c.date_depart DESC, c.heure_depart DESC
";

$stmtValider = $pdo->prepare($sqlValider);
$stmtValider->execute(['user_id' => $userId]);
$trajetsAValider = $stmtValider->fetchAll(PDO::FETCH_ASSOC);

// Tous les trajets de l'utilisateur (chauffeur et passager), avec validation du passager si applicable
$sql = "
(
    SELECT c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.heure_arrivee, c.prix,
           c.statut, 'chauffeur' AS role, u.pseudo AS conducteur, NULL AS est_valide
    FROM covoiturages c
    JOIN utilisateurs u ON c.conducteur_id = u.id
    WHERE c.conducteur_id = :user_id
)
UNION
(
    SELECT c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.heure_arrivee, c.prix,
           c.statut, 'passager' AS role, u.pseudo AS conducteur, p.est_valide
    FROM participations p
    JOIN covoiturages c ON p.covoiturage_id = c.id
    JOIN utilisateurs u ON c.conducteur_id = u.id
    WHERE p.utilisateur_id = :user_id
)
ORDER BY date_depart DESC, heure_depart DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $userId]);
$allTrajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trier en "À venir" / "Passés" (exclure les trajets à valider)
$trajetsAVenir = [];
$trajetsPasses = [];

foreach ($allTrajets as $trajet) {
    // Exclure les trajets passager terminés non validés (à valider)
    if (
        $trajet['statut'] === 'termine' &&
        $trajet['role'] === 'passager' &&
        ($trajet['est_valide'] === null || $trajet['est_valide'] == 0)
    ) {
        continue;
    }

    if ($trajet['statut'] === 'termine') {
        $trajetsPasses[] = $trajet;
    } else {
        $trajetsAVenir[] = $trajet;
    }
}

// Affichage message flash
if (!empty($_SESSION['message'])) {
    echo "<div class='alert alert-info text-center mt-4'>" . htmlspecialchars($_SESSION['message']) . "</div>";
    unset($_SESSION['message']);
}
?>

<div class="container my-5">
  <h2 class="text-center mb-4">Mes trajets ✅</h2>

  <ul class="nav nav-tabs justify-content-center mb-4" id="trajetsTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link eco-box active" id="avenir-tab" data-bs-toggle="tab" data-bs-target="#avenir" type="button" role="tab" aria-controls="avenir" aria-selected="true">À venir</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link eco-box" id="a-valider-tab" data-bs-toggle="tab" data-bs-target="#a-valider" type="button" role="tab" aria-controls="a-valider" aria-selected="false">À valider</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link eco-box" id="passes-tab" data-bs-toggle="tab" data-bs-target="#passes" type="button" role="tab" aria-controls="passes" aria-selected="false">Passés</button>
    </li>
  </ul>

  <div class="tab-content" id="trajetsTabContent">

    <!-- À venir -->
    <div class="tab-pane fade show active" id="avenir" role="tabpanel" aria-labelledby="avenir-tab">
      <?php if (empty($trajetsAVenir)): ?>
        <div class="alert alert-info text-center">Aucun trajet à venir.</div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($trajetsAVenir as $trajet): ?>
            <div class="col-md-6 mb-4">
              <div class="card eco-box shadow-sm p-3">
                <h5 class="card-title">Trajet vers <?= htmlspecialchars($trajet['adresse_arrivee']) ?></h5>
                <p class="card-text">
                  <strong>Départ :</strong> <?= htmlspecialchars($trajet['adresse_depart']) ?><br>
                  <strong>Arrivée :</strong> <?= htmlspecialchars($trajet['adresse_arrivee']) ?><br>
                  <strong>Date :</strong> <?= htmlspecialchars($trajet['date_depart']) ?> à <?= substr($trajet['heure_depart'], 0, 5) ?><br>
                  <strong>Conducteur :</strong> <?= htmlspecialchars($trajet['conducteur']) ?><br>
                  <strong>Prix :</strong> <?= htmlspecialchars($trajet['prix']) ?> crédits<br>
                  <strong style="color: red;">Rôle :</strong> <?= htmlspecialchars(ucfirst($trajet['role'])) ?><br>
                </p>
                <a href="detail.php?id=<?= $trajet['id'] ?>" class="btn custom-btn">Voir les détails</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- À valider -->
    <div class="tab-pane fade" id="a-valider" role="tabpanel" aria-labelledby="a-valider-tab">
      <?php if (empty($trajetsAValider)): ?>
        <div class="alert alert-info text-center">Aucun trajet à valider.</div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($trajetsAValider as $trajet): ?>
            <div class="col-md-6 mb-4">
              <div class="card eco-box shadow-sm p-3">
                <h5 class="card-title">Trajet vers <?= htmlspecialchars($trajet['adresse_arrivee']) ?></h5>
                <p class="card-text">
                  <strong>Départ :</strong> <?= htmlspecialchars($trajet['adresse_depart']) ?><br>
                  <strong>Arrivée :</strong> <?= htmlspecialchars($trajet['adresse_arrivee']) ?><br>
                  <strong>Date :</strong> <?= htmlspecialchars($trajet['date_depart']) ?> à <?= substr($trajet['heure_depart'], 0, 5) ?><br>
                  <strong>Conducteur :</strong> <?= htmlspecialchars($trajet['conducteur']) ?><br>
                  <strong>Prix :</strong> <?= htmlspecialchars($trajet['prix']) ?> crédits<br>
                  <strong style="color: red;">Rôle :</strong> <?= htmlspecialchars(ucfirst($trajet['role'])) ?><br>
                </p>
                <a href="valider_trajet.php?id=<?= $trajet['id'] ?>" class="btn custom-btn">Valider ce trajet</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Passés -->
    <div class="tab-pane fade" id="passes" role="tabpanel" aria-labelledby="passes-tab">
      <?php if (empty($trajetsPasses)): ?>
        <div class="alert alert-info text-center">Aucun trajet passé.</div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($trajetsPasses as $trajet): ?>
            <div class="col-md-6 mb-4">
              <div class="card eco-box shadow-sm p-3">
                <h5 class="card-title">Trajet vers <?= htmlspecialchars($trajet['adresse_arrivee']) ?></h5>
                <p class="card-text">
                  <strong>Départ :</strong> <?= htmlspecialchars($trajet['adresse_depart']) ?><br>
                  <strong>Arrivée :</strong> <?= htmlspecialchars($trajet['adresse_arrivee']) ?><br>
                  <strong>Date :</strong> <?= htmlspecialchars($trajet['date_depart']) ?> à <?= substr($trajet['heure_depart'], 0, 5) ?><br>
                  <strong>Conducteur :</strong> <?= htmlspecialchars($trajet['conducteur']) ?><br>
                  <strong>Prix :</strong> <?= htmlspecialchars($trajet['prix']) ?> crédits<br>
                  <strong style="color: red;">Rôle :</strong> <?= htmlspecialchars(ucfirst($trajet['role'])) ?><br>
                </p>
                <a href="detail.php?id=<?= $trajet['id'] ?>" class="btn custom-btn">Voir les détails</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once '../templates/footer.php'; ?>
