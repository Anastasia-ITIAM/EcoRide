<?php
session_start();
require_once '../config/db.php';

// Redirection si pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

// Récupérer l'utilisateur (rôle + crédits)
$stmt = $pdo->prepare("SELECT role, credits FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification du rôle
if (!$user || !in_array($user['role'], ['chauffeur', 'passager_chauffeur'])) {
    header('Location: profil.php');
    exit;
}

// Récupérer véhicules utilisateur
$stmt = $pdo->prepare("SELECT id, marque, modele, plaque FROM vehicules WHERE utilisateur_id = ?");
$stmt->execute([$userId]);
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion du message flash (session)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $depart = trim($_POST['adresse_depart'] ?? '');
    $arrivee = trim($_POST['adresse_arrivee'] ?? '');
    $date_depart = $_POST['date_depart'] ?? '';
    $heure_depart = $_POST['heure_depart'] ?? '';
    $heure_arrivee = $_POST['heure_arrivee'] ?? '';
    $places = intval($_POST['places_disponibles'] ?? 0);
    $prix = intval($_POST['prix'] ?? 0);
    $vehicule_id = intval($_POST['vehicule_id'] ?? 0);
    $ecolo = isset($_POST['voyage_ecologique']) ? intval($_POST['voyage_ecologique']) : 0;
    $duree = intval($_POST['duree_minutes'] ?? 0);

    // Validation simple
    if (!$depart || !$arrivee || !$date_depart || !$heure_depart || !$heure_arrivee || $places <= 0 || $prix <= 0 || $vehicule_id <= 0) {
        $message = "Tous les champs obligatoires doivent être remplis correctement.";
    } elseif ($user['credits'] < 2) {
        $message = "Vous n'avez pas assez de crédits pour publier ce trajet (minimum requis : 2).";
    } else {
        try {
            $pdo->beginTransaction();

            // Déduire les crédits
            $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits - 2 WHERE id = ?");
            $stmt->execute([$userId]);

            // Insérer trajet
            $stmt = $pdo->prepare("INSERT INTO covoiturages 
                (vehicule_id, conducteur_id, adresse_depart, adresse_arrivee, date_depart, heure_depart, heure_arrivee, places_disponibles, prix, voyage_ecologique)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $vehicule_id,
                $userId,
                $depart,
                $arrivee,
                $date_depart,
                $heure_depart,
                $heure_arrivee,
                $places,
                $prix,
                $ecolo,
            ]);

            $pdo->commit();

            $_SESSION['message'] = "Trajet publié avec succès.";
            header('Location: mes_trajets.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Erreur lors de la publication : " . $e->getMessage();
        }
    }
}
?>

<?php require_once '../templates/header.php'; ?>

<div class="container my-5" style="max-width: 700px;">
    <h2 class="text-center mb-4">Publier un trajet ♻️</h2>

    <?php if ($message): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="adresse_depart" class="form-label">Ville de départ *</label>
            <input type="text" name="adresse_depart" id="adresse_depart" class="form-control" required value="<?= htmlspecialchars($_POST['adresse_depart'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="adresse_arrivee" class="form-label">Ville d’arrivée *</label>
            <input type="text" name="adresse_arrivee" id="adresse_arrivee" class="form-control" required value="<?= htmlspecialchars($_POST['adresse_arrivee'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="date_depart" class="form-label">Date de départ *</label>
            <input type="date" name="date_depart" id="date_depart" class="form-control" required value="<?= htmlspecialchars($_POST['date_depart'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="heure_depart" class="form-label">Heure de départ *</label>
            <input type="time" name="heure_depart" id="heure_depart" class="form-control" required value="<?= htmlspecialchars($_POST['heure_depart'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="heure_arrivee" class="form-label">Heure d'arrivée *</label>
            <input type="time" name="heure_arrivee" id="heure_arrivee" class="form-control" required value="<?= htmlspecialchars($_POST['heure_arrivee'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="places_disponibles" class="form-label">Places disponibles *</label>
            <input type="number" name="places_disponibles" id="places_disponibles" class="form-control" min="1" required value="<?= htmlspecialchars($_POST['places_disponibles'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Voyage écologique ?</label>
            <select name="voyage_ecologique" class="form-select eco-select">
                <option value="1" <?= (isset($_POST['voyage_ecologique']) && $_POST['voyage_ecologique'] == '1') ? 'selected' : '' ?>>Oui</option>
                <option value="0" <?= (!isset($_POST['voyage_ecologique']) || $_POST['voyage_ecologique'] == '0') ? 'selected' : '' ?>>Non</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="prix" class="form-label">Prix (crédit) fixé par vous *</label>
            <input type="number" name="prix" id="prix" class="form-control" min="1" step="1" required value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>">
            <small class="form-text text-muted">2 crédits seront automatiquement prélevés par la plateforme.</small>
        </div>

        <div class="mb-3">
            <label for="vehicule_id" class="form-label">Choisir un véhicule *</label>
            <select name="vehicule_id" id="vehicule_id" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($vehicules as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= (isset($_POST['vehicule_id']) && $_POST['vehicule_id'] == $v['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['marque'] . ' ' . $v['modele'] . ' (' . $v['plaque'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">
                Aucun véhicule ? <a class="eco-link" href="mes_vehicules.php">Ajouter un nouveau véhicule</a>.
            </small>
        </div>

        <div class="text-center">
            <button type="submit" class="btn custom-btn">Publier ce trajet</button>
        </div>
    </form>
</div>

<?php require_once '../templates/footer.php'; ?>
