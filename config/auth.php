<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /EcoRide/pages/connexion.php');
    exit;
}
