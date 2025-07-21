<?php
require_once 'templates/header.php';
?>

<main class="flex-grow-1 container my-4">
    <!-- Description -->
    <h2 class="text-center">Bienvenue chez EcoRide 🌿</h2>
    <p class="description text-center">
        <span>EcoRide,</span> c’est bien plus qu’un simple service de covoiturage. C’est un mouvement vers une mobilité plus verte,
        plus responsable et plus conviviale. Ensemble, réduisons notre empreinte carbone et prenons soin de notre planète.
        Agissons aujourd’hui pour préserver demain.🌍
    </p>

    <!-- Carrousel + Formulaire -->
    <div class="row align-items-center g-4 mt-5">
        <!-- Carrousel -->
        <div class="col-lg-6">
            <div id="carouselEcoRide" class="carousel slide rounded shadow" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $images = [
                        "carrousel_1.jpg", "carrousel_2.jpg", "carrousel_3.jpg",
                        "carrousel_4.jpg", "carrousel_5.jpg", "carrousel_6.jpg", "carrousel_7.jpg"
                    ];
                    foreach ($images as $index => $img) {
                        $active = $index === 0 ? 'active' : '';
                        echo "<div class='carousel-item $active'>
                                <img src='assets/$img' class='d-block w-100' alt='Image $index'>
                                </div>";
                    }
                    ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselEcoRide" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                    <span class="visually-hidden">Précédent</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselEcoRide" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                    <span class="visually-hidden">Suivant</span>
                </button>
            </div>
        </div>

        <!-- Formulaire de recherche -->
        <div class="col-lg-6">
            <h3 class="text-center mb-5">Trouvez votre prochain trajet en toute simplicité !</h3>

            <form class="row g-3" action="pages/recherche.php" method="GET">
                <div class="col-12">
                    <input type="text" class="form-control" name="depart" placeholder="Départ" required>
                </div>
                <div class="col-12">
                    <input type="text" class="form-control" name="arrivee" placeholder="Arrivée" required>
                </div>
                <div class="col-12">
                    <input type="datetime-local" class="form-control" name="datetime" required>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" class="btn custom-btn">Rechercher</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
require_once 'templates/footer.php';
?>
