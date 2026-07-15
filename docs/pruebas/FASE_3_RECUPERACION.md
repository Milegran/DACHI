# Fase 3 - Recuperar contrasena

Fecha: 2026-07-11
Proyecto: DACHI
Modulo trabajado: autenticacion (recuperacion de contrasena)

## Objetivo

Implementar un flujo funcional de recuperacion de contrasena: solicitud por correo, codigo temporal de un solo uso con expiracion, formulario de nueva contrasena, y guardado con `password_hash`.

## Migracion de base de datos (regla 4.1 del plan)

Antes de modificar la estructura de la base de datos se creo un script reversible:

```text
C:\xampp\htdocs\DACHI\docs\migraciones\FASE_3_recuperacion_contrasena.sql
```

Este script crea la tabla `recuperacion_contrasena` con su respectivo `FOREIGN KEY` hacia `usuarios`, e incluye el `DROP TABLE` de rollback comentado al final. `dachitos.sql` tambien se actualizo para reflejar la tabla nueva en el dump principal.

**Accion pendiente del usuario**: ejecutar el script en phpMyAdmin (o importar el `dachitos.sql` actualizado) antes de probar esta fase. Sin esta tabla, `recuperar_solicitud` y `recuperar_confirmacion` fallaran con error de base de datos.

