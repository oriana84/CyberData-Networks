-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-07-2026 a las 20:10:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cyberdata_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_amenaza`
--

CREATE TABLE `categorias_amenaza` (
  `id_categoria` int(11) NOT NULL,
  `nombre_vector` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_amenaza`
--

INSERT INTO `categorias_amenaza` (`id_categoria`, `nombre_vector`) VALUES
(6, 'Acceso no autorizado'),
(4, 'DDoS'),
(5, 'Fuga de información'),
(2, 'Malware'),
(1, 'Phishing'),
(3, 'Ransomware'),
(7, 'TEST');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `rut_empresa` varchar(12) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `estado_cliente` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `rut_empresa`, `razon_social`, `estado_cliente`, `email`, `telefono`) VALUES
(1, '76543210-1', 'Banco de Chile - Corporativo', 'ACTIVO', 'soc@bancochile.cl', '+56222222222'),
(2, '170457978', 'TEST3', 'ACTIVO', 'TEST@TEST.COM', '828282828'),
(3, '81668361', 'TESTSTT', 'ACTIVO', 'teest@gmail.com', '+56930053129');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_estados`
--

CREATE TABLE `historial_estados` (
  `id_historial` int(11) NOT NULL,
  `estado_actual` enum('ABIERTO','EN_PROCESO','CERRADO') NOT NULL,
  `comentario` text DEFAULT NULL,
  `fecha_cambio` timestamp NULL DEFAULT current_timestamp(),
  `INCIDENTES_id_incidente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial_estados`
--

INSERT INTO `historial_estados` (`id_historial`, `estado_actual`, `comentario`, `fecha_cambio`, `INCIDENTES_id_incidente`) VALUES
(1, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-05 07:24:37', 1),
(2, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-05 07:45:29', 2),
(3, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-05 07:46:17', 3),
(4, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-05 07:46:24', 4),
(5, 'EN_PROCESO', 'ddsdfsdf', '2026-07-05 08:08:23', 4),
(6, 'CERRADO', 'dsfsfdsf', '2026-07-05 08:08:32', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidentes`
--

CREATE TABLE `incidentes` (
  `id_incidente` int(11) NOT NULL,
  `criticidad` enum('BAJA','MEDIA','ALTA','CRITICA') NOT NULL,
  `logs_crudos` longtext DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `CLIENTE_id_cliente` int(11) NOT NULL,
  `CATEGORIAS_AMENAZA_id_categoria` int(11) NOT NULL,
  `USUARIO_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `incidentes`
--

INSERT INTO `incidentes` (`id_incidente`, `criticidad`, `logs_crudos`, `fecha_registro`, `CLIENTE_id_cliente`, `CATEGORIAS_AMENAZA_id_categoria`, `USUARIO_id_usuario`) VALUES
(1, 'BAJA', 'sdfdsfdsfdsfsdfdsfdsfdsfdsf', '2026-07-05 07:24:37', 1, 1, 1),
(2, 'MEDIA', 'DFHDFHGFHDGHDFHDFHGF', '2026-07-05 07:45:29', 2, 4, 1),
(3, 'CRITICA', 'asdasdasdasd', '2026-07-05 07:46:17', 1, 5, 1),
(4, 'MEDIA', 'sdfdsfdsfdsf', '2026-07-05 07:46:24', 1, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Analista SOC'),
(3, 'Cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `token_recovery` varchar(64) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `ROL_id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `email`, `password_hash`, `token_recovery`, `token_expira`, `estado`, `ROL_id_rol`) VALUES
(1, 'Administrador', 'admin@cyberdata.cl', '$2y$10$8p4ZJLnXulTLneAes/7aFuddJvOPq/iouWPc6BIT43OF4S8uqdx6q', NULL, NULL, 'ACTIVO', 1),
(2, 'israel irarrazabal', 'israel.irarrazabal@gmail.com', '$2y$10$s7g5zhuTZ04pn.m3X1TKHeu.RqAQtb85cUX4iMEUesukOKcXm5n0e', NULL, NULL, 'ACTIVO', 1),
(3, 'TEST2', 'TEST@gmail.com', '$2y$10$e397L/4BKsmAHvhUf4j6deMKCdmx8ptDbMnP4MUu84M.qm2v8MCem', NULL, NULL, 'INACTIVO', 2),
(4, 'TEST2', 'TEST2@gmail.com', '$2y$10$aVekT6xYpJG/Hn01X2dmPuq5Wo8ZE8BNY3y.MOKpI9M8sWph5Ii7W', NULL, NULL, 'ACTIVO', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_cliente`
--

CREATE TABLE `usuario_cliente` (
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_cliente`
--

INSERT INTO `usuario_cliente` (`id_usuario`, `id_cliente`) VALUES
(4, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias_amenaza`
--
ALTER TABLE `categorias_amenaza`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uq_categorias_nombre_vector` (`nombre_vector`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `rut_empresa` (`rut_empresa`);

--
-- Indices de la tabla `historial_estados`
--
ALTER TABLE `historial_estados`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_HISTORIAL_ESTADOS_INCIDENTES1_idx` (`INCIDENTES_id_incidente`);

--
-- Indices de la tabla `incidentes`
--
ALTER TABLE `incidentes`
  ADD PRIMARY KEY (`id_incidente`),
  ADD KEY `fk_INCIDENTES_CLIENTE1_idx` (`CLIENTE_id_cliente`),
  ADD KEY `fk_INCIDENTES_CATEGORIAS_AMENAZA1_idx` (`CATEGORIAS_AMENAZA_id_categoria`),
  ADD KEY `fk_INCIDENTES_USUARIO1_idx` (`USUARIO_id_usuario`),
  ADD KEY `idx_analitica` (`CLIENTE_id_cliente`,`criticidad`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_USUARIO_ROL_idx` (`ROL_id_rol`),
  ADD KEY `idx_token_recovery` (`token_recovery`);

--
-- Indices de la tabla `usuario_cliente`
--
ALTER TABLE `usuario_cliente`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `idx_usuario_cliente_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias_amenaza`
--
ALTER TABLE `categorias_amenaza`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `historial_estados`
--
ALTER TABLE `historial_estados`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `incidentes`
--
ALTER TABLE `incidentes`
  MODIFY `id_incidente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_estados`
--
ALTER TABLE `historial_estados`
  ADD CONSTRAINT `fk_HISTORIAL_ESTADOS_INCIDENTES1` FOREIGN KEY (`INCIDENTES_id_incidente`) REFERENCES `incidentes` (`id_incidente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidentes`
--
ALTER TABLE `incidentes`
  ADD CONSTRAINT `fk_INCIDENTES_CATEGORIAS_AMENAZA1` FOREIGN KEY (`CATEGORIAS_AMENAZA_id_categoria`) REFERENCES `categorias_amenaza` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_INCIDENTES_CLIENTE1` FOREIGN KEY (`CLIENTE_id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_INCIDENTES_USUARIO1` FOREIGN KEY (`USUARIO_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_USUARIO_ROL` FOREIGN KEY (`ROL_id_rol`) REFERENCES `rol` (`id_rol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_cliente`
--
ALTER TABLE `usuario_cliente`
  ADD CONSTRAINT `fk_usuario_cliente_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_cliente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
