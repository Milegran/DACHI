-- ============================================================
-- Migracion 002 - Modulo de Administracion (Fase 1)
-- Proyecto: DACHI
-- Fecha: 2026-07-14
-- Regla 4.1 del plan: script reversible antes de modificar estructura.
-- ============================================================

-- ============================================================
-- 1. USUARIOS
-- ============================================================
ALTER TABLE `usuarios`
  ADD COLUMN `foto_perfil` varchar(255) DEFAULT NULL AFTER `telefono`,
  ADD COLUMN `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP AFTER `foto_perfil`,
  ADD COLUMN `ultimo_acceso` datetime DEFAULT NULL AFTER `fecha_registro`,
  ADD COLUMN `estado` enum('activo','inactivo') DEFAULT 'activo' AFTER `ultimo_acceso`,
  ADD COLUMN `ubicacion_finca` text DEFAULT NULL AFTER `estado`,
  ADD COLUMN `datos_bancarios` text DEFAULT NULL AFTER `ubicacion_finca`,
  ADD COLUMN `practicas_produccion` text DEFAULT NULL AFTER `datos_bancarios`;

-- Migrar datos existentes: marcar todos los usuarios actuales como activos
UPDATE `usuarios` SET `fecha_registro` = CURRENT_TIMESTAMP WHERE `fecha_registro` IS NULL;

-- ============================================================
-- 2. CATEGORIAS
-- ============================================================
ALTER TABLE `categorias`
  ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `descripcion`;

INSERT INTO `categorias` (`nombre`, `descripcion`) VALUES
  ('Verduras y Hortalizas', 'Productos frescos del campo'),
  ('Frutas', 'Frutas frescas de temporada'),
  ('Café y Cacao', 'Café de altura y cacao artesanal'),
  ('Miel y Derivados', 'Miel natural y productos apícolas'),
  ('Lácteos', 'Quesos, yogures y derivados lácteos'),
  ('Carnes y Embutidos', 'Carnes frescas y procesadas'),
  ('Granos y Semillas', 'Arroz, frijoles, maíz y semillas'),
  ('Tubérculos', 'Papas, ñames, yucas y otros'),
  ('Hierbas y Especias', 'Hierbas aromáticas y especias naturales');

-- ============================================================
-- 3. PRODUCTOS
-- ============================================================
ALTER TABLE `productos`
  ADD COLUMN `id_usuario` int(11) DEFAULT NULL AFTER `id`,
  ADD COLUMN `id_categoria` int(11) DEFAULT NULL AFTER `id_usuario`,
  ADD COLUMN `estado_aprobacion` enum('pendiente','aprobado','rechazado','inactivo') DEFAULT 'pendiente' AFTER `estado`,
  ADD COLUMN `motivo_rechazo` text DEFAULT NULL AFTER `estado_aprobacion`,
  ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `motivo_rechazo`,
  ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD KEY `idx_productos_usuario` (`id_usuario`),
  ADD KEY `idx_productos_categoria` (`id_categoria`);

-- Migrar datos existentes de productos:
-- estado = 1 (activo) → estado_aprobacion = 'aprobado'
-- estado = 0 (inactivo) → estado_aprobacion = 'inactivo'
UPDATE `productos` SET `estado_aprobacion` = 'aprobado' WHERE `estado` = 1;
UPDATE `productos` SET `estado_aprobacion` = 'inactivo' WHERE `estado` = 0;
UPDATE `productos` SET `created_at` = CURRENT_TIMESTAMP WHERE `created_at` IS NULL;

ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `productos_ibfk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- 4. PEDIDOS
-- ============================================================
ALTER TABLE `pedidos`
  ADD COLUMN `estado_detallado` enum('pendiente','en_preparacion','en_transito','entregado','cancelado') DEFAULT 'pendiente' AFTER `estado`,
  ADD COLUMN `notas` text DEFAULT NULL AFTER `estado_detallado`,
  ADD COLUMN `fecha_entrega` datetime DEFAULT NULL AFTER `notas`;

