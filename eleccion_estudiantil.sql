-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-03-2026 a las 04:28:36
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
-- Base de datos: `elecciones_estudiantiles`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `detalles`, `ip_address`, `fecha`) VALUES
(1, NULL, 'REGISTRO', 'Nuevo votante registrado: marksquintanilla@gmail.com', '::1', '2026-03-30 00:53:32'),
(2, 2, 'LOGIN', 'Inicio de sesión: marksquintanilla@gmail.com', '::1', '2026-03-30 00:54:22'),
(3, 2, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 00:54:45'),
(4, 2, 'LOGIN', 'Inicio de sesión: marksquintanilla@gmail.com', '::1', '2026-03-30 00:59:33'),
(5, 2, 'LOGIN', 'Inicio de sesión: marksquintanilla@gmail.com', '::1', '2026-03-30 01:05:30'),
(6, 2, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:06:09'),
(7, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:08:48'),
(8, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:11:25'),
(9, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:13:37'),
(10, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:15:06'),
(11, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:17:18'),
(12, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:22:18'),
(13, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:24:02'),
(14, 1, 'PARTIDO_CREADO', 'Partido: FRENTE EL LIBERTADOR', '::1', '2026-03-30 01:27:15'),
(15, 1, 'PARTIDO_CREADO', 'Partido: FRENTE CONSERVADOR', '::1', '2026-03-30 01:27:58'),
(16, 1, 'CANDIDATO_CREADO', 'Candidato usuario_id=2 cargo=1', '::1', '2026-03-30 01:29:01'),
(17, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:31:56'),
(18, 2, 'LOGIN', 'Inicio de sesión: marksquintanilla@gmail.com', '::1', '2026-03-30 01:32:13'),
(19, 2, 'VOTO_EMITIDO', 'Voto por candidato_id=1 cargo=Presidente/a', '::1', '2026-03-30 01:32:37'),
(20, 2, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:33:36'),
(21, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:40:48'),
(22, 1, 'USUARIO_CREADO', 'Votante: estudiante@gmail.com', '::1', '2026-03-30 01:42:28'),
(23, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:42:47'),
(24, 6, 'LOGIN', 'Inicio de sesión: estudiante@gmail.com', '::1', '2026-03-30 01:42:57'),
(25, 6, 'VOTO_EMITIDO', 'Voto por candidato_id=1 cargo=Presidente/a', '::1', '2026-03-30 01:43:06'),
(26, 6, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:43:21'),
(27, NULL, 'REGISTRO', 'Nuevo votante registrado: estudiante2@gmail.com', '::1', '2026-03-30 01:44:15'),
(28, 7, 'LOGIN', 'Inicio de sesión: estudiante2@gmail.com', '::1', '2026-03-30 01:44:39'),
(29, 7, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:44:45'),
(30, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:45:14'),
(31, 1, 'CANDIDATO_CREADO', 'Candidato usuario_id=6 cargo=1', '::1', '2026-03-30 01:45:48'),
(32, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:46:14'),
(33, 7, 'LOGIN', 'Inicio de sesión: estudiante2@gmail.com', '::1', '2026-03-30 01:46:29'),
(34, 7, 'VOTO_EMITIDO', 'Voto por candidato_id=2 cargo=Presidente/a', '::1', '2026-03-30 01:46:36'),
(35, 7, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:47:36'),
(36, 1, 'LOGIN', 'Inicio de sesión: admin@elecciones.edu', '::1', '2026-03-30 01:49:36'),
(37, 1, 'LOGOUT', 'Cierre de sesión', '::1', '2026-03-30 01:51:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `candidatos`
--

CREATE TABLE `candidatos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `partido_id` int(11) DEFAULT NULL,
  `cargo_id` int(11) NOT NULL,
  `propuesta` text DEFAULT NULL,
  `foto_url` varchar(500) DEFAULT '',
  `numero_candidato` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `candidatos`
--

INSERT INTO `candidatos` (`id`, `usuario_id`, `partido_id`, `cargo_id`, `propuesta`, `foto_url`, `numero_candidato`, `activo`, `created_at`) VALUES
(1, 2, 1, 1, 'Proyectos para el desarrollo del CEMG Pascual Fajardo', '', 1, 1, '2026-03-30 01:29:01'),
(2, 6, 2, 1, '', '', 2, 1, '2026-03-30 01:45:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `nombre`, `descripcion`, `orden`) VALUES
(1, 'Presidente/a', 'Máxima representación del gobierno estudiantil', 1),
(2, 'Vicepresidente/a', 'Suplencia y apoyo al presidente/a', 2),
(3, 'Secretario/a General', 'Gestión documental y comunicaciones', 3),
(4, 'Tesorero/a', 'Administración de fondos estudiantiles', 4),
(5, 'Vocal 1', 'Representante de actividades culturales', 5),
(6, 'Vocal 2', 'Representante de actividades deportivas', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_eleccion` varchar(255) NOT NULL DEFAULT 'Elecciones Estudiantiles 2026',
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `logo_url` varchar(500) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_eleccion`, `fecha_inicio`, `fecha_fin`, `activa`, `logo_url`, `created_at`) VALUES
(1, 'Elecciones de Gobierno Estudiantil 2026', '2026-03-29 18:49:54', '2026-04-28 18:49:54', 1, '', '2026-03-30 00:49:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `slogan` varchar(500) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#6366f1',
  `logo_url` varchar(500) DEFAULT '',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `nombre`, `slogan`, `color`, `logo_url`, `descripcion`, `activo`, `created_at`) VALUES
(1, 'FRENTE EL LIBERTADOR', 'Unidos por un solo proposito', '#d80e0e', '', 'Es en homenaje a Franciso Morazan', 1, '2026-03-30 01:27:15'),
(2, 'FRENTE CONSERVADOR', 'Unidos por Ilama', '#0004ff', '', 'En honor a nuestro pueblo', 1, '2026-03-30 01:27:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','votante') NOT NULL DEFAULT 'votante',
  `grado` varchar(50) DEFAULT NULL,
  `seccion` varchar(10) DEFAULT NULL,
  `codigo_estudiantil` varchar(20) DEFAULT NULL,
  `foto_url` varchar(500) DEFAULT '',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `grado`, `seccion`, `codigo_estudiantil`, `foto_url`, `activo`, `created_at`) VALUES
(1, 'Administrador del Sistema', 'admin@elecciones.edu', '$2y$10$9j8/4JIzGV36orD2nSCtEuUl4UPvZlW7zh.wBpsu3AnB5WZA79rMi', 'admin', NULL, NULL, 'ADM001', '', 1, '2026-03-30 00:49:54'),
(2, 'MARCOS QUINTANILLA', 'marksquintanilla@gmail.com', '$2y$10$ybKjFtUqOW.Qiqoi3F2XButMSawbKP6G/Kt/AxTiWaQbMDa/cKqZK', 'votante', '12', 'A', '1627198800806', '', 1, '2026-03-30 00:53:32'),
(6, 'NORLAN HERNANDEZ', 'estudiante@gmail.com', '$2y$10$i980FVMUhRfpOu8NG8AD1.HjE8Z9adblhOExP1rr7cOyWJ6etVteO', 'votante', '12vo', 'A', '1623200900025', '', 1, '2026-03-30 01:42:28'),
(7, 'BRANDO GARCIA', 'estudiante2@gmail.com', '$2y$10$tZB1fzaW4YsIXHLouCMzYeD.y.wlbDOkOVdB6JJCRv7o5/K7M5W8e', 'votante', '11vo', 'B', '1623200900032', '', 1, '2026-03-30 01:44:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `votos`
--

CREATE TABLE `votos` (
  `id` int(11) NOT NULL,
  `votante_id` int(11) NOT NULL,
  `candidato_id` int(11) NOT NULL,
  `cargo_id` int(11) NOT NULL,
  `fecha_voto` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `votos`
--

INSERT INTO `votos` (`id`, `votante_id`, `candidato_id`, `cargo_id`, `fecha_voto`, `ip_address`) VALUES
(1, 2, 1, 1, '2026-03-30 01:32:37', '::1'),
(2, 6, 1, 1, '2026-03-30 01:43:06', '::1'),
(3, 7, 2, 1, '2026-03-30 01:46:36', '::1');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_resultados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_resultados` (
`candidato_id` int(11)
,`nombre_candidato` varchar(150)
,`foto_url` varchar(500)
,`cargo` varchar(150)
,`cargo_id` int(11)
,`partido` varchar(200)
,`partido_color` varchar(7)
,`total_votos` bigint(21)
,`total_votantes` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_resultados`
--
DROP TABLE IF EXISTS `v_resultados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_resultados`  AS SELECT `c`.`id` AS `candidato_id`, `u`.`nombre` AS `nombre_candidato`, `u`.`foto_url` AS `foto_url`, `ca`.`nombre` AS `cargo`, `ca`.`id` AS `cargo_id`, `p`.`nombre` AS `partido`, `p`.`color` AS `partido_color`, count(`v`.`id`) AS `total_votos`, (select count(0) from `usuarios` where `usuarios`.`rol` = 'votante' and `usuarios`.`activo` = 1) AS `total_votantes` FROM ((((`candidatos` `c` join `usuarios` `u` on(`c`.`usuario_id` = `u`.`id`)) join `cargos` `ca` on(`c`.`cargo_id` = `ca`.`id`)) left join `partidos` `p` on(`c`.`partido_id` = `p`.`id`)) left join `votos` `v` on(`c`.`id` = `v`.`candidato_id`)) WHERE `c`.`activo` = 1 GROUP BY `c`.`id`, `u`.`nombre`, `u`.`foto_url`, `ca`.`nombre`, `ca`.`id`, `p`.`nombre`, `p`.`color` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `candidatos`
--
ALTER TABLE `candidatos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `partido_id` (`partido_id`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `codigo_estudiantil` (`codigo_estudiantil`);

--
-- Indices de la tabla `votos`
--
ALTER TABLE `votos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_voto_cargo` (`votante_id`,`cargo_id`),
  ADD KEY `candidato_id` (`candidato_id`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `candidatos`
--
ALTER TABLE `candidatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `votos`
--
ALTER TABLE `votos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `candidatos`
--
ALTER TABLE `candidatos`
  ADD CONSTRAINT `candidatos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidatos_ibfk_2` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `candidatos_ibfk_3` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `votos`
--
ALTER TABLE `votos`
  ADD CONSTRAINT `votos_ibfk_1` FOREIGN KEY (`votante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votos_ibfk_2` FOREIGN KEY (`candidato_id`) REFERENCES `candidatos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votos_ibfk_3` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
