-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 28 juin 2023 à 15:59
-- Version du serveur : 8.0.31
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `project`
--

-- --------------------------------------------------------

--
-- Structure de la table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `message` varchar(100) NOT NULL,
  `cycle_id` int NOT NULL,
  `due_date` date NOT NULL,
  `group_id` int NOT NULL,
  `done` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `message`, `cycle_id`, `due_date`, `group_id`, `done`) VALUES
(4, 'First assign!', 'Hello faites ca pour vendredi svp', 5, '2023-06-30', 3, 0),
(6, 'Second assign yeah!', 'Gros bibi pour le 14 juillet', 2, '2023-07-14', 4, 0);

-- --------------------------------------------------------

--
-- Structure de la table `community`
--

DROP TABLE IF EXISTS `community`;
CREATE TABLE IF NOT EXISTS `community` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `theme` varchar(100) NOT NULL,
  `creator` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `community`
--

INSERT INTO `community` (`id`, `title`, `theme`, `creator`) VALUES
(3, 'Groupe test', 'Super theme', 3),
(4, 'Groupe bien bibi', 'Que les bras', 1);

-- --------------------------------------------------------

--
-- Structure de la table `cycles`
--

DROP TABLE IF EXISTS `cycles`;
CREATE TABLE IF NOT EXISTS `cycles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `theme` varchar(100) NOT NULL,
  `creator` int NOT NULL,
  `breaktime` int NOT NULL,
  `repetition` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cycles`
--

INSERT INTO `cycles` (`id`, `title`, `theme`, `creator`, `breaktime`, `repetition`) VALUES
(5, 'Entrainement du Dos', 'Dos', 3, 20, 5),
(2, 'secondcycle', 'courseapied', 1, 40, 7),
(3, 'cyclepolo', 'courseapied', 4, 40, 7);

-- --------------------------------------------------------

--
-- Structure de la table `exercices`
--

DROP TABLE IF EXISTS `exercices`;
CREATE TABLE IF NOT EXISTS `exercices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `descr` varchar(100) NOT NULL,
  `duration` int NOT NULL,
  `theme` varchar(100) NOT NULL,
  `creator` int NOT NULL,
  `image` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `exercices`
--

INSERT INTO `exercices` (`id`, `title`, `descr`, `duration`, `theme`, `creator`, `image`) VALUES
(6, 'Super Exercice', 'Cet exercice fait bien les pecs !', 120, 'Pectoraux', 1, ''),
(7, 'premierexo', 'yeya', 50, 'Biceps', 1, '/project/pompe.jpg'),
(8, 'Squat', 'Plier les jambes et musclez les fesses !', 120, 'Summer body', 3, '/project/squat.jpg'),
(9, 'Traction', 'Hop hop hop on monte le torse', 30, 'Dos large', 3, '/project/traction.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `innercycles`
--

DROP TABLE IF EXISTS `innercycles`;
CREATE TABLE IF NOT EXISTS `innercycles` (
  `id_exercice` int NOT NULL,
  `id_cycle` int NOT NULL,
  `order_ex` int NOT NULL,
  `duration` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `innercycles`
--

INSERT INTO `innercycles` (`id_exercice`, `id_cycle`, `order_ex`, `duration`) VALUES
(7, 5, 2, 180),
(8, 5, 1, 120),
(6, 5, 3, 420);

-- --------------------------------------------------------

--
-- Structure de la table `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE IF NOT EXISTS `members` (
  `id_user` int NOT NULL,
  `id_group` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `members`
--

INSERT INTO `members` (`id_user`, `id_group`) VALUES
(1, 3),
(4, 3),
(3, 3),
(4, 4),
(1, 4),
(2, 4);

-- --------------------------------------------------------

--
-- Structure de la table `scores`
--

DROP TABLE IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
  `id_user` int NOT NULL,
  `id_assignment` int NOT NULL,
  `score` int NOT NULL,
  `feedback` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(100) NOT NULL,
  `pass` varchar(100) NOT NULL,
  `hash` varchar(100) NOT NULL,
  `trainer` tinyint(1) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `pass`, `hash`, `trainer`, `admin`) VALUES
(1, 'antho', 'azer', '823679bebd3b19712899506974ceba5a', 1, 1),
(2, 'testos', 'oui', '846fc8c9401170423bc58d7729fb6d0a', 0, 0),
(3, 'guilhem', 'super', 'bba7c629e080bdd40083f9d9ff32a898', 1, 0),
(4, 'marco', 'polo', 'a18cc49e47b9dd77e5aebef85601c316', 0, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
