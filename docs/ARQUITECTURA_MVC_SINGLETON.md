# Arquitectura MVC y Singleton de DACHI

## Resumen

DACHI utiliza una arquitectura MVC gradual, compatible con la estructura que ya existia. Las paginas PHP mantienen sus vistas y rutas actuales, pero las solicitudes principales ahora se reciben en controladores. Los controladores delegan las reglas de negocio al Facade y a los Services, mientras que los Repositories realizan el acceso a datos.

La conexion a MariaDB se centraliza mediante un Singleton reutilizable por todas las entradas del sistema.

## Modelo

El Modelo representa datos y reglas relacionadas con el dominio.

- `app/Models/Producto.php`: representa un producto y transforma una fila de base de datos a una estructura segura para la vista.
- `app/Repositories/ProductoRepository.php`: consulta productos activos y detalles mediante consultas preparadas.
- `app/Services/ProductoService.php`: aplica limites de busqueda y transforma la fila consultada usando el modelo `Producto`.
- Los demas Repositories y Services siguen la misma separacion para usuarios, pedidos, carrito, recuperacion y seguimiento.

Flujo del modelo para catalogo:

```text
Base de datos -> ProductoRepository -> ProductoService -> Producto -> respuesta para la vista
```

## Vista

La Vista es la capa que presenta HTML, estilos y JavaScript al usuario.

- `index.php`: vista de acceso, registro y recuperacion.
- `panel.php`: vista principal por rol y dashboard del consumidor.
- `public/catalogo.php`: vista de catalogo legado para compatibilidad.
- `public/carrito.php`: vista del carrito y formulario de pago.
- `public/seguimiento.php`: vista de pedidos y direccion de entrega.
- `public/partials/navigation.php`: componente visual compartido entre vistas publicas.

Las vistas ya no necesitan conocer consultas SQL ni construir reglas de negocio para los flujos refactorizados.

## Controlador

El Controlador recibe la peticion HTTP, valida el contexto basico, invoca el Facade y devuelve una respuesta para la vista.

- `app/Controllers/AuthController.php`: login, registro y recuperacion.
- `app/Controllers/CatalogoController.php`: busqueda, detalle y carga inicial del catalogo.
- `app/Controllers/CarritoController.php`: agregar, actualizar, eliminar, vaciar, resumen y confirmar compra.
- `app/Controllers/SeguimientoController.php`: listar pedidos y guardar direccion.
- `app/Controllers/PanelController.php`: valida la sesion, normaliza el rol y entrega el contexto del dashboard.

Ejemplo del flujo de carrito:

```text
Vista public/carrito.php
    -> CarritoController
        -> SistemaDachiFacade
            -> CarritoService / PedidoService
                -> Repositories
                    -> MariaDB
```

## Facade, Services y Repositories

`app/Facades/SistemaDachiFacade.php` funciona como punto de entrada de la logica de aplicacion. Los controladores no crean consultas SQL directamente: llaman al Facade, que coordina Services, Validators y Repositories.

Esta capa conserva el patron Facade ya implementado y evita que la refactorizacion MVC cambie el comportamiento del carrito, pagos, stock o seguimiento.

## Singleton

El Singleton esta implementado en `app/Core/Database.php`.

- `private static ?self $instance`: almacena la unica instancia.
- `private function __construct()`: impide crear la clase con `new` desde otras partes.
- `public static function getInstance()`: crea la instancia una sola vez y luego la reutiliza.
- `private function __clone()`: evita clonar la conexion.
- `__wakeup()`: evita reconstruir la instancia mediante serializacion.
- `connection()`: expone el objeto `mysqli` ya configurado con UTF-8.

`conexion.php` es el punto comun que solicita `Database::getInstance()`. Por eso login, panel, catalogo, carrito y seguimiento utilizan la misma instancia durante cada peticion HTTP.

En PHP esto significa una instancia por peticion. No significa compartir una conexion viva entre usuarios o peticiones diferentes, ya que el proceso de la peticion termina al finalizar la respuesta.

## Regla de mantenimiento

Las funcionalidades nuevas deben seguir esta secuencia:

```text
Vista -> Controller -> Facade -> Service/Validator -> Repository -> Model/Database
```

No se deben agregar consultas SQL directamente en las vistas ni crear nuevas conexiones con `new mysqli`. La conexion debe obtenerse desde `conexion.php`, y las operaciones de dominio deben pasar por el Facade correspondiente.
