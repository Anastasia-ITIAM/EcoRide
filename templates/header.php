<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/EcoRide/css/styles.css" />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="/EcoRide/assets/logo.svg" />
</head>
<body>  

<header class="py-3 mb-4 border-bottom">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start gap-3">

        <!-- Logo -->
        <a href="/EcoRide/index.php" class="logo-container text-decoration-none">
            <img src="/EcoRide/assets/logo.svg" alt="Logo d'EcoRide" width="40" height="40">
            <span class="brand-name">EcoRide</span>
        </a>

        <!-- Navigation -->
        <ul class="nav mx-auto">
            <li><a href="/EcoRide/index.php" class="nav-link px-3">Accueil</a></li>
            <li><a href="/EcoRide/pages/recherche.php" class="nav-link px-3">Covoiturages</a></li>
            <li><a href="/EcoRide/pages/contact.php" class="nav-link px-3">Contacts</a></li>
        </ul>

        <!-- Utilisateur connecté -->
        <?php if (isset($_SESSION['user_pseudo'])): ?>
            <div class="d-flex align-items-center gap-2">
                <span class="eco-text fw-semibold" style="font-family: var(--eco-font); color: var(--eco-text);">
                    Bonjour, <?= htmlspecialchars($_SESSION['user_pseudo']) ?>
                </span>

                <!-- Icône utilisateur avec dropdown -->
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle text-decoration-none d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="/EcoRide/assets/icone_utilisateur.png" alt="Icône utilisateur" width="32" height="32" />
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="var(--eco-green)" class="ms-1" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.646 5.646a.5.5 0 0 1 .708 0L8 11.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end text-small" style="font-family: var(--eco-font); min-width: 140px;">
                        <li><a class="dropdown-item" href="/EcoRide/pages/profil.php">Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/EcoRide/config/deconnexion.php">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="text-end">
                <a href="/EcoRide/pages/inscription.php" class="btn custom-btn">S'inscrire</a>
                <a href="/EcoRide/pages/connexion.php" class="btn custom-btn">Connexion</a>
            </div>
        <?php endif; ?>

    </div>
</header>
