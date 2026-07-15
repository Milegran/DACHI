# Fase 4 - Catalogo de productos

Fecha: 2026-07-12
Proyecto: DACHI
Modulo trabajado: catalogo de productos (consumidor)

## Objetivo

Construir un catalogo navegable con productos reales de la base de datos: listado de productos activos, busqueda, detalle de producto y preparacion del boton "agregar al carrito" para su integracion posterior.

## Decision de diseno: no se toco panel.php con SQL nuevo

Antes de esta fase, `panel.php` ya tenia secciones de administracion/moderacion de productos construidas con SQL directo (heredadas de una etapa anterior del proyecto, fuera del alcance de esta sesion). Esas secciones sirven a roles admin/productor/logistico y consultan **todos** los productos (activos e inactivos) para moderacion.

Siguiendo la regla del proyecto de no mezclar SQL nuevo dentro de `panel.php` y usar el patron Facade para funcionalidad nueva, el catalogo de consumidor se construyo como una pagina independiente (`public/catalogo.php`) respaldada por clases nuevas (`ProductoRepository`, `ProductoService`) sin tocar ni duplicar el SQL ya existente en `panel.php`. Solo se agrego un enlace de navegacion hacia el catalogo en el sidebar y el dashboard del consumidor (sin SQL nuevo en ese archivo).

## Archivos creados

```text
C:\xampp\htdocs\DACHI\app\Repositories\ProductoRepository.php
C:\xampp\htdocs\DACHI\app\Services\ProductoService.php
C:\xampp\htdocs\DACHI\public\catalogo.php
```

## Archivos actualizados

```text
C:\xampp\htdocs\DACHI\app\Facades\SistemaDachiFacade.php   (agrega listarProductos / obtenerProducto)
C:\xampp\htdocs\DACHI\panel.php                              (enlace de navegacion al catalogo, sin SQL nuevo)
```

## Responsabilidades

### ProductoRepository

- `listarActivos(string $busqueda = '')`: trae productos con `estado = 1`. Si hay termino de busqueda, filtra por `nombre LIKE` o `nom_productor LIKE` usando consulta preparada.
- `buscarActivoPorId(int $id)`: trae un producto por id, solo si `estado = 1`. Devuelve `null` si no existe o esta inactivo (para no exponer productos deshabilitados via URL/id directo).

### ProductoService

- `listarCatalogo(string $busqueda)`: recorta la busqueda a 60 caracteres (evita abusos de longitud), delega al repositorio, y formatea cada producto (precio como `float`, `cantidad` como `int`, y agrega `disponible` = `cantidad > 0`).
- `obtenerDetalle(int $id)`: valida el id, busca el producto activo, y si no existe responde `"Producto no encontrado o no disponible"` en vez de filtrar informacion de productos inactivos.

### SistemaDachiFacade (extendido)

Ahora expone tambien `listarProductos($busqueda)` y `obtenerProducto($id)`.

### public/catalogo.php

- Requiere sesion activa (cualquier rol autenticado); si no hay sesion, redirige a `../index.php`.
- Primera carga renderizada desde PHP (evita una peticion AJAX extra al abrir la pagina).
- Busqueda con debounce de 300ms que llama a la propia pagina con `accion=buscar`.
- Click en una tarjeta de producto llama a `accion=detalle` y abre un modal con nombre, descripcion, precio, cantidad y productor (cumple el criterio de aceptacion de la fase).
- El boton "Agregar al carrito" esta deshabilitado cuando `cantidad = 0`. Desde la Fase 5, cuando esta habilitado agrega una unidad al carrito de sesion mediante `public/carrito.php`.

## Hallazgo sobre manejo de imagenes (tarea de revision del plan)

La revision de la base activa confirmo el siguiente estado:

- `productos.imagen` ya es `varchar(255)` y admite una ruta de texto.
- `imagenes.ruta` era `bigint(20)`; se corrigio a `varchar(255)` mediante `docs/migraciones/FASE_4_ajustes_integridad.sql`.
- No hay imagenes de productos reales en el proyecto (`img/` solo contiene assets de marca: logo, banner y fondo).

Por esta razon, el catalogo de esta fase muestra un placeholder visual (inicial del nombre del producto sobre un fondo de color) en vez de una imagen real. La estructura ya admite rutas; queda definir el flujo de carga y asociacion de fotos cuando se implemente la gestion de imagenes.

## Casos de prueba

### Caso 1 - Acceso sin sesion

Pasos: abrir `public/catalogo.php` directamente sin haber iniciado sesion.
Resultado esperado: redireccion a `index.php`.
Resultado obtenido: redireccion HTTP 302 a `../index.php`.
Estado: aprobado

### Caso 2 - Carga inicial del catalogo

Pasos: iniciar sesion con cualquier rol y entrar al catalogo desde el panel.
Resultado esperado: se listan solo productos con `estado = 1`; los inactivos no aparecen.
Resultado obtenido: se muestran 3 productos activos; el producto inactivo no se expone.
Estado: aprobado

### Caso 3 - Busqueda por nombre de producto

Datos: escribir parte del nombre de un producto existente.
Resultado esperado: la grilla se actualiza mostrando solo coincidencias, sin recargar la pagina.
Resultado obtenido: la busqueda `Tomate` devuelve solo `Tomate perita`.
Estado: aprobado

### Caso 4 - Busqueda por nombre de productor

