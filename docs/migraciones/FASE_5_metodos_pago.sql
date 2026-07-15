-- Fase 5 - Metodos de pago disponibles en el carrito.
-- Script idempotente. Yappy se conserva solo si tiene pedidos historicos.

INSERT INTO metodos (nombre)
SELECT 'Efectivo contra entrega'
WHERE NOT EXISTS (SELECT 1 FROM metodos WHERE nombre = 'Efectivo contra entrega');

UPDATE metodos
SET nombre = 'Transferencia'
WHERE nombre IN ('Transferencia bancaria', 'Transferencia con tarjeta');

INSERT INTO metodos (nombre)
SELECT 'Transferencia'
WHERE NOT EXISTS (SELECT 1 FROM metodos WHERE nombre = 'Transferencia');

DELETE m
FROM metodos m
LEFT JOIN pedidos p ON p.metodo_pago = m.id
WHERE m.nombre = 'Yappy' AND p.id IS NULL;
