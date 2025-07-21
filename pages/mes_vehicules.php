<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Récupérer le rôle de l'utilisateur
$stmt = $pdo->prepare("SELECT role FROM utilisateurs WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Vérification des droits d'accès : uniquement chauffeurs ou passager_chauffeur
if ($user['role'] !== 'chauffeur' && $user['role'] !== 'passager_chauffeur') {
    echo "Accès refusé : vous n'êtes pas autorisé.";
    exit;
}

// Gestion des requêtes POST (ajout, modification, suppression de véhicule)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour d'un véhicule existant
    if (isset($_POST['update_vehicle'])) {
        $plaque = trim($_POST['plaque']);
        $date = $_POST['date_immatriculation'];
        $marque = trim($_POST['marque']);
        $modele = trim($_POST['modele']);
        $couleur = trim($_POST['couleur']);
        $energie = trim($_POST['energie']);
        $places = (int)$_POST['places_disponibles'];

        $errors = [];
        // Vérification des champs obligatoires
        if (!$plaque || !$date || !$marque || !$modele || $places < 1) {
            $errors[] = "Tous les champs obligatoires doivent être remplis.";
        }

        if (empty($errors)) {
            // Mise à jour en base
            $stmt = $pdo->prepare("UPDATE vehicules SET date_immatriculation=?, marque=?, modele=?, couleur=?, energie=?, places_disponibles=? WHERE utilisateur_id=? AND plaque=?");
            $stmt->execute([$date, $marque, $modele, $couleur, $energie, $places, $userId, $plaque]);
            $_SESSION['message'] = "Véhicule mis à jour.";
        } else {
            $_SESSION['message'] = implode("<br>", $errors);
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Suppression d'un véhicule
    if (isset($_POST['delete_vehicle'])) {
        $plaque = trim($_POST['plaque']);
        if ($plaque) {
            $stmt = $pdo->prepare("DELETE FROM vehicules WHERE utilisateur_id=? AND plaque=?");
            $stmt->execute([$userId, $plaque]);
            $_SESSION['message'] = "Véhicule supprimé.";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Ajout d'un nouveau véhicule
    if (isset($_POST['add_vehicle'])) {
        $plaque = trim($_POST['new_plaque']);
        $date = $_POST['new_date_immatriculation'];
        $marque = trim($_POST['new_marque']);
        $modele = trim($_POST['new_modele']);
        $couleur = trim($_POST['new_couleur']);
        $energie = trim($_POST['new_energie']);
        $places = (int)$_POST['new_places_disponibles'];

        // Préférences spécifiques
        $fumeur = isset($_POST['new_fumeur']) ? 1 : 0;
        $animaux = isset($_POST['new_animaux_acceptes']) ? 1 : 0;
        $preferences = trim($_POST['new_preferences_personnalisees']);

        $errors = [];
        // Vérification des champs obligatoires
        if (!$plaque || !$date || !$marque || !$modele || $places < 1) {
            $errors[] = "Veuillez remplir tous les champs obligatoires.";
        }

        // Vérification doublon plaque
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicules WHERE utilisateur_id=? AND plaque=?");
        $stmt->execute([$userId, $plaque]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Un véhicule avec cette plaque existe déjà.";
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Insertion du véhicule
                $stmt = $pdo->prepare("INSERT INTO vehicules (utilisateur_id, plaque, date_immatriculation, marque, modele, couleur, energie, places_disponibles) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $plaque, $date, $marque, $modele, $couleur, $energie, $places]);

                // Mise à jour ou insertion des préférences du conducteur
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM preferences_conducteurs WHERE conducteur_id = ?");
                $stmt->execute([$userId]);
                if ($stmt->fetchColumn()) {
                    $stmt = $pdo->prepare("UPDATE preferences_conducteurs SET fumeur=?, animaux_acceptes=?, preferences_personnalisees=? WHERE conducteur_id=?");
                    $stmt->execute([$fumeur, $animaux, $preferences, $userId]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO preferences_conducteurs (conducteur_id, fumeur, animaux_acceptes, preferences_personnalisees) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$userId, $fumeur, $animaux, $preferences]);
                }

                $pdo->commit();
                $_SESSION['message'] = "Véhicule ajouté avec succès.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['message'] = "Erreur : " . $e->getMessage();
            }
        } else {
            $_SESSION['message'] = implode("<br>", $errors);
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Récupération du message flash
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Récupération des véhicules de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE utilisateur_id = :id");
$stmt->execute(['id' => $userId]);
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des préférences du conducteur 
$stmt = $pdo->prepare("SELECT * FROM preferences_conducteurs WHERE conducteur_id = :id");
$stmt->execute(['id' => $userId]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php require_once '../templates/header.php'; ?>

<div class="container my-4" style="max-width:900px;">
    <h2 class="text-center mb-4">Mes véhicules 🧩</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Affichage liste des véhicules à modifier -->
    <?php if (!$vehicules): ?>
        <div class="alert alert-info text-center">Aucun véhicule ajouté.</div>
    <?php else: ?>
        <h4 class="text-center mb-3">Modifier mes véhicules</h4>
        <?php foreach ($vehicules as $v): ?>
            <form method="post" class="card p-3 mb-2 shadow-sm eco-box">
                <input type="hidden" name="update_vehicle" value="1">
                <input type="hidden" name="plaque" value="<?= htmlspecialchars($v['plaque']) ?>">

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>Plaque</label>
                        <input class="form-control" value="<?= htmlspecialchars($v['plaque']) ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>Date immatriculation *</label>
                        <input type="date" name="date_immatriculation" class="form-control" required value="<?= $v['date_immatriculation'] ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label>Marque *</label>
                        <input type="text" name="marque" class="form-control" required value="<?= htmlspecialchars($v['marque']) ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Modèle *</label>
                        <input type="text" name="modele" class="form-control" required value="<?= htmlspecialchars($v['modele']) ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Couleur</label>
                        <input type="text" name="couleur" class="form-control" value="<?= htmlspecialchars($v['couleur']) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label>Énergie</label>
                        <input type="text" name="energie" class="form-control" value="<?= htmlspecialchars($v['energie']) ?>">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>Places disponibles *</label>
                        <input type="number" name="places_disponibles" min="1" required class="form-control" value="<?= (int)$v['places_disponibles'] ?>">
                    </div>
                </div>
                <div class="text-center">
                <button type="submit" class="btn custom-btn">Enregistrer</button>
                </div>
            </form>

            <!-- Formulaire suppression véhicule -->
            <form method="post" class="mb-4 text-center">
                <input type="hidden" name="delete_vehicle" value="1">
                <input type="hidden" name="plaque" value="<?= htmlspecialchars($v['plaque']) ?>">
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr>

    <!-- Formulaire ajout nouveau véhicule -->
    <h4 class="text-center">Ajouter un nouveau véhicule</h4>
    <form method="post" class="card p-3 shadow-sm eco-box">
        <input type="hidden" name="add_vehicle" value="1">

        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Plaque *</label>
                <input type="text" name="new_plaque" class="form-control" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Date immatriculation *</label>
                <input type="date" name="new_date_immatriculation" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-2">
                <label>Marque *</label>
                <input type="text" name="new_marque" class="form-control" required>
            </div>
            <div class="col-md-4 mb-2">
                <label>Modèle *</label>
                <input type="text" name="new_modele" class="form-control" required>
            </div>
            <div class="col-md-4 mb-2">
                <label>Couleur</label>
                <input type="text" name="new_couleur" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-2">
                <label>Énergie</label>
                <input type="text" name="new_energie" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
                <label>Places disponibles *</label>
                <input type="number" name="new_places_disponibles" class="form-control" min="1" required>
            </div>
        </div>

        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" name="new_fumeur" id="fumeur">
            <label for="fumeur" class="form-check-label">Accepte les fumeurs</label>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" name="new_animaux_acceptes" id="animaux">
            <label for="animaux" class="form-check-label">Accepte les animaux</label>
        </div>

        <label>Préférences personnalisées</label>
        <textarea name="new_preferences_personnalisees" class="form-control mb-3" rows="3"></textarea>
        <div class="text-center">
        <button type="submit" class="btn custom-btn">Ajouter le véhicule</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="profil.php" class="btn eco-link">Retour au profil</a>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