-- Sincronizar estado_detallado desde estado existente segun convencion:
-- estado = 0 → pendiente, estado = 1 → en_transito, estado = 2 → entregado
UPDATE `pedidos` SET `estado_detallado` = 'pendiente' WHERE `estado` = 0;
UPDATE `pedidos` SET `estado_detallado` = 'entregado' WHERE `estado` = 2;

-- ============================================================
-- 5. ENTREGAS
-- ============================================================
ALTER TABLE `entregas`
  ADD COLUMN `evidencia` varchar(255) DEFAULT NULL AFTER `fecha`,
  ADD COLUMN `notas` text DEFAULT NULL AFTER `evidencia`;

-- ============================================================
-- 6. CALIFICACION
-- ============================================================
ALTER TABLE `calificacion`
  ADD COLUMN `tipo` enum('producto','productor','logistica') DEFAULT 'producto' AFTER `id_consumer`,
  ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `comentario`,
  ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `updated_at`;

-- Migrar datos existentes: todas las calificaciones actuales son de tipo 'producto'
UPDATE `calificacion` SET `tipo` = 'producto' WHERE `tipo` IS NULL;

-- ============================================================
-- 7. DIRECCION (permitir multiples direcciones por usuario)
-- ============================================================
ALTER TABLE `direccion`
  DROP INDEX `id_usuario`,
  ADD KEY `idx_direccion_usuario` (`id_usuario`),
  ADD COLUMN `nombre_direccion` varchar(50) DEFAULT NULL AFTER `id_usuario`,
  ADD COLUMN `latitud` decimal(10,8) DEFAULT NULL AFTER `detalle`,
  ADD COLUMN `longitud` decimal(11,8) DEFAULT NULL AFTER `latitud`,
  ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `longitud`,
  ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- ============================================================
-- ROLLBACK (ejecutar solo si se necesita revertir esta migracion)
-- ============================================================
/*
-- 1. USUARIOS
ALTER TABLE `usuarios`
  DROP COLUMN `practicas_produccion`,
  DROP COLUMN `datos_bancarios`,
  DROP COLUMN `ubicacion_finca`,
  DROP COLUMN `estado`,
  DROP COLUMN `ultimo_acceso`,
  DROP COLUMN `fecha_registro`,
  DROP COLUMN `foto_perfil`;

-- 2. CATEGORIAS
DELETE FROM `categorias`;
ALTER TABLE `categorias` DROP COLUMN `created_at`;

-- 3. PRODUCTOS
ALTER TABLE `productos`
  DROP FOREIGN KEY `productos_ibfk_categoria`,
  DROP FOREIGN KEY `productos_ibfk_usuario`;
ALTER TABLE `productos`
  DROP COLUMN `updated_at`,
  DROP COLUMN `created_at`,
  DROP COLUMN `motivo_rechazo`,
  DROP COLUMN `estado_aprobacion`,
  DROP COLUMN `id_categoria`,
  DROP COLUMN `id_usuario`;

-- 4. PEDIDOS
ALTER TABLE `pedidos`
  DROP COLUMN `fecha_entrega`,
  DROP COLUMN `notas`,
  DROP COLUMN `estado_detallado`;

-- 5. ENTREGAS
ALTER TABLE `entregas`
  DROP COLUMN `notas`,
  DROP COLUMN `evidencia`;

-- 6. CALIFICACION
ALTER TABLE `calificacion`
  DROP COLUMN `updated_at`,
  DROP COLUMN `tipo`;

-- 7. DIRECCION
ALTER TABLE `direccion`
  DROP COLUMN `updated_at`,
  DROP COLUMN `created_at`,
  DROP COLUMN `longitud`,
  DROP COLUMN `latitud`,
  DROP COLUMN `nombre_direccion`,
  DROP INDEX `idx_direccion_usuario`,
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);
*/
