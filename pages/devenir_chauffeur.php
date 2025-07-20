<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

// Récupération infos utilisateur
$stmt = $pdo->prepare("SELECT id, role FROM utilisateurs WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Empêcher de redevenir chauffeur si déjà chauffeur ou passager_chauffeur
if ($user['role'] === 'chauffeur' || $user['role'] === 'passager_chauffeur') {
    header('Location: profil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plaque = trim($_POST['plaque'] ?? '');
    $date_immat = $_POST['date_immatriculation'] ?? '';
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $couleur = trim($_POST['couleur'] ?? '');
    $energie = trim($_POST['energie'] ?? '');
    $places = (int)($_POST['places_disponibles'] ?? 0);
    $fumeur = isset($_POST['fumeur']) ? 1 : 0;
    $animaux = isset($_POST['animaux_acceptes']) ? 1 : 0;
    $preferences_personnalisees = trim($_POST['preferences_personnalisees'] ?? '');

    $errors = [];

    if (!$plaque) $errors[] = "La plaque d'immatriculation est obligatoire.";
    if (!$date_immat) $errors[] = "La date de première immatriculation est obligatoire.";
    if (!$marque) $errors[] = "La marque est obligatoire.";
    if (!$modele) $errors[] = "Le modèle est obligatoire.";
    if ($places < 1) $errors[] = "Le nombre de places doit être au moins 1.";

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Mettre à jour le rôle
            $nouveauRole = ($user['role'] === 'passager') ? 'passager_chauffeur' : 'chauffeur';
            $stmt = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
            $stmt->execute([$nouveauRole, $userId]);

            // Insérer véhicule
            $stmt = $pdo->prepare("INSERT INTO vehicules 
                (utilisateur_id, plaque, date_immatriculation, marque, modele, couleur, energie, places_disponibles)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $plaque, $date_immat, $marque, $modele, $couleur, $energie, $places]);

            // Insérer préférences
            $conducteurId = $userId;
            $stmt = $pdo->prepare("INSERT INTO preferences_conducteurs 
                (conducteur_id, fumeur, animaux_acceptes, preferences_personnalisees)
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$conducteurId, $fumeur, $animaux, $preferences_personnalisees]);

            $pdo->commit();

            // Enregistrer message succès en session
            $_SESSION['success_message'] = "Félicitations! Vous voilà officiellement chauffeur EcoRide 🦖";

            // Redirection vers profil après succès
            header('Location: profil.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Erreur lors de l'inscription comme chauffeur : " . $e->getMessage();
        }
    } else {
        $message = implode('<br>', $errors);
    }
}
?>

<?php require_once '../templates/header.php'; ?>

<div class="container my-5" style="max-width:600px;">
    <h2 class="text-center mb-4">Devenir Chauffeur 🐊</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="plaque" class="form-label">Plaque d'immatriculation *</label>
            <input type="text" id="plaque" name="plaque" class="form-control" required value="<?= htmlspecialchars($_POST['plaque'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="date_immatriculation" class="form-label">Date de première immatriculation *</label>
            <input type="date" id="date_immatriculation" name="date_immatriculation" class="form-control" required value="<?= htmlspecialchars($_POST['date_immatriculation'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="marque" class="form-label">Marque *</label>
            <input type="text" id="marque" name="marque" class="form-control" required value="<?= htmlspecialchars($_POST['marque'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="modele" class="form-label">Modèle *</label>
            <input type="text" id="modele" name="modele" class="form-control" required value="<?= htmlspecialchars($_POST['modele'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="couleur" class="form-label">Couleur</label>
            <input type="text" id="couleur" name="couleur" class="form-control" value="<?= htmlspecialchars($_POST['couleur'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="energie" class="form-label">Motorisation</label>
            <input type="text" id="energie" name="energie" class="form-control" value="<?= htmlspecialchars($_POST['energie'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="places_disponibles" class="form-label">Nombre de places disponibles *</label>
            <input type="number" id="places_disponibles" name="places_disponibles" class="form-control" min="1" required value="<?= htmlspecialchars($_POST['places_disponibles'] ?? '') ?>">
        </div>

        <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" id="fumeur" name="fumeur" <?= isset($_POST['fumeur']) ? 'checked' : '' ?>>
            <label class="form-check-label ms-2" for="fumeur">Accepte les fumeurs</label>
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" class="form-check-input" id="animaux_acceptes" name="animaux_acceptes" <?= isset($_POST['animaux_acceptes']) ? 'checked' : '' ?>>
            <label class="form-check-label ms-2" for="animaux_acceptes">Accepte les animaux</label>
        </div>

        <div class="mb-3">
            <label for="preferences_personnalisees" class="form-label">Préférences personnalisées</label>
            <textarea id="preferences_personnalisees" name="preferences_personnalisees" class="form-control" rows="3"><?= htmlspecialchars($_POST['preferences_personnalisees'] ?? '') ?></textarea>
        </div>

        <p align="center">
            <button type="submit" class="btn custom-btn">Devenir chauffeur</button>
        </p>
    </form>

    <div class="text-center mt-3">
        <a href="profil.php" class="btn eco-link">Retour au profil</a>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
