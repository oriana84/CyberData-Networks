-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql211.infinityfree.com
-- Tiempo de generación: 20-07-2026 a las 12:25:58
-- Versión del servidor: 11.4.12-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_42394554_cyberdata`
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
(10, 'Fraude Financiero'),
(5, 'Fuga de información'),
(2, 'Malware'),
(1, 'Phishing'),
(3, 'Ransomware'),
(9, 'Robo Identidad'),
(11, 'SQL Injection');

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
(5, '77.333.666-1', 'Inversiones Capital Norte SpA', 'ACTIVO', 'ciso@capitalnorte.cl', '+56 2 2999 3344'),
(6, '75.345.678-0', 'Farmacéutica BioHealth Chile SpA', 'ACTIVO', 'TEST@TEST.COM', '+56 2 2234 5678'),
(7, '78.111.222-6', 'Seguros Fortaleza Chile SpA', 'ACTIVO', 'seguridad@segurosfortaleza.cl', '+56 2 2987 6543'),
(8, '76.888.555-3', 'Constructora AltoSur Ltda', 'ACTIVO', 'sistemas@altosur.cl', '+56 9 7888 5566'),
(9, '76.555.111-2', 'Hotel Costa Azul Internacional', 'ACTIVO', 'soporte@costazulhotel.cl', '+56 2 2555 1100'),
(10, '79.123.456-7', 'Universidad Tecnológica del Pacífico', 'ACTIVO', 'soporte@utp.cl', '+56 2 2123 4567');

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
(12, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-13 16:32:42', 5),
(13, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-13 16:44:25', 6),
(14, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-13 16:49:39', 7),
(15, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-13 16:51:12', 8),
(16, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-13 16:54:56', 9),
(17, 'EN_PROCESO', 'se ejecuta el incidente', '2026-07-13 16:58:11', 9),
(18, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-15 20:44:32', 10),
(19, 'ABIERTO', 'Incidente registrado por ingesta de logs.', '2026-07-16 02:50:25', 11),
(20, 'EN_PROCESO', 'se procede a realizar el proceso', '2026-07-16 21:26:05', 10),
(21, 'CERRADO', 'log resuelto', '2026-07-16 21:44:45', 6);

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
(5, 'ALTA', '2026-06-18T17:20:55Z EMAIL SECURITY ALERT EXECUTIVE_PHISHING\r\n\r\nsender=ceo-capitalnorte@gmail.com\r\ntarget=finanzas@capitalnorte.cl\r\nsubject=\"Transferencia urgente\"\r\nattachment=orden_pago.exe\r\naction=BLOCKED\r\nseverity=HIGH', '2026-07-13 16:32:42', 5, 1, 1),
(6, 'CRITICA', '2026-06-18T18:40:10Z FILESERVER ALERT RANSOMWARE\r\n\r\nserver=DOCUMENTOS-01\r\nfiles_encrypted=18450\r\nextension=.locked\r\nbackup_status=FAILED\r\naction=NETWORK_ISOLATION\r\nseverity=CRITICAL', '2026-07-13 16:44:25', 8, 3, 1),
(7, 'CRITICA', '2026-06-18T16:40:22Z EDR ALERT RANSOMWARE\r\n\r\nhost=LAB-SERVER-02\r\nmalware=LockBit_variant\r\nencrypted_files=56000\r\nprocess=crypto.exe\r\naction=ISOLATED\r\nseverity=CRITICAL', '2026-07-13 16:49:39', 6, 3, 1),
(8, 'MEDIA', '2026-06-18T15:50:30Z IAM ALERT IDENTITY_THEFT\r\n\r\nuser=cliente_online\r\nlogin_country=Unknown\r\ndevice=new\r\nbehavior=ANOMALOUS\r\naction=MFA_REQUIRED\r\nseverity=MEDIUM', '2026-07-13 16:51:12', 7, 9, 1),
(9, 'MEDIA', 'host=LAB-PC-045\r\nuser=alumno123\r\nfile=matricula_update.exe\r\nhash=8f4a91bc772dd891\r\nmalware_family=Trojan.Agent\r\naction=QUARANTINE\r\nseverity=medium', '2026-07-13 16:54:56', 10, 2, 1),
(10, 'ALTA', 'host=LOGISUR-SRV01\r\nfile_extension=.encrypted\r\nprocess=unknown.exe\r\nencryption_rate=95%\r\nfiles_affected=23840\r\naction=ISOLATED\r\nseverity=CRITICAL', '2026-07-15 20:44:32', 6, 3, 9),
(11, 'ALTA', 'user=administrator\r\nsystem=SCADA_CONTROL_SERVER\r\nsource_ip=185.22.10.45\r\naccess_type=REMOTE_LOGIN\r\nauthentication=FAILED\r\nattempts=12\r\naction=BLOCKED\r\nseverity=HIGH', '2026-07-16 02:50:25', 10, 5, 1);

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
(1, 'Administrador', 'admin@cyberdata.cl', '$2y$10$50nCsQVmQgOGzeYYGv97Yelj/X4aOEEhGKwg.a5GyxvQ0jqUNMEVK', '655fffc09fa61965862ea605d0f4d1cf7a902104beb0fc15687d5c8b8cd8a0e3', '2026-07-13 13:25:38', 'ACTIVO', 1),
(7, 'Administrador 2', 'ankeluz@gmail.com', '$2y$10$nkDA.vWgx9Nea8gYnLE55ugnZiGE8QYyZjQEMkS1tyNSJbA1glLJq', 'b3a1f4b4afeed400dbe07158f700f82e4c8ce68784f81262fadd882359447037', '2026-07-16 21:48:18', 'ACTIVO', 1),
(8, 'Oriana M', 'analista@cyberdata.cl', '$2y$10$cN3ctWMtHxLCzkj3HMUVW.gJmAxSeZvg25VthL2wmfpNM6OrftnbS', NULL, NULL, 'ACTIVO', 2),
(9, 'Oriana', 'cyberdata.soporte1@gmail.com', '$2y$10$LzqZIA/1Trw479aYsc8v.OKv.9pR5.Sgl9rv8TRc34J/.rYmGdWQy', NULL, NULL, 'INACTIVO', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_cliente`
--

CREATE TABLE `usuario_cliente` (
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `historial_estados`
--
ALTER TABLE `historial_estados`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `incidentes`
--
ALTER TABLE `incidentes`
  MODIFY `id_incidente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
