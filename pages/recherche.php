<?php

session_start();
require_once '../templates/header.php';
require_once __DIR__ . '/../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupération et nettoyage des données GET
$depart = trim($_GET['depart'] ?? '');
$arrivee = trim($_GET['arrivee'] ?? '');
$datetime = $_GET['datetime'] ?? '';

$ecologique = isset($_GET['ecologique']);
$prix_max = (isset($_GET['prix_max']) && is_numeric($_GET['prix_max'])) ? (float)$_GET['prix_max'] : null;
$duree_max = (isset($_GET['duree_max']) && is_numeric($_GET['duree_max'])) ? (int)$_GET['duree_max'] : null;
$note_min = (isset($_GET['note_min']) && is_numeric($_GET['note_min'])) ? (float)$_GET['note_min'] : null;

// Message de bienvenue après inscription
if (isset($_GET['inscription']) && $_GET['inscription'] === 'success') {
    $pseudo = htmlspecialchars($_SESSION['user_pseudo'] ?? 'voyageur');
    echo '<div class="alert custom-success text-center container mt-4">Bienvenue ' . $pseudo . ' ! Vous faites désormais partie de la famille EcoRide 💚 20 crédits vous ont été offerts 🎁</div>';
}

?>

<main class="flex-grow-1 container my-2">
  <h3>Partez à l'aventure en quelques clics!</h3>

  <div class="my-3">
    <form method="GET" action="recherche.php" id="rechercheForm">
      <div class="row justify-content-center">
        <div class="col-md-7">
          <div class="d-flex align-items-end justify-content-center gap-2">
            <div style="flex: 1;">
              <input type="text" class="form-control" name="depart" id="depart" placeholder="Départ" value="<?= htmlspecialchars($depart) ?>">
            </div>

            <div class="d-flex align-items-center justify-content-center mb-2">
              <button type="button" onclick="echangerAdresses()" class="arrow-swap-btn" title="Échanger Départ / Arrivée">&#x21C4;</button>
            </div>

            <div style="flex: 1;">
              <input type="text" class="form-control" name="arrivee" id="arrivee" placeholder="Arrivée" value="<?= htmlspecialchars($arrivee) ?>">
            </div>
          </div>

          <div class="row mt-4 justify-content-center">
            <div class="col-md-5 text-center">
              <input type="datetime-local" class="form-control" name="datetime" id="datetime" value="<?= htmlspecialchars($datetime) ?>">
            </div>
          </div>

          <div class="text-end mb-3">
            <button class="btn custom-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterSidebar">
              🔍 Filtres
            </button>
          </div>

          <div class="text-center mt-1">
            <button type="submit" class="btn custom-btn">Rechercher</button>
          </div>
          
        </div>
      </div>
    </form>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="filterSidebar" aria-labelledby="filterSidebarLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="filterSidebarLabel">Filtres de recherche</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body">
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="ecologique" name="ecologique" value="1" form="rechercheForm" <?= $ecologique ? 'checked' : '' ?>>
        <label class="form-check-label" for="ecologique">Voyage écologique</label>
      </div>

      <div class="mb-3">
        <label for="prix_max" class="form-label">Prix maximum (crédit)</label>
        <input type="number" class="form-control" id="prix_max" name="prix_max" value="<?= htmlspecialchars($_GET['prix_max'] ?? '') ?>" form="rechercheForm" min="0" step="0.01">
      </div>

      <div class="mb-3">
        <label for="duree_max" class="form-label">Durée maximum (minutes)</label>
        <input type="number" class="form-control" id="duree_max" name="duree_max" value="<?= htmlspecialchars($_GET['duree_max'] ?? '') ?>" form="rechercheForm" min="1" step="1">
      </div>

      <div class="mb-3">
        <label for="note_min" class="form-label">Note conducteur·rice minimale</label>
        <input type="number" step="0.1" min="0" max="5" class="form-control" id="note_min" name="note_min" value="<?= htmlspecialchars($_GET['note_min'] ?? '') ?>" form="rechercheForm">
      </div>

      <div class="text-end mt-4">
        <button type="submit" class="btn custom-btn" form="rechercheForm">Appliquer les filtres</button>
      </div>
    </div>
  </div>

