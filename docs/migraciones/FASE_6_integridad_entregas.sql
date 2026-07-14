-- Fase 6 - Integridad de seguimiento y entregas.
-- Ejecutar una sola vez sobre la base dachitos.

-- Un pedido solo puede tener una entrega asociada.
ALTER TABLE entregas
  ADD UNIQUE KEY uq_entregas_pedido (id_pedidos);

ALTER TABLE entregas
  DROP INDEX id_pedidos;

-- Relaciones necesarias para la asignacion logistica.
ALTER TABLE entregas
  ADD KEY idx_entregas_repartidor (id_repartidor),
  ADD KEY idx_entregas_direccion (id_direccion),
  ADD CONSTRAINT entregas_ibfk_2 FOREIGN KEY (id_repartidor) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT entregas_ibfk_3 FOREIGN KEY (id_direccion) REFERENCES direccion (id)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ROLLBACK:
-- ALTER TABLE entregas DROP FOREIGN KEY entregas_ibfk_2, DROP FOREIGN KEY entregas_ibfk_3;
-- ALTER TABLE entregas DROP INDEX idx_entregas_repartidor, DROP INDEX idx_entregas_direccion;
-- ALTER TABLE entregas DROP INDEX uq_entregas_pedido;
-- ALTER TABLE entregas ADD UNIQUE KEY id_pedidos (id_pedidos, id_repartidor, id_direccion);
