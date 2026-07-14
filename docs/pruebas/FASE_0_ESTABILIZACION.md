# Fase 0 - Estabilizacion y diagnostico

Fecha: 2026-07-11
Proyecto: DACHI
Entorno: XAMPP, Apache, MariaDB/MySQL en puerto 3307

## Objetivo

Validar que la aplicacion puede conectarse a la base de datos, registrar usuarios, iniciar sesion y permitir multiples usuarios con el mismo rol.

## Acciones realizadas

- Se verifico que la base `dachitos` esta disponible en `127.0.0.1:3307`.
- Se corrigio `conexion.php` para usar el puerto `3307`.
- Se corrigio la validacion inicial de roles en `panel.php`.
- Se corrigio el rol `consumidor` para que no sea mostrado como `distribuidor`.
- Se respaldo la tabla `usuarios` antes de modificar indices.
- Se cambio `usuarios.id_rol` de indice unico a indice normal.
- Se actualizo `dachitos.sql` para que el dump no vuelva a crear `id_rol` como unico.

## Respaldo

Archivo:

```text
C:\xampp\htdocs\DACHI\docs\backups\usuarios_before_id_rol_fix.sql
```

## Problema encontrado

La tabla `usuarios` tenia esta restriccion:

```sql
UNIQUE KEY `id_rol` (`id_rol`)
```

Eso impedia registrar mas de un usuario con el mismo rol. Por ejemplo, solo podia existir un usuario `consumidor`, un usuario `productor`, etc.

## Correccion aplicada

La restriccion unica fue reemplazada por un indice normal:

```sql
KEY `idx_usuarios_id_rol` (`id_rol`)
```

La llave foranea hacia `rol(id)` se conserva.

## Pruebas realizadas

### Prueba 1 - Registro de usuario consumidor adicional

Datos:

```text
Nombre: Fase0
Apellido: Consumidor
Correo: fase0.consumer.20260711193251@example.com
Contrasena: Test12345
Rol: consumidor
```

Resultado esperado:

```text
El sistema debe registrar el usuario sin error de duplicidad por id_rol.
```

Resultado obtenido:

```json
{"status":"success","redirect":"panel.php"}
```

Estado: aprobado.

### Prueba 2 - Login del usuario nuevo

Datos:

```text
Correo: fase0.consumer.20260711193251@example.com
Contrasena: Test12345
```

Resultado obtenido:

```json
{"status":"success","redirect":"panel.php"}
```

Estado: aprobado.

### Prueba 3 - Visualizacion correcta de rol consumidor

Datos:

```text
Correo: fase0.consumer.20260711193251@example.com
Rol esperado: consumidor
```

Resultado verificado en `panel.php`:

```text
"rol":"consumidor"
PERFIL DE CONSUMIDOR
Panel de Consumidor
```

Tambien se verifico que no aparezca:

```text
PERFIL DE DISTRIBUIDOR
```

Estado: aprobado.

### Prueba 4 - Validacion de sintaxis PHP

Archivos revisados:

- `index.php`
- `panel.php`
- `conexion.php`

Resultado:

```text
No syntax errors detected
```

Estado: aprobado.

## Estado de la fase

Fase 0 queda parcialmente cerrada para autenticacion y base de datos. Pendiente para cierre total: definir usuarios de prueba finales por rol y limpiar datos temporales si el equipo lo requiere.
