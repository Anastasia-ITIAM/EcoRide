# EcoRide

EcoRide est une application dédiée à la gestion et au suivi des trajets écologiques, favorisant l’utilisation de moyens de transport durables.

---

## Installation

Pour cloner et installer EcoRide, exécutez les commandes suivantes :

```bash
git clone https://github.com/Anastasia-ITIAM/EcoRide.git
cd EcoRide

 ---

## Configuration

Assurez-vous que votre serveur Apache/MySQL (par exemple XAMPP, MAMP ou WAMP) est démarré.
Importez le fichier EcoRide.sql dans votre base de données via phpMyAdmin.
Configurez les identifiants de connexion à la base de données dans le fichier : config/db.php

$host = "localhost";
$dbname = "ecoride_db";
$user = "root";
$password = "";

---

## Lancer l'application

Placez le dossier EcoRide dans le dossier htdocs (si vous utilisez XAMPP).
Ouvrez votre navigateur et accédez à : http://localhost/EcoRide

---

## Structure du projet

EcoRide/
├── index.php              # Point d’entrée de l’application
├── composer.json          # Fichier de configuration des dépendances PHP (Composer)
├── composer.lock          # Verrouillage des versions des dépendances
├── EcoRide.sql            # Fichier SQL de la base de données (structure ou données)
│
├── admin/                 # Interface d’administration (ex : gestion des utilisateurs, statistiques)
├── assets/                # Fichiers statiques : images, icônes, etc.
├── config/                # Fichiers de configuration (connexion BDD, constantes)
├── css/                   # Feuilles de style CSS
├── js/                    # Scripts JavaScript
├── includes/              # Composants PHP réutilisables (ex. : `mailer.php` pour l'envoi d’e-mails)
├── pages/                 # Pages principales de l'application (accueil, tableau de bord, etc.)
├── templates/             # Gabarits HTML (en-tête, pied de page)
├── employe/               # Section liée aux employés 
├── uploads/               # Fichiers téléversés par les utilisateurs
└── vendor/                # Dépendances installées via Composer (ne pas modifier manuellement)

---

## Fonctionnalités principales

 - Gestion et suivi de trajets écologiques : enregistrement, visualisation et suivi des déplacements effectués.
 - Création, modification et suppression de trajets.
 - Interface utilisateur claire et intuitive.
 - Connexion à une base de données MySQL pour la gestion des utilisateurs, trajets et autres données.
 - Affichage de statistiques et de données agrégées.

---

## Auteur

**Anastasiia Degtiar**  
[GitHub - Anastasia-ITIAM](https://github.com/Anastasia-ITIAM)  
anastasiia_degtiar@icloud.com
