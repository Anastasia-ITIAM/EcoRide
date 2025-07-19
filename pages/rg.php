<?php
session_start();
require_once '../templates/header.php';
require_once __DIR__ . '/../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Message de succès si trajet publié
if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success text-center">
        ✅ Trajet publié avec succès !
    </div>
<?php endif; ?>

<?php
$userId = $_SESSION['user_id'] ?? null;
$userCredits = $_SESSION['user_credits'] ?? 0;

// Vérification paramètre id valide
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
    echo "<div class='alert alert-danger text-center my-3'>ID du trajet invalide.</div>";
    require_once '../templates/footer.php';
    exit;
}

$id = (int) $_GET['id'];

function afficherErreur(string $msg) {
    echo "<div class='alert alert-danger text-center my-3'>{$msg}</div>";
    require_once '../templates/footer.php';
    exit;
}

try {
    // Requête trajet
    $stmt = $pdo->prepare("
        SELECT c.*, u.pseudo, u.email, u.id AS conducteur_id, u.photo_profil,
               v.marque, v.modele, v.couleur, v.energie
        FROM covoiturages c
        JOIN utilisateurs u ON c.conducteur_id = u.id
        JOIN vehicules v ON c.vehicule_id = v.id
        WHERE c.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    afficherErreur("ID du trajet invalide.");
}

    // Formatage date (entourer de try/catch car DateTime peut échouer)
    try {
        $date = new DateTime($trajet['date_depart']);
        $heureDepart = new DateTime($trajet['heure_depart']);
        $heureArrivee = new DateTime($trajet['heure_arrivee']);
    } catch (Exception $e) {
        afficherErreur("Erreur dans le format de la date/heure du trajet.");
    }

    // Préférences conducteur
    $stmtPrefs = $pdo->prepare("SELECT * FROM preferences_conducteurs WHERE conducteur_id = :id");
    $stmtPrefs->execute(['id' => $trajet['conducteur_id']]);
    $prefs = $stmtPrefs->fetch(PDO::FETCH_ASSOC);

    // Avis conducteur
    $stmtAvis = $pdo->prepare("
        SELECT a.commentaire, a.note, a.date_avis, u.pseudo AS auteur
        FROM avis a
        JOIN utilisateurs u ON a.auteur_id = u.id
        WHERE a.conducteur_id = :id
        ORDER BY a.date_avis DESC
    ");
    $stmtAvis->execute(['id' => $trajet['conducteur_id']]);
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    // Vérification participation utilisateur
    $dejaInscrit = false;
    if ($userId) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE utilisateur_id = :uid AND covoiturage_id = :cid");
        $check->execute(['uid' => $userId, 'cid' => $id]);
        $dejaInscrit = $check->fetchColumn() > 0;
    }
    
} catch (PDOException $e) {
    error_log('Erreur PDO : ' . $e->getMessage()); // Log côté serveur
    afficherErreur("Une erreur technique est survenue. Veuillez réessayer plus tard.");
}
?>