```sql
CREATE TABLE `recuperacion_contrasena` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recuperacion_usuario` (`id_usuario`),
  KEY `idx_recuperacion_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Archivos creados

```text
C:\xampp\htdocs\DACHI\docs\migraciones\FASE_3_recuperacion_contrasena.sql
C:\xampp\htdocs\DACHI\app\Repositories\RecuperacionRepository.php
C:\xampp\htdocs\DACHI\app\Validators\RecuperacionValidator.php
C:\xampp\htdocs\DACHI\app\Services\RecuperacionService.php
```

## Archivos actualizados

```text
C:\xampp\htdocs\DACHI\dachitos.sql
C:\xampp\htdocs\DACHI\app\Validators\AuthValidator.php
C:\xampp\htdocs\DACHI\app\Repositories\UsuarioRepository.php
C:\xampp\htdocs\DACHI\app\Facades\SistemaDachiFacade.php
C:\xampp\htdocs\DACHI\index.php
```

## Responsabilidades

### RecuperacionRepository

Centraliza las consultas SQL de la tabla `recuperacion_contrasena` (todas con consultas preparadas): invalidar tokens activos, crear token, buscar token valido, marcar como usado.

### RecuperacionValidator

Valida los datos de entrada de la recuperacion (correo, formato de codigo de 6 digitos, reglas de la nueva contrasena). Reutiliza `AuthValidator::correoValido()` y `AuthValidator::contrasenaValida()` por composicion, en vez de duplicar las reglas.

### RecuperacionService

Contiene la logica de negocio:

- Genera un codigo numerico de 6 digitos con `random_int` (aleatoriedad criptograficamente segura).
- Guarda solo el hash SHA-256 del codigo en la base de datos, nunca el codigo en texto plano.
- Invalida cualquier codigo anterior sin usar del mismo usuario antes de crear uno nuevo (un solo token activo).
- Define expiracion de 15 minutos (`MINUTOS_EXPIRACION`).
- Al confirmar, valida que el token exista, no este usado y no haya expirado antes de actualizar la contrasena con `password_hash` y marcarlo como usado.
- No revela si un correo esta o no registrado (mensaje generico), igual que el login.
- Intenta enviar el codigo por correo con `mail()` de forma best-effort.

### SistemaDachiFacade (extendido)

Ahora expone tambien `solicitarRecuperacion($correo)` y `confirmarRecuperacion($correo, $codigo, $nuevaContrasena)`.

### index.php (extendido)

- Nuevas acciones AJAX: `recuperar_solicitud` y `recuperar_confirmacion`.
- El bloque que crea sesion y redirige a `panel.php` ahora esta condicionado a que la accion sea `login` o `registro`, para no interferir con las respuestas de recuperacion.
- La seccion de recuperacion ahora tiene 2 pasos: solicitar codigo, y confirmar codigo + nueva contrasena.
- Se agrego una caja de exito (`loginSuccess`) separada de la caja de error (`loginError`) para evitar reusar clases CSS de un estado en el otro.

## Limitacion conocida: envio real de correo

XAMPP no trae un servidor SMTP configurado por defecto, por lo que `mail()` normalmente no entrega el correo en el entorno local del proyecto. El flujo esta completo y es funcional de extremo a extremo; lo unico que depende del entorno es la entrega real del correo. Para probar sin configurar SMTP, el codigo puede consultarse directamente en la base de datos:

```sql
SELECT id_usuario, token_hash, expira_en, usado
FROM recuperacion_contrasena
ORDER BY id DESC
LIMIT 1;
```

El `token_hash` es el SHA-256 del codigo, no el codigo mismo. Para obtener el codigo en texto plano durante pruebas, se puede comparar manualmente probando codigos de 6 digitos, o (recomendado solo para depuracion local) agregar temporalmente un `error_log($codigo)` dentro de `RecuperacionService::solicitarCodigo` y leerlo en el log de PHP/Apache. No se recomienda dejar eso activo fuera de pruebas.

## Casos de prueba

Requiere haber ejecutado la migracion de esta fase antes de probar.

### Caso 1 - Solicitud con correo vacio

Resultado esperado: mensaje "Debe ingresar su correo electronico" (frontend bloquea con `required`, backend tambien valida).
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 2 - Solicitud con correo de formato invalido

Datos: `correo-invalido`.
Resultado esperado: mensaje "Ingrese un correo electronico valido".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 3 - Solicitud con correo no registrado

Datos: correo que no existe en `usuarios`.
Resultado esperado: `{"status":"success","message":"Si el correo esta registrado..."}` (mensaje generico, no revela si existe) y NO se crea fila en `recuperacion_contrasena`.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 4 - Solicitud con correo registrado

Datos: correo de un usuario existente.
Resultado esperado: mismo mensaje generico de exito, se crea una fila nueva en `recuperacion_contrasena` con `usado = 0` y `expira_en` = ahora + 15 minutos. El formulario avanza al paso 2 (codigo + nueva contrasena).
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 5 - Solicitud repetida invalida el codigo anterior

Pasos: solicitar codigo dos veces seguidas para el mismo correo.
Resultado esperado: el primer registro en `recuperacion_contrasena` queda con `usado = 1`; solo el segundo codigo generado es valido.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 6 - Confirmacion con codigo invalido

Datos: correo valido, codigo `000000` (o cualquiera que no coincida).
Resultado esperado: mensaje "Codigo invalido o expirado".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 7 - Confirmacion con codigo correcto pero expirado

Pasos: generar un codigo, esperar mas de 15 minutos (o actualizar manualmente `expira_en` a una fecha pasada en la base de datos), luego intentar confirmar con el codigo correcto.
Resultado esperado: mensaje "Codigo invalido o expirado".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 8 - Confirmacion con nueva contrasena debil

Datos: codigo correcto, contrasena `abc123` (no cumple las reglas).
Resultado esperado: mensaje de regla de contrasena, igual al de registro.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 9 - Confirmacion exitosa

Datos: codigo correcto y vigente, nueva contrasena valida (ej. `NuevaPass1`).
Resultado esperado: `{"status":"success","message":"Contrasena actualizada correctamente"}`, la fila del token queda `usado = 1`, y el usuario puede iniciar sesion con la nueva contrasena (la anterior deja de funcionar).
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 10 - Reutilizar el mismo codigo despues de usarlo

Pasos: repetir el Caso 9 con el mismo codigo ya usado.
Resultado esperado: mensaje "Codigo invalido o expirado" (uso unico).
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 11 - Error de red/servidor en el flujo de recuperacion

Pasos: detener Apache o MySQL temporalmente y solicitar o confirmar recuperacion.
Resultado esperado: mensaje "Error de conexion con el servidor." sin que la interfaz quede colgada.
Resultado obtenido: _pendiente_
Estado: _pendiente_

## Estado de la fase

Fase 3 completada a nivel de codigo, incluyendo migracion reversible de base de datos, patron Facade extendido, y flujo frontend/backend de 2 pasos. Pendiente: 1) ejecutar la migracion SQL en el ambiente local, 2) ejecutar los 11 casos de prueba y documentar resultados, 3) decidir si se configura SMTP real para el envio de correos o si se mantiene la verificacion manual por base de datos durante el desarrollo academico.
