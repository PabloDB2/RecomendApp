-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-05-2025 a las 15:43:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Base de datos: `recomendapp`
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `recomendapp`;
USE `recomendapp`;

-- --------------------------------------------------------
-- Tabla `usuarios`
-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.jpg',
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla `favoritos`
-- --------------------------------------------------------

CREATE TABLE `favoritos` (
  `id_favorito` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `api_id` varchar(255) NOT NULL,
  `categoria` enum('película','serie','libro') NOT NULL,
  PRIMARY KEY (`id_favorito`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla `lista` (ver más tarde)
-- --------------------------------------------------------

CREATE TABLE `lista` (
  `id_lista` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `api_id` varchar(255) NOT NULL,
  `categoria` enum('película','serie','libro') NOT NULL,
  PRIMARY KEY (`id_lista`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla `reseñas`
-- --------------------------------------------------------

CREATE TABLE `reseñas` (
 `id_reseña` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `api_id` varchar(255) NOT NULL,
  `categoria` enum('película','serie','libro') NOT NULL,
  `texto` text DEFAULT NULL,
  `puntuacion` INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
  `likes` int(11) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reseña`),
  KEY `id_usuario` (`id_usuario`),
  KEY `api_id` (`api_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla `visualizaciones`
-- --------------------------------------------------------

CREATE TABLE `visualizaciones` (
  `id_visualizacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `api_id` varchar(255) NOT NULL,
  `categoria` enum('película','serie','libro') NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id_visualizacion`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla para registrar likes únicos por usuario y reseña
CREATE TABLE IF NOT EXISTS `likes_resena` (
  `id_like` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_resena` int(11) NOT NULL,
  PRIMARY KEY (`id_like`),
  UNIQUE KEY `usuario_resena_unique` (`id_usuario`, `id_resena`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_resena` (`id_resena`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Claves foráneas
-- --------------------------------------------------------

ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

ALTER TABLE `lista`
  ADD CONSTRAINT `lista_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

ALTER TABLE `reseñas`
  ADD CONSTRAINT `reseñas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

ALTER TABLE `visualizaciones`
  ADD CONSTRAINT `visualizaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