<?php
if (!empty($depart) && !empty($arrivee)) {
    // Construction de la requête principale
    $query = "
        SELECT c.*, u.pseudo, u.photo_profil, v.marque, v.modele,
               COALESCE(AVG(a.note), 0) AS note_moyenne
        FROM covoiturages c
        JOIN utilisateurs u ON c.conducteur_id = u.id
        JOIN vehicules v ON c.vehicule_id = v.id
        LEFT JOIN avis a ON a.conducteur_id = c.conducteur_id
        WHERE c.adresse_depart LIKE :depart
          AND c.adresse_arrivee LIKE :arrivee
          AND c.statut != 'termine'
    ";

    $params = [
        'depart' => "%$depart%",
        'arrivee' => "%$arrivee%"
    ];

    if (!empty($datetime)) {
        // Extraction de la date uniquement
        $date = explode('T', $datetime)[0];
        $query .= " AND c.date_depart = :date";
        $params['date'] = $date;
    }

    if ($ecologique) {
        $query .= " AND c.voyage_ecologique = 1";
    }

    if ($prix_max !== null) {
        $query .= " AND c.prix <= :prix_max";
        $params['prix_max'] = $prix_max;
    }

    if ($duree_max !== null) {
        $query .= " AND c.duree_minutes <= :duree_max";
        $params['duree_max'] = $duree_max;
    }

    $query .= " GROUP BY c.id, u.pseudo, u.photo_profil, v.marque, v.modele";

    if ($note_min !== null) {
        $query .= " HAVING note_moyenne >= :note_min";
        $params['note_min'] = $note_min;
    }

    $query .= " ORDER BY c.date_depart ASC, c.heure_depart ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    if ($results) {
        afficherTrajets($results);
    } else {
        echo "<div class='alert alert-info mt-3'>Aucun trajet exact trouvé. Voici des trajets alternatifs contenant « " . htmlspecialchars($depart) . " » ou « " . htmlspecialchars($arrivee) . " » dans le trajet.</div>";

        $altQuery = "
            SELECT c.*, u.pseudo, u.photo_profil, v.marque, v.modele,
                   COALESCE(AVG(a.note), 0) AS note_moyenne
            FROM covoiturages c
            JOIN utilisateurs u ON c.conducteur_id = u.id
            JOIN vehicules v ON c.vehicule_id = v.id
            LEFT JOIN avis a ON a.conducteur_id = c.conducteur_id
            WHERE (c.adresse_depart LIKE :ville OR c.adresse_arrivee LIKE :ville)
            AND c.statut != 'termine'
            GROUP BY c.id, u.pseudo, u.photo_profil, v.marque, v.modele
            ORDER BY c.date_depart ASC, c.heure_depart ASC
            LIMIT 10
        ";

        $stmtAlt = $pdo->prepare($altQuery);
        $stmtAlt->execute(['ville' => "%$depart%"]);
        $altResults = $stmtAlt->fetchAll();

        if (!$altResults) {
            $stmtAlt->execute(['ville' => "%$arrivee%"]);
            $altResults = $stmtAlt->fetchAll();
        }

        if ($altResults) {
            afficherTrajets($altResults);
        } else {
            echo "<div class='alert alert-info mt-3'>Aucun trajet alternatif trouvé. Voici quelques trajets récents :</div>";

            $recentQuery = "
                SELECT c.*, u.pseudo, u.photo_profil, v.marque, v.modele,
                       COALESCE(AVG(a.note), 0) AS note_moyenne
                FROM covoiturages c
                JOIN utilisateurs u ON c.conducteur_id = u.id
                JOIN vehicules v ON c.vehicule_id = v.id
                LEFT JOIN avis a ON a.conducteur_id = c.conducteur_id
                WHERE c.statut != 'termine'
                GROUP BY c.id, u.pseudo, u.photo_profil, v.marque, v.modele
                ORDER BY c.date_depart DESC, c.heure_depart DESC
                LIMIT 10
            ";

            $stmtRecent = $pdo->query($recentQuery);
            $recentResults = $stmtRecent->fetchAll();
            if ($recentResults) {
                afficherTrajets($recentResults);
            } else {
                echo "<div class='alert alert-info'>Aucun trajet disponible pour le moment.</div>";
            }
        }
    }
} else {
    echo "<div class='alert alert-info mt-3'>Aucun critère de recherche renseigné, voici quelques trajets récents :</div>";

    $recentQuery = "
        SELECT c.*, u.pseudo, u.photo_profil, v.marque, v.modele,
               COALESCE(AVG(a.note), 0) AS note_moyenne
        FROM covoiturages c
        JOIN utilisateurs u ON c.conducteur_id = u.id
        JOIN vehicules v ON c.vehicule_id = v.id
        LEFT JOIN avis a ON a.conducteur_id = c.conducteur_id
        WHERE c.statut != 'termine'
        GROUP BY c.id, u.pseudo, u.photo_profil, v.marque, v.modele
        ORDER BY c.date_depart DESC, c.heure_depart DESC
        LIMIT 10
    ";

    $stmtRecent = $pdo->query($recentQuery);
    $recentResults = $stmtRecent->fetchAll();
    if ($recentResults) {
        afficherTrajets($recentResults);
    } else {
        echo "<div class='alert alert-info'>Aucun trajet disponible pour le moment.</div>";
    }
}

