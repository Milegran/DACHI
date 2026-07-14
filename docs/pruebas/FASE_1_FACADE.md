# Fase 1 - Base tecnica y patron Facade

Fecha: 2026-07-11
Proyecto: DACHI
Modulo trabajado: autenticacion

## Objetivo

Crear una base orientada a objetos para centralizar operaciones del sistema mediante el patron Facade, sin romper el login, registro ni carga del panel.

## Archivos creados

```text
C:\xampp\htdocs\DACHI\app\Facades\SistemaDachiFacade.php
C:\xampp\htdocs\DACHI\app\Services\AuthService.php
C:\xampp\htdocs\DACHI\app\Repositories\UsuarioRepository.php
C:\xampp\htdocs\DACHI\app\Validators\AuthValidator.php
```

## Archivo actualizado

```text
C:\xampp\htdocs\DACHI\index.php
```

## Responsabilidades

### SistemaDachiFacade

Punto de entrada principal para las operaciones del sistema. Por ahora expone:

- `iniciarSesion`
- `registrarUsuario`

### AuthService

Contiene reglas de negocio de autenticacion:

- Validar credenciales.
- Verificar contrasena con `password_verify`.
- Crear datos de sesion.
- Registrar usuarios con `password_hash`.

### UsuarioRepository

Centraliza consultas SQL relacionadas con usuarios y roles:

- Buscar usuario por correo.
- Verificar correo existente.
- Obtener id de rol.
- Crear usuario.

### AuthValidator

Valida datos de entrada de login y registro antes de llegar a la base de datos.

## Pruebas realizadas

### Prueba 1 - Sintaxis PHP

Archivos revisados:

- `index.php`
- `SistemaDachiFacade.php`
- `AuthService.php`
- `UsuarioRepository.php`
- `AuthValidator.php`

Resultado:

```text
No syntax errors detected
```

Estado: aprobado.

### Prueba 2 - Login con usuario existente

Datos:

```text
Correo: fase0.consumer.20260711193251@example.com
Contrasena: Test12345
```

Resultado:

```json
{"status":"success","redirect":"panel.php"}
```

Estado: aprobado.

### Prueba 3 - Registro usando Facade

Datos:

```text
Nombre: Fase1
Apellido: Facade
Correo: fase1.facade.20260711195009@example.com
Contrasena: Test12345
Rol: consumidor
```

Resultado:

```json
{"status":"success","redirect":"panel.php"}
```

Estado: aprobado.

### Prueba 4 - Login invalido

Datos:

```text
Correo: noexiste@example.com
Contrasena: wrong
```

Resultado:

```json
{"status":"error","message":"Credenciales incorrectas"}
```

Estado: aprobado.

### Prueba 5 - Carga de panel con sesion

Usuario:

```text
fase1.facade.20260711195009@example.com
```

Resultado verificado:

```text
DACHI | Panel de Control
"rol":"consumidor"
PERFIL DE CONSUMIDOR
Panel de Consumidor
```

Estado: aprobado.

## Estado de la fase

Fase 1 queda completada para autenticacion. El patron Facade ya existe y sera extendido en las siguientes fases para recuperacion de contrasena, catalogo, carrito, pagos y seguimiento.