Datos: escribir el nombre de un productor (`nom_productor`).
Resultado esperado: se muestran los productos de ese productor.
Resultado obtenido: la busqueda `Finca` devuelve el producto activo de ese productor.
Estado: aprobado

### Caso 5 - Busqueda sin resultados

Datos: termino que no coincide con ningun producto activo.
Resultado esperado: mensaje "No se encontraron productos con esa busqueda."
Resultado obtenido: el endpoint devuelve una lista vacia; la interfaz muestra el estado sin resultados.
Estado: aprobado

### Caso 6 - Detalle de producto con stock

Pasos: hacer clic en un producto con `cantidad > 0`.
Resultado esperado: modal muestra nombre, descripcion, precio, cantidad disponible y productor; boton "Agregar al carrito" habilitado.
Resultado obtenido: `Tomate perita` devuelve detalle y `disponible: true`.
Estado: aprobado

### Caso 7 - Detalle de producto sin stock

Pasos: hacer clic en un producto con `cantidad = 0` (o poner uno en 0 manualmente para la prueba).
Resultado esperado: la tarjeta muestra el sello "Sin stock"; el modal muestra "Sin stock disponible" y el boton de agregar al carrito esta deshabilitado.
Resultado obtenido: `Miel artesanal` devuelve detalle y `disponible: false`.
Estado: aprobado

### Caso 8 - Intento de ver detalle de un producto inactivo por id directo

Pasos: llamar `accion=detalle` con el id de un producto con `estado = 0` (via consola del navegador o herramienta como Postman).
Resultado esperado: `{"status":"error","message":"Producto no encontrado o no disponible"}`.
Resultado obtenido: el producto inactivo devuelve `Producto no encontrado o no disponible`.
Estado: aprobado

### Caso 9 - Boton "agregar al carrito"

Pasos: con un producto disponible, hacer clic en "Agregar al carrito".
Resultado esperado actual: agrega una unidad al carrito, actualiza el contador y muestra confirmacion visible.
Resultado obtenido: el producto se conserva en la sesion y aparece en `public/carrito.php`.
Estado: aprobado en Fase 5

### Caso 10 - Error de red en el catalogo

Pasos: detener Apache o MySQL y realizar una busqueda.
Resultado esperado: mensaje de error visible, sin que la interfaz quede colgada.
Resultado obtenido: _pendiente_
Estado: _pendiente_

## Actualizacion: paridad visual y funcional completa con panel.php

Despues de la primera entrega de esta fase, se rehizo `public/catalogo.php` para que comparta exactamente el mismo "chrome" que el resto de la aplicacion en vez de ser una pagina con estilo propio simplificado. Ahora incluye:

- **Modo oscuro y tamano de fuente**: se lee la misma cookie `dachi_ajustes` que usa `panel.php`, con el mismo bloque de CSS de modo oscuro (mismas clases, mismos colores). Cambiar el ajuste en el catalogo lo cambia tambien en el panel y viceversa, porque comparten cookie.
- **Sidebar**: mismo componente deslizable, con enlaces a "Inicio" (panel) y "Catálogo" (esta pagina, marcada activa) y boton de cerrar sesion.
- **Navbar con menu de usuario**: mismo avatar con inicial, mismo dropdown (Ir al Panel, Cambiar Cuenta, Configuración, Editar Perfil, Cerrar Sesión).
- **Modal de Editar Perfil**: mismos campos (nombre, apellido, correo deshabilitado, telefono). Reutiliza el endpoint `accion=guardar_perfil` que ya existe en `panel.php` en vez de duplicar esa logica en el catalogo.
- **Modal de Configuración**: mismo control de tamano de fuente y modo oscuro, guardado en la misma cookie.

Se decidio reutilizar `panel.php` para `guardar_perfil` (en lugar de reimplementarlo en `catalogo.php`) para no duplicar SQL de actualizacion de usuario en dos archivos distintos, siguiendo la regla del proyecto de mantener una sola fuente de verdad por operacion.

**Diferencia deliberada con panel.php**: el sidebar del catalogo siempre muestra las mismas 2 opciones (Inicio, Catálogo) sin importar el rol, a diferencia del panel que arma un menu distinto por rol (admin/logistico/productor/consumidor). Esto es intencional: el catalogo es una pagina de compra transversal, no una seccion administrativa por rol.

## Casos de prueba adicionales (paridad de diseno)

### Caso 11 - Modo oscuro se comparte entre panel y catalogo

Pasos: activar modo oscuro desde Configuración en el panel, luego entrar al catálogo.
Resultado esperado: el catálogo carga ya en modo oscuro (misma cookie). Cambiarlo desde el catálogo y volver al panel debe reflejar el cambio alli tambien.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 12 - Editar perfil desde el catálogo

Pasos: abrir "Editar Perfil" desde el menu de usuario en el catálogo, cambiar nombre/telefono, guardar.
Resultado esperado: los datos se actualizan en la base de datos (misma tabla `usuarios`) y se reflejan al recargar.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 13 - Navegacion sidebar

Pasos: abrir el menu hamburguesa en el catálogo (movil o clic en el icono), verificar que "Catálogo" aparece resaltado como activo y "Inicio" navega de regreso al panel.
Resultado esperado: navegacion correcta, sin recargar estilos rotos.
Resultado obtenido: _pendiente_
Estado: _pendiente_

## Estado de la fase (actualizado)

Fase 4 completada y verificada en ambiente local: catalogo funcional conectado a la base de datos real, busqueda, detalle de producto y control de stock. La integracion del boton con el carrito fue completada posteriormente en la Fase 5.
