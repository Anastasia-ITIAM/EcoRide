-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 21 juil. 2025 à 19:40
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `EcoRide`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `conducteur_id` int(11) NOT NULL,
  `auteur_id` int(11) DEFAULT NULL,
  `note` tinyint(4) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `conducteur_id`, `auteur_id`, `note`, `commentaire`, `date_avis`) VALUES
(1, 1, 2, 5, 'Super trajet, conducteur sympa et ponctuel !', '2025-07-15 19:34:39'),
(2, 2, 3, 4, 'Bon trajet mais un peu de retard au départ.', '2025-07-15 19:34:39'),
(3, 3, 1, 5, 'Très agréable, bonne ambiance et bonne musique.', '2025-07-15 19:34:39'),
(4, 4, 5, 3, 'Trajet correct mais peu de discussion.', '2025-07-15 19:34:39'),
(5, 5, 6, 5, 'Conducteur au top, très prudent sur la route.', '2025-07-15 19:34:39'),
(6, 6, 4, 2, 'Un peu trop pressé, conduite brusque.', '2025-07-15 19:34:39'),
(7, 7, 8, 4, 'Très sympa, véhicule propre et confortable.', '2025-07-15 19:34:39'),
(8, 8, 9, 5, 'Excellente expérience, je recommande !', '2025-07-15 19:34:39'),
(9, 9, 10, 3, 'Bien mais aurait pu mieux communiquer.', '2025-07-15 19:34:39'),
(10, 10, 7, 5, 'Conducteur ponctuel et trajet fluide.', '2025-07-15 19:34:39'),
(11, 11, 12, 4, 'Bonne conduite, manque un peu de musique.', '2025-07-15 19:34:39'),
(12, 12, 13, 5, 'Chauffeur très professionnel et souriant.', '2025-07-15 19:34:39'),
(13, 13, 14, 2, 'Beaucoup de bruit dans la voiture.', '2025-07-15 19:34:39'),
(14, 14, 11, 3, 'Un peu froid, pas très communicatif.', '2025-07-15 19:34:39'),
(15, 15, 16, 5, 'Parfait, rien à redire !', '2025-07-15 19:34:39'),
(16, 16, 15, 4, 'Bonne ambiance, véhicule agréable.', '2025-07-15 19:34:39'),
(17, 17, 18, 5, 'Top top top ! Je referais un trajet avec plaisir.', '2025-07-15 19:34:39'),
(18, 18, 17, 3, 'Correct mais pas très ponctuel.', '2025-07-15 19:34:39'),
(19, 19, 20, 4, 'Bonne musique et discussion sympa.', '2025-07-15 19:34:39'),
(20, 20, 19, 5, 'Très bon conducteur, voiture confortable.', '2025-07-15 19:34:39');

-- --------------------------------------------------------

--
-- Structure de la table `covoiturages`
--

