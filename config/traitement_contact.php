<?php
session_start();

// Optionnel : traiter les données
$email = $_POST['email'] ?? '';
$sujet = $_POST['sujet'] ?? '';
$message = $_POST['message'] ?? '';

// Ici tu peux enregistrer ou envoyer l'email, etc.

// Rediriger vers la page de confirmation
header('Location: ../pages/confirmation_contact.php');
exit;
