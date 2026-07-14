# Plan de desarrollo por fases - Proyecto DACHI

Materia: Ingenieria de Software Aplicada 2
Proyecto: DACHI
Base actual: PHP, MySQL/MariaDB, XAMPP

## 1. Objetivo del plan

Organizar el desarrollo de DACHI por fases controladas, con entregables tecnicos y academicos por cada avance. El orden busca evitar errores acumulados: primero estabilizar conexion, autenticacion y estructura; luego avanzar en las historias funcionales vistas en las imagenes; finalmente documentar con UML, pruebas e integracion.

## 2. Alcance funcional inicial

Historias o modulos principales:

- SCRUM-7 / SCRUM-28: Validaciones de formularios de registro y login.
- Recuperar contrasena.
- Interfaz de catalogo de productos.
- Carrito de compras y proceso de pago.
- Seguimiento de pedidos.
- Patron Facade: SistemaDachiFacade.

## 3. Estado actual detectado

- La web abre desde `http://localhost/DACHI/`.
- La conexion a la base usa MariaDB en puerto `3307`.
- La base `dachitos` existe.
- Hay tablas para usuarios, roles, productos, pedidos, info_pedidos, entregas, metodos, categorias, calificacion e imagenes.
- El login y registro ya conectan con la base.
- Existe un panel principal en `panel.php`.
- Existen bocetos HTML en `BOCETO/` para catalogo, productos, home y panel.
- La tabla `usuarios.id_rol` ya permite multiples usuarios por rol y la conexion usa MariaDB por el puerto `3307`.

## 4. Reglas de desarrollo

### 4.1 Reglas tecnicas

- Todo acceso a base de datos debe usar consultas preparadas.
- Toda validacion importante debe existir en frontend y backend.
- No confiar en datos enviados por el navegador.
- Las acciones de escritura deben validar sesion, rol y datos obligatorios.
- Evitar mezclar nuevas funcionalidades grandes dentro de un solo archivo sin separar responsabilidades.
- Cada fase debe terminar con una prueba manual documentada.
- Antes de modificar estructura de base de datos, hacer respaldo o script reversible.
- Mantener nombres consistentes con el dominio: usuario, producto, pedido, carrito, pago, entrega.
- No introducir librerias nuevas si el proyecto puede resolverlo de forma clara con PHP actual.

### 4.2 Reglas academicas

- Cada fase debe tener descripcion, alcance, criterios de aceptacion y prueba.
- Las funcionalidades principales deben poder relacionarse con casos de uso.
- Las operaciones criticas deben tener diagrama de secuencia o flujo.
- Las clases o componentes nuevos deben tener responsabilidad clara.
- El patron Facade debe justificarse como punto unico para operaciones del sistema.
- Mantener trazabilidad entre historia SCRUM, modulo, archivos modificados y prueba realizada.

### 4.3 Regla de trabajo por fase

No se inicia una fase nueva si la fase anterior tiene errores bloqueantes. Se permite avanzar solo si el error pendiente esta documentado y no afecta el flujo que se va a construir.

## 5. Estructura propuesta de carpetas

Estructura sugerida para ordenar el proyecto sin romperlo de golpe:

```text
DACHI/
  index.php
  panel.php
  conexion.php
  dachitos.sql
  img/
  BOCETO/
  docs/
    PLAN_FASES_DACHI.md
    pruebas/
    diagramas/
  app/
    Facades/
      SistemaDachiFacade.php
    Services/
      AuthService.php
      ProductoService.php
      CarritoService.php
      PedidoService.php
      RecuperacionService.php
    Repositories/
      UsuarioRepository.php
      RecuperacionRepository.php
      ProductoRepository.php
      PedidoRepository.php
    Validators/
      AuthValidator.php
      RecuperacionValidator.php
      PedidoValidator.php
  public/
    catalogo.php
    carrito.php
    seguimiento.php
```

Nota: esta estructura se aplicara de forma gradual. Primero se documenta; luego se mueve logica cuando sea necesario.

## 6. Fases de desarrollo

### Fase 0 - Estabilizacion y diagnostico

Objetivo: asegurar que el proyecto corre correctamente antes de agregar funcionalidades.

Tareas:

- Verificar Apache, MySQL/MariaDB y puerto.
- Confirmar conexion con `dachitos`.
- Confirmar login, registro y carga del panel.
- Revisar restricciones incorrectas en base de datos.
- Crear usuarios de prueba por rol.

Entregables:

- Conexion funcionando.
- Credenciales de prueba.
- Lista de errores detectados.

Criterios de aceptacion:

- Login correcto redirige al panel.
- Registro no genera error de servidor.
- No hay errores fatales recientes en logs al probar login.

Estado: autenticacion y base de datos aprobadas; pendiente definir usuarios finales de prueba.

