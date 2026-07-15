<?php
session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/app/Core/App.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar_perfil') {
    header('Content-Type: text/plain');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id = (int)($_SESSION['usuario']['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellido=?, telefono=? WHERE id=?");
        $stmt->bind_param('sssi', $nombre, $apellido, $telefono, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['telefono'] = $telefono;
    }
    echo 'ok';
    exit;
}

$app = new App($conn);

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? 'dashboard');

$prefixesLogistica = ['listar_logisticos', 'ver_logistico'];
$prefixesConsumidor = ['listar_consumidores', 'ver_consumidor'];
$prefixesProductor = ['listar_productores', 'ver_productor'];
$prefixesProducto = ['listar_productos', 'cambiar_estado_producto', 'crear_producto', 'editar_producto'];
$prefixesCategoria = ['listar_categorias', 'crear_categoria', 'editar_categoria', 'eliminar_categoria', 'cambiar_estado_categoria'];
$prefixesPedido = ['listar_pedidos', 'cambiar_estado_pedido',
                   'pedido_estadisticas', 'pedido_por_estado', 'pedido_por_dia',
                   'exportar_pdf_pedidos', 'exportar_excel_pedidos'];
$prefixesCalificacion = ['listar_calificaciones', 'cambiar_estado_calificacion', 'crear_reporte', 'responder_comentario', 'suspender_usuario', 'resolver_reporte'];

switch (true) {
    case in_array($accion, $prefixesLogistica):
        $controller = $app->crearAdminLogisticaController();
        break;
    case in_array($accion, $prefixesConsumidor):
        $controller = $app->crearAdminConsumidorController();
        break;
    case in_array($accion, $prefixesProducto):
        $controller = $app->crearAdminProductoController();
        break;
    case in_array($accion, $prefixesCategoria):
        $controller = $app->crearAdminCategoriaController();
        break;
    case in_array($accion, $prefixesPedido):
        $controller = $app->crearAdminPedidoController();
        break;
    case in_array($accion, $prefixesCalificacion):
        $controller = $app->crearAdminCalificacionController();
        break;
    default:
        $controller = $app->crearAdminUsuarioController();
        break;
}

$controller->handle($_GET, $_POST, $_SESSION);
$conn->close();
