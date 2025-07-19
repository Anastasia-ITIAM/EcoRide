<?php
// includes/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function envoyerEmailParticipant($destinataire, $pseudo) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';  // ou smtp.gmail.com si tu veux
        $mail->SMTPAuth = true;
        $mail->Username = 'd153a679dc20ab';        // à modifier avec tes identifiants
        $mail->Password = '62d939fddb28cd';        // à modifier
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('contact@ecoride.fr', 'EcoRide');
        $mail->addAddress($destinataire, $pseudo);

        $mail->Subject = "🛣️ Merci de valider votre trajet sur EcoRide";
        $mail->Body = "Bonjour $pseudo,

Le covoiturage auquel vous avez participé est maintenant terminé.

Merci de vous connecter à votre espace personnel sur EcoRide pour :
- Confirmer que le trajet s’est bien déroulé,
- Laisser une note et un commentaire si vous le souhaitez.

▶️ http://localhost/EcoRide/pages/mon_espace.php

Merci pour votre confiance 🌱

L’équipe EcoRide";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur envoi mail: " . $mail->ErrorInfo);
        return false;
    }
}
