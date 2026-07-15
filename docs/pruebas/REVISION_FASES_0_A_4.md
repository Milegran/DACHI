# Revision tecnica de Fases 0 a 4

Fecha: 2026-07-12
Ambiente: XAMPP local, Apache en puerto 80, MariaDB en puerto 3307, base `dachitos`.

## Resultado general

Las fases 0 a 4 quedan aptas para continuar con la Fase 5. Se ejecuto revision de sintaxis PHP, conexion, autenticacion, recuperacion de contrasena, catalogo y estructura de base de datos.

## Correcciones aplicadas

- Se restauro el directorio activo `C:\xampp\htdocs\DACHI` desde el respaldo entregado cuando no estaba disponible en `htdocs`.
- Se agregaron cuatro productos semilla mediante `FASE_4_datos_catalogo.sql`; tres activos y uno inactivo para cubrir las pruebas del catalogo.
- Se corrigio `imagenes.ruta` de tipo numerico a `varchar(255)`.
- Se retiraron los `UNIQUE` incorrectos de `pedidos.id_consumer`, `pedidos.metodo_pago` y `calificacion.calificacion`.
- Se agregaron indices normales para las relaciones de pedidos, clave unica para `usuarios.correo`, una FK de consumidor en calificaciones y `AUTO_INCREMENT` para los identificadores de las entidades.

## Verificaciones realizadas

- Sintaxis: todos los archivos PHP pasan `php -l` sin errores.
- Conexion: el proyecto conecta con `127.0.0.1:3307` y la base `dachitos`.
- Login: credenciales validas redirigen a `panel.php`; credenciales invalidas devuelven mensaje de error.
- Recuperacion: solicitud, validacion del codigo, cambio de contrasena y bloqueo de reutilizacion del token fueron comprobados en secuencia.
- Catalogo: acceso sin sesion redirige al login; listado, busqueda por producto/productor, detalle, stock agotado y ocultamiento de productos inactivos responden correctamente.

## Pendientes no bloqueantes

- Configurar SMTP solo si se requiere enviar el codigo de recuperacion por correo real. Para pruebas academicas el codigo se consulta en `recuperacion_contrasena`.
- Definir la carga de archivos e interfaz de administracion de fotos de productos. La columna de ruta ya esta preparada.
- Ejecutar las pruebas visuales manuales de configuracion, edicion de perfil y navegacion movil antes de la entrega final.
