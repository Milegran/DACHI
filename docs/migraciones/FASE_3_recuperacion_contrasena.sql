-- ============================================================
-- Migracion Fase 3 - Recuperar contrasena
-- Proyecto: DACHI
-- Fecha: 2026-07-11
-- Regla 4.1 del plan: script reversible antes de modificar estructura.
-- ============================================================

-- --------------------------------------------------------
-- FORWARD: crear tabla de tokens de recuperacion
-- --------------------------------------------------------

CREATE TABLE `recuperacion_contrasena` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recuperacion_usuario` (`id_usuario`),
  KEY `idx_recuperacion_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `recuperacion_contrasena`
  ADD CONSTRAINT `recuperacion_contrasena_ibfk_1`
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Notas de diseno:
-- - Se guarda el hash SHA-256 del codigo de 6 digitos, nunca el codigo en texto plano,
--   igual que la contrasena se guarda con password_hash y nunca en texto plano.
-- - `usado` permite forzar un solo uso por token (criterio de aceptacion de la Fase 3).
-- - `expira_en` permite invalidar el token pasado el tiempo limite (15 minutos, definido
--   en RecuperacionService::MINUTOS_EXPIRACION).
-- - ON DELETE CASCADE: si se elimina un usuario, sus tokens de recuperacion se eliminan con el.

-- --------------------------------------------------------
-- ROLLBACK (ejecutar solo si se necesita revertir esta migracion)
-- --------------------------------------------------------
-- ALTER TABLE `recuperacion_contrasena` DROP FOREIGN KEY `recuperacion_contrasena_ibfk_1`;
-- DROP TABLE `recuperacion_contrasena`;
