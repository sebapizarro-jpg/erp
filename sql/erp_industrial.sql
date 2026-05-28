-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 28-05-2026 a las 00:48:48
-- Versión del servidor: 8.0.46
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `erp_industrial`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo_piezas`
--

CREATE TABLE `catalogo_piezas` (
  `id` int NOT NULL,
  `codigo_sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_pieza` enum('Standard','Variable','Comercial') COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `catalogo_piezas`
--

INSERT INTO `catalogo_piezas` (`id`, `codigo_sku`, `nombre`, `tipo_pieza`, `proveedor_id`, `activo`, `created_at`) VALUES
(1, 'SK-EJE-32MM', 'EJE CENTRAL DE TRANSMISION DE ACERO', 'Comercial', 1, 1, '2026-05-28 00:32:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int NOT NULL,
  `razon_social` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuit_rut` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contacto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Ingresado', 'Nota de fabricación recién creada'),
(2, 'Pendiente_Stock', 'A la espera de verificación o liberación de materiales'),
(3, 'En_Produccion', 'Actualmente en taller o maquinaria'),
(4, 'En_QC', 'Esperando revisión de Control de Calidad'),
(5, 'Aprobado', 'Revisión exitosa, listo para ensamble o entrega'),
(6, 'Rechazado', 'No pasó QC, requiere acciones correctivas'),
(7, 'En_Revision', 'Bajo análisis de ingeniería o cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nf_desglose`
--

CREATE TABLE `nf_desglose` (
  `id` int NOT NULL,
  `nf_id` int NOT NULL,
  `pieza_catalogo_id` int NOT NULL,
  `cantidad_requerida` decimal(10,2) NOT NULL,
  `estado_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int NOT NULL,
  `tipo_orden` enum('Interna','Proveedor','Taller_Externo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedor_id` int DEFAULT NULL,
  `estado_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_detalle`
--

CREATE TABLE `ordenes_detalle` (
  `id` int NOT NULL,
  `orden_id` int NOT NULL,
  `nf_desglose_id` int NOT NULL,
  `cantidad` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int NOT NULL,
  `razon_social` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuit_rut` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contacto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `razon_social`, `cuit_rut`, `contacto`, `activo`, `created_at`) VALUES
(1, 'Aceros Industriales S.A.', '30-12345678-9', 'Carlos Pérez', 1, '2026-05-28 00:10:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos_nf`
--

CREATE TABLE `proyectos_nf` (
  `id` int NOT NULL,
  `codigo_nf` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` int NOT NULL,
  `estado_id` int NOT NULL,
  `fecha_recepcion` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Admin', 'Acceso total al sistema y configuraciones'),
(2, 'Supervisor', 'Gestiona NFs, aprueba órdenes y supervisa planta'),
(3, 'Calidad', 'Personal de QC, aprueba o rechaza piezas'),
(4, 'Operario', 'Personal de planta, registra avances de producción');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_inventario`
--

CREATE TABLE `stock_inventario` (
  `id` int NOT NULL,
  `pieza_catalogo_id` int NOT NULL,
  `cantidad_fisica` decimal(10,2) DEFAULT '0.00',
  `cantidad_reservada` decimal(10,2) DEFAULT '0.00',
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `stock_inventario`
--

INSERT INTO `stock_inventario` (`id`, `pieza_catalogo_id`, `cantidad_fisica`, `cantidad_reservada`, `ubicacion`) VALUES
(1, 1, 0.00, 0.00, 'No Asignada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trazabilidad_estados`
--

CREATE TABLE `trazabilidad_estados` (
  `id` int NOT NULL,
  `entidad_tipo` enum('NF','Pieza_Desglose','Orden') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int NOT NULL,
  `estado_anterior_id` int DEFAULT NULL,
  `estado_nuevo_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `motivo_observacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `rol_id` int NOT NULL,
  `legajo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `legajo`, `nombre_completo`, `password_hash`, `activo`, `ultimo_acceso`, `created_at`) VALUES
(1, 1, 'ADMIN01', 'Administrador del Sistema', '$2y$10$EiVE9Hm63f9L21M3f8GUgOVU8e1Hl0J1.ZvU/p09HlWAey1CzIWdS', 1, '2026-05-28 00:20:28', '2026-05-28 00:01:30');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `catalogo_piezas`
--
ALTER TABLE `catalogo_piezas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_sku` (`codigo_sku`),
  ADD KEY `fk_pieza_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cuit_rut` (`cuit_rut`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `nf_desglose`
--
ALTER TABLE `nf_desglose`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_desglose_nf` (`nf_id`),
  ADD KEY `fk_desglose_pieza` (`pieza_catalogo_id`),
  ADD KEY `fk_desglose_estado` (`estado_id`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orden_proveedor` (`proveedor_id`),
  ADD KEY `fk_orden_estado` (`estado_id`);

--
-- Indices de la tabla `ordenes_detalle`
--
ALTER TABLE `ordenes_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_orden` (`orden_id`),
  ADD KEY `fk_detalle_desglose` (`nf_desglose_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cuit_rut` (`cuit_rut`);

--
-- Indices de la tabla `proyectos_nf`
--
ALTER TABLE `proyectos_nf`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_nf` (`codigo_nf`),
  ADD KEY `fk_nf_cliente` (`cliente_id`),
  ADD KEY `fk_nf_estado` (`estado_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `stock_inventario`
--
ALTER TABLE `stock_inventario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pieza_catalogo_id` (`pieza_catalogo_id`);

--
-- Indices de la tabla `trazabilidad_estados`
--
ALTER TABLE `trazabilidad_estados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trazabilidad_est_ant` (`estado_anterior_id`),
  ADD KEY `fk_trazabilidad_est_nue` (`estado_nuevo_id`),
  ADD KEY `fk_trazabilidad_usuario` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legajo` (`legajo`),
  ADD KEY `fk_usuario_rol` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `catalogo_piezas`
--
ALTER TABLE `catalogo_piezas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `nf_desglose`
--
ALTER TABLE `nf_desglose`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_detalle`
--
ALTER TABLE `ordenes_detalle`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proyectos_nf`
--
ALTER TABLE `proyectos_nf`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `stock_inventario`
--
ALTER TABLE `stock_inventario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `trazabilidad_estados`
--
ALTER TABLE `trazabilidad_estados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `catalogo_piezas`
--
ALTER TABLE `catalogo_piezas`
  ADD CONSTRAINT `fk_pieza_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `nf_desglose`
--
ALTER TABLE `nf_desglose`
  ADD CONSTRAINT `fk_desglose_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_desglose_nf` FOREIGN KEY (`nf_id`) REFERENCES `proyectos_nf` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_desglose_pieza` FOREIGN KEY (`pieza_catalogo_id`) REFERENCES `catalogo_piezas` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `fk_orden_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_orden_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `ordenes_detalle`
--
ALTER TABLE `ordenes_detalle`
  ADD CONSTRAINT `fk_detalle_desglose` FOREIGN KEY (`nf_desglose_id`) REFERENCES `nf_desglose` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_detalle_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proyectos_nf`
--
ALTER TABLE `proyectos_nf`
  ADD CONSTRAINT `fk_nf_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_nf_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `stock_inventario`
--
ALTER TABLE `stock_inventario`
  ADD CONSTRAINT `fk_stock_pieza` FOREIGN KEY (`pieza_catalogo_id`) REFERENCES `catalogo_piezas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trazabilidad_estados`
--
ALTER TABLE `trazabilidad_estados`
  ADD CONSTRAINT `fk_trazabilidad_est_ant` FOREIGN KEY (`estado_anterior_id`) REFERENCES `estados` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_trazabilidad_est_nue` FOREIGN KEY (`estado_nuevo_id`) REFERENCES `estados` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_trazabilidad_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