### Fase 1 - Base tecnica y patron Facade

Objetivo: preparar una estructura minima orientada a objetos para que las siguientes fases no crezcan desordenadas.

Tareas:

- Crear `SistemaDachiFacade`.
- Crear servicios base: autenticacion, productos, carrito, pedidos.
- Crear repositorios para consultas principales.
- Mantener compatibilidad con `index.php` y `panel.php`.
- Documentar clases principales.

Entregables:

- Clase `SistemaDachiFacade`.
- Servicios y repositorios iniciales.
- Diagrama de clases inicial.

Criterios de aceptacion:

- Login y registro siguen funcionando.
- Las operaciones nuevas pasan por el Facade o servicios.
- El codigo queda listo para extender catalogo, carrito y pedidos.

Estado: completada para autenticacion; pendiente extender servicios para productos, carrito y pedidos en fases posteriores.

### Fase 2 - Validaciones de formularios

Objetivo: mejorar validaciones de registro y login.

Tareas:

- Validar nombre, apellido, correo, contrasena y rol.
- Mostrar errores claros en pantalla.
- Validar formato de correo.
- Validar longitud minima y reglas de contrasena.
- Validar duplicidad de correo.
- Evitar errores silenciosos en `fetch`.

Entregables:

- Validaciones frontend.
- Validaciones backend.
- Casos de prueba de login y registro.

Criterios de aceptacion:

- Campos vacios muestran mensaje.
- Correo invalido muestra mensaje.
- Contrasena invalida muestra mensaje.
- Correo duplicado muestra mensaje.
- Credenciales incorrectas muestran mensaje.

Estado: completada. Validaciones frontend (`index.php`) y backend (`AuthValidator`, `AuthService`, `UsuarioRepository`) implementadas y verificadas por revision de codigo. Ver `docs/pruebas/FASE_2_VALIDACIONES.md` para trazabilidad y casos de prueba pendientes de ejecucion manual en ambiente local.

### Fase 3 - Recuperar contrasena

Objetivo: implementar flujo funcional de recuperacion de contrasena.

Tareas:

- Crear flujo de solicitud por correo.
- Crear token temporal o codigo de recuperacion.
- Crear formulario para nueva contrasena.
- Validar expiracion o uso unico del token.
- Guardar nueva contrasena con `password_hash`.

Entregables:

- Tabla o campos necesarios para recuperacion.
- Pantalla o modal de recuperacion funcional.
- Pruebas de recuperacion exitosa y fallida.

Criterios de aceptacion:

- Usuario registrado puede solicitar recuperacion.
- Token invalido o vencido no permite cambiar contrasena.
- Nueva contrasena permite iniciar sesion.

Estado: completada a nivel de codigo. Se agrego la tabla `recuperacion_contrasena` (migracion reversible en `docs/migraciones/FASE_3_recuperacion_contrasena.sql`), el flujo de 2 pasos (solicitar codigo / confirmar codigo + nueva contrasena) en frontend y backend, y el patron Facade se extendio con `solicitarRecuperacion` y `confirmarRecuperacion`. Pendiente: el envio real de correo depende de configurar SMTP en el XAMPP local (no incluido en el alcance academico); para pruebas, el codigo puede verificarse en la tabla `recuperacion_contrasena`. Ver `docs/pruebas/FASE_3_RECUPERACION.md`.

### Fase 4 - Catalogo de productos

Objetivo: construir catalogo navegable con productos reales de la base de datos.

Tareas:

- Integrar boceto `BOCETO/catalogo.html`.
- Mostrar productos activos desde tabla `productos`.
- Agregar busqueda y filtros.
- Mostrar detalle de producto.
- Preparar boton "agregar al carrito".
- Revisar manejo de imagenes.

Entregables:

- Pagina de catalogo funcional.
- Consulta de productos por estado/categoria.
- Prueba de busqueda y detalle.

Criterios de aceptacion:

- El catalogo carga productos desde la base.
- La busqueda filtra correctamente.
- El detalle muestra nombre, descripcion, precio, cantidad y productor.
- Producto sin stock no se puede agregar al carrito.

Estado: completada y verificada. Se creo `ProductoRepository` y `ProductoService`, y se extendio el Facade con `listarProductos`/`obtenerProducto`. El catalogo vive en `public/catalogo.php`, tomando el boceto `BOCETO/catalogo.html` como referencia visual pero recortando su alcance a lo que pide esta fase (sin categorias/zonas, comentarios ni carrito completo). Se agregaron datos semilla y se corrigio `imagenes.ruta` a texto. Tambien se retiraron restricciones que bloqueaban pedidos repetidos por consumidor o metodo de pago, dejando la base preparada para la Fase 5. Ver `docs/pruebas/FASE_4_CATALOGO.md` y `docs/migraciones/FASE_4_ajustes_integridad.sql`.

