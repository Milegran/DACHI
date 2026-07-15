-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 14-07-2026 a las 05:00:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dachitos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificacion`
--

CREATE TABLE `calificacion` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_consumer` int(11) NOT NULL,
  `calificacion` int(11) NOT NULL,
  `comentario` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calificacion`
--

INSERT INTO `calificacion` (`id`, `id_pedido`, `id_producto`, `id_consumer`, `calificacion`, `comentario`) VALUES
(1, 4, 1, 2, 5, 'Fresco, firme y con muy buen sabor.'),
(2, 5, 2, 3, 4, 'Las hojas llegaron crujientes y bien empacadas.'),
(3, 4, 3, 4, 5, 'Tiene un sabor natural y una textura agradable.'),
(4, 5, 4, 5, 4, 'Aroma intenso y tostado equilibrado.'),
(5, 4, 5, 6, 5, 'Dulce, jugosa y en buen estado.'),
(6, 5, 6, 2, 5, 'Excelente aroma floral; vale el precio.'),
(7, 4, 7, 3, 4, 'Buen balance entre cacao y dulzor.'),
(8, 5, 8, 4, 4, 'Llego maduro y listo para consumir.'),
(9, 4, 9, 5, 5, 'Cremoso y de excelente tamaño.'),
(10, 5, 10, 6, 5, 'Pequeños, dulces y muy frescos.'),
(11, 4, 11, 2, 4, 'Color bonito y sabor suave.'),
(12, 5, 12, 3, 4, 'Buena textura para ensaladas.'),
(13, 4, 13, 4, 5, 'Mezcla fresca y practica.'),
(14, 5, 14, 5, 5, 'Sabor profundo, ideal para desayunos.'),
(15, 4, 15, 6, 4, 'La canela complementa bien la miel.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `provincia` varchar(50) NOT NULL,
  `distrito` varchar(20) NOT NULL,
  `corregimiento` varchar(50) NOT NULL,
  `detalle` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id`, `id_usuario`, `provincia`, `distrito`, `corregimiento`, `detalle`) VALUES
