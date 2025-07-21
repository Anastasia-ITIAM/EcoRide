
<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once '../templates/header.php';

// Récupération des infos utilisateur depuis la session
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$userCredits = $_SESSION['user_credits'] ?? 0;

// Vérification de l'ID du trajet
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
    require_once '../templates/footer.php';
    exit('<div class="alert alert-danger text-center my-3">ID du trajet invalide.</div>');
}

$id = (int) $_GET['id'];

function afficherErreur(string $msg) {
    echo "<div class='alert alert-danger text-center my-3'>" . htmlspecialchars($msg) . "</div>";
    require_once '../templates/footer.php';
    exit;
}
    
// Récupération des infos du trajet + conducteur + véhicule
try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.pseudo, u.email, u.id AS conducteur_id, u.photo_profil,
               v.marque, v.modele, v.couleur, v.energie
        FROM covoiturages c
        JOIN utilisateurs u ON c.conducteur_id = u.id
        JOIN vehicules v ON c.vehicule_id = v.id
        WHERE c.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trajet) {
        afficherErreur("Trajet introuvable.");
    }

    // Dates et heures formatées
    $date = new DateTime($trajet['date_depart']);
    $heureDepart = new DateTime($trajet['heure_depart']);
    $heureArrivee = new DateTime($trajet['heure_arrivee']);

    // Préférences du conducteur
    $stmtPrefs = $pdo->prepare("SELECT * FROM preferences_conducteurs WHERE conducteur_id = :id LIMIT 1");
    $stmtPrefs->execute(['id' => $trajet['conducteur_id']]);
    $prefs = $stmtPrefs->fetch(PDO::FETCH_ASSOC);

    // Avis sur le conducteur
    $stmtAvis = $pdo->prepare("
        SELECT a.commentaire, a.note, a.date_avis, u.pseudo AS auteur
        FROM avis a
        JOIN utilisateurs u ON a.auteur_id = u.id
        WHERE a.conducteur_id = :id
        ORDER BY a.date_avis DESC
    ");
    $stmtAvis->execute(['id' => $trajet['conducteur_id']]);
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    // Vérification si utilisateur déjà inscrit à ce trajet
    $dejaInscrit = false;
    if ($userId) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE utilisateur_id = :uid AND covoiturage_id = :cid");
        $check->execute(['uid' => $userId, 'cid' => $id]);
        $dejaInscrit = (bool) $check->fetchColumn();
    }
} catch (PDOException $e) {
    error_log('Erreur PDO : ' . $e->getMessage());
    afficherErreur("Une erreur technique est survenue.");
}
?>

<?php if ($dejaInscrit): ?>
    <div class="alert alert-info text-center my-3">
        Vous participez déjà à ce trajet.
        <form action="../config/annuler_participation.php" method="post" class="mt-2">
            <input type="hidden" name="covoiturage_id" value="<?= htmlspecialchars($id) ?>">
            <button type="submit" class="btn btn-danger">Annuler ma participation</button>
        </form>
    </div>
    <?php else: ?>
        <?php if ($trajet['statut'] !== 'termine'): ?>
            <h2 class="text-center mt-1">En route vers un trajet plus vert 🥝</h2>
        <?php endif; ?>
    <?php endif; ?>