### Fase 5 - Carrito de compras y proceso de pago

Objetivo: permitir que un consumidor seleccione productos y cree un pedido.

Tareas:

- Crear carrito por sesion.
- Agregar, quitar y actualizar cantidad.
- Calcular subtotal y total.
- Seleccionar metodo de pago.
- Registrar pedido en `pedidos`.
- Registrar detalle en `info_pedidos`.
- Descontar inventario si aplica.
- Usar transaccion de base de datos.

Entregables:

- Carrito funcional.
- Flujo de pago.
- Registro de pedido y detalle.
- Pruebas de compra.

Criterios de aceptacion:

- El carrito conserva productos durante la sesion.
- No permite comprar mas que el stock disponible.
- El pedido queda registrado con total correcto.
- El detalle del pedido queda registrado.

Estado: completada y verificada en ambiente local. El carrito se conserva en sesion, valida stock y cantidades, ofrece efectivo o transferencia y registra `pedidos`/`info_pedidos` dentro de una transaccion que tambien descuenta inventario. La transferencia muestra un formulario de tarjeta para simulacion, valida titular, numero, vencimiento y CVV sin almacenar esos datos. La interfaz vive en `public/carrito.php` y el catalogo agrega productos mediante el Facade. Ver `docs/pruebas/FASE_5_CARRITO_PAGO.md`.

### Fase 6 - Seguimiento de pedidos

Objetivo: mostrar estado del pedido para consumidor y operaciones logisticas.

Tareas:

- Crear vista de pedidos del usuario.
- Mostrar estados: pendiente, en camino, entregado.
- Integrar datos de `entregas`.
- Permitir que logistica tome o confirme entrega.
- Mostrar historial.

Entregables:

- Vista de seguimiento.
- Actualizacion de estado.
- Pruebas por rol.

Criterios de aceptacion:

- Consumidor ve solo sus pedidos.
- Logistica ve pedidos pendientes o asignados.
- Cambio de estado se refleja en la vista.

Estado: completada y verificada en ambiente local. Se creo `public/seguimiento.php` con pedidos aislados por consumidor, linea de estados, historial y direccion de entrega. Las operaciones logisticas de asignar y confirmar ahora pasan por `SistemaDachiFacade`, usan transacciones y validan que solo el repartidor asignado complete la entrega. Ver `docs/pruebas/FASE_6_SEGUIMIENTO.md` y `docs/migraciones/FASE_6_integridad_entregas.sql`.

### Fase 7 - Integracion, documentacion y cierre

Objetivo: preparar entrega academica y tecnica.

Tareas:

- Revisar flujo completo: registro, login, catalogo, carrito, pago, seguimiento.
- Crear diagramas de clases.
- Crear diagramas de secuencia para login, compra y seguimiento.
- Documentar patron Facade.
- Preparar matriz de pruebas.
- Limpiar usuarios o datos de prueba no necesarios.

Entregables:

- Documento final de arquitectura.
- Diagramas UML.
- Matriz de pruebas.
- Proyecto funcional para demostracion.

Criterios de aceptacion:

- Flujo principal funciona de inicio a fin.
- Documentacion explica el diseno orientado a objetos.
- El patron Facade esta implementado y justificado.

## 7. Backlog inicial

| Prioridad | Historia | Modulo | Estado |
| --- | --- | --- | --- |
| Alta | Corregir conexion y login | Autenticacion | En progreso |
| Alta | Validaciones de registro/login | Autenticacion | Completado (pendiente prueba manual en XAMPP) |
| Alta | Corregir restriccion `usuarios.id_rol` | Base de datos | Completado |
| Alta | SistemaDachiFacade | Arquitectura | Completado |
| Media | Recuperar contrasena | Autenticacion | Completado (pendiente prueba manual en XAMPP; SMTP no configurado) |
| Media | Catalogo de productos | Productos | Completado (pendiente prueba manual en XAMPP) |
| Media | Carrito y pago | Pedidos | Completado |
| Media | Seguimiento de pedidos | Pedidos/Logistica | Completado |
| Baja | Documentacion UML final | Documentacion | Pendiente |

## 8. Pruebas minimas por fase

Cada fase debe registrar:

- Fecha de prueba.
- Usuario usado.
- Pasos ejecutados.
- Resultado esperado.
- Resultado obtenido.
- Evidencia si aplica.
- Error pendiente si aplica.

## 9. Orden recomendado inmediato

1. Cerrar fase 0: corregir restriccion de `usuarios.id_rol` y crear usuarios de prueba limpios.
2. Implementar fase 1: Facade y servicios base sin romper el flujo actual.
3. Implementar fase 2: validaciones completas de login y registro.
4. Continuar con recuperacion, catalogo, carrito y seguimiento.