(3, 2, 'Panama Oeste', 'Nuevo Arraiján', 'Juan Demostenes Arosemena', 'rghuebugvhrhvbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `id_pedidos` int(11) NOT NULL,
  `id_repartidor` int(11) NOT NULL,
  `id_direccion` int(11) NOT NULL,
  `tarifa_envio` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes`
--

CREATE TABLE `imagenes` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `info_pedidos`
--

CREATE TABLE `info_pedidos` (
  `id` int(11) NOT NULL,
  `id_pedidos` int(11) NOT NULL,
  `id_productos` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `info_pedidos`
--

INSERT INTO `info_pedidos` (`id`, `id_pedidos`, `id_productos`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(2, 4, 2, 1, 0.95, 0.95),
(3, 5, 1, 1, 1.25, 1.25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos`
--

CREATE TABLE `metodos` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos`
--

INSERT INTO `metodos` (`id`, `nombre`) VALUES
(2, 'Efectivo contra entrega'),
(3, 'Yappy'),
(4, 'Transferencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_consumer` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `total_compra` decimal(10,2) NOT NULL,
  `metodo_pago` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_consumer`, `fecha`, `total_compra`, `metodo_pago`, `estado`) VALUES
(4, 2, '2026-07-12', 0.95, 4, 0),
(5, 2, '2026-07-12', 1.25, 3, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `nom_productor` varchar(100) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `nom_productor`, `cantidad`, `estado`) VALUES
(1, 'Tomate perita', 'Tomate fresco de productor local, ideal para ensaladas y salsas.', 1.25, 'img/products/tomate.jpg', 'Finca El Roble', 34, 1),
(2, 'Lechuga criolla', 'Lechuga cultivada localmente con hojas frescas y crocantes.', 0.95, 'img/products/lechuga.jpg', 'Huerto Santa Maria', 21, 1),
(3, 'Miel artesanal', 'Miel natural envasada por productor panameno.', 4.50, 'img/products/miel.jpg', 'Apiario Valle Verde', 0, 1),
(4, 'Cafe especial', 'Cafe de altura con tostado medio y notas de cacao.', 7.75, 'img/products/cafe.jpg', 'Finca El Roble', 12, 1),
(5, 'Piña perolera', 'Piña dulce cosechada a mano, de pulpa jugosa e ideal para jugos y postres.', 2.10, 'img/products/pina.jpg', 'Finca Los Robles', 18, 1),
(6, 'Café Geisha', 'Café de especialidad con aroma floral, acidez delicada y tostado medio.', 18.50, 'img/products/cafe.jpg', 'Café Boquete Dorado', 9, 1),
(7, 'Chocolate artesanal', 'Chocolate oscuro elaborado con cacao panameño y un proceso artesanal de pequeña escala.', 6.75, 'img/products/cacao.jpg', 'Cacao Darién Orgánico', 15, 1),
(8, 'Plátano amarillo', 'Plátano nacional madurado naturalmente, listo para consumir o preparar.', 1.80, 'img/products/platano.jpg', 'Hacienda El Valle', 40, 1),
(9, 'Aguacate criollo', 'Aguacate de textura cremosa y sabor suave, cultivado en clima de montana.', 2.50, 'img/products/aguacate.jpg', 'Finca Cerro Azul', 14, 1),
(10, 'Tomate cherry', 'Tomates pequeños, dulces y firmes para ensaladas o aperitivos.', 2.30, 'img/products/tomate.jpg', 'Finca El Roble', 28, 1),
(11, 'Tomate amarillo', 'Variedad de tomate de sabor suave, color brillante y pulpa jugosa.', 2.60, 'img/products/tomate.jpg', 'Finca Cerro Azul', 16, 1),
(12, 'Lechuga romana', 'Lechuga de hojas alargadas y crujientes, ideal para ensaladas frescas.', 1.15, 'img/products/lechuga.jpg', 'Huerto Santa Maria', 25, 1),
(13, 'Mix de hojas verdes', 'Seleccion de hojas tiernas lavadas y listas para preparar.', 1.80, 'img/products/lechuga.jpg', 'Huerto Santa Maria', 12, 1),
(14, 'Miel de montaña', 'Miel de flores silvestres con aroma profundo y dulzor balanceado.', 5.25, 'img/products/miel.jpg', 'Apiario Valle Verde', 10, 1),
(15, 'Miel con canela', 'Miel artesanal infusionada con canela natural.', 5.75, 'img/products/miel.jpg', 'Apiario Valle Verde', 8, 1),
(16, 'Piña golden', 'Piña dorada de pulpa dulce, baja acidez y cosecha local.', 2.85, 'img/products/pina.jpg', 'Finca Los Robles', 20, 1),
(17, 'Piña organica', 'Piña cultivada sin agroquimicos sinteticos y seleccionada a mano.', 3.10, 'img/products/pina.jpg', 'Finca Los Robles', 11, 1),
(18, 'Cafe Caturra', 'Cafe de altura con notas citricas y tostado medio.', 12.50, 'img/products/cafe.jpg', 'Cafe Boquete Dorado', 14, 1),
(19, 'Cafe Pacamara', 'Cafe de grano grande, aroma floral y cuerpo sedoso.', 16.90, 'img/products/cafe.jpg', 'Cafe Boquete Dorado', 7, 1),
(20, 'Chocolate 70%', 'Chocolate oscuro con setenta por ciento de cacao panameno.', 7.20, 'img/products/cacao.jpg', 'Cacao Darien Organico', 13, 1),
(21, 'Chocolate con cafe', 'Barra artesanal de cacao con cafe de altura molido.', 7.95, 'img/products/cacao.jpg', 'Cacao Darien Organico', 9, 1),
(22, 'Cacao tostado', 'Granos de cacao tostados para reposteria, bebidas o consumo directo.', 5.90, 'img/products/cacao.jpg', 'Cacao Darien Organico', 17, 1),
(23, 'Platano verde', 'Platano firme para cocinar, cosechado antes de su maduracion.', 1.35, 'img/products/platano.jpg', 'Hacienda El Valle', 36, 1),
(24, 'Banano bocadillo', 'Banano pequeño de sabor dulce y textura cremosa.', 1.60, 'img/products/platano.jpg', 'Hacienda El Valle', 30, 1),
(25, 'Aguacate Hass', 'Aguacate de pulpa cremosa, cascara rugosa y sabor intenso.', 2.90, 'img/products/aguacate.jpg', 'Finca Cerro Azul', 15, 1),
(26, 'Aguacate mantequilla', 'Variedad grande y suave, ideal para ensaladas y cremas.', 3.20, 'img/products/aguacate.jpg', 'Finca Cerro Azul', 10, 1),
(27, 'Tomate reliquia', 'Tomate de variedad tradicional con sabor intenso y pulpa carnosa.', 2.75, 'img/products/tomate.jpg', 'Finca El Roble', 18, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion_contrasena`
--

CREATE TABLE `recuperacion_contrasena` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recuperacion_contrasena`
--

INSERT INTO `recuperacion_contrasena` (`id`, `id_usuario`, `token_hash`, `expira_en`, `usado`, `creado_en`) VALUES
(1, 3, 'b9f92d7f6e35a84fd28ee2c257cc92f56e68006928c5c0fb6416bdace05b37f1', '2026-07-12 04:12:11', 1, '2026-07-11 20:57:11'),
(2, 3, 'ce3750eadc4dcefad14d60665f8ef1835c43c072a91a9b87496913c6a90feac9', '2026-07-12 04:12:13', 1, '2026-07-11 20:57:13'),
(3, 3, 'c5cf07991c8efe753aa180dd8f54978572ab60dae1e09c7ffdcc7a5b9e7ef2f1', '2026-07-12 04:12:15', 1, '2026-07-11 20:57:15'),
(4, 3, '076755978ce1b5e75d231f914b7d4df51597e3a46d1147005f33cc3a7370f7ba', '2026-07-12 04:12:17', 0, '2026-07-11 20:57:17'),
(5, 6, 'ce09e03783ffc20968b281112469572b41dd4b524a1ae1f7d389eeed27866e11', '2026-07-12 04:13:56', 1, '2026-07-11 20:58:56'),
(6, 6, '464f178683350badd31988abdf06b5f23d5d2dbbc1be45fc4e52efec71069c99', '2026-07-12 04:15:33', 1, '2026-07-11 21:00:33'),
(7, 6, 'b58fac64c5c2c9d694b9eb3c64676849dee44002580b76946564cf44581b0cab', '2026-07-12 04:15:35', 1, '2026-07-11 21:00:35'),
(8, 6, '8964b24d571e49c02071af5f589e7fed9e27d337dfe529b1cf8ad08510b3cb47', '2026-07-12 04:15:43', 1, '2026-07-11 21:00:43'),
(9, 5, '09f36a8feb8c83831649d2bd01130e8df3012c8c80dc57ede4ebe9eec4192931', '2026-07-12 22:51:59', 0, '2026-07-12 15:36:59'),
(10, 7, 'eee869f366cfde05d973bb0e8a6c862e7bc559a6c46abc88da79c3f4dbf3d950', '2026-07-12 22:54:32', 1, '2026-07-12 15:39:32'),
(11, 7, '3976d2d8094d451a090e1197c7c2b973b749863116c342e3103b1840329e0c0c', '2026-07-12 22:55:18', 1, '2026-07-12 15:40:18'),
(12, 6, '259d6a1804fe8ec1d71da04b34cb9a824421d69e9a564d50bb6f98b798518933', '2026-07-13 00:55:09', 1, '2026-07-12 17:40:09'),
(13, 6, 'f76beb8ba62577fb164f7ebe6389b18fa43ccd21b9f0b0b3a5dc42082df9d5a1', '2026-07-13 00:55:11', 0, '2026-07-12 17:40:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `nom_rol` text NOT NULL,
  `permisos` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nom_rol`, `permisos`) VALUES
