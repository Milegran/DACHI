<?php
require_once __DIR__ . '/conexion.php';

echo "<!DOCTYPE html><html lang='es'><head><title>Fix Migración</title>";
echo "<style>body{font-family:sans-serif;padding:40px;background:#f7faf8;color:#121a17}";
echo ".ok{color:#157145}.err{color:#ba1a1a}hr{margin:20px 0}</style></head><body>";
echo "<h1>Verificando columnas necesarias...</h1><hr>";

$fixes = [
    // table => [column => sql]
    "ALTER TABLE `calificacion` ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `updated_at`",
    "ALTER TABLE `usuarios` ADD COLUMN `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP AFTER `foto_perfil`",
    "ALTER TABLE `usuarios` ADD COLUMN `ultimo_acceso` datetime DEFAULT NULL AFTER `fecha_registro`",
    "ALTER TABLE `usuarios` ADD COLUMN `estado` enum('activo','inactivo') DEFAULT 'activo' AFTER `ultimo_acceso`",
    "ALTER TABLE `usuarios` ADD COLUMN `foto_perfil` varchar(255) DEFAULT NULL AFTER `telefono`",
    "ALTER TABLE `usuarios` ADD COLUMN `ubicacion_finca` text DEFAULT NULL AFTER `estado`",
    "ALTER TABLE `usuarios` ADD COLUMN `datos_bancarios` text DEFAULT NULL AFTER `ubicacion_finca`",
    "ALTER TABLE `usuarios` ADD COLUMN `practicas_produccion` text DEFAULT NULL AFTER `datos_bancarios`",
    "ALTER TABLE `productos` ADD COLUMN `estado_aprobacion` enum('pendiente','aprobado','rechazado','inactivo') DEFAULT 'pendiente' AFTER `estado`",
    "ALTER TABLE `productos` ADD COLUMN `motivo_rechazo` text DEFAULT NULL AFTER `estado_aprobacion`",
    "ALTER TABLE `productos` ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `motivo_rechazo`",
    "ALTER TABLE `productos` ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
    "ALTER TABLE `calificacion` ADD COLUMN `tipo` enum('producto','productor','logistica') DEFAULT 'producto' AFTER `id_consumer`",
    "ALTER TABLE `calificacion` ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `comentario`",
    "ALTER TABLE `pedidos` ADD COLUMN `estado_detallado` enum('pendiente','en_camino','entregado','cancelado') DEFAULT 'pendiente' AFTER `estado`",
    "ALTER TABLE `pedidos` ADD COLUMN `notas_admin` text DEFAULT NULL AFTER `estado_detallado`",
    "ALTER TABLE `pedidos` ADD COLUMN `fecha_confirmacion` datetime DEFAULT NULL AFTER `notas_admin`",
    "ALTER TABLE `entregas` ADD COLUMN `evidencia` varchar(255) DEFAULT NULL AFTER `fecha`",
    "ALTER TABLE `entregas` ADD COLUMN `notas` text DEFAULT NULL AFTER `evidencia`",
    "ALTER TABLE `categorias` ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `descripcion`",
    "ALTER TABLE `direccion` ADD COLUMN `nombre_direccion` varchar(50) DEFAULT NULL AFTER `id_usuario`",
    "ALTER TABLE `direccion` ADD COLUMN `latitud` decimal(10,8) DEFAULT NULL AFTER `detalle`",
    "ALTER TABLE `direccion` ADD COLUMN `longitud` decimal(11,8) DEFAULT NULL AFTER `latitud`",
    "ALTER TABLE `direccion` ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `longitud`",
    "ALTER TABLE `direccion` ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
];

$count = 0;
foreach ($fixes as $sql) {
    try {
        $conn->query($sql);
        $table = preg_replace('/.*`([^`]+)`.*/', '$1', $sql);
        $col = preg_replace('/.*ADD COLUMN `([^`]+)`.*/', '$1', $sql);
        echo "<p class='ok'>✓ $table.$col</p>";
        $count++;
    } catch (\mysqli_sql_exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            $table = preg_replace('/.*`([^`]+)`.*/', '$1', $sql);
            $col = preg_replace('/.*ADD COLUMN `([^`]+)`.*/', '$1', $sql);
            echo "<p class='ok'>✓ $table.$col (ya existe)</p>";
        } else {
            echo "<p class='err'>✗ " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

echo "<hr><h2>✓ $count columnas agregadas/verificadas</h2>";
echo "<p><a href='admin.php' style='color:#157145;font-weight:bold'>Ir al panel admin</a></p>";
echo "</body></html>";
$conn->close();