/**
 * Fonction d'affichage des trajets
 *
 * @param array $trajets
 * @return void
 */
function afficherTrajets(array $trajets): void {
    echo '<div class="container mt-4">';
    echo '<div class="row">';
    foreach ($trajets as $trajet) {
        $places_restantes = (int)$trajet['places_disponibles'];
        $photo = !empty($trajet['photo_profil']) ? '../' . htmlspecialchars($trajet['photo_profil']) : '../assets/images/profiles/default.jpg';

        try {
            $heureDepart = new DateTime($trajet['heure_depart']);
        } catch (Exception $e) {
            $heureDepart = new DateTime('00:00');
        }

        $heureArrivee = clone $heureDepart;
        $heureArrivee->modify('+' . (int)$trajet['duree_minutes'] . ' minutes');

        echo '<div class="col-md-4 mb-4">';
        echo '<div class="card h-100" style="background-color: var(--eco-bg)">';
        echo '<div class="card-body">';

        // Photo et infos trajet
        echo '<div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">';
        echo '<img src="' . $photo . '" alt="Photo du conducteur·rice" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">';
        echo '<div>';
        echo '<strong>' . htmlspecialchars($trajet['adresse_depart']) . '</strong> → <strong>' . htmlspecialchars($trajet['adresse_arrivee']) . '</strong><br>';
        echo '<u>Conducteur·rice</u> : ' . htmlspecialchars($trajet['pseudo']) . '<br>';
        echo '<u>Note moyenne</u> : ' . number_format((float)$trajet['note_moyenne'], 2) . ' / 5<br>';
        echo '<u>Places restantes</u> : ' . $places_restantes . '<br>';
        echo '<u>Prix</u> : ' . htmlspecialchars($trajet['prix']) . ' crédits<br>';
        echo '<u>Départ</u> : ' . htmlspecialchars($trajet['date_depart']) . ' à ' . $heureDepart->format('H:i') . '<br>';
        echo '<u>Arrivée prévue</u> : ' . $heureArrivee->format('H:i') . '<br>';
        echo '</div></div>';

        // Badge écologique
        if (!empty($trajet['voyage_ecologique'])) {
            echo '<div class="text-center mb-3">
                    <span class="btn custom-btn btn-sm">🌱 EcoRide</span>
                  </div>';
        }

        // Bouton détail
        echo '<div class="text-center">
                <a class="btn custom-btn btn-sm" href="detail.php?id=' . intval($trajet['id']) . '">Voir détails</a>
              </div>';

        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // col
    }
    echo '</div>'; // row
    echo '</div>'; // container
}

?>

</main>

<?php require_once '../templates/footer.php'; ?>