CREATE TABLE `covoiturages` (
  `id` int(11) NOT NULL,
  `vehicule_id` int(11) NOT NULL,
  `conducteur_id` int(11) NOT NULL,
  `adresse_depart` varchar(255) NOT NULL,
  `adresse_arrivee` varchar(255) NOT NULL,
  `date_depart` date NOT NULL,
  `heure_depart` time NOT NULL,
  `heure_arrivee` time NOT NULL,
  `places_disponibles` int(11) NOT NULL,
  `prix` int(11) NOT NULL,
  `voyage_ecologique` tinyint(1) NOT NULL DEFAULT 0,
  `statut` enum('prévu','en_cours','termine') DEFAULT 'prévu',
  `termine` tinyint(1) DEFAULT 0,
  `validation_participant` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `covoiturages`
--

INSERT INTO `covoiturages` (`id`, `vehicule_id`, `conducteur_id`, `adresse_depart`, `adresse_arrivee`, `date_depart`, `heure_depart`, `heure_arrivee`, `places_disponibles`, `prix`, `voyage_ecologique`, `statut`, `termine`, `validation_participant`) VALUES
(1, 1, 1, 'Paris', 'Lyon', '2025-07-11', '08:00:00', '11:00:00', 3, 15, 1, 'prévu', 0, 0),
(2, 2, 2, 'Lyon', 'Marseille', '2025-07-12', '09:30:00', '13:15:00', 2, 18, 0, 'prévu', 0, 0),
(3, 3, 3, 'Paris', 'Lyon', '2025-07-13', '10:00:00', '12:30:00', 4, 12, 1, 'prévu', 0, 0),
(4, 4, 4, 'Lyon', 'Marseille', '2025-07-14', '07:45:00', '09:30:00', 3, 10, 0, 'prévu', 0, 0),
(5, 5, 5, 'Lyon', 'Marseille', '2025-07-15', '14:00:00', '16:00:00', 2, 14, 1, 'prévu', 0, 0),
(6, 6, 6, 'Nice', 'Cannes', '2025-07-16', '11:15:00', '12:00:00', 3, 11, 1, 'prévu', 0, 0),
(7, 7, 7, 'Paris', 'Lyon', '2025-07-17', '08:30:00', '09:30:00', 1, 7, 0, 'prévu', 0, 0),
(8, 8, 8, 'Nice', 'Cannes', '2025-07-18', '16:45:00', '17:30:00', 4, 9, 0, 'prévu', 0, 0),
(9, 9, 9, 'Lyon', 'Nice', '2025-07-19', '13:00:00', '14:45:00', 3, 8, 1, 'prévu', 0, 0),
(10, 10, 10, 'Cannes', 'Paris', '2025-07-20', '15:00:00', '16:30:00', 1, 13, 0, 'prévu', 0, 0),
(11, 11, 11, 'Paris', 'Lyon', '2025-07-21', '09:00:00', '11:00:00', 3, 14, 1, 'prévu', 0, 0),
(12, 12, 12, 'Lyon', 'Marseille', '2025-07-22', '08:00:00', '10:00:00', 3, 10, 0, 'prévu', 0, 0),
(13, 13, 13, 'Marseille', 'Nice', '2025-07-23', '07:30:00', '09:30:00', 2, 11, 1, 'prévu', 0, 0),
(14, 14, 14, 'Cannes', 'Lyon', '2025-07-24', '12:00:00', '14:00:00', 3, 12, 1, 'prévu', 0, 0),
(15, 15, 15, 'Lyon', 'Paris', '2025-07-25', '10:15:00', '11:00:00', 2, 8, 0, 'prévu', 0, 0),
(16, 16, 16, 'Marseille', 'Paris', '2025-07-26', '14:45:00', '17:30:00', 3, 15, 1, 'prévu', 0, 0),
(17, 17, 17, 'Lyon', 'Cannes', '2025-07-27', '09:30:00', '10:15:00', 3, 7, 1, 'prévu', 0, 0),
(18, 18, 18, 'Cannes', 'Nice', '2025-07-28', '11:00:00', '12:30:00', 4, 9, 1, 'prévu', 0, 0),
(19, 19, 19, 'Marseille', 'Lyon', '2025-07-29', '08:00:00', '10:00:00', 1, 13, 0, 'prévu', 0, 0),
(82, 38, 70, 'Paris', 'Pula', '2025-07-24', '22:20:00', '20:20:00', 1, 1, 1, 'termine', 0, 0),
(83, 38, 70, 'Parissssssss', 'London', '2025-07-23', '23:24:00', '19:26:00', 0, 1, 1, 'termine', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `participations`
--

CREATE TABLE `participations` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `covoiturage_id` int(11) NOT NULL,
  `date_participation` datetime NOT NULL,
  `est_valide` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participations`
--

INSERT INTO `participations` (`id`, `utilisateur_id`, `covoiturage_id`, `date_participation`, `est_valide`) VALUES
(65, 71, 82, '2025-07-21 18:21:19', 0),
(66, 71, 83, '2025-07-21 19:24:55', 0);

-- --------------------------------------------------------

--
-- Structure de la table `preferences_conducteurs`
--

CREATE TABLE `preferences_conducteurs` (
  `id` int(11) NOT NULL,
  `conducteur_id` int(11) NOT NULL,
  `musique` tinyint(1) DEFAULT 0,
  `discussion` tinyint(1) DEFAULT 0,
  `animaux_acceptes` tinyint(1) DEFAULT 0,
  `climatisation` tinyint(1) DEFAULT 0,
  `fumeur` tinyint(1) DEFAULT 0,
  `preferences_personnalisees` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `preferences_conducteurs`
--

INSERT INTO `preferences_conducteurs` (`id`, `conducteur_id`, `musique`, `discussion`, `animaux_acceptes`, `climatisation`, `fumeur`, `preferences_personnalisees`) VALUES
(1, 1, 1, 1, 0, 1, 0, NULL),
(2, 2, 1, 1, 0, 1, 0, NULL),
(3, 3, 0, 1, 1, 0, 0, NULL),
(4, 4, 1, 0, 0, 1, 0, NULL),
(5, 5, 0, 0, 1, 1, 0, NULL),
(6, 6, 1, 1, 1, 1, 0, NULL),
(7, 7, 0, 1, 0, 0, 0, NULL),
(8, 8, 1, 0, 1, 0, 0, NULL),
(9, 9, 1, 1, 0, 0, 0, NULL),
(10, 10, 0, 0, 0, 1, 0, NULL),
(11, 11, 1, 1, 1, 0, 0, NULL),
(12, 12, 0, 0, 1, 1, 0, NULL),
(13, 13, 1, 0, 0, 0, 0, NULL),
(14, 14, 0, 1, 1, 1, 0, NULL),
(15, 15, 1, 1, 0, 0, 0, NULL),
(16, 16, 0, 0, 0, 1, 0, NULL),
(17, 17, 1, 0, 1, 1, 0, NULL),
(18, 18, 1, 1, 1, 1, 0, NULL),
(19, 19, 0, 1, 0, 1, 0, NULL),
(20, 20, 1, 0, 1, 0, 0, NULL),
(36, 70, 0, 0, 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `nom_famille` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse_postale` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `credits` int(11) DEFAULT 20,
  `role` enum('passager','chauffeur','passager_chauffeur','employe','administrateur') NOT NULL DEFAULT 'passager',
  `date_creation` datetime DEFAULT current_timestamp(),
  `photo_profil` varchar(255) DEFAULT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `pseudo`, `prenom`, `nom_famille`, `date_naissance`, `adresse_postale`, `telephone`, `email`, `mot_de_passe`, `credits`, `role`, `date_creation`, `photo_profil`, `statut`) VALUES
(1, 'alice75', '', '', '0000-00-00', '', '', 'alice75@example.com', 'motdepasse1', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/1.jpg', 'actif'),
(2, 'bob_paris', '', '', '0000-00-00', '', '', 'bob.paris@example.com', 'motdepasse2', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/1.jpg', 'actif'),
(3, 'claire78', '', '', '0000-00-00', '', '', 'claire78@example.com', 'motdepasse3', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/2.jpg', 'actif'),
(4, 'davidx', '', '', '0000-00-00', '', '', 'david@example.com', 'motdepasse4', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/2.jpg', 'actif'),
(5, 'emma_green', '', '', '0000-00-00', '', '', 'emma.green@example.com', 'motdepasse5', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/3.jpg', 'actif'),
(6, 'francois22', '', '', '0000-00-00', '', '', 'francois22@example.com', 'motdepasse6', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/3.avif', 'actif'),
(7, 'gwen_l', '', '', '0000-00-00', '', '', 'gwen.l@example.com', 'motdepasse7', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/4.jpg', 'actif'),
(8, 'hugo_leroy', '', '', '0000-00-00', '', '', 'hugo.leroy@example.com', 'motdepasse8', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/4.avif', 'actif'),
(9, 'ines.roux', '', '', '0000-00-00', '', '', 'ines.roux@example.com', 'motdepasse9', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/5.jpg', 'actif'),
(10, 'j.fournier', '', '', '0000-00-00', '', '', 'julien.fournier@example.com', 'motdepasse10', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/5.avif', 'actif'),
(11, 'karine.g', '', '', '0000-00-00', '', '', 'karine.g@example.com', 'motdepasse11', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/6.avif', 'actif'),
(12, 'louis_f', '', '', '0000-00-00', '', '', 'louis.f@example.com', 'motdepasse12', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/6.avif', 'actif'),
(13, 'manon34', '', '', '0000-00-00', '', '', 'manon34@example.com', 'motdepasse13', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/7.jpeg', 'actif'),
(14, 'nathan_dev', '', '', '0000-00-00', '', '', 'nathan.dev@example.com', 'motdepasse14', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/7.jpeg', 'actif'),
(15, 'oceane22', '', '', '0000-00-00', '', '', 'oceane.l@example.com', 'motdepasse15', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/8.jpeg', 'actif'),
(16, 'paulh', '', '', '0000-00-00', '', '', 'paul.h@example.com', 'motdepasse16', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/8.avif', 'actif'),
(17, 'quentin_q', '', '', '0000-00-00', '', '', 'quentin.q@example.com', 'motdepasse17', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/9.jpg', 'actif'),
(18, 'romane.r', '', '', '0000-00-00', '', '', 'romane.r@example.com', 'motdepasse18', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/10.jpg', 'actif'),
(19, 'sophieG', '', '', '0000-00-00', '', '', 'sophie.gomez@example.com', 'motdepasse19', 20, 'passager', '2025-07-10 13:23:48', 'assets/girls/9.avif', 'actif'),
(20, 'thomas_r', '', '', '0000-00-00', '', '', 'thomas.renaud@example.com', 'motdepasse20', 20, 'passager', '2025-07-10 13:23:48', 'assets/boys/11.avif', 'actif'),
(56, 'employe', NULL, NULL, NULL, NULL, NULL, 'employe@exemple.com', '$2y$10$tGMWMR4Td.hKo/E6PeW5uO0bzM/KCv3h5J9UlOn5nZZMGHcWhVD1O', 20, 'employe', '2025-07-18 17:28:28', NULL, 'actif'),
(63, 'admin', NULL, NULL, NULL, NULL, NULL, 'admin@example.com', '$2y$10$SUUcSUI9NdDECmGkbmZtW.gko4cmhTDocLaIc1zte5lH2Bmhp99h6', 20, 'administrateur', '2025-07-19 18:57:17', NULL, 'actif'),
(70, 'Ana', 'Anastasiia', 'Anastasiia', '2025-07-10', '13 Rue NicolaÏ', '0753045414', 'xoxolchic@mail.ru', '$2y$10$T1Eian1kT0GUgZBfM/sxDOvkz66MvTEmQcYCj/GFOocACAJd431yK', 16, 'passager_chauffeur', '2025-07-21 18:19:05', NULL, 'actif'),
(71, 'Anastasia', NULL, NULL, NULL, NULL, NULL, 'anastasiia_degtiar@icloud.com', '$2y$10$FpRgd3zpcXid365TFvcGPeyeq5TgzLqotYDEbO5IsEO7Gj/QFOY46', 18, 'passager', '2025-07-21 18:20:53', NULL, 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `validation_trajet`
--

CREATE TABLE `validation_trajet` (
  `id` int(11) NOT NULL,
  `covoiturage_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `statut` enum('non_valide','valide','probleme') DEFAULT 'non_valide',
  `commentaire` text DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `validation_employe` enum('en_attente','valide','refuse') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

CREATE TABLE `vehicules` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `plaque` varchar(20) NOT NULL,
  `date_immatriculation` date NOT NULL,
  `modele` varchar(50) NOT NULL,
  `marque` varchar(50) NOT NULL,
  `couleur` varchar(30) DEFAULT NULL,
  `energie` enum('essence','diesel','electrique','hybride') NOT NULL,
  `places_disponibles` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicules`
--

INSERT INTO `vehicules` (`id`, `utilisateur_id`, `plaque`, `date_immatriculation`, `modele`, `marque`, `couleur`, `energie`, `places_disponibles`) VALUES
(1, 1, 'AB-123-CD', '2021-06-15', 'Corsa-e', 'Opel', 'Bleu nuit', 'electrique', 1),
(2, 2, 'EF-456-GH', '2020-10-25', 'Accord', 'Honda', 'Gris métal', 'hybride', 1),
(3, 3, 'IJ-789-KL', '2022-10-05', 'Ioniq 5', 'Hyundai', 'Bleu', 'electrique', 1),
(4, 4, 'MN-012-OP', '2020-08-23', '308', 'Peugeot', 'Noir', 'diesel', 1),
(5, 5, 'QR-345-ST', '2021-09-11', 'Astra', 'Opel', 'Vert', 'diesel', 1),
(6, 6, 'UV-678-WX', '2023-03-15', 'Zoe', 'Renault', 'Blanc', 'electrique', 1),
(7, 7, 'YZ-901-AB', '2022-01-10', 'Civic', 'Honda', 'Gris', 'hybride', 1),
(8, 8, 'CD-234-EF', '2023-05-22', 'Model Y', 'Tesla', 'Rouge', 'electrique', 1),
(9, 9, 'GH-567-IJ', '2019-03-14', 'Golf', 'Volkswagen', 'Bleu', 'essence', 1),
(10, 10, 'KL-890-MN', '2021-12-20', '308 SW', 'Peugeot', 'Rouge foncé', 'essence', 1),
(11, 11, 'OP-123-QR', '2023-04-03', 'ID.3', 'Volkswagen', 'Argent', 'electrique', 1),
(12, 12, 'ST-456-UV', '2021-05-12', 'Clio', 'Renault', 'Rouge', 'essence', 1),
(13, 13, 'WX-789-YZ', '2022-11-17', 'Spring', 'Dacia', 'Jaune', 'electrique', 1),
(14, 14, 'AB-012-CD', '2023-02-01', 'Leaf', 'Nissan', 'Vert', 'electrique', 1),
(15, 15, 'EF-345-GH', '2023-03-03', 'Passat', 'Volkswagen', 'Bleu clair', 'diesel', 1),
(16, 16, 'IJ-678-KL', '2023-06-01', 'Corolla', 'Toyota', 'Blanc', 'hybride', 1),
(17, 17, 'MN-901-OP', '2022-01-10', 'Model 3', 'Tesla', 'Noir', 'electrique', 1),
(18, 18, 'QR-234-ST', '2021-06-20', 'e-208', 'Peugeot', 'Gris', 'electrique', 1),
(19, 19, 'UV-567-WX', '2022-02-17', 'Focus', 'Ford', 'Noir', 'essence', 1),
(20, 20, 'YZ-890-AB', '2022-08-08', 'i3', 'BMW', 'Gris foncé', 'electrique', 1),
(38, 70, 'AB-123-CA', '2025-07-29', 'Corsa-e', 'Opel', 'rouge', 'electrique', 2);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conducteur_id` (`conducteur_id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Index pour la table `covoiturages`
--
ALTER TABLE `covoiturages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicule_id` (`vehicule_id`),
  ADD KEY `conducteur_id` (`conducteur_id`);

--
-- Index pour la table `participations`
--
ALTER TABLE `participations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `covoiturage_id` (`covoiturage_id`);

--
-- Index pour la table `preferences_conducteurs`
--
ALTER TABLE `preferences_conducteurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conducteur_id` (`conducteur_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `validation_trajet`
--
ALTER TABLE `validation_trajet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `covoiturage_id` (`covoiturage_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `covoiturages`
--
ALTER TABLE `covoiturages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT pour la table `participations`
--
ALTER TABLE `participations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `preferences_conducteurs`
--
ALTER TABLE `preferences_conducteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT pour la table `validation_trajet`
--
ALTER TABLE `validation_trajet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `vehicules`
--
ALTER TABLE `vehicules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `covoiturages`
--
ALTER TABLE `covoiturages`
  ADD CONSTRAINT `covoiturages_ibfk_1` FOREIGN KEY (`vehicule_id`) REFERENCES `vehicules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `covoiturages_ibfk_2` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `participations`
--
ALTER TABLE `participations`
  ADD CONSTRAINT `participations_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `participations_ibfk_2` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturages` (`id`);

--
-- Contraintes pour la table `preferences_conducteurs`
--
ALTER TABLE `preferences_conducteurs`
  ADD CONSTRAINT `preferences_conducteurs_ibfk_1` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `validation_trajet`
--
ALTER TABLE `validation_trajet`
  ADD CONSTRAINT `validation_trajet_ibfk_1` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturages` (`id`),
  ADD CONSTRAINT `validation_trajet_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
