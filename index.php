<?php
require_once 'templates/header.php';
?>

<main class="flex-grow-1 container my-2">
        <!--Description-->

                <h2>Bienvenue chez EcoRide 🌿</h2>
                <p class="description">
                    <span>EcoRide,</span> c’est bien plus qu’un simple service de covoiturage. C’est un mouvement vers une mobilité plus verte,
                    plus responsable et plus conviviale. Ensemble, réduisons notre empreinte carbone et prenons soin de notre planète.
                    Agissons aujourd’hui pour préserver demain.🌍
                </p>


        <!-- Carrousel -->
        
                <div class="container my-5">
                <div class="row align-items-center">
                    <div class="col-md-6">
                    <div id="carouselEcoRide" class="carousel slide rounded shadow" data-bs-ride="carousel">
                        <div class="carousel-inner">

                        <div class="carousel-item active">
                            <img src="assets/carrousel_1.jpg" class="d-block w-100" alt="Écologie 1">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/carrousel_2.jpg" class="d-block w-100" alt="Écologie 2">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/carrousel_3.jpg" class="d-block w-100" alt="Écologie 3">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/carrousel_4.jpg" class="d-block w-100" alt="Écologie 3">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/carrousel_5.jpg" class="d-block w-100" alt="Écologie 3">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/carrousel_6.jpg" class="d-block w-100" alt="Écologie 3">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/carrousel_7.jpg" class="d-block w-100" alt="Écologie 3">
                        </div>
                        

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


        <!--Barres de recherche + button de recherche-->

            <div class="col-md-6">
                <h3 class="eco-title text-center mb-3">
                Trouvez votre prochain trajet en toute simplicité!</h3>

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
</main>
    
<?php
require_once 'templates/footer.php';
?>