(1, 'consumidor', 'consumidor'),
(2, 'productor', 'productor'),
(3, 'logistico', 'logistico'),
(4, 'administrador', 'administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `apellido` text NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contraseña` varchar(100) NOT NULL,
  `telefono` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `id_rol`, `nombre`, `apellido`, `correo`, `contraseña`, `telefono`) VALUES
(1, 3, 'Aldhair', 'Logistica', 'Aldhair@gmail.com', '$2b$10$ichXCcvd/xrJL6QUmkU2juOdpL5WddOiY5sAvTSCKb.Vmh4Iw2nrm', '60000000'),
(2, 1, 'juan', 'florez', 'codex.prueba.fix@example.com', '$2y$10$dc2ljGsvL1r3Qb5kP.ayrelE6fi99Zq.e0gc3M4N1emBjtPgBSThO', ''),
(3, 1, 'Fase0', 'Consumidor', 'fase0.consumer.20260711193251@example.com', '$2y$10$n6..unrsz3NHq0gr9J5b3OWeyKdIo/pesKhLEiV5hCaDWLPxrvHOm', ''),
(4, 1, 'Fase1', 'Facade', 'fase1.facade.20260711195009@example.com', '$2y$10$GaxlWqc2Gn0aANhiQ6ebvu1JJ0B3wdPnyGpnbEOIiahQKCHLM9RwS', ''),
(5, 1, 'Fase', 'Validacion', 'fase2.validacion.20260711195523@example.com', '$2y$10$ZiEW/xMi8FiYPBE.eAtioeukQC./gH7uZ0ETcc/WermCfHhqCJSva', ''),
(6, 1, 'juan', 'florez', 'juan.florez2@utp.ac.pa', '$2y$10$Brgir/DOYylG6C8oXPHtiOD9zWeoxg75JsZWz11l.T4wqzK4vBt/G', ''),
(7, 1, 'Revision', 'Fase', 'revision.fase4.20260712153923@example.com', '$2y$10$m8haJBuHtreoxxioT/bUTOXz3Z4dlxn07DVzgQzPc/KGPfUF468LG', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`,`id_producto`,`id_consumer`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `idx_calificacion_consumidor` (`id_consumer`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_entregas_pedido` (`id_pedidos`),
  ADD KEY `idx_entregas_repartidor` (`id_repartidor`),
  ADD KEY `idx_entregas_direccion` (`id_direccion`);

--
-- Indices de la tabla `imagenes`
--
ALTER TABLE `imagenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `info_pedidos`
--
ALTER TABLE `info_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_pedidos` (`id_pedidos`,`id_productos`),
  ADD KEY `id_productos` (`id_productos`);

--
-- Indices de la tabla `metodos`
--
ALTER TABLE `metodos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pedidos_consumidor` (`id_consumer`),
  ADD KEY `idx_pedidos_metodo_pago` (`metodo_pago`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recuperacion_contrasena`
--
ALTER TABLE `recuperacion_contrasena`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recuperacion_usuario` (`id_usuario`),
  ADD KEY `idx_recuperacion_token` (`token_hash`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo`),
  ADD KEY `idx_usuarios_id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `imagenes`
--
ALTER TABLE `imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `info_pedidos`
--
ALTER TABLE `info_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `metodos`
--
ALTER TABLE `metodos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `recuperacion_contrasena`
--
ALTER TABLE `recuperacion_contrasena`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD CONSTRAINT `calificacion_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `calificacion_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `calificacion_ibfk_3` FOREIGN KEY (`id_consumer`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `direccion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`id_pedidos`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entregas_ibfk_2` FOREIGN KEY (`id_repartidor`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `entregas_ibfk_3` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `imagenes`
--
ALTER TABLE `imagenes`
  ADD CONSTRAINT `imagenes_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `info_pedidos`
--
ALTER TABLE `info_pedidos`
  ADD CONSTRAINT `info_pedidos_ibfk_1` FOREIGN KEY (`id_productos`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `info_pedidos_ibfk_2` FOREIGN KEY (`id_pedidos`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_consumer`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`metodo_pago`) REFERENCES `metodos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recuperacion_contrasena`
--
ALTER TABLE `recuperacion_contrasena`
  ADD CONSTRAINT `recuperacion_contrasena_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
