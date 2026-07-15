-- ============================================================
-- Migracion 003 - Calificaciones y Reputacion
-- Proyecto: DACHI
-- Fecha: 2026-07-14
-- ============================================================

ALTER TABLE `calificacion`
  ADD COLUMN `estado` enum('visible','oculto','investigacion') DEFAULT 'visible' AFTER `tipo`,
  ADD COLUMN `respuesta_admin` text DEFAULT NULL AFTER `comentario`;

CREATE TABLE IF NOT EXISTS `calificacion_reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_calificacion` int(11) NOT NULL,
  `id_usuarioreportado` int(11) NOT NULL,
  `id_usuarioreporta` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `estado` enum('pendiente','investigando','resuelto','cerrado') DEFAULT 'pendiente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reporte_calificacion` (`id_calificacion`),
  KEY `idx_reporte_reportado` (`id_usuarioreportado`),
  KEY `idx_reporte_reporta` (`id_usuarioreporta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `calificacion_reportes`
  ADD CONSTRAINT `fk_reporte_calificacion` FOREIGN KEY (`id_calificacion`) REFERENCES `calificacion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reporte_usuarioreportado` FOREIGN KEY (`id_usuarioreportado`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reporte_usuarioreporta` FOREIGN KEY (`id_usuarioreporta`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ============================================================
-- ROLLBACK
-- ============================================================
/*
ALTER TABLE `calificacion_reportes` DROP FOREIGN KEY `fk_reporte_usuarioreporta`;
ALTER TABLE `calificacion_reportes` DROP FOREIGN KEY `fk_reporte_usuarioreportado`;
ALTER TABLE `calificacion_reportes` DROP FOREIGN KEY `fk_reporte_calificacion`;
DROP TABLE IF EXISTS `calificacion_reportes`;
ALTER TABLE `calificacion` DROP COLUMN `respuesta_admin`;
ALTER TABLE `calificacion` DROP COLUMN `estado`;
*/
