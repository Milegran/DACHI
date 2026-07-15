-- Fase 4 - Datos semilla para validar catalogo de productos.
-- Script idempotente: no duplica productos si ya existen por nombre.

INSERT INTO productos (nombre, descripcion, precio, imagen, nom_productor, cantidad, estado)
SELECT 'Tomate perita', 'Tomate fresco de productor local, ideal para ensaladas y salsas.', 1.25, NULL, 'Finca El Roble', 35, 1
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Tomate perita');

INSERT INTO productos (nombre, descripcion, precio, imagen, nom_productor, cantidad, estado)
SELECT 'Lechuga criolla', 'Lechuga cultivada localmente con hojas frescas y crocantes.', 0.95, NULL, 'Huerto Santa Maria', 22, 1
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Lechuga criolla');

INSERT INTO productos (nombre, descripcion, precio, imagen, nom_productor, cantidad, estado)
SELECT 'Miel artesanal', 'Miel natural envasada por productor panameno.', 4.50, NULL, 'Apiario Valle Verde', 0, 1
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Miel artesanal');

INSERT INTO productos (nombre, descripcion, precio, imagen, nom_productor, cantidad, estado)
SELECT 'Cafe especial', 'Cafe de altura con tostado medio, desactivado para prueba de visibilidad.', 7.75, NULL, 'Finca El Roble', 12, 0
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Cafe especial');

