<?php
session_start();
require_once '../config/db.php';
require_once '../config/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

// Récupération des infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { exit('Utilisateur non trouvé.'); }

// Vérif profil complet
$champs_obligatoires = ['prenom', 'nom_famille', 'date_naissance', 'adresse_postale', 'telephone'];
$profil_complet = true;
foreach ($champs_obligatoires as $champ) {
    if (empty($user[$champ])) { $profil_complet = false; break; }
}

// Traitement du formulaire de mise à jour profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['publier_trajet'])) {
    $email = $_POST['email'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';
    $prenom = trim($_POST['prenom'] ?? '');
    $nom_famille = trim($_POST['nom_famille'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $adresse_postale = trim($_POST['adresse_postale'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation basique
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } elseif ($password !== '' && $password !== $password_confirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Upload photo
        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['photo_profil']['type'], $allowedTypes)) {
                $uploadDir = '../uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = uniqid() . '_' . basename($_FILES['photo_profil']['name']);
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $targetFile)) {
                    // Supprimer ancienne photo si existe
                    if ($user['photo_profil'] && file_exists('../' . $user['photo_profil'])) {
                        unlink('../' . $user['photo_profil']);
                    }
                    $photoPath = 'uploads/profiles/' . $filename;
                } else {
                    $message = "Erreur lors de l'upload de la photo.";
                }
            } else {
                $message = "Format de photo non supporté (jpg, png, gif uniquement).";
            }
        } else {
            $photoPath = $user['photo_profil'];
        }

        if ($message === '') {
            $sql = "UPDATE utilisateurs SET email = :email, pseudo = :pseudo, photo_profil = :photo_profil, prenom = :prenom, nom_famille = :nom_famille, date_naissance = :date_naissance, adresse_postale = :adresse_postale, telephone = :telephone";
            $params = [
                'email' => $email,
                'pseudo' => $pseudo,
                'photo_profil' => $photoPath,
                'prenom' => $prenom,
                'nom_famille' => $nom_famille,
                'date_naissance' => $date_naissance,
                'adresse_postale' => $adresse_postale,
                'telephone' => $telephone,
                'id' => $userId
            ];

            if ($password !== '') {
                $sql .= ", mot_de_passe = :mot_de_passe";
                $params['mot_de_passe'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $message = "Profil mis à jour avec succès.";

                // Mise à jour des données $user
                $user['email'] = $email;
                $user['pseudo'] = $pseudo;
                $user['photo_profil'] = $photoPath;
                $user['prenom'] = $prenom;
                $user['nom_famille'] = $nom_famille;
                $user['date_naissance'] = $date_naissance;
                $user['adresse_postale'] = $adresse_postale;
                $user['telephone'] = $telephone;

                // Recalcule profil complet
                $profil_complet = true;
                foreach ($champs_obligatoires as $champ) {
                    if (empty($user[$champ])) {
                        $profil_complet = false;
                        break;
                    }
                }

                $_SESSION['user_pseudo'] = $pseudo;
            } else {
                $message = "Erreur lors de la mise à jour.";
            }
        }
    }
}

// Traitement bouton publier trajet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier_trajet'])) {
    if (($user['role'] === 'chauffeur' || $user['role'] === 'passager_chauffeur') && $profil_complet) {
        header('Location: publier_trajet.php');
        exit;
    } else {
        $message = "Vous devez d'abord compléter votre profil et devenir chauffeur pour publier un trajet.";
    }
}
?>

<?php require_once '../templates/header.php'; ?>

<div class="container my-1 mb-2">
    <h2 class="text-center mb-4">Mon profil 🌍</h2>

    <?php if ($message): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Colonne gauche -->
        <div class="col-md-4 text-center p-4 shadow rounded">
            <?php if ($user['photo_profil']): ?>
                <img src="../<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo profil" 
                     class="img-fluid rounded-circle mb-3" style="width:150px; height:150px; object-fit:cover;">
            <?php else: ?>
                <div style="width:150px; height:150px; line-height:150px; border-radius:50%; background:#ddd; margin:auto; margin-bottom:15px;">
                    Pas de photo
                </div>
            <?php endif; ?>

            <h4>Bonjour, <?= htmlspecialchars($user['pseudo']) ?> 👋</h4>
            <p>Vous êtes enregistré(e) comme <strong><?= htmlspecialchars($user['role']) ?></strong> et possédez <strong><?= htmlspecialchars($user['credits']) ?> crédits</strong></p>

            <div class="d-grid gap-3 w-50 mx-auto">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'employe'): ?>
                    <a href="/EcoRide/employe/moderation_avis.php" class="btn custom-btn">Espace Employé</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrateur'): ?>
                    <a href="/EcoRide/admin/espace_admin.php" class="btn custom-btn">Espace Admin</a>
                <?php endif; ?>    

                <?php if ($user['role'] !== 'chauffeur' && $user['role'] !== 'passager_chauffeur'): ?>
                    <a href="devenir_chauffeur.php" class="btn custom-btn">Devenir chauffeur</a>
                <?php else: ?>
                    <a href="mes_vehicules.php" class="btn custom-btn">Mes véhicules</a>
                <?php endif; ?>

                <form method="post" action="" class="my-3">
                    <button type="submit" name="publier_trajet" class="btn custom-btn w-100">Publier un trajet</button>
                </form>

                <a href="mes_trajets.php" class="btn custom-btn">Mes trajets</a>
            </div>

            <?php if (($user['role'] === 'chauffeur' || $user['role'] === 'passager_chauffeur') && !$profil_complet): ?>
                <div class="mt-3 text-danger">
                    <small>Complétez votre profil pour accéder à toutes les fonctionnalités.</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Colonne droite : formulaire -->
        <div class="col-md-8 p-4 shadow rounded">
            <h5 class="text-center mb-4">Vous pouvez compléter votre profil</h5>
            <form action="" method="post" enctype="multipart/form-data" novalidate>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="mb-3">
                    <label for="pseudo" class="form-label">Pseudo</label>
                    <input type="text" name="pseudo" id="pseudo" class="form-control" required value="<?= htmlspecialchars($user['pseudo']) ?>">
                </div>

                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" required value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="nom_famille" class="form-label">Nom de famille</label>
                    <input type="text" name="nom_famille" id="nom_famille" class="form-control" required value="<?= htmlspecialchars($user['nom_famille'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="date_naissance" class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" id="date_naissance" class="form-control" required value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="adresse_postale" class="form-label">Adresse postale</label>
                    <textarea name="adresse_postale" id="adresse_postale" class="form-control" required><?= htmlspecialchars($user['adresse_postale'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" id="telephone" class="form-control" required value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="photo_profil" class="form-label">Photo de profil</label>
                    <input type="file" name="photo_profil" id="photo_profil" accept="image/*" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" autocomplete="new-password">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn custom-btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div> <!-- fin row -->
</div>


<?php require_once '../templates/footer.php'; ?>
