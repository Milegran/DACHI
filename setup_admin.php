<?php
session_start();
require_once __DIR__ . '/conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre'] ?? 'Admin');
    $apellido = trim($_POST['apellido'] ?? 'DACHI');
    $correo = trim($_POST['correo'] ?? 'admin@dachi.com');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($contrasena === '') {
        $mensaje = '<div class="error">Debe ingresar una contraseña</div>';
    } else {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $idRol = 4; // administrador

        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = '<div class="error">El correo ya está registrado</div>';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO usuarios (id_rol, nombre, apellido, correo, contraseña, telefono, estado, fecha_registro)
                 VALUES (?, ?, ?, ?, ?, '', 'activo', NOW())"
            );
            $stmt->bind_param('issss', $idRol, $nombre, $apellido, $correo, $hash);
            $stmt->execute();
            $nuevoId = $stmt->insert_id;
            $stmt->close();

            $mensaje = '<div class="success">✅ Cuenta admin creada exitosamente</div>';
            $mensaje .= '<div class="info">';
            $mensaje .= "<p><strong>ID:</strong> {$nuevoId}</p>";
            $mensaje .= "<p><strong>Correo:</strong> " . htmlspecialchars($correo) . "</p>";
            $mensaje .= "<p><strong>Rol:</strong> Administrador</p>";
            $mensaje .= '<p><strong>URL de acceso:</strong> <a href="admin.php">admin.php</a></p>';
            $mensaje .= '</div>';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>DACHI | Setup Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f7faf8; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; border-radius: 24px; padding: 40px; width: 100%; max-width: 480px; box-shadow: 0 10px 30px -5px rgba(21,113,69,0.06); border: 1px solid #d1d9d4; }
        h1 { font-family: 'Playfair Display', serif; color: #157145; font-size: 28px; margin-bottom: 8px; }
        .error { background: #ffdad6; color: #93000a; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 14px; }
        .success { background: #dcfbe7; color: #0d5633; padding: 16px; border-radius: 12px; margin-bottom: 16px; font-size: 16px; font-weight: 600; }
        .info { background: #f1f5f2; padding: 16px; border-radius: 12px; font-size: 14px; }
        .info p { margin: 4px 0; }
        label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #52625b; margin-bottom: 4px; }
        input { width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid #d1d9d4; font-size: 14px; margin-bottom: 16px; }
        input:focus { outline: none; border-color: #157145; box-shadow: 0 0 0 3px rgba(21,113,69,0.15); }
        button { width: 100%; padding: 12px; background: #157145; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #0d5633; }
        .warning { background: #ffdfa0; color: #5c4300; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔧 Setup Admin</h1>
        <p style="color:#52625b;font-size:14px;margin-bottom:24px;">Crear cuenta de administrador para DACHI</p>

        <div class="warning">
            ⚠️ Elimina este archivo (<strong>setup_admin.php</strong>) después de crear la cuenta.
        </div>

        <?= $mensaje ?>

        <form method="post">
            <label>Nombre</label>
            <input type="text" name="nombre" value="Admin" required />

            <label>Apellido</label>
            <input type="text" name="apellido" value="DACHI" required />

            <label>Correo electrónico</label>
            <input type="email" name="correo" value="admin@dachi.com" required />

            <label>Contraseña</label>
            <input type="text" name="contrasena" value="Admin123" required />

            <button type="submit" name="crear" value="1">Crear Cuenta Admin</button>
        </form>
    </div>
</body>
</html>
