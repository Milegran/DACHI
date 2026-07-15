-- ============================================================
-- Migracion Fase 4 - Ajustes de integridad para pedidos e imagenes
-- Proyecto: DACHI
-- Fecha: 2026-07-12
-- Ejecutar una sola vez sobre la base dachitos.
-- ============================================================

-- Los identificadores de las entidades se generan desde MariaDB.
ALTER TABLE `calificacion` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `direccion` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `entregas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `imagenes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `info_pedidos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `metodos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pedidos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `rol` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Un consumidor puede realizar varios pedidos y un metodo de pago puede
-- utilizarse en muchos pedidos. Se conservan indices normales para las FK.
ALTER TABLE `pedidos`
  DROP INDEX `id_consumer`,
  DROP INDEX `metodo_pago`,
  ADD KEY `idx_pedidos_consumidor` (`id_consumer`),
  ADD KEY `idx_pedidos_metodo_pago` (`metodo_pago`);

-- La puntuacion (por ejemplo, 5 estrellas) puede repetirse entre usuarios.
-- La unicidad correcta sigue siendo pedido + producto + consumidor.
ALTER TABLE `calificacion`
  DROP INDEX `calificacion`,
  ADD KEY `idx_calificacion_consumidor` (`id_consumer`),
  ADD CONSTRAINT `calificacion_ibfk_3`
    FOREIGN KEY (`id_consumer`) REFERENCES `usuarios` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- El correo es el identificador de inicio de sesion y debe ser unico tambien
-- a nivel de base de datos, no solo por validacion de la aplicacion.
ALTER TABLE `usuarios`
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo`);

-- Una ruta de archivo no es un numero. La tabla queda lista para imagenes
-- reales en una fase posterior.
ALTER TABLE `imagenes`
  MODIFY `ruta` varchar(255) NOT NULL;

-- ROLLBACK (ejecutar solo si se necesita revertir la migracion):
-- ALTER TABLE `imagenes` MODIFY `ruta` bigint(20) NOT NULL;
-- ALTER TABLE `usuarios` DROP INDEX `uq_usuarios_correo`;
-- ALTER TABLE `calificacion` DROP FOREIGN KEY `calificacion_ibfk_3`,
--   DROP INDEX `idx_calificacion_consumidor`, ADD UNIQUE KEY `calificacion` (`calificacion`);
-- ALTER TABLE `pedidos` DROP INDEX `idx_pedidos_consumidor`, DROP INDEX `idx_pedidos_metodo_pago`,
--   ADD UNIQUE KEY `id_consumer` (`id_consumer`), ADD UNIQUE KEY `metodo_pago` (`metodo_pago`);
