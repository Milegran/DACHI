# Fase 6 - Seguimiento de pedidos

Fecha: 2026-07-12
Proyecto: DACHI
Modulo: seguimiento del consumidor y operacion logistica

## Objetivo

Mostrar al consumidor solo sus pedidos y permitir que el rol logistico tome una entrega, la marque en camino y confirme su finalizacion.

## Componentes

- `SeguimientoRepository`: consulta pedidos por propietario, administra la direccion y ejecuta asignacion/confirmacion dentro de transacciones.
- `SeguimientoService`: formatea estados, valida datos y traduce errores de dominio.
- `SeguimientoValidator`: valida provincia, distrito, corregimiento y detalle de entrega.
- `SistemaDachiFacade`: expone listado, direccion, asignacion y confirmacion.
- `public/seguimiento.php`: vista del consumidor con historial, etapas y direccion de entrega.
- `panel.php`: las acciones logisticas existentes ahora pasan por el Facade y muestran errores del servidor.

## Estados

- `0 - pendiente`: pedido confirmado, disponible para logistica.
- `1 - en camino`: un repartidor logistico tomo el pedido.
- `2 - entregado`: el mismo repartidor confirmo la entrega.

## Integridad y seguridad

- Cada pedido admite una sola fila en `entregas`.
- `entregas.id_repartidor` referencia a `usuarios` y `entregas.id_direccion` referencia a `direccion`.
- No se puede asignar un pedido sin direccion del consumidor.
- No se puede tomar un pedido que ya no esta pendiente.
- Solo el repartidor asignado puede confirmar la entrega.
- Una cuenta consumidora solo consulta pedidos cuyo `id_consumer` coincide con su sesion.
- El panel consumidor ya no incluye usuarios, productos administrativos ni pedidos ajenos en variables JavaScript.

## Prueba integral ejecutada

1. Se crearon cuentas temporales consumidora y logistica.
2. La consumidora guardo una direccion y confirmo un pedido.
3. Seguimiento devolvio exactamente un pedido propio con estado pendiente.
4. Logistica tomo el pedido y seguimiento mostro estado en camino y nombre del repartidor.
5. La consumidora intento confirmar la entrega y fue rechazada por rol.
6. La cuenta logistica asignada confirmo la entrega.
7. Seguimiento mostro estado entregado.
8. Se eliminaron pedido, entrega, direccion y cuentas temporales; el inventario se restauro.

Resultado: aprobado.

## Criterios de aceptacion

- Consumidor ve solo sus pedidos: aprobado.
- Se muestran pendiente, en camino y entregado: aprobado.
- Logistica ve y puede tomar pedidos pendientes: aprobado.
- Solo logistica asignada confirma la entrega: aprobado.
- Los cambios se reflejan al consultar seguimiento: aprobado.
- Se conserva historial de pedidos entregados: aprobado.
