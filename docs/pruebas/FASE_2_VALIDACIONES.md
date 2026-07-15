# Fase 2 - Validaciones de formularios

Fecha: 2026-07-11
Proyecto: DACHI
Modulo trabajado: autenticacion (login y registro)
Historias relacionadas: SCRUM-7, SCRUM-28

## Objetivo

Garantizar que los formularios de login y registro validen datos en frontend y backend, sin confiar en lo que envia el navegador, mostrando mensajes claros para cada caso de error y evitando fallos silenciosos en las llamadas `fetch`.

## Archivos revisados

```text
C:\xampp\htdocs\DACHI\index.php
C:\xampp\htdocs\DACHI\app\Validators\AuthValidator.php
C:\xampp\htdocs\DACHI\app\Services\AuthService.php
C:\xampp\htdocs\DACHI\app\Repositories\UsuarioRepository.php
C:\xampp\htdocs\DACHI\app\Facades\SistemaDachiFacade.php
```

Nota: no se modifico codigo en esta fase. La revision encontro que las validaciones ya habian quedado implementadas durante el trabajo de Fase 1 (Facade/Services). Este documento formaliza la trazabilidad y las pruebas para cerrar la Fase 2 segun `PLAN_FASES_DACHI.md`.

## Trazabilidad requisito -> implementacion

| Requisito (Fase 2) | Frontend (`index.php`) | Backend (`AuthValidator` / `AuthService`) |
| --- | --- | --- |
| Campos obligatorios completos | `validateLoginForm`, `validateSignupForm` | `validarLogin`, `validarRegistro` (chequeo de vacios) |
| Formato de nombre y apellido | `isValidName` (regex letras/espacios, 2-60) + `pattern`/`minlength`/`maxlength` en el input | `nombreValido` (misma regla) |
| Formato de correo | `isValidEmail` + `type="email"`, `maxlength="150"` | `correoValido` (`FILTER_VALIDATE_EMAIL`, largo <= 150) |
| Reglas de contrasena | `isValidPassword` (8+ caract., mayuscula, minuscula, numero) + `minlength="8"` | `contrasenaValida` (misma regla con regex) |
| Rol valido | radio `role` con `required` en la opcion "consumidor" (exige seleccion del grupo) + chequeo JS `roleInput` | `in_array($rol, ["consumidor","productor"], true)` |
| Correo duplicado | - (se valida contra el backend) | `UsuarioRepository::existeCorreo()` -> mensaje "Este correo ya esta registrado" |
| Credenciales incorrectas | - | `password_verify()` contra hash; si falla -> "Credenciales incorrectas" |
| No exponer si el correo existe o no en login | - | El mensaje de login es generico ("Credenciales incorrectas") tanto si el correo no existe como si la contrasena es incorrecta |
| Errores silenciosos en `fetch` | `.catch()` en `handleLogin` y `handleSignup` muestra "Error de conexion con el servidor." | - |
| Consultas preparadas (no confiar en el navegador) | - | Todas las consultas en `UsuarioRepository` usan `mysqli::prepare` + `bind_param` |

## Casos de prueba

Cada caso debe ejecutarse en el navegador contra `http://localhost/DACHI/` con Apache y MariaDB (puerto 3307) activos. Completa "Resultado obtenido" y "Estado" al ejecutar.

### Caso 1 - Login: campos vacios

Pasos: dejar correo y/o contrasena vacios, enviar formulario.
Resultado esperado: mensaje "Debe completar todos los campos", sin llamada al servidor (bloqueado por JS y por `required` del HTML).
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 2 - Login: correo con formato invalido

Datos: correo `usuario-sin-arroba`, contrasena `Test12345`.
Resultado esperado: mensaje "Ingrese un correo electronico valido".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 3 - Login: credenciales incorrectas

Datos: correo existente, contrasena incorrecta.
Resultado esperado: `{"status":"error","message":"Credenciales incorrectas"}`.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 4 - Login: correo inexistente

Datos: correo que no esta en la base, cualquier contrasena.
Resultado esperado: mismo mensaje generico "Credenciales incorrectas" (no revela si el correo existe).
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 5 - Login: exitoso

Datos: usuario de prueba valido (ver `FASE_1_FACADE.md` o crear uno nuevo).
Resultado esperado: `{"status":"success","redirect":"panel.php"}` y redireccion al panel.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 6 - Registro: campos vacios

Pasos: enviar el formulario de registro sin llenar uno o mas campos.
Resultado esperado: mensaje "Debe completar todos los campos".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 7 - Registro: nombre/apellido con numeros o simbolos

Datos: nombre `Juan123`.
Resultado esperado: mensaje "El nombre solo debe contener letras y espacios".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 8 - Registro: correo con formato invalido

Datos: correo `correo@sin-dominio`.
Resultado esperado: mensaje "Ingrese un correo electronico valido".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 9 - Registro: contrasena debil

Datos: contrasena `abc123` (sin mayuscula, menos de 8 caracteres).
Resultado esperado: mensaje "La contrasena debe tener minimo 8 caracteres, una mayuscula, una minuscula y un numero".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 10 - Registro: sin seleccionar rol

Pasos: llenar todos los campos, no marcar "Consumidor" ni "Productor".
Resultado esperado: el navegador exige seleccion por el `required` del radio; si se fuerza el envio, JS responde "Debe seleccionar un rol".
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 11 - Registro: correo duplicado

Datos: correo de un usuario ya existente en `usuarios`.
Resultado esperado: `{"status":"error","message":"Este correo ya esta registrado"}`.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 12 - Registro: exitoso

Datos: nombre, apellido, correo nuevo, contrasena valida (ej. `Test12345`), rol `consumidor` o `productor`.
Resultado esperado: `{"status":"success","redirect":"panel.php"}`, usuario creado con `password_hash` y redireccion al panel.
Resultado obtenido: _pendiente_
Estado: _pendiente_

### Caso 13 - Manejo de error de red/servidor en `fetch`

Pasos: detener temporalmente Apache o MySQL y enviar login o registro.
Resultado esperado: mensaje "Error de conexion con el servidor." en pantalla, sin que la pagina quede colgada ni sin respuesta visual.
Resultado obtenido: _pendiente_
Estado: _pendiente_

## Verificacion de codigo (revision estatica realizada)

- Se confirmo que `conexion.php` no expone credenciales al frontend y que `UsuarioRepository` usa exclusivamente consultas preparadas (`prepare` + `bind_param`), cumpliendo la regla tecnica 4.1 del plan.
- Se confirmo que la tabla `usuarios` usa collation `utf8mb4_general_ci`, por lo que la deteccion de correo duplicado (`existeCorreo`) es insensible a mayusculas/minusculas de forma nativa.
- Se confirmo que `AuthService::registrarUsuario` captura `mysqli_sql_exception` para evitar errores fatales no controlados durante el insert.
- Se confirmo que el mensaje de login no distingue entre "correo no existe" y "contrasena incorrecta", evitando enumeracion de usuarios.

## Estado de la fase

Fase 2 completada a nivel de codigo (frontend y backend). Pendiente que el usuario ejecute los 13 casos de prueba en su ambiente XAMPP local y reporte resultados para dejar evidencia formal, tal como exige la regla academica 4.2 del plan. Una vez confirmados los resultados, se puede iniciar la Fase 3 (Recuperar contrasena) sin bloqueos, segun la regla de trabajo por fase (4.3).