<div class="container my-5">
    <div class="row">

        <!-- Infos trajet -->
        <div class="col-md-4 mb-4">
            <div class="card eco-box p-3 shadow-sm h-100">
                <h4 class="mb-3 text-center">Infos du trajet</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item eco-box"><strong>Départ :</strong> <?= htmlspecialchars($trajet['adresse_depart']) ?></li>
                    <li class="list-group-item eco-box"><strong>Arrivée :</strong> <?= htmlspecialchars($trajet['adresse_arrivee']) ?></li>
                    <li class="list-group-item eco-box"><strong>Date :</strong> <?= htmlspecialchars($date->format('d/m/Y')) ?></li>
                    <li class="list-group-item eco-box"><strong>Heure :</strong> <?= htmlspecialchars($heureDepart->format('H:i')) ?> - <?= htmlspecialchars($heureArrivee->format('H:i')) ?></li>
                    <li class="list-group-item eco-box"><strong>Prix :</strong> <?= htmlspecialchars($trajet['prix']) ?> crédits</li>
                    <li class="list-group-item eco-box"><strong>Places disponibles :</strong> <?= htmlspecialchars($trajet['places_disponibles']) ?></li>
                    <li class="list-group-item eco-box"><strong>Écologique :</strong> <?= $trajet['voyage_ecologique'] ? 'Oui' : 'Non' ?></li>
                    <li class="list-group-item eco-box"><strong>Conducteur :</strong> <?= htmlspecialchars($trajet['pseudo']) ?> (<?= htmlspecialchars($trajet['email']) ?>)</li>
                    <li class="list-group-item eco-box"><strong>Véhicule :</strong> <?= htmlspecialchars("{$trajet['marque']} {$trajet['modele']} - {$trajet['couleur']} - {$trajet['energie']}") ?></li>
                </ul>
            </div>
        </div>

        <!-- Préférences -->
        <div class="col-md-4 mb-4">
            <div class="card eco-box p-3 shadow-sm h-100">
                <h4 class="text-center mb-3">Préférences du conducteur</h4>
                <?php if ($prefs): ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item eco-box">Musique : <?= $prefs['musique'] ? 'Oui' : 'Non' ?></li>
                        <li class="list-group-item eco-box">Discussion : <?= $prefs['discussion'] ? 'Oui' : 'Non' ?></li>
                        <li class="list-group-item eco-box">Animaux : <?= $prefs['animaux_acceptes'] ? 'Oui' : 'Non' ?></li>
                        <li class="list-group-item eco-box">Climatisation : <?= $prefs['climatisation'] ? 'Oui' : 'Non' ?></li>
                        <li class="list-group-item eco-box">Fumeur : <?= $prefs['fumeur'] ? 'Oui' : 'Non' ?></li>
                    </ul>
                <?php else: ?>
                    <p class="text-center">Aucune préférence indiquée.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Avis -->
        <div class="col-md-4 mb-4">
            <div class="card eco-box p-3 shadow-sm h-100">
                <h4 class="text-center mb-3">Avis du conducteur</h4>
                <?php if (!empty($avis)): ?>
                    <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($avis as $a): ?>
                            <li class="list-group-item eco-box">
                                <strong><?= htmlspecialchars($a['auteur']) ?></strong> — <?= (int)$a['note'] ?>/5<br>
                                <small><?= date('d/m/Y', strtotime($a['date_avis'])) ?></small><br>
                                <?= nl2br(htmlspecialchars($a['commentaire'])) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">Aucun avis pour ce conducteur.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Boutons -->
    <div class="text-center mt-4">
        <?php if (!$userId): ?>
            <div class="alert alert-info">Connectez-vous pour réserver ce trajet.</div>
            <a href="connexion.php" class="btn custom-btn mx-2">Se connecter</a>
            <a href="inscription.php" class="btn custom-btn mx-2">Créer un compte</a>

        <?php elseif ($userId === (int)$trajet['conducteur_id']): ?>

            <?php if ($trajet['statut'] !== 'termine'): ?>
                <!-- Boutons conducteur (Annuler + changer statut) -->
                <form action="../config/annuler_trajet.php" method="post" class="d-inline-block me-2">
                    <input type="hidden" name="role" value="chauffeur">
                    <input type="hidden" name="trajet_id" value="<?= htmlspecialchars($id) ?>">
                    <button type="submit" class="btn btn-danger">Annuler le trajet</button>
                </form>

                <?php if ($trajet['statut'] === 'prévu'): ?>
                    <form action="../config/changer_statut.php" method="post" class="d-inline-block me-2">
                        <input type="hidden" name="trajet_id" value="<?= htmlspecialchars($id) ?>">
                        <input type="hidden" name="nouveau_statut" value="en_cours">
                        <button type="submit" class="btn custom-btn">🚗 Démarrer le covoiturage</button>
                    </form>
                <?php elseif ($trajet['statut'] === 'en_cours'): ?>
                    <form action="../config/changer_statut.php" method="post" class="d-inline-block me-2">
                        <input type="hidden" name="trajet_id" value="<?= htmlspecialchars($id) ?>">
                        <input type="hidden" name="nouveau_statut" value="termine">
                        <button type="submit" class="btn custom-btn">✅ Arrivée à destination</button>
                    </form>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info text-center mb-3">Trajet terminé ✅</div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="mes_trajets.php" class="btn custom-btn">← Retour à mes trajets</a>
            </div>

        <?php elseif (!$dejaInscrit): ?>
            <?php
            if ($trajet['places_disponibles'] <= 0) {
                echo '<div class="alert alert-danger">Aucune place disponible.</div>';
            } elseif ($userCredits < $trajet['prix']) {
                echo '<div class="alert alert-danger">Crédits insuffisants. Vous avez ' . htmlspecialchars($userCredits) . ' crédits.</div>';
            } else {
                echo '<button class="btn custom-btn me-2" data-bs-toggle="modal" data-bs-target="#confirmParticipationModal">
                        Participer au covoiturage
                      </button>';
            }
            ?>
            <a href="mes_trajets.php" class="btn custom-btn">← Retour à mes trajets</a>

        <?php endif; ?>
    </div>

    <!-- Modal participation -->
    <?php if ($userId && !$dejaInscrit && $trajet['places_disponibles'] > 0 && $userCredits >= $trajet['prix']): ?>
        <div class="modal fade" id="confirmParticipationModal" tabindex="-1" aria-labelledby="confirmParticipationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content eco-box">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer votre participation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p>Ce trajet coûte <strong><?= htmlspecialchars($trajet['prix']) ?> crédits</strong>.</p>
                        <p>Crédits restants après : <strong><?= htmlspecialchars($userCredits - $trajet['prix']) ?></strong></p>
                    </div>
                    <div class="modal-footer">
                        <form action="../config/traitement_participer.php" method="post">
                            <input type="hidden" name="covoiturage_id" value="<?= htmlspecialchars($id) ?>">
                            <input type="hidden" name="confirm" value="yes">
                            <button type="submit" class="btn custom-btn">Oui, je confirme</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($userId && $userId === (int)$trajet['conducteur_id']): ?>
    <div class="container mb-5" style="max-width: 600px;">
        <div class="card eco-box shadow-sm">
            <div class="card-header text-white" style="background-color: var(--eco-green); font-family: var(--eco-font);">
                <h5 class="mb-0">🐸 Passagers inscrits à ce trajet</h5>
            </div>
            <div class="card-body">
                <?php
                $stmtPassagers = $pdo->prepare("
                    SELECT u.pseudo, u.email
                    FROM participations p
                    JOIN utilisateurs u ON p.utilisateur_id = u.id
                    WHERE p.covoiturage_id = :trajet_id
                ");
                $stmtPassagers->execute(['trajet_id' => $id]);
                $passagers = $stmtPassagers->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (count($passagers) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($passagers as $p): ?>
                            <li class="list-group-item eco-box">
                                <?= htmlspecialchars($p['pseudo']) ?> (<?= htmlspecialchars($p['email']) ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun passager inscrit pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../templates/footer.php'; ?>
