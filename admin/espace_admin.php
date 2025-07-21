<?php
session_start();
require_once '../config/db.php';

// Vérification rôle admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrateur') {
    header('Location: /EcoRide/pages/connexion.php');
    exit;
}

// Création compte employé
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_employe'])) {
    $pseudo = $_POST['pseudo'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($pseudo && $email && $password) {
        // Vérifier si email existe
        $stmtCheck = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->rowCount() === 0) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe, role, statut, credits) VALUES (?, ?, ?, 'employe', 'actif', 0)");
            $stmtInsert->execute([$pseudo, $email, $hash]);
            $message = "Employé créé avec succès.";
        } else {
            $message = "Email déjà utilisé.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// Suspension / Réactivation comptes
if (isset($_GET['suspendre'], $_GET['user_id'])) {
    $userId = (int) $_GET['user_id'];
    $newStatut = $_GET['suspendre'] === '1' ? 'suspendu' : 'actif';
    $stmt = $pdo->prepare("UPDATE utilisateurs SET statut = ? WHERE id = ?");
    $stmt->execute([$newStatut, $userId]);
    header('Location: espace_admin.php');
    exit;
}

// Total crédits gagnés (somme des prix des covoiturages terminés)
$stmtCredits = $pdo->query("SELECT SUM(prix) as total_credits FROM covoiturages WHERE statut = 'termine'");
$totalCredits = $stmtCredits->fetchColumn();

// Données covoiturages par jour
$stmtCovoiturages = $pdo->query("
    SELECT date_depart, COUNT(*) AS nb_covoiturages
    FROM covoiturages
    WHERE statut = 'termine'
    GROUP BY date_depart
    ORDER BY date_depart ASC
");
$covoituragesData = $stmtCovoiturages->fetchAll(PDO::FETCH_ASSOC);

// Données crédits par jour
$stmtCreditsJour = $pdo->query("
    SELECT date_depart, SUM(prix) AS total_jour
    FROM covoiturages
    WHERE statut = 'termine'
    GROUP BY date_depart
    ORDER BY date_depart ASC
");
$creditsData = $stmtCreditsJour->fetchAll(PDO::FETCH_ASSOC);

// Récupérer liste utilisateurs et employés
$stmtUsers = $pdo->query("SELECT id, pseudo, email, role, statut FROM utilisateurs WHERE role IN ('utilisateur', 'employe') ORDER BY role, pseudo");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Espace Administrateur</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
table { width: 100%; border-collapse: collapse; }
th, td { padding: 8px; border: 1px solid #ddd; }
.btn-suspend { color: red; cursor: pointer; }
.btn-activate { color: green; cursor: pointer; }
</style>
</head>
<body>
<h1>Espace Administrateur</h1>

<h2>Créer un compte employé</h2>
<form method="POST" style="margin-bottom: 20px;">
    <input type="text" name="pseudo" placeholder="Pseudo" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit" name="create_employe">Créer</button>
</form>
<p><?= htmlspecialchars($message) ?></p>

<h2>Utilisateurs et Employés</h2>
<table>
    <thead>
        <tr><th>Pseudo</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['pseudo']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= htmlspecialchars($user['statut']) ?></td>
            <td>
                <?php if ($user['statut'] === 'actif'): ?>
                    <a href="?suspendre=1&user_id=<?= $user['id'] ?>" class="btn-suspend">Suspendre</a>
                <?php else: ?>
                    <a href="?suspendre=0&user_id=<?= $user['id'] ?>" class="btn-activate">Réactiver</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Statistiques</h2>
<p>Total crédits gagnés par la plateforme : <strong><?= number_format($totalCredits, 2) ?> crédits</strong></p>

<canvas id="chartCovoiturages" width="600" height="300"></canvas>
<canvas id="chartCredits" width="600" height="300" style="margin-top: 40px;"></canvas>

<script>
// Préparation données covoiturages par jour
const labelsCovoiturages = <?= json_encode(array_column($covoituragesData, 'date_depart')) ?>;
const dataCovoiturages = <?= json_encode(array_column($covoituragesData, 'nb_covoiturages')) ?>;

// Préparation données crédits par jour
const labelsCredits = <?= json_encode(array_column($creditsData, 'date_depart')) ?>;
const dataCredits = <?= json_encode(array_column($creditsData, 'total_jour')) ?>;

// Graphique 1: Nombre de covoiturages par jour
const ctx1 = document.getElementById('chartCovoiturages').getContext('2d');
const chartCovoiturages = new Chart(ctx1, {
    type: 'line',
    data: {
        labels: labelsCovoiturages,
        datasets: [{
            label: 'Nombre de covoiturages',
            data: dataCovoiturages,
            borderColor: 'blue',
            backgroundColor: 'lightblue',
            fill: false,
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Graphique 2: Gains en crédits par jour
const ctx2 = document.getElementById('chartCredits').getContext('2d');
const chartCredits = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: labelsCredits,
        datasets: [{
            label: 'Crédits gagnés par jour',
            data: dataCredits,
            backgroundColor: 'green'
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
