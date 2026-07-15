# Fase 5 - Carrito de compras y proceso de pago

Fecha: 2026-07-12
Proyecto: DACHI
Modulo: carrito, confirmacion de compra y registro de pedido

## Objetivo

Permitir que una cuenta consumidora agregue productos activos al carrito, cambie cantidades, seleccione un metodo de pago y confirme un pedido con total e inventario consistentes.

## Arquitectura

- `CarritoService`: administra cantidades en la sesion y construye el resumen usando precios y stock actuales de la base.
- `PedidoValidator`: valida producto, cantidad, usuario, metodo de pago, estructura del carrito y datos de tarjeta para transferencia.
- `PedidoRepository`: registra pedido, detalle y descuento de inventario dentro de una transaccion MariaDB.
- `PedidoService`: coordina validaciones, metodos de pago y confirmacion de la compra.
- `SistemaDachiFacade`: expone las operaciones de carrito y pedido a las paginas publicas.
- `public/carrito.php`: endpoint y vista del carrito; solo una cuenta consumidora puede modificar o confirmar compras.

## Reglas implementadas

- El precio y el total se calculan en el servidor con los datos actuales de `productos`.
- No se permite agregar un producto inactivo, inexistente o sin stock.
- Una cantidad debe estar entre 1 y 99 y no puede superar el inventario disponible.
- El metodo de pago debe existir en la tabla `metodos`.
- El carrito ofrece solamente `Efectivo contra entrega` y `Transferencia` para pedidos nuevos.
- Transferencia exige titular, numero de tarjeta valido por algoritmo de Luhn, vencimiento vigente y CVV de 3 o 4 digitos.
- El numero, vencimiento y CVV se validan durante la solicitud y no se guardan en la base de datos.
- La compra bloquea las filas de productos con `FOR UPDATE` antes de verificar y descontar stock.
- `pedidos`, `info_pedidos` y el descuento de inventario se confirman juntos; ante un error se ejecuta `ROLLBACK`.
- El carrito se vacia solamente despues de confirmar correctamente el pedido.

## Prueba automatizada ejecutada

1. Se creo una cuenta consumidora temporal.
2. Se agregaron 2 unidades de `Tomate perita` al carrito.
3. Se intento actualizar la cantidad a 99 y el servidor rechazo la operacion por stock insuficiente.
4. Se confirmo la compra con un metodo registrado.
5. Se verifico un pedido por `$2.50`, detalle de 2 unidades y descuento de inventario de 35 a 33.
6. Se verifico que el carrito quedara vacio despues de la compra.
7. Se elimino el usuario y pedido temporales y se restauro el inventario a 35.

Resultado: aprobado.

## Pruebas adicionales de pago

- Transferencia sin datos de tarjeta: rechazada.
- Transferencia con numero de tarjeta invalido: rechazada.
- Transferencia con tarjeta de demostracion valida: pedido registrado.
- Efectivo sin datos de tarjeta: pedido registrado.
- Yappy: oculto y rechazado para pedidos nuevos; se conserva en la base solo si existen pedidos historicos asociados.

## Criterios de aceptacion

- El carrito conserva productos durante la sesion: aprobado.
- Agregar, quitar y actualizar cantidad: aprobado.
- No permite comprar mas que el stock disponible: aprobado.
- Calcula subtotal y total en el servidor: aprobado.
- Registra pedido y detalle: aprobado.
- Descuenta inventario dentro de una transaccion: aprobado.
- Limpia el carrito despues de una compra correcta: aprobado.

## Alcance del pago

La fase simula la autorizacion de transferencia, registra el metodo seleccionado y confirma el pedido. No realiza un cargo bancario real. Para produccion se debe sustituir el formulario directo por componentes tokenizados de una pasarela certificada, habilitar HTTPS y evitar que DACHI reciba numeros de tarjeta o CVV.
