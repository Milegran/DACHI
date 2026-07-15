<?php
// BLOQUE CONEXION - PATRON DE DISEÑO: SINGLETON
// Envuelve el $conn de conexion.php para que exista una sola instancia por peticion.
// El Singleton se centraliza en conexion.php para que todas las entradas
// utilicen exactamente el mismo punto de acceso a la base de datos.
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/app/Facades/SistemaDachiFacade.php';
require_once __DIR__ . '/app/Controllers/PanelController.php';

// BLOQUE SESION
session_start();

//BLOQUE ADMIN - los administradores no deben entrar aqui
if (isset($_SESSION['usuario']) && strtolower(trim($_SESSION['usuario']['nom_rol'] ?? '')) === 'administrador') {
    header('Location: admin.php');
    exit;
}

//BLOQUE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// CONTROLADOR MVC: valida sesion/rol y entrega el contexto a la vista.
$panelContext = (new PanelController())->context($_SESSION);
if ($panelContext === null) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$usuarioActual = $panelContext['usuario'];
$totalCarrito = $panelContext['totalCarrito'];
$rolActual = $panelContext['rol'];

$sistema = new SistemaDachiFacade($conn);

// BLOQUE ACCIONES (antes era acciones.php, ahora vive aqui mismo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: text/plain');
    $accion = $_POST['accion'];

    switch ($accion) {
        case 'guardar_perfil':
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellido=?, telefono=? WHERE id=?");
            $stmt->bind_param('sssi', $nombre, $apellido, $telefono, $usuarioActual['id']);
            $stmt->execute();
            $stmt->close();
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['apellido'] = $apellido;
            $_SESSION['usuario']['telefono'] = $telefono;
            echo 'ok';
            break;

        case 'toggle_producto':
            if ($rolActual !== 'admin') {
                http_response_code(403);
                die('No autorizado');
            }
            $id = (int) ($_POST['id'] ?? 0);
            $estado = (int) ($_POST['estado'] ?? 0);
            $stmt = $conn->prepare("UPDATE productos SET estado=? WHERE id=?");
            $stmt->bind_param('ii', $estado, $id);
            $stmt->execute();
            $stmt->close();
            echo 'ok';
            break;

        case 'eliminar_producto':
            if ($rolActual !== 'admin') {
                http_response_code(403);
                die('No autorizado');
            }
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM productos WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            echo 'ok';
            break;

        case 'eliminar_comentario':
            if ($rolActual !== 'admin') {
                http_response_code(403);
                die('No autorizado');
            }
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM calificacion WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            echo 'ok';
            break;

        case 'asignar_entrega':
            if ($rolActual !== 'logistico') {
                http_response_code(403);
                echo json_encode(["status" => "error", "message" => "No autorizado"]);
                break;
            }
            $idPedido = (int) ($_POST['id_pedido'] ?? 0);
            $idRepartidor = (int) $usuarioActual['id'];
            $respuesta = $sistema->asignarEntrega($idPedido, $idRepartidor);
            if ($respuesta['status'] !== 'success') {
                http_response_code(409);
            }
            echo json_encode($respuesta);
            break;

        case 'confirmar_entrega':
            if ($rolActual !== 'logistico') {
                http_response_code(403);
                echo json_encode(["status" => "error", "message" => "No autorizado"]);
                break;
            }
            $idPedido = (int) ($_POST['id_pedido'] ?? 0);
            $idRepartidor = (int) $usuarioActual['id'];
            $respuesta = $sistema->confirmarEntrega($idPedido, $idRepartidor);
            if ($respuesta['status'] !== 'success') {
                http_response_code(409);
            }
            echo json_encode($respuesta);
            break;

        default:
            http_response_code(400);
            echo 'Accion no reconocida';
    }
    exit;
}

// BLOQUE AJUSTES (APARIENCIA) - GUARDADO EN COOKIES
$ajustes = isset($_COOKIE['dachi_ajustes']) ? json_decode($_COOKIE['dachi_ajustes'], true) : [];
$fontSize = $ajustes['fontSize'] ?? 'mediano';
$darkMode = !empty($ajustes['darkMode']);
$fontSizesPx = ['pequeno' => '14px', 'mediano' => '16px', 'grande' => '18px'];

// BLOQUE DATOS: PRODUCTOS
$productos = [];
{
    $resProd = $conn->query("SELECT id, nombre, descripcion, precio, imagen, nom_productor, cantidad, estado FROM productos ORDER BY id ASC");
    while ($p = $resProd->fetch_assoc()) {
        $stmtCal = $conn->prepare("SELECT calificacion.calificacion, calificacion.comentario, usuarios.nombre AS nombre_consumer
            FROM calificacion JOIN usuarios ON calificacion.id_consumer = usuarios.id WHERE calificacion.id_producto = ?");
        $stmtCal->bind_param('i', $p['id']);
        $stmtCal->execute();
        $calRes = $stmtCal->get_result();
        $ratings = [];
        while ($c = $calRes->fetch_assoc())
            $ratings[] = $c;
        $stmtCal->close();

        $p['ratings'] = $ratings;
        $p['promedio'] = count($ratings) ? round(array_sum(array_column($ratings, 'calificacion')) / count($ratings), 1) : 0;
        $productos[] = $p;
    }
}

// BLOQUE DATOS: USUARIOS
$usuarios = [];
if ($rolActual === 'admin') {
    $resUsr = $conn->query("SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, r.nom_rol
        FROM usuarios u JOIN rol r ON u.id_rol = r.id ORDER BY u.id DESC");
    while ($u = $resUsr->fetch_assoc())
        $usuarios[] = $u;
}

// BLOQUE DATOS: PEDIDOS
$ESTADOS_PEDIDO = [0 => 'pendiente', 1 => 'en camino', 2 => 'entregado'];
$pedidos = [];
$sqlPedidos = "SELECT p.id, p.fecha, p.total_compra, p.estado, p.id_consumer,
            u.nombre AS comprador_nombre, u.apellido AS comprador_apellido, u.correo AS comprador_correo,
            d.provincia AS zona,
            e.id_repartidor, ur.nombre AS repartidor_nombre
     FROM pedidos p
     JOIN usuarios u ON p.id_consumer = u.id
     LEFT JOIN direccion d ON d.id_usuario = u.id
     LEFT JOIN entregas e ON e.id_pedidos = p.id
     LEFT JOIN usuarios ur ON ur.id = e.id_repartidor";
if ($rolActual === 'consumidor') {
    $sqlPedidos .= " WHERE p.id_consumer = ?";
    $stmtPedidos = $conn->prepare($sqlPedidos . " ORDER BY p.id DESC");
    $idConsumidorSesion = (int) $usuarioActual['id'];
    $stmtPedidos->bind_param('i', $idConsumidorSesion);
    $stmtPedidos->execute();
    $resPed = $stmtPedidos->get_result();
} else {
    $resPed = $conn->query($sqlPedidos . " ORDER BY p.id DESC");
}
while ($ped = $resPed->fetch_assoc()) {
    $stmtItems = $conn->prepare("SELECT ip.cantidad, ip.precio_unitario, ip.subtotal, pr.nombre AS producto_nombre, pr.id AS producto_id
         FROM info_pedidos ip JOIN productos pr ON ip.id_productos = pr.id WHERE ip.id_pedidos = ?");
    $stmtItems->bind_param('i', $ped['id']);
    $stmtItems->execute();
    $itemsRes = $stmtItems->get_result();
    $items = [];
    while ($it = $itemsRes->fetch_assoc())
        $items[] = $it;
    $stmtItems->close();

    $ped['items'] = $items;
    $ped['estado_label'] = $ESTADOS_PEDIDO[(int) $ped['estado']] ?? 'pendiente';
    $pedidos[] = $ped;
}
if (isset($stmtPedidos)) {
    $stmtPedidos->close();
}

$pedidosPendientes = count(array_filter($pedidos, static fn(array $pedido): bool => (int) $pedido['estado'] < 2));
$productosSinStock = count(array_filter($productos, static fn(array $producto): bool => (int) $producto['estado'] === 1 && (int) $producto['cantidad'] <= 0));
$inventoryNotificationTarget = match ($rolActual) {
    'admin' => 'sec-productos',
    'productor' => 'dist-inventario',
    default => null
};
$notificationCount = $rolActual === 'consumidor'
    ? (($totalCarrito > 0 ? 1 : 0) + ($pedidosPendientes > 0 ? 1 : 0))
    : ((($inventoryNotificationTarget !== null && $productosSinStock > 0) ? 1 : 0) + ($pedidosPendientes > 0 ? 1 : 0));

// BLOQUE DATOS: COMENTARIOS
$comentarios = [];
if (in_array($rolActual, ['admin', 'productor'], true)) {
    $resCom = $conn->query(
        "SELECT c.id, c.comentario, c.calificacion, u.nombre AS consumidor, pr.nombre AS producto, pr.id AS producto_id
         FROM calificacion c
         JOIN usuarios u ON c.id_consumer = u.id
         JOIN productos pr ON c.id_producto = pr.id
         ORDER BY c.id DESC"
    );
    while ($c = $resCom->fetch_assoc())
        $comentarios[] = $c;
}
?>
<!DOCTYPE html>
<html class="light<?= $darkMode ? ' dark' : '' ?>" lang="es" style="font-size:<?= $fontSizesPx[$fontSize] ?>">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Panel de Control</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="css/dachi-brand.css" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#003118", "primary-container": "#16482b", "on-primary-container": "#83b691",
                        "primary-fixed": "#b9efc7", "primary-fixed-dim": "#9ed3ac", "on-primary-fixed": "#00210e",
                        "on-primary-fixed-variant": "#1f5032", "secondary": "#795900", "secondary-container": "#ffc641",
                        "on-secondary-container": "#715300", "secondary-fixed": "#ffdfa0", "secondary-fixed-dim": "#f6be39",
                        "on-secondary-fixed": "#261a00", "tertiary": "#491a21", "tertiary-container": "#642f36",
                        "on-tertiary-container": "#e0979f", "tertiary-fixed": "#ffdadc", "tertiary-fixed-dim": "#ffb2b9",
                        "on-tertiary-fixed": "#370c14", "on-tertiary-fixed-variant": "#6d363d", "surface": "#f7faf5",
                        "surface-dim": "#d8dbd6", "surface-bright": "#f7faf5", "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f1f4f0", "surface-container": "#ecefea", "surface-container-high": "#e6e9e4",
                        "surface-container-highest": "#e0e3df", "on-surface": "#191c1a", "on-surface-variant": "#414942",
                        "outline": "#717971", "outline-variant": "#c1c9bf", "background": "#f7faf5", "on-background": "#191c1a",
                        "error": "#ba1a1a", "error-container": "#ffdad6", "on-error": "#ffffff", "on-error-container": "#93000a",
                        "inverse-surface": "#2d312e", "inverse-on-surface": "#eff2ed", "inverse-primary": "#9ed3ac", "surface-tint": "#386848"
                    },
                    "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                    "spacing": { "stack-sm": "8px", "stack-md": "16px", "margin-mobile": "16px", "stack-lg": "32px", "stack-xl": "64px", "container-max": "1280px", "gutter": "24px", "base": "8px", "margin-desktop": "48px" },
                    "fontFamily": { "headline-sm": ["\"Source Serif 4\""], "label-md": ["Hanken Grotesk"], "display-lg-mobile": ["\"Source Serif 4\""], "body-lg": ["Hanken Grotesk"], "headline-md": ["\"Source Serif 4\""], "display-lg": ["\"Source Serif 4\""], "body-md": ["Hanken Grotesk"], "label-sm": ["Hanken Grotesk"] },
                    "fontSize": { "headline-sm": ["24px", { "lineHeight": "32px", "fontWeight": "600" }], "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "600" }], "display-lg-mobile": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }], "headline-md": ["32px", { "lineHeight": "40px", "fontWeight": "600" }], "display-lg": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }], "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }] }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .shadow-forest {
            box-shadow: 0px 4px 20px rgba(22, 72, 43, 0.04);
        }

        .shadow-forest-active {
            box-shadow: 0px 10px 30px rgba(22, 72, 43, 0.08);
        }

        .table-row-hover:hover {
            background-color: rgba(241, 244, 240, 0.5);
        }

        .sidebar-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .glass-container {
            background: radial-gradient(circle at top left, rgba(185, 239, 199, 0.15), transparent 40%), radial-gradient(circle at bottom right, rgba(255, 223, 160, 0.15), transparent 40%);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f7faf5;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c9bf;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #717971;
        }

        body {
            background-color: #f7faf5;
        }

        .user-menu {
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: none;
            transition: all .15s ease;
        }

        .user-menu.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .modal-overlay {
            transition: opacity .2s ease;
        }

        html.dark body {
            background-color: #10130f;
            color: #dfe5dc;
        }

        html.dark .glass-container {
            background: radial-gradient(circle at top left, rgba(185, 239, 199, 0.04), transparent 40%), radial-gradient(circle at bottom right, rgba(255, 223, 160, 0.04), transparent 40%);
        }

        html.dark .bg-surface,
        html.dark .bg-background {
            background-color: #10130f !important;
        }

        html.dark .bg-surface-container-lowest,
        html.dark .bg-white {
            background-color: #1c211b !important;
        }

        html.dark .bg-surface-container-low {
            background-color: #20261f !important;
        }

        html.dark .bg-surface-container {
            background-color: #242b23 !important;
        }

        html.dark .bg-surface-container-high {
            background-color: #2b3229 !important;
        }

        html.dark .bg-surface-container-highest,
        html.dark .bg-surface-container-highest\/80 {
            background-color: #333c30 !important;
        }

        html.dark .glass-card {
            background: rgba(28, 33, 27, 0.75) !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        html.dark .shadow-forest,
        html.dark .shadow-forest-active,
        html.dark .forest-shadow,
        html.dark .forest-shadow-active {
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.35) !important;
        }

        html.dark .text-on-surface,
        html.dark .text-on-background {
            color: #e5e9e2 !important;
        }

        html.dark .text-on-surface-variant {
            color: #9fab9a !important;
        }

        html.dark .text-primary {
            color: #8fd6a3 !important;
        }

        html.dark .text-on-primary {
            color: #02160c !important;
        }

        html.dark .text-secondary {
            color: #f6be39 !important;
        }

        html.dark .bg-primary {
            background-color: #3a7a55 !important;
        }

        html.dark .bg-primary-fixed {
            background-color: #1e3b28 !important;
        }

        html.dark .bg-primary-fixed\/20,
        html.dark .bg-primary-fixed\/30 {
            background-color: rgba(143, 214, 163, 0.12) !important;
        }

        html.dark .bg-primary-container\/10 {
            background-color: rgba(143, 214, 163, 0.08) !important;
        }

        html.dark .text-on-primary-fixed {
            color: #dfe5dc !important;
        }

        html.dark .bg-secondary-container {
            background-color: #5c4200 !important;
            color: #ffdfa0 !important;
        }

        html.dark .text-on-secondary-container {
            color: #ffdfa0 !important;
        }

        html.dark .bg-secondary-fixed,
        html.dark .bg-secondary-fixed\/30 {
            background-color: rgba(246, 190, 57, 0.18) !important;
        }

        html.dark .border-outline-variant {
            border-color: #333c30 !important;
        }

        html.dark .border-outline {
            border-color: #4a5546 !important;
        }

        html.dark .table-row-hover:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        html.dark input,
        html.dark select,
        html.dark textarea {
            background-color: #1c211b !important;
            color: #e5e9e2 !important;
            border-color: #333c30 !important;
        }

        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #77826f !important;
        }

        html.dark .bg-primary-fixed\/10 {
            background-color: rgba(143, 214, 163, 0.06) !important;
        }

        html.dark ::-webkit-scrollbar-track {
            background: #10130f !important;
        }

        html.dark ::-webkit-scrollbar-thumb {
            background: #333c30 !important;
        }

        /* BLOQUE: CLASES CON OPACIDAD QUE FALTABAN (causaban el gris "lavado" en modo oscuro) */
        html.dark .bg-surface\/80 {
            background-color: rgba(16, 19, 15, 0.85) !important;
        }

        html.dark .bg-surface-container-highest\/40 {
            background-color: rgba(51, 60, 48, 0.5) !important;
        }

        html.dark .bg-surface-container-low\/30 {
            background-color: rgba(32, 38, 31, 0.4) !important;
        }

        html.dark .bg-surface-container\/30 {
            background-color: rgba(36, 43, 35, 0.4) !important;
        }

        html.dark .bg-secondary-container\/80 {
            background-color: rgba(92, 66, 0, 0.8) !important;
            color: #ffdfa0 !important;
        }

        html.dark .bg-primary-fixed\/40 {
            background-color: rgba(143, 214, 163, 0.16) !important;
        }

        html.dark .bg-primary-fixed\/60 {
            background-color: rgba(143, 214, 163, 0.22) !important;
        }

        html.dark .bg-primary-fixed\/80 {
            background-color: rgba(143, 214, 163, 0.28) !important;
        }

        html.dark .bg-primary\/10 {
            background-color: rgba(58, 122, 85, 0.18) !important;
        }

        html.dark .bg-error-container\/40 {
            background-color: rgba(147, 0, 10, 0.25) !important;
            color: #ffb4ab !important;
        }

        html.dark .bg-tertiary-fixed\/30 {
            background-color: rgba(255, 178, 185, 0.12) !important;
        }

        html.dark .bg-white\/10 {
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        html.dark .border-outline-variant\/30 {
            border-color: rgba(51, 60, 48, 0.5) !important;
        }

        html.dark .border-outline-variant\/50 {
            border-color: rgba(51, 60, 48, 0.7) !important;
        }

        html.dark .border-primary-container\/20 {
            border-color: rgba(22, 72, 43, 0.3) !important;
        }

        html.dark .border-white\/20 {
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        html.dark .text-on-surface-variant\/60 {
            color: rgba(159, 171, 154, 0.7) !important;
        }

        html.dark .text-primary-fixed-dim\/80 {
            color: rgba(158, 211, 172, 0.85) !important;
        }

        html.dark .text-primary-fixed\/70 {
            color: rgba(185, 239, 199, 0.75) !important;
        }

        /* BLOQUE: ESTADOS HOVER QUE FALTABAN (causaban el fondo blanco al pasar el cursor) */
        html.dark .hover\:bg-surface-container-low:hover {
            background-color: #20261f !important;
        }

        html.dark .hover\:bg-primary-fixed:hover {
            background-color: #1e3b28 !important;
        }

        html.dark .hover\:bg-primary-fixed\/20:hover {
            background-color: rgba(143, 214, 163, 0.12) !important;
        }

        html.dark .hover\:bg-primary-container:hover {
            background-color: #16482b !important;
            color: #dfe5dc !important;
        }

        html.dark .hover\:bg-error-container:hover,
        html.dark .hover\:bg-error-container\/40:hover {
            background-color: rgba(147, 0, 10, 0.25) !important;
        }

        html.dark .hover\:text-primary:hover {
            color: #8fd6a3 !important;
        }
    </style>
    <link href="css/dachi-botanical.css" rel="stylesheet" />
</head>

<body class="dachi-app bg-background text-on-background font-body-md min-h-screen glass-container">
    <!-- BLOQUE SIDEBAR OVERLAY -->
    <div class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity" id="sidebarOverlay"
        onclick="toggleSidebar()"></div>
    <!-- BLOQUE SIDEBAR -->
    <aside
        class="fixed left-0 top-0 h-full w-72 bg-surface-container-lowest border-r border-outline-variant z-[70] -translate-x-full sidebar-transition flex flex-col"
        id="sidebar">
        <div class="p-gutter flex items-center justify-between border-b border-outline-variant h-16">
            <div class="dachi-sidebar-brand">
                <img src="img/LG.png" alt="DACHI" />
                <span>Gesti&oacute;n agr&iacute;cola</span>
            </div>
            <button class="dachi-sidebar-close p-2 hover:bg-surface-container-low rounded-full" onclick="toggleSidebar()" title="Compactar men&uacute;" type="button">
                <span class="material-symbols-outlined dachi-sidebar-close-icon">left_panel_close</span>
            </button>
        </div>
        <nav class="p-4 space-y-2 flex-grow" id="sidebarNav"></nav>
        <div class="p-4 border-t border-outline-variant">
            <button
                class="w-full flex items-center gap-4 p-3 rounded-xl text-error hover:bg-error-container/40 transition-all"
                onclick="logout()">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-md">Cerrar Sesión</span>
            </button>
        </div>
    </aside>
    <!-- BLOQUE NAVBAR -->
    <nav class="dachi-topbar bg-surface/80 backdrop-blur-md w-full top-0 sticky z-50 border-b border-outline-variant shadow-sm">
        <div class="flex items-center gap-3 px-gutter max-w-container-max mx-auto h-[72px]">
            <div class="dachi-mobile-brand flex items-center gap-stack-md flex-none">
                <button class="dachi-sidebar-toggle p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-colors"
                    onclick="toggleSidebar()" title="Abrir men&uacute;" type="button">
                    <span class="material-symbols-outlined text-primary dachi-sidebar-toggle-icon">menu</span>
                </button>
                <img alt="DACHI" class="dachi-topbar-logo" src="img/LG.png" />
            </div>

            <?php if ($rolActual === 'consumidor'): ?>
                <div class="dachi-global-search relative flex-1 max-w-2xl mx-auto">
                    <span class="material-symbols-outlined">search</span>
                    <input autocomplete="off" id="globalProductSearch" maxlength="60"
                        oninput="filterConsumerProducts(this.value)"
                        placeholder="Buscar productos o productores..." type="search" />
                    <div class="dachi-search-results hidden" id="globalSearchResults"></div>
                </div>
            <?php else: ?>
                <div class="flex-1"></div>
            <?php endif; ?>

            <div class="dachi-topbar-actions flex items-center gap-1 sm:gap-2 relative flex-none">
                <?php if ($rolActual === 'consumidor'): ?>
                    <a class="dachi-icon-button relative" href="public/carrito.php" title="Ver carrito">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span class="<?= $totalCarrito > 0 ? '' : 'hidden ' ?>dachi-action-badge" id="panelCartCount"><?= $totalCarrito ?></span>
                    </a>
                <?php endif; ?>

                <div class="relative">
                    <button class="dachi-icon-button relative" id="notificationButton" onclick="toggleNotificationMenu()"
                        title="Notificaciones" type="button">
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="<?= $notificationCount > 0 ? '' : 'hidden ' ?>dachi-action-badge" id="notificationCount"><?= $notificationCount ?></span>
                    </button>
                    <div class="dachi-notification-menu hidden" id="notificationMenu">
                        <div class="dachi-popover-heading">
                            <div>
                                <p>Notificaciones</p>
                                <span>Actividad reciente de tu cuenta</span>
                            </div>
                            <span class="material-symbols-outlined">notifications</span>
                        </div>
                        <div class="dachi-notification-list">
                            <?php if ($rolActual === 'consumidor' && $totalCarrito > 0): ?>
                                <a href="public/carrito.php">
                                    <span class="material-symbols-outlined">shopping_bag</span>
                                    <div><strong>Carrito pendiente</strong><small>Tienes <?= $totalCarrito ?> unidad<?= $totalCarrito === 1 ? '' : 'es' ?> por confirmar.</small></div>
                                </a>
                            <?php endif; ?>
                            <?php if ($pedidosPendientes > 0): ?>
                                <a href="<?= $rolActual === 'consumidor' ? 'public/seguimiento.php' : '#sec-dashboard' ?>">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                    <div><strong>Pedidos en proceso</strong><small><?= $pedidosPendientes ?> pedido<?= $pedidosPendientes === 1 ? '' : 's' ?> requiere<?= $pedidosPendientes === 1 ? '' : 'n' ?> seguimiento.</small></div>
                                </a>
                            <?php endif; ?>
                            <?php if ($inventoryNotificationTarget !== null && $productosSinStock > 0): ?>
                                <button onclick="showSection('<?= $inventoryNotificationTarget ?>', document.querySelector('[data-target=<?= $inventoryNotificationTarget ?>]')); toggleNotificationMenu()" type="button">
                                    <span class="material-symbols-outlined">inventory</span>
                                    <div><strong>Inventario agotado</strong><small><?= $productosSinStock ?> producto<?= $productosSinStock === 1 ? '' : 's' ?> sin stock.</small></div>
                                </button>
                            <?php endif; ?>
                            <?php if ($notificationCount === 0): ?>
                                <div class="dachi-notification-empty">
                                    <span class="material-symbols-outlined">task_alt</span>
                                    <p>Todo est&aacute; al d&iacute;a.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <button class="dachi-icon-button" onclick="openSettingsModal()" title="Configuraci&oacute;n" type="button">
                    <span class="material-symbols-outlined">settings</span>
                </button>
                <span class="dachi-topbar-divider hidden sm:block"></span>
                <div class="hidden md:block text-right mr-1">
                    <span class="font-semibold text-sm text-on-surface block" id="panelUserName"></span>
                    <small class="text-on-surface-variant"><?= htmlspecialchars(ucfirst($rolActual), ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <button
                    class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden flex items-center justify-center font-bold text-primary cursor-pointer"
                    id="userAvatarBtn" onclick="toggleUserMenu()" title="Abrir perfil" type="button">
                    <span class="avatar-initial" id="panelUserAvatar">?</span>
                </button>
                <div class="user-menu absolute right-0 top-12 w-72 bg-white rounded-2xl border border-outline-variant/50 shadow-xl p-2 z-[200]"
                    id="userMenu">
                    <div class="flex items-center gap-3 p-3 border-b border-outline-variant/30 mb-2">
                        <div
                            class="w-12 h-12 rounded-full bg-primary-fixed overflow-hidden border border-outline-variant flex items-center justify-center font-bold text-primary flex-none">
                            <span class="avatar-initial" id="menuInitial">?</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-label-md text-label-md text-on-surface truncate" id="userMenuName">Usuario
                            </p>
                            <p class="text-label-sm text-on-surface-variant truncate" id="userMenuEmail"></p>
                            <span
                                class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide"
                                id="menuRoleTag">ROL</span>
                        </div>
                    </div>
                    <button
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-on-surface flex items-center gap-3"
                        onclick="logout()">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">swap_horiz</span>
                        Cambiar Cuenta
                    </button>
                    <button
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-on-surface flex items-center gap-3"
                        onclick="openSettingsModal()">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">tune</span>
                        Configuración
                    </button>
                    <button
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-on-surface flex items-center gap-3"
                        onclick="openProfileModal()">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span> Editar
                        Perfil
                    </button>
                    <hr class="border-outline-variant/50 my-1" />
                    <button
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-error flex items-center gap-3"
                        onclick="logout()">
                        <span class="material-symbols-outlined text-[18px]">logout</span> Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-stack-lg relative z-10">
        <div class="flex flex-col md:flex-row md:justify-between md:items-end mb-stack-lg gap-4">
            <div>
                <h1 class="font-display-lg text-headline-md text-primary" id="panelTitle">Panel de Control</h1>
                <p class="text-on-surface-variant font-body-md mt-1" id="roleSubtitle"></p>
            </div>
        </div>

        <!-- BLOQUE ADMIN: DASHBOARD -->
        <section class="space-y-stack-xl hidden" id="sec-dashboard">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-primary-fixed/30 flex items-center justify-center text-primary mb-6">
                        <span class="material-symbols-outlined font-bold">inventory_2</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Productos</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiProducts">0</h2>
                </div>
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-secondary-fixed/30 flex items-center justify-center text-secondary mb-6">
                        <span class="material-symbols-outlined font-bold">group</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Usuarios</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiUsers">0</h2>
                </div>
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-tertiary-fixed/30 flex items-center justify-center text-tertiary mb-6">
                        <span class="material-symbols-outlined font-bold">local_shipping</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Pedidos Totales
                    </p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiOrders">0</h2>
                </div>
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-primary-container/10 flex items-center justify-center border border-primary-container/20 text-primary mb-6">
                        <span class="material-symbols-outlined font-bold">payments</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Ingresos</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiRevenue">$0.00</h2>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
                <div class="lg:col-span-2 glass-card rounded-[32px] shadow-forest overflow-hidden flex flex-col">
                    <div class="px-gutter py-6 flex justify-between items-center">
                        <h3 class="font-headline-sm text-primary text-[22px]">Pedidos Recientes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-surface-container-low/30 backdrop-blur-sm">
                                <tr>
                                    <th
                                        class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                        Pedido</th>
                                    <th
                                        class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                        Cliente</th>
                                    <th
                                        class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                        Estado</th>
                                    <th
                                        class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/30" id="recentOrdersBody"></tbody>
                        </table>
                    </div>
                </div>
                <div
                    class="dachi-reputation-card bg-primary-container text-on-primary-container p-gutter rounded-[32px] flex flex-col shadow-xl border border-primary relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10"><span
                            class="material-symbols-outlined text-[120px]">monitoring</span></div>
                    <div class="relative z-10">
                        <h3 class="font-headline-sm text-white text-[22px] mb-1">Calidad y Reputación</h3>
                        <p class="font-body-md text-primary-fixed-dim/80 text-[14px]">Basado en calificaciones de
                            consumidores</p>
                    </div>
                    <div class="mt-8 space-y-6 relative z-10">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-label-sm font-bold text-white">Calificación promedio</span>
                                <span class="font-bold text-primary-fixed" id="avgRatingValue">0.0</span>
                            </div>
                            <div class="w-full h-2.5 bg-black/30 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-fixed transition-all duration-700" id="avgRatingBar"
                                    style="width:0%"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white/10 p-4 rounded-2xl border border-white/20">
                            <div
                                class="w-10 h-10 rounded-full bg-secondary-fixed/20 flex items-center justify-center text-secondary-fixed">
                                <span class="material-symbols-outlined">forum</span></div>
                            <div class="text-[13px]">
                                <p class="font-bold text-white" id="commentCountText">0 comentarios</p>
                                <p class="text-primary-fixed/70">Registrados en el catálogo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BLOQUE ADMIN: USUARIOS -->
        <section class="space-y-stack-lg hidden" id="sec-usuarios">
            <div class="glass-card rounded-[32px] shadow-forest overflow-hidden">
                <div class="px-gutter py-6">
                    <h3 class="font-headline-sm text-primary text-[22px]">Usuarios Registrados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low/30">
                            <tr>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Nombre</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Correo</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Rol</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Teléfono</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30" id="usersBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- BLOQUE ADMIN: PRODUCTOS -->
        <section class="space-y-stack-lg hidden" id="sec-productos">
            <div class="glass-card rounded-[32px] shadow-forest overflow-hidden">
                <div class="px-gutter py-6">
                    <h3 class="font-headline-sm text-primary text-[22px]">Moderación de Productos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low/30">
                            <tr>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Producto</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Productor</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Precio</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Calificación</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Estado</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md text-center">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30" id="productsAdminBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- BLOQUE ADMIN: COMENTARIOS -->
        <section class="space-y-stack-lg hidden" id="sec-comentarios">
            <div class="glass-card rounded-[32px] shadow-forest p-gutter">
                <h3 class="font-headline-sm text-primary text-[22px] mb-6">Comentarios de Consumidores</h3>
                <div class="space-y-4" id="commentsAdminList"></div>
            </div>
        </section>

        <!-- BLOQUE ADMIN: LOGISTICA -->
        <section class="space-y-stack-lg hidden" id="sec-logistica-admin">
            <div class="glass-card rounded-[32px] shadow-forest p-gutter">
                <h3 class="font-headline-sm text-primary text-[22px] mb-6">Monitoreo de Entregas</h3>
                <div class="space-y-4" id="logisticaAdminList"></div>
            </div>
        </section>

        <!-- BLOQUE REPARTIDOR: DISPONIBLES -->
        <section class="space-y-stack-lg hidden" id="sec-disponibles">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-headline-sm text-primary flex items-center gap-3">
                    <span
                        class="material-symbols-outlined text-primary bg-primary-fixed/20 p-2 rounded-xl">explore</span>
                    Entregas Disponibles
                </h3>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter" id="availableDeliveriesList"></div>
            <p class="hidden text-center py-16 text-on-surface-variant" id="noAvailable">No hay entregas disponibles por
                el momento.</p>
        </section>

        <!-- BLOQUE REPARTIDOR: ACTIVA -->
        <section class="space-y-stack-lg hidden" id="sec-activa">
            <h3 class="font-headline-sm text-primary flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary bg-primary-fixed/20 p-2 rounded-xl">package_2</span>
                Mi Entrega Activa
            </h3>
            <div id="activeDeliveryContainer"></div>
        </section>

        <!-- BLOQUE REPARTIDOR: HISTORIAL -->
        <section class="space-y-stack-lg hidden" id="sec-historial">
            <h3 class="font-headline-sm text-primary flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary bg-primary-fixed/20 p-2 rounded-xl">history</span>
                Historial de Entregas
            </h3>
            <div class="space-y-4" id="historyList"></div>
        </section>

        <!-- BLOQUE DISTRIBUIDOR: DASHBOARD -->
        <section class="space-y-stack-xl hidden" id="dist-dashboard">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-primary-fixed/30 flex items-center justify-center text-primary mb-6">
                        <span class="material-symbols-outlined font-bold">inventory_2</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Productos
                        Disponibles</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiInventario">0</h2>
                </div>
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-secondary-fixed/30 flex items-center justify-center text-secondary mb-6">
                        <span class="material-symbols-outlined font-bold">agriculture</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Productores
                        Asociados</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiProductores">0</h2>
                </div>
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-tertiary-fixed/30 flex items-center justify-center text-tertiary mb-6">
                        <span class="material-symbols-outlined font-bold">local_shipping</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Pedidos en
                        Proceso</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiPedidosProceso">0</h2>
                </div>
                <div class="glass-card p-6 rounded-[24px] shadow-forest">
                    <div
                        class="w-12 h-12 rounded-2xl bg-primary-container/10 flex items-center justify-center border border-primary-container/20 text-primary mb-6">
                        <span class="material-symbols-outlined font-bold">payments</span></div>
                    <p class="text-label-sm uppercase tracking-widest text-on-surface-variant font-bold">Valor de
                        Inventario</p>
                    <h2 class="text-headline-md font-bold text-primary" id="kpiValorInventario">$0.00</h2>
                </div>
            </div>
            <div class="glass-card rounded-[32px] shadow-forest overflow-hidden">
                <div class="px-gutter py-6">
                    <h3 class="font-headline-sm text-primary text-[22px]">Pedidos Recientes en Proceso</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low/30 backdrop-blur-sm">
                            <tr>
                                <th
                                    class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                    Pedido</th>
                                <th
                                    class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                    Zona</th>
                                <th
                                    class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                    Cliente</th>
                                <th
                                    class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                    Estado</th>
                                <th
                                    class="px-gutter py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30" id="distribuidorOrdersBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- BLOQUE DISTRIBUIDOR: INVENTARIO -->
        <section class="space-y-stack-lg hidden" id="dist-inventario">
            <div class="glass-card rounded-[32px] shadow-forest overflow-hidden">
                <div class="px-gutter py-6">
                    <h3 class="font-headline-sm text-primary text-[22px]">Inventario Disponible por Productor</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low/30">
                            <tr>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Producto</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Productor</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Cantidad</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Precio</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Valor Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30" id="inventarioBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- BLOQUE DISTRIBUIDOR: PRODUCTORES -->
        <section class="space-y-stack-lg hidden" id="dist-productores">
            <div class="glass-card rounded-[32px] shadow-forest overflow-hidden">
                <div class="px-gutter py-6">
                    <h3 class="font-headline-sm text-primary text-[22px]">Productores Asociados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low/30">
                            <tr>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Productor</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Productos Activos</th>
                                <th
                                    class="px-gutter py-4 text-[11px] uppercase tracking-wider text-on-surface-variant font-label-md">
                                    Unidades Disponibles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30" id="productoresBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- BLOQUE DISTRIBUIDOR: PEDIDOS -->
        <section class="space-y-stack-lg hidden" id="dist-pedidos">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-headline-sm text-primary flex items-center gap-3">
                    <span
                        class="material-symbols-outlined text-primary bg-primary-fixed/20 p-2 rounded-xl">local_shipping</span>
                    Pedidos en Movimiento
                </h3>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter" id="pedidosDistribuidorList"></div>
            <p class="hidden text-center py-16 text-on-surface-variant" id="noPedidosDistribuidor">No hay pedidos en
                proceso por el momento.</p>
        </section>

        <!-- BLOQUE CONSUMIDOR: INICIO -->
        <section class="space-y-stack-lg hidden" id="consumer-dashboard">
            <div class="consumer-brand-banner" aria-labelledby="dachiCommitmentTitle" role="region">
                <div class="consumer-brand-banner-content">
                    <p class="consumer-brand-banner-eyebrow">DACHI &middot; DEL CAMPO A TU MESA</p>
                    <h2 id="dachiCommitmentTitle">Cultivamos confianza en cada entrega</h2>
                    <p>Detr&aacute;s de cada producto hay dedicaci&oacute;n, manos expertas y el compromiso de quienes trabajan cada d&iacute;a para llevar calidad paname&ntilde;a hasta tu hogar.</p>
                    <div class="consumer-brand-values" aria-label="Valores DACHI">
                        <span><span class="material-symbols-outlined">handshake</span> Compromiso</span>
                        <span><span class="material-symbols-outlined">verified</span> Calidad</span>
                        <span><span class="material-symbols-outlined">groups</span> Trabajo en equipo</span>
                    </div>
                </div>
            </div>

            <div class="consumer-offers-section" aria-labelledby="consumerOffersTitle" role="region">
                <div class="consumer-catalog-heading">
                    <div>
                        <p class="consumer-eyebrow">Precios de temporada</p>
                        <h2 id="consumerOffersTitle">Ofertas para ti</h2>
                        <p>Descubre productos locales seleccionados a un precio especial.</p>
                    </div>
                    <div class="consumer-rail-controls">
                        <button onclick="changeConsumerOffer(-1)" title="Oferta anterior" type="button">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </button>
                        <button onclick="changeConsumerOffer(1)" title="Siguiente oferta" type="button">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </div>
                <div class="consumer-offers-viewport" id="consumerOffersViewport"
                    onfocusin="pauseConsumerOfferAutoplay()" onfocusout="startConsumerOfferAutoplay()"
                    onmouseenter="pauseConsumerOfferAutoplay()" onmouseleave="startConsumerOfferAutoplay()">
                    <div class="consumer-offers-track" id="consumerOffersTrack"
                        ontransitionend="handleConsumerOfferTransition()"></div>
                </div>
            </div>

            <div class="consumer-catalog-section" id="productos">
                <div class="consumer-catalog-heading">
                    <div>
                        <p class="consumer-eyebrow">Mercado local</p>
                        <h2>Productos disponibles</h2>
                        <p>Selecciona productos frescos de productores paname&ntilde;os.</p>
                    </div>
                </div>
                <div class="consumer-cart-message hidden" id="consumerCartMessage" role="status"></div>
                <div class="consumer-product-grid" id="consumerProductRail"></div>
                <div class="consumer-product-empty hidden" id="consumerProductEmpty">
                    <span class="material-symbols-outlined">search_off</span>
                    <h3>No encontramos productos</h3>
                    <p>Prueba con otro nombre o productor.</p>
                </div>
                <div class="consumer-product-pagination" id="consumerProductPagination">
                    <p id="consumerProductRange"></p>
                    <div id="consumerProductPages"></div>
                </div>
            </div>

            <div class="consumer-home-grid">
                <a class="consumer-orders-card" href="public/seguimiento.php">
                    <span class="material-symbols-outlined">package_2</span>
                    <div>
                        <p class="consumer-eyebrow">Pedidos</p>
                        <h3>Seguimiento e historial</h3>
                        <p><?= $pedidosPendientes > 0 ? $pedidosPendientes . ' pedido' . ($pedidosPendientes === 1 ? '' : 's') . ' en proceso.' : 'No tienes pedidos pendientes.' ?></p>
                    </div>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </section>

    </main>
    <footer class="bg-surface-container-highest/40 backdrop-blur-md w-full mt-stack-xl border-t border-outline-variant">
        <div
            class="flex flex-col md:flex-row justify-between items-center px-margin-desktop py-stack-lg w-full max-w-container-max mx-auto">
            <div class="mb-stack-md md:mb-0">
                <span class="dachi-wordmark text-outline text-[14px]">DACHI</span>
                <p class="text-on-surface-variant font-body-md text-[13px]">© 2026 DACHI. Cultivando confianza.</p>
            </div>
        </div>
    </footer>

    <!-- BLOQUE DETALLE DE PRODUCTO DEL CONSUMIDOR -->
    <div class="consumer-product-drawer hidden" id="consumerProductDrawer" onclick="if (event.target === this) closeConsumerProduct()">
        <aside aria-labelledby="consumerDrawerName" aria-modal="true" role="dialog">
            <div class="consumer-drawer-topbar">
                <div>
                    <p class="consumer-eyebrow">Detalle del producto</p>
                    <span>Informaci&oacute;n, procedencia y rese&ntilde;as</span>
                </div>
                <button onclick="closeConsumerProduct()" title="Cerrar detalle" type="button"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="consumer-drawer-content">
                <div class="consumer-drawer-hero">
                    <div class="consumer-drawer-gallery">
                        <div class="consumer-drawer-main-image">
                            <button onclick="changeConsumerGallery(-1)" title="Imagen anterior" type="button"><span class="material-symbols-outlined">chevron_left</span></button>
                            <img alt="" id="consumerDrawerImage" src="" />
                            <button onclick="changeConsumerGallery(1)" title="Siguiente imagen" type="button"><span class="material-symbols-outlined">chevron_right</span></button>
                        </div>
                        <div class="consumer-drawer-thumbnails" id="consumerDrawerThumbnails"></div>
                    </div>
                    <div class="consumer-drawer-summary">
                        <div class="consumer-drawer-badges">
                            <span id="consumerDrawerStock"></span>
                            <span><span class="material-symbols-outlined">star</span><b id="consumerDrawerRating">0.0</b><small id="consumerDrawerRatingCount"></small></span>
                        </div>
                        <h2 id="consumerDrawerName"></h2>
                        <p class="consumer-drawer-origin"><span class="material-symbols-outlined">location_on</span><span id="consumerDrawerOrigin"></span></p>
                        <strong class="consumer-drawer-price" id="consumerDrawerPrice"></strong>
                        <div class="consumer-drawer-producer">
                            <div class="consumer-drawer-producer-heading">
                                <span class="material-symbols-outlined">agriculture</span>
                                <div>
                                    <small>PRODUCTOR</small>
                                    <strong id="consumerDrawerProducerName"></strong>
                                </div>
                                <span class="consumer-drawer-producer-rating"><span class="material-symbols-outlined">star</span><b id="consumerDrawerProducerRating">0.0</b><small id="consumerDrawerProducerRatingCount"></small></span>
                            </div>
                            <p id="consumerDrawerProducerInfo"></p>
                            <button class="consumer-drawer-producer-link" onclick="document.getElementById('consumerProducerReviews')?.scrollIntoView({ behavior: 'smooth', block: 'start' })" type="button">Ver perfil y reseñas <span class="material-symbols-outlined">arrow_forward</span></button>
                        </div>
                    </div>
                </div>
                <div class="consumer-drawer-info-grid">
                    <div class="consumer-drawer-section">
                        <h3>Descripci&oacute;n del producto</h3>
                        <p id="consumerDrawerDescription"></p>
                    </div>
                    <div class="consumer-drawer-section consumer-cultivation-section">
                        <h3><span class="material-symbols-outlined">eco</span> Cultivo y procedencia</h3>
                        <dl class="consumer-cultivation-list">
                            <div><dt>Lugar exacto</dt><dd id="consumerDrawerCultivationPlace"></dd></div>
                            <div><dt>Condiciones</dt><dd id="consumerDrawerCultivationConditions"></dd></div>
                            <div><dt>Proceso</dt><dd id="consumerDrawerCultivationProcess"></dd></div>
                        </dl>
                    </div>
                </div>
                <div class="consumer-drawer-section">
                    <div class="consumer-review-heading">
                        <h3>Rese&ntilde;as del producto</h3>
                        <span id="consumerDrawerReviewTotal"></span>
                    </div>
                    <div class="consumer-review-list" id="consumerDrawerReviews"></div>
                </div>
                <div class="consumer-drawer-section" id="consumerProducerReviews">
                    <div class="consumer-review-heading">
                        <h3>Rese&ntilde;as del productor</h3>
                        <span id="consumerProducerReviewTotal"></span>
                    </div>
                    <div class="consumer-review-list" id="consumerDrawerProducerReviews"></div>
                </div>
            </div>
            <div class="consumer-drawer-footer">
                <div><span>Precio por unidad</span><strong id="consumerDrawerFooterPrice"></strong></div>
                <button id="consumerDrawerAdd" type="button"><span class="material-symbols-outlined">add_shopping_cart</span><span>A&ntilde;adir al carrito</span></button>
            </div>
        </aside>
    </div>

    <!-- BLOQUE MODAL PERFIL -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay"
        id="profileModal">
        <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-primary">Editar Perfil</h3>
                    <span
                        class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide"
                        id="profileRoleTag">ROL</span>
                </div>
                <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeProfileModal()"><span
                        class="material-symbols-outlined">close</span></button>
            </div>
            <form class="space-y-stack-md" id="profileForm">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre</label>
                    <input
                        class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white"
                        id="profileNombre" required type="text" />
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Apellido</label>
                    <input
                        class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white"
                        id="profileApellido" type="text" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md">
                    <div>
                        <label
                            class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Correo</label>
                        <input
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant outline-none"
                            disabled id="profileCorreo" type="email" />
                    </div>
                    <div>
                        <label
                            class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Teléfono</label>
                        <input
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white"
                            id="profileTelefono" placeholder="+507 6000-0000" type="tel" />
                    </div>
                </div>
                <button
                    class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98] mt-2"
                    type="submit">GUARDAR CAMBIOS</button>
            </form>
        </div>
    </div>

    <!-- BLOQUE MODAL CONFIGURACION -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay"
        id="settingsModal">
        <div class="bg-white rounded-[24px] w-full max-w-md p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline-sm text-headline-sm text-primary">Configuración</h3>
                <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeSettingsModal()"><span
                        class="material-symbols-outlined">close</span></button>
            </div>
            <form class="space-y-stack-lg" id="settingsForm">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">Tamaño de
                        Fuente</label>
                    <select
                        class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary outline-none bg-white"
                        id="settingsFontSize">
                        <option value="pequeno">Pequeño</option>
                        <option value="mediano">Mediano</option>
                        <option value="grande">Grande</option>
                    </select>
                </div>
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-label-md text-label-md text-on-surface mb-1">Modo Oscuro</h4>
                        <p class="text-on-surface-variant text-label-sm">Reduce el brillo de la interfaz.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input class="sr-only peer" id="settingsDarkMode" type="checkbox" />
                        <div
                            class="w-12 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                        </div>
                    </label>
                </div>
                <button
                    class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98]"
                    type="submit">GUARDAR</button>
            </form>
        </div>
    </div>

    <!-- BLOQUE LOGICA JS -->
    <script>
        const SESSION = <?= json_encode([
            'id' => $usuarioActual['id'],
            'nombre' => $usuarioActual['nombre'],
            'apellido' => $usuarioActual['apellido'],
            'correo' => $usuarioActual['correo'],
            'telefono' => $usuarioActual['telefono'],
            'rol' => $rolActual
        ]) ?>;
        const DB_PRODUCTOS = <?= json_encode($productos) ?>;
        const DB_USUARIOS = <?= json_encode($usuarios) ?>;
        const DB_PEDIDOS = <?= json_encode($pedidos) ?>;
        const DB_COMENTARIOS = <?= json_encode($comentarios) ?>;
        const AJUSTES_ACTUALES = <?= json_encode(['fontSize' => $fontSize, 'darkMode' => $darkMode]) ?>;
        const INITIAL_PRODUCT_SEARCH = <?= json_encode(trim($_GET['buscar'] ?? '')) ?>;
        const ROLE_LABELS = {
            admin: 'PERFIL DE ADMIN',
            logistico: 'PERFIL LOGISTICO',
            productor: 'PERFIL DE PRODUCTOR',
            consumidor: 'PERFIL DE CONSUMIDOR'
        };

        const CONSUMER_PRODUCER_PROFILES = {
            'finca el roble': {
                rating: 4.8,
                reviewCount: 24,
                location: 'Boquete, Chiriqui',
                info: 'Finca familiar enfocada en cultivos frescos y cafe de altura, con cosecha responsable y trazabilidad local.',
                reviews: [
                    { name: 'Maria G.', rating: 5, text: 'Productos frescos y muy bien seleccionados.' },
                    { name: 'Carlos R.', rating: 4.5, text: 'La calidad se mantiene en cada compra.' }
                ]
            },
            'huerto santa maria': {
                rating: 4.7,
                reviewCount: 18,
                location: 'La Chorrera, Panama Oeste',
                info: 'Huerto de produccion de hojas verdes con practicas de bajo impacto y cosecha por pedido.',
                reviews: [
                    { name: 'Ana P.', rating: 5, text: 'Las hojas llegan crujientes y limpias.' },
                    { name: 'Luis M.', rating: 4.5, text: 'Buen producto y entrega consistente.' }
                ]
            },
            'apiario valle verde': {
                rating: 4.9,
                reviewCount: 31,
                location: 'El Valle de Anton, Cocle',
                info: 'Apiario artesanal rodeado de floracion nativa y manejo cuidadoso de las colmenas.',
                reviews: [
                    { name: 'Sofia T.', rating: 5, text: 'Miel con aroma natural y excelente textura.' },
                    { name: 'Jorge A.', rating: 5, text: 'Se nota el cuidado del productor.' }
                ]
            },
            'finca los robles': {
                rating: 4.6,
                reviewCount: 16,
                location: 'Tierras Altas, Chiriqui',
                info: 'Productor de frutas de temporada que trabaja con seleccion manual y rotacion de cultivos.',
                reviews: [
                    { name: 'Elena C.', rating: 4.5, text: 'Frutas dulces y de buena madurez.' },
                    { name: 'Ramon D.', rating: 4.5, text: 'Muy buena presentacion del producto.' }
                ]
            },
            'cafe boquete dorado': {
                rating: 4.9,
                reviewCount: 27,
                location: 'Los Naranjos, Boquete',
                info: 'Cafe de especialidad producido en altura, con procesos de seleccion y tostado por lotes.',
                reviews: [
                    { name: 'Diego S.', rating: 5, text: 'Aroma floral y tostado muy equilibrado.' },
                    { name: 'Paola V.', rating: 4.5, text: 'Excelente cafe para preparar en casa.' }
                ]
            },
            'cacao darien organico': {
                rating: 4.8,
                reviewCount: 21,
                location: 'Meteti, Darien',
                info: 'Organizacion de productores de cacao con manejo organico y transformacion artesanal.',
                reviews: [
                    { name: 'Natalia B.', rating: 5, text: 'Sabor intenso y origen muy bien explicado.' },
                    { name: 'Omar L.', rating: 4.5, text: 'Producto artesanal de gran calidad.' }
                ]
            },
            'hacienda el valle': {
                rating: 4.5,
                reviewCount: 14,
                location: 'Penonome, Cocle',
                info: 'Hacienda de frutas tropicales con maduracion natural y manejo de cosecha por temporada.',
                reviews: [
                    { name: 'Gloria F.', rating: 4.5, text: 'Muy buen sabor y maduracion natural.' },
                    { name: 'Ivan N.', rating: 4.5, text: 'Compra sencilla y producto confiable.' }
                ]
            },
            'finca cerro azul': {
                rating: 4.7,
                reviewCount: 19,
                location: 'Cerro Azul, Panama',
                info: 'Finca de clima fresco dedicada a variedades de aguacate y tomate de seleccion local.',
                reviews: [
                    { name: 'Teresa J.', rating: 5, text: 'Aguacates cremosos y bien empacados.' },
                    { name: 'Marco E.', rating: 4.5, text: 'El producto corresponde con la descripcion.' }
                ]
            }
        };

        const CONSUMER_CULTIVATION_BY_PRODUCER = {
            'finca el roble': { place: 'El Bajo Mono, Boquete, Chiriqui', conditions: 'Clima templado de montana, suelo volcanico y riego controlado.', process: 'Cosecha manual y seleccion por madurez.' },
            'huerto santa maria': { place: 'Santa Rita, La Chorrera, Panama Oeste', conditions: 'Sombra parcial, humedad constante y suelo enriquecido con compost.', process: 'Corte por pedido y lavado el mismo dia.' },
            'apiario valle verde': { place: 'El Macano, El Valle de Anton, Cocle', conditions: 'Colmenas ubicadas cerca de floracion nativa y sin fumigacion cercana.', process: 'Extraccion en frio, filtrado y envasado artesanal.' },
            'finca los robles': { place: 'Volcan, Tierras Altas, Chiriqui', conditions: 'Altitud media, dias soleados y noches frescas.', process: 'Cosecha manual y clasificacion por tamano.' },
            'cafe boquete dorado': { place: 'Los Naranjos, Boquete, Chiriqui', conditions: 'Altitud de montana, sombra natural y lluvias moderadas.', process: 'Recoleccion selectiva y tostado por lotes.' },
            'cacao darien organico': { place: 'Meteti, Chepigana, Darien', conditions: 'Bosque humedo tropical y manejo sin agroquimicos sinteticos.', process: 'Fermentacion controlada, secado natural y seleccion manual.' },
            'hacienda el valle': { place: 'El Coco, Penonome, Cocle', conditions: 'Clima tropical con ventilacion natural y riego de apoyo.', process: 'Maduracion natural y cosecha escalonada.' },
            'finca cerro azul': { place: 'Cerro Azul, Panama', conditions: 'Clima fresco, suelo arcilloso y drenaje natural.', process: 'Cosecha manual y revision pieza por pieza.' }
        };

        const CONSUMER_GALLERY_POSITIONS = ['center center', '25% center', '75% center', 'center 25%', 'center 75%'];
        let consumerGalleryItems = [];
        let consumerGalleryIndex = 0;

        function logout() {
            window.location.href = 'panel.php?logout=1';
        }

        function escapeMarkup(value) {
            return String(value ?? '').replace(/[&<>'"]/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
            })[character]);
        }

        function getProductImage(product) {
            return product.imagen || 'img/Banner.png';
        }

        function getConsumerProducerProfile(product) {
            const key = normalizeProductSearch(product.nom_productor);
            return CONSUMER_PRODUCER_PROFILES[key] || {
                rating: 4.5,
                reviewCount: 0,
                location: 'Panama',
                info: 'Productor local registrado en la plataforma DACHI.',
                reviews: []
            };
        }

        function getConsumerCultivation(product) {
            const key = normalizeProductSearch(product.nom_productor);
            return CONSUMER_CULTIVATION_BY_PRODUCER[key] || {
                place: 'Panama',
                conditions: 'Condiciones de cultivo informadas por el productor.',
                process: 'Seleccion y manejo responsable del producto.'
            };
        }

        function getConsumerProductGallery(product) {
            const baseImage = getProductImage(product);
            const provided = Array.isArray(product.galeria) ? product.galeria.filter(Boolean).slice(0, 5) : [];
            const sources = provided.length ? provided : [baseImage, baseImage, baseImage, baseImage, baseImage];
            return sources.slice(0, 5).map((src, index) => ({
                src,
                position: CONSUMER_GALLERY_POSITIONS[index] || 'center center',
                alt: `${product.nombre} - vista ${index + 1}`
            }));
        }

        function setConsumerGalleryImage(index) {
            const image = document.getElementById('consumerDrawerImage');
            if (!image || !consumerGalleryItems.length) return;
            consumerGalleryIndex = (index + consumerGalleryItems.length) % consumerGalleryItems.length;
            const item = consumerGalleryItems[consumerGalleryIndex];
            image.src = item.src;
            image.alt = item.alt;
            image.style.objectPosition = item.position;
            document.querySelectorAll('#consumerDrawerThumbnails button').forEach((button, buttonIndex) => {
                button.classList.toggle('is-active', buttonIndex === consumerGalleryIndex);
                button.setAttribute('aria-current', buttonIndex === consumerGalleryIndex ? 'true' : 'false');
            });
        }

        function renderConsumerGallery(product) {
            consumerGalleryItems = getConsumerProductGallery(product);
            const thumbnails = document.getElementById('consumerDrawerThumbnails');
            if (!thumbnails) return;
            thumbnails.innerHTML = consumerGalleryItems.map((item, index) => `
                <button aria-label="Ver imagen ${index + 1}" onclick="setConsumerGalleryImage(${index})" type="button">
                    <img alt="" src="${escapeMarkup(item.src)}" style="object-position: ${escapeMarkup(item.position)}" />
                </button>`).join('');
            setConsumerGalleryImage(0);
        }

        function changeConsumerGallery(direction) {
            setConsumerGalleryImage(consumerGalleryIndex + direction);
        }

        function availableConsumerProducts() {
            return DB_PRODUCTOS.filter(product => Number(product.estado) === 1);
        }

        function normalizeProductSearch(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim()
                .toLocaleLowerCase('es');
        }

        const CONSUMER_PRODUCTS_PER_PAGE = 9;
        const CONSUMER_OFFERS = [
            { id: 1, discount: 15 },
            { id: 2, discount: 10 },
            { id: 5, discount: 18 },
            { id: 7, discount: 12 },
            { id: 9, discount: 20 }
        ];
        let consumerFilteredProducts = [];
        let consumerProductPage = 1;
        let consumerOfferIndex = 0;
        let consumerOfferTimer = null;

        function renderConsumerProducts(products = availableConsumerProducts(), requestedPage = 1) {
            const rail = document.getElementById('consumerProductRail');
            const empty = document.getElementById('consumerProductEmpty');
            const pagination = document.getElementById('consumerProductPagination');
            if (!rail || !empty || !pagination) return;

            consumerFilteredProducts = products;

            if (!products.length) {
                rail.innerHTML = '';
                rail.classList.add('hidden');
                empty.classList.remove('hidden');
                pagination.classList.add('hidden');
                return;
            }

            const pageCount = Math.ceil(products.length / CONSUMER_PRODUCTS_PER_PAGE);
            consumerProductPage = Math.min(Math.max(Number(requestedPage) || 1, 1), pageCount);
            const start = (consumerProductPage - 1) * CONSUMER_PRODUCTS_PER_PAGE;
            const visibleProducts = products.slice(start, start + CONSUMER_PRODUCTS_PER_PAGE);

            rail.classList.remove('hidden');
            empty.classList.add('hidden');
            pagination.classList.remove('hidden');
            rail.innerHTML = visibleProducts.map(product => {
                const ratingCount = (product.ratings || []).length;
                const rating = Number(product.promedio || 0).toFixed(1);
                const hasStock = Number(product.cantidad) > 0;
                return `
                    <article class="consumer-product-card" onclick="openConsumerProduct(${Number(product.id)})">
                        <img alt="${escapeMarkup(product.nombre)}" loading="lazy" src="${escapeMarkup(getProductImage(product))}" />
                        <span class="consumer-product-stock ${hasStock ? '' : 'is-empty'}">${hasStock ? `${Number(product.cantidad)} disponibles` : 'Sin stock'}</span>
                        <div class="consumer-product-card-body">
                            <small>${escapeMarkup(product.nom_productor)}</small>
                            <h3 title="${escapeMarkup(product.nombre)}">${escapeMarkup(product.nombre)}</h3>
                            <div class="consumer-product-rating">
                                <span class="material-symbols-outlined">star</span>
                                <strong>${rating}</strong>
                                <span>${ratingCount ? `(${ratingCount})` : 'Sin rese&ntilde;as'}</span>
                            </div>
                            <div class="consumer-product-card-footer">
                                <strong class="consumer-product-price">$${Number(product.precio).toFixed(2)}</strong>
                                <button class="consumer-product-add" ${hasStock ? '' : 'disabled'} onclick="event.stopPropagation(); addConsumerProduct(${Number(product.id)}, this)" title="A&ntilde;adir ${escapeMarkup(product.nombre)} al carrito" type="button">
                                    <span class="material-symbols-outlined">add_shopping_cart</span>
                                </button>
                            </div>
                        </div>
                    </article>`;
            }).join('');

            const firstVisible = start + 1;
            const lastVisible = Math.min(start + CONSUMER_PRODUCTS_PER_PAGE, products.length);
            document.getElementById('consumerProductRange').textContent = `Mostrando ${firstVisible}-${lastVisible} de ${products.length} productos`;
            document.getElementById('consumerProductPages').innerHTML = `
                <button ${consumerProductPage === 1 ? 'disabled' : ''} onclick="goToConsumerProductPage(${consumerProductPage - 1})" title="P&aacute;gina anterior" type="button"><span class="material-symbols-outlined">chevron_left</span></button>
                ${Array.from({ length: pageCount }, (_, index) => {
                    const page = index + 1;
                    return `<button class="${page === consumerProductPage ? 'is-active' : ''}" onclick="goToConsumerProductPage(${page})" type="button">${page}</button>`;
                }).join('')}
                <button ${consumerProductPage === pageCount ? 'disabled' : ''} onclick="goToConsumerProductPage(${consumerProductPage + 1})" title="P&aacute;gina siguiente" type="button"><span class="material-symbols-outlined">chevron_right</span></button>`;
        }

        function goToConsumerProductPage(page) {
            renderConsumerProducts(consumerFilteredProducts, page);
            document.getElementById('productos')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function renderConsumerOffers() {
            const track = document.getElementById('consumerOffersTrack');
            if (!track) return;

            const products = availableConsumerProducts();
            const offers = CONSUMER_OFFERS.map(offer => {
                const product = products.find(item => Number(item.id) === offer.id);
                return product ? { ...offer, product } : null;
            }).filter(Boolean);

            if (!offers.length) {
                document.querySelector('.consumer-offers-section')?.classList.add('hidden');
                return;
            }

            const offerCard = ({ product, discount }, clone = false) => {
                const hasStock = Number(product.cantidad) > 0;
                const currentPrice = Number(product.precio);
                const previousPrice = currentPrice / (1 - discount / 100);
                return `
                    <article class="consumer-offer-card" ${clone ? 'aria-hidden="true"' : ''} onclick="openConsumerProduct(${Number(product.id)})">
                        <div class="consumer-offer-image">
                            <img alt="${escapeMarkup(product.nombre)}" loading="lazy" src="${escapeMarkup(getProductImage(product))}" />
                            <span>-${discount}%</span>
                        </div>
                        <div class="consumer-offer-body">
                            <small>${escapeMarkup(product.nom_productor)}</small>
                            <h3>${escapeMarkup(product.nombre)}</h3>
                            <p>${hasStock ? `${Number(product.cantidad)} disponibles` : 'Sin stock'}</p>
                            <div class="consumer-offer-footer">
                                <div><del>$${previousPrice.toFixed(2)}</del><strong>$${currentPrice.toFixed(2)}</strong></div>
                                <button ${clone ? 'tabindex="-1"' : ''} ${hasStock ? '' : 'disabled'} onclick="event.stopPropagation(); addConsumerProduct(${Number(product.id)}, this)" title="A&ntilde;adir ${escapeMarkup(product.nombre)} al carrito" type="button">
                                    <span class="material-symbols-outlined">add_shopping_cart</span>
                                </button>
                            </div>
                        </div>
                    </article>`;
            };

            track.innerHTML = offers.map(offer => offerCard(offer)).join('')
                + offers.map(offer => offerCard(offer, true)).join('');
            consumerOfferIndex = 0;
            setConsumerOfferPosition(false);
            startConsumerOfferAutoplay();
        }

        function consumerOfferStep() {
            const track = document.getElementById('consumerOffersTrack');
            const card = track?.querySelector('.consumer-offer-card');
            if (!track || !card) return 0;
            return card.getBoundingClientRect().width + (parseFloat(getComputedStyle(track).columnGap) || 0);
        }

        function setConsumerOfferPosition(animate = true) {
            const track = document.getElementById('consumerOffersTrack');
            if (!track) return;
            track.style.transition = animate ? 'transform 560ms cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
            track.style.transform = `translate3d(${-consumerOfferIndex * consumerOfferStep()}px, 0, 0)`;
        }

        function moveConsumerOffer(direction) {
            if (direction < 0 && consumerOfferIndex === 0) {
                consumerOfferIndex = CONSUMER_OFFERS.length;
                setConsumerOfferPosition(false);
                requestAnimationFrame(() => {
                    consumerOfferIndex -= 1;
                    setConsumerOfferPosition(true);
                });
                return;
            }
            consumerOfferIndex += direction;
            setConsumerOfferPosition(true);
        }

        function handleConsumerOfferTransition() {
            if (consumerOfferIndex < CONSUMER_OFFERS.length) return;
            consumerOfferIndex = 0;
            setConsumerOfferPosition(false);
        }

        function pauseConsumerOfferAutoplay() {
            clearInterval(consumerOfferTimer);
            consumerOfferTimer = null;
        }

        function startConsumerOfferAutoplay() {
            pauseConsumerOfferAutoplay();
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            consumerOfferTimer = setInterval(() => moveConsumerOffer(1), 4200);
        }

        function changeConsumerOffer(direction) {
            moveConsumerOffer(direction);
            startConsumerOfferAutoplay();
        }

        function filterConsumerProducts(value) {
            const term = normalizeProductSearch(value);
            const products = availableConsumerProducts().filter(product =>
                normalizeProductSearch(product.nombre).includes(term)
                || normalizeProductSearch(product.nom_productor).includes(term)
            );
            renderConsumerProducts(products);

            const results = document.getElementById('globalSearchResults');
            if (!results) return;
            if (!term) {
                results.classList.add('hidden');
                results.innerHTML = '';
                return;
            }

            results.classList.remove('hidden');
            results.innerHTML = products.length
                ? products.slice(0, 5).map(product => `
                    <button onclick="selectGlobalProduct(${Number(product.id)})" type="button">
                        <img alt="" src="${escapeMarkup(getProductImage(product))}" />
                        <span><strong>${escapeMarkup(product.nombre)}</strong><small>${escapeMarkup(product.nom_productor)}</small></span>
                        <b>$${Number(product.precio).toFixed(2)}</b>
                    </button>`).join('')
                : '<p>No hay coincidencias.</p>';
        }

        function selectGlobalProduct(id) {
            document.getElementById('globalSearchResults')?.classList.add('hidden');
            document.getElementById('productos')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            openConsumerProduct(id);
        }

        function openConsumerProduct(id) {
            const product = availableConsumerProducts().find(item => Number(item.id) === Number(id));
            if (!product) return;

            const ratings = product.ratings || [];
            const hasStock = Number(product.cantidad) > 0;
            const price = `$${Number(product.precio).toFixed(2)}`;
            const producer = getConsumerProducerProfile(product);
            const cultivation = getConsumerCultivation(product);
            renderConsumerGallery(product);
            document.getElementById('consumerDrawerName').textContent = product.nombre;
            document.getElementById('consumerDrawerOrigin').textContent = `Producido por ${product.nom_productor} - ${producer.location}`;
            document.getElementById('consumerDrawerDescription').textContent = product.descripcion;
            document.getElementById('consumerDrawerPrice').textContent = price;
            document.getElementById('consumerDrawerFooterPrice').textContent = price;
            document.getElementById('consumerDrawerRating').textContent = Number(product.promedio || 0).toFixed(1);
            document.getElementById('consumerDrawerProducerName').textContent = product.nom_productor;
            document.getElementById('consumerDrawerProducerRating').textContent = Number(producer.rating).toFixed(1);
            document.getElementById('consumerDrawerProducerRatingCount').textContent = producer.reviewCount ? `(${producer.reviewCount})` : '(sin resenas)';
            document.getElementById('consumerDrawerProducerInfo').textContent = producer.info;
            document.getElementById('consumerDrawerCultivationPlace').textContent = cultivation.place;
            document.getElementById('consumerDrawerCultivationConditions').textContent = cultivation.conditions;
            document.getElementById('consumerDrawerCultivationProcess').textContent = cultivation.process;
            document.getElementById('consumerProducerReviewTotal').textContent = producer.reviewCount ? `${producer.reviewCount} resenas` : 'Sin resenas';
            document.getElementById('consumerDrawerRatingCount').textContent = ratings.length ? `(${ratings.length})` : '(sin reseñas)';
            document.getElementById('consumerDrawerReviewTotal').textContent = `${ratings.length} reseña${ratings.length === 1 ? '' : 's'}`;

            const stock = document.getElementById('consumerDrawerStock');
            stock.textContent = hasStock ? `${Number(product.cantidad)} unidades disponibles` : 'Sin stock';
            stock.classList.toggle('is-empty', !hasStock);

            document.getElementById('consumerDrawerReviews').innerHTML = ratings.length
                ? ratings.map(rating => `
                    <article class="consumer-review-card">
                        <div><strong>${escapeMarkup(rating.nombre_consumer || 'Consumidor')}</strong><span>${Number(rating.calificacion).toFixed(1)} &#9733;</span></div>
                        <p>${escapeMarkup(rating.comentario || 'Sin comentario escrito.')}</p>
                    </article>`).join('')
                : '<p class="consumer-review-empty">A&uacute;n no hay rese&ntilde;as para este producto.</p>';

            document.getElementById('consumerDrawerProducerReviews').innerHTML = producer.reviews.length
                ? producer.reviews.map(review => `
                    <article class="consumer-review-card">
                        <div><strong>${escapeMarkup(review.name)}</strong><span>${Number(review.rating).toFixed(1)} &#9733;</span></div>
                        <p>${escapeMarkup(review.text)}</p>
                    </article>`).join('')
                : '<p class="consumer-review-empty">A&uacute;n no hay rese&ntilde;as del productor.</p>';

            const addButton = document.getElementById('consumerDrawerAdd');
            addButton.disabled = !hasStock;
            addButton.querySelector('span:last-child').textContent = hasStock ? 'Añadir al carrito' : 'Sin stock';
            addButton.onclick = hasStock ? () => addConsumerProduct(product.id, addButton) : null;

            document.getElementById('consumerProductDrawer').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeConsumerProduct() {
            document.getElementById('consumerProductDrawer')?.classList.add('hidden');
            document.body.style.overflow = '';
        }

        let consumerMessageTimer;
        function showConsumerCartMessage(message, isError = false) {
            const box = document.getElementById('consumerCartMessage');
            if (!box) return;
            clearTimeout(consumerMessageTimer);
            box.textContent = message;
            box.classList.toggle('is-error', isError);
            box.classList.remove('hidden');
            consumerMessageTimer = setTimeout(() => box.classList.add('hidden'), 4200);
        }

        async function addConsumerProduct(id, button) {
            if (button) button.disabled = true;
            const body = new URLSearchParams({ accion: 'agregar', id_producto: id, cantidad: 1 });
            try {
                const response = await fetch('public/carrito.php', { method: 'POST', body });
                const data = await response.json();
                showConsumerCartMessage(data.message, data.status !== 'success');
                if (data.status === 'success') {
                    const badge = document.getElementById('panelCartCount');
                    if (badge) {
                        badge.textContent = data.total_unidades;
                        badge.classList.remove('hidden');
                    }
                    closeConsumerProduct();
                }
            } catch (_error) {
                showConsumerCartMessage('No se pudo conectar con el carrito.', true);
            } finally {
                if (button) button.disabled = false;
            }
        }

        function toggleNotificationMenu() {
            document.getElementById('userMenu')?.classList.remove('open');
            document.getElementById('globalSearchResults')?.classList.add('hidden');
            document.getElementById('notificationMenu')?.classList.toggle('hidden');
        }

        // BLOQUE AJUSTES
        function applyAjustes(a) {
            const sizes = { pequeno: '14px', mediano: '16px', grande: '18px' };
            document.documentElement.style.fontSize = sizes[a.fontSize] || '16px';
            document.documentElement.classList.toggle('dark', !!a.darkMode);
        }
        function guardarAjustesCookie(a) {
            document.cookie = 'dachi_ajustes=' + encodeURIComponent(JSON.stringify(a)) + ';path=/;max-age=' + (60 * 60 * 24 * 365);
        }

        // BLOQUE PERFIL
        function refreshAvatarUI() {
            const initial = SESSION.nombre.charAt(0).toUpperCase();
            document.querySelectorAll('.avatar-initial').forEach(el => el.textContent = initial);
            document.getElementById('menuRoleTag').textContent = ROLE_LABELS[SESSION.rol] || '';
            document.getElementById('userMenuName').textContent = SESSION.nombre + ' ' + (SESSION.apellido || '');
            document.getElementById('userMenuEmail').textContent = SESSION.correo;
            document.getElementById('panelUserName').textContent = SESSION.nombre;
        }
        function toggleUserMenu() {
            document.getElementById('notificationMenu')?.classList.add('hidden');
            document.getElementById('globalSearchResults')?.classList.add('hidden');
            document.getElementById('userMenu').classList.toggle('open');
        }
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('userMenu');
            const btn = document.getElementById('userAvatarBtn');
            if (menu && menu.classList.contains('open') && !menu.contains(e.target) && !btn.contains(e.target)) menu.classList.remove('open');

            const notifications = document.getElementById('notificationMenu');
            const notificationButton = document.getElementById('notificationButton');
            if (notifications && !notifications.classList.contains('hidden') && !notifications.contains(e.target) && !notificationButton.contains(e.target)) {
                notifications.classList.add('hidden');
            }

            const search = document.querySelector('.dachi-global-search');
            const results = document.getElementById('globalSearchResults');
            if (search && results && !search.contains(e.target)) results.classList.add('hidden');
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeConsumerProduct();
                document.getElementById('notificationMenu')?.classList.add('hidden');
                document.getElementById('globalSearchResults')?.classList.add('hidden');
            }
        });
        function openProfileModal() {
            document.getElementById('userMenu').classList.remove('open');
            document.getElementById('profileNombre').value = SESSION.nombre;
            document.getElementById('profileApellido').value = SESSION.apellido || '';
            document.getElementById('profileCorreo').value = SESSION.correo;
            document.getElementById('profileTelefono').value = SESSION.telefono || '';
            document.getElementById('profileRoleTag').textContent = ROLE_LABELS[SESSION.rol] || '';
            document.getElementById('profileModal').classList.remove('hidden');
            document.getElementById('profileModal').classList.add('flex');
        }
        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
            document.getElementById('profileModal').classList.remove('flex');
        }
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const body = new URLSearchParams({
                accion: 'guardar_perfil',
                nombre: document.getElementById('profileNombre').value.trim(),
                apellido: document.getElementById('profileApellido').value.trim(),
                telefono: document.getElementById('profileTelefono').value.trim()
            });
            await fetch('panel.php', { method: 'POST', body });
            location.reload();
        });

        function openSettingsModal() {
            document.getElementById('userMenu').classList.remove('open');
            document.getElementById('settingsFontSize').value = AJUSTES_ACTUALES.fontSize || 'mediano';
            document.getElementById('settingsDarkMode').checked = !!AJUSTES_ACTUALES.darkMode;
            document.getElementById('settingsModal').classList.remove('hidden');
            document.getElementById('settingsModal').classList.add('flex');
        }
        function closeSettingsModal() {
            document.getElementById('settingsModal').classList.add('hidden');
            document.getElementById('settingsModal').classList.remove('flex');
        }
        document.getElementById('settingsForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const a = { fontSize: document.getElementById('settingsFontSize').value, darkMode: document.getElementById('settingsDarkMode').checked };
            guardarAjustesCookie(a);
            applyAjustes(a);
            closeSettingsModal();
        });

        // BLOQUE NAVEGACION SIDEBAR
        const NAV_ADMIN = [
            { id: 'sec-dashboard', label: 'Dashboard', icon: 'dashboard' },
            { id: 'sec-usuarios', label: 'Usuarios', icon: 'group' },
            { id: 'sec-productos', label: 'Productos', icon: 'inventory_2' },
            { id: 'sec-comentarios', label: 'Comentarios', icon: 'forum' },
            { id: 'sec-logistica-admin', label: 'Logística', icon: 'local_shipping' }
        ];
        const NAV_REPARTIDOR = [
            { id: 'sec-disponibles', label: 'Entregas Disponibles', icon: 'explore' },
            { id: 'sec-activa', label: 'Mi Entrega Activa', icon: 'package_2' },
            { id: 'sec-historial', label: 'Historial', icon: 'history' }
        ];
        const NAV_DISTRIBUIDOR = [
            { id: 'dist-dashboard', label: 'Dashboard', icon: 'dashboard' },
            { id: 'dist-inventario', label: 'Inventario', icon: 'inventory_2' },
            { id: 'dist-productores', label: 'Productores', icon: 'agriculture' },
            { id: 'dist-pedidos', label: 'Pedidos', icon: 'local_shipping' }
        ];
        const NAV_CONSUMIDOR = [
            { id: 'consumer-dashboard', label: 'Inicio', icon: 'person' },
            { href: 'public/carrito.php', label: 'Carrito', icon: 'shopping_cart' },
            { href: 'public/seguimiento.php', label: 'Mis pedidos', icon: 'package_2' }
        ];
        function buildSidebar(items) {
            const nav = document.getElementById('sidebarNav');
            nav.innerHTML = items.map((it, i) => {
                if (it.href) {
                    return `
        <a class="nav-item flex items-center gap-4 p-3 rounded-xl transition-all cursor-pointer hover:bg-primary-fixed/20 text-on-surface-variant hover:text-primary" href="${it.href}" title="${it.label}">
            <span class="material-symbols-outlined">${it.icon}</span>
            <span class="font-label-md">${it.label}</span>
        </a>`;
                }
                return `
        <a class="nav-item flex items-center gap-4 p-3 rounded-xl transition-all cursor-pointer ${i === 0 ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'hover:bg-primary-fixed/20 text-on-surface-variant hover:text-primary'}" data-target="${it.id}" onclick="showSection('${it.id}', this)" title="${it.label}">
            <span class="material-symbols-outlined">${it.icon}</span>
            <span class="font-label-md">${it.label}</span>
        </a>`;
            }).join('');
        }
        function showSection(id, el) {
            document.querySelectorAll('main section').forEach(s => s.classList.add('hidden'));
            document.getElementById(id).classList.remove('hidden');
            document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('bg-primary', 'text-on-primary', 'shadow-lg', 'shadow-primary/20'));
            document.querySelectorAll('.nav-item').forEach(a => a.classList.add('text-on-surface-variant'));
            const activeItem = el || document.querySelector(`.nav-item[data-target="${id}"]`);
            if (activeItem) activeItem.classList.add('bg-primary', 'text-on-primary', 'shadow-lg', 'shadow-primary/20');
            if (el && window.innerWidth < 1024) toggleSidebar();
        }
        const SIDEBAR_STATE_KEY = 'dachiSidebarCollapsed';

        function updateSidebarControls() {
            const collapsed = document.body.classList.contains('dachi-sidebar-collapsed');
            document.querySelectorAll('.dachi-sidebar-toggle-icon').forEach(icon => {
                icon.textContent = collapsed ? 'left_panel_open' : 'menu';
            });
        }

        function applySavedSidebarState() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth >= 1024) {
                document.body.classList.toggle('dachi-sidebar-collapsed', localStorage.getItem(SIDEBAR_STATE_KEY) === '1');
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            } else {
                document.body.classList.remove('dachi-sidebar-collapsed');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
            updateSidebarControls();
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth >= 1024) {
                const collapsed = document.body.classList.toggle('dachi-sidebar-collapsed');
                localStorage.setItem(SIDEBAR_STATE_KEY, collapsed ? '1' : '0');
                updateSidebarControls();
                return;
            }
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        let sidebarUsesDesktopLayout = window.innerWidth >= 1024;
        window.addEventListener('resize', () => {
            const usesDesktopLayout = window.innerWidth >= 1024;
            if (usesDesktopLayout !== sidebarUsesDesktopLayout) {
                sidebarUsesDesktopLayout = usesDesktopLayout;
                applySavedSidebarState();
            }
            if (SESSION.rol === 'consumidor') {
                setConsumerOfferPosition(false);
            }
        });

        // BLOQUE INIT
        function init() {
            applyAjustes(AJUSTES_ACTUALES);
            applySavedSidebarState();
            refreshAvatarUI();

            if (SESSION.rol === 'admin') {
                document.getElementById('panelTitle').textContent = 'Panel de Administración';
                document.getElementById('roleSubtitle').textContent = 'Monitoreo global de la plataforma DACHI.';
                buildSidebar(NAV_ADMIN);
                showSection('sec-dashboard');
                renderAdminDashboard();
                renderAdminUsers();
                renderAdminProducts();
                renderAdminComments();
                renderAdminLogistics();
            } else if (SESSION.rol === 'logistico') {
                document.getElementById('panelTitle').textContent = 'Panel de Logística';
                document.getElementById('roleSubtitle').textContent = 'Gestiona tus entregas de campo a mesa.';
                buildSidebar(NAV_REPARTIDOR);
                showSection('sec-disponibles');
                renderAvailableDeliveries();
                renderActiveDelivery();
                renderHistory();
            } else if (SESSION.rol === 'productor') {
                document.getElementById('panelTitle').textContent = 'Panel de Productor';
                document.getElementById('roleSubtitle').textContent = 'Coordina inventario y pedidos entre productores y consumidores.';
                buildSidebar(NAV_DISTRIBUIDOR);
                showSection('dist-dashboard');
                renderDistribuidorDashboard();
                renderInventario();
                renderProductores();
                renderPedidosDistribuidor();
            } else if (SESSION.rol === 'consumidor') {
                const customerName = `${SESSION.nombre} ${SESSION.apellido || ''}`.trim()
                    .split(/\s+/)
                    .map(part => part.charAt(0).toLocaleUpperCase('es') + part.slice(1))
                    .join(' ');
                document.getElementById('panelTitle').textContent = `Le damos la bienvenida, ${customerName}`;
                document.getElementById('roleSubtitle').textContent = 'Productos locales, compras y pedidos en un solo lugar.';
                buildSidebar(NAV_CONSUMIDOR);
                showSection('consumer-dashboard');
                renderConsumerProducts();
                renderConsumerOffers();
                const searchInput = document.getElementById('globalProductSearch');
                if (searchInput && INITIAL_PRODUCT_SEARCH) {
                    searchInput.value = INITIAL_PRODUCT_SEARCH;
                    filterConsumerProducts(INITIAL_PRODUCT_SEARCH);
                }
                if (window.location.hash === '#productos' || INITIAL_PRODUCT_SEARCH) {
                    setTimeout(() => document.getElementById('productos')?.scrollIntoView({ block: 'start' }), 0);
                }
            }

            if (new URLSearchParams(window.location.search).get('config') === '1') {
                openSettingsModal();
            }
        }

        // BLOQUE ADMIN: DASHBOARD
        function renderAdminDashboard() {
            const revenue = DB_PEDIDOS.reduce((a, o) => a + parseFloat(o.total_compra), 0);
            document.getElementById('kpiProducts').textContent = DB_PRODUCTOS.length;
            document.getElementById('kpiUsers').textContent = DB_USUARIOS.length;
            document.getElementById('kpiOrders').textContent = DB_PEDIDOS.length;
            document.getElementById('kpiRevenue').textContent = `$${revenue.toFixed(2)}`;

            const allRatings = DB_PRODUCTOS.flatMap(p => p.ratings || []);
            const avg = allRatings.length ? allRatings.reduce((a, r) => a + Number(r.calificacion), 0) / allRatings.length : 0;
            document.getElementById('avgRatingValue').textContent = avg.toFixed(1);
            document.getElementById('avgRatingBar').style.width = `${(avg / 5) * 100}%`;
            document.getElementById('commentCountText').textContent = `${DB_COMENTARIOS.length} comentarios`;

            const statusColor = { pendiente: 'bg-surface-container-highest/80 text-on-surface-variant', 'en camino': 'bg-secondary-container/80 text-on-secondary-container', entregado: 'bg-primary-fixed/80 text-on-primary-fixed' };
            const tbody = document.getElementById('recentOrdersBody');
            tbody.innerHTML = DB_PEDIDOS.slice(0, 8).map(o => `
        <tr class="table-row-hover transition-colors">
            <td class="px-gutter py-5 font-bold text-primary">#${o.id}</td>
            <td class="px-gutter py-5">${o.comprador_nombre} ${o.comprador_apellido}</td>
            <td class="px-gutter py-5"><span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase ${statusColor[o.estado_label] || statusColor.pendiente}">${o.estado_label}</span></td>
            <td class="px-gutter py-5 font-bold text-primary">$${parseFloat(o.total_compra).toFixed(2)}</td>
        </tr>
    `).join('') || '<tr><td class="px-gutter py-5 text-on-surface-variant" colspan="4">Sin pedidos aún.</td></tr>';
        }

        // BLOQUE ADMIN: USUARIOS
        function renderAdminUsers() {
            const tbody = document.getElementById('usersBody');
            tbody.innerHTML = DB_USUARIOS.map(u => `
        <tr class="table-row-hover transition-colors">
            <td class="px-gutter py-5 font-bold text-on-surface">${u.nombre} ${u.apellido}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${u.correo}</td>
            <td class="px-gutter py-5"><span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase bg-primary-fixed/40 text-on-primary-fixed-variant">${u.nom_rol}</span></td>
            <td class="px-gutter py-5 text-on-surface-variant">${u.telefono || '—'}</td>
        </tr>
    `).join('') || '<tr><td class="px-gutter py-5 text-on-surface-variant" colspan="4">No hay usuarios registrados.</td></tr>';
        }

        // BLOQUE ADMIN: PRODUCTOS
        function renderAdminProducts() {
            const tbody = document.getElementById('productsAdminBody');
            tbody.innerHTML = DB_PRODUCTOS.map(p => `
        <tr class="table-row-hover transition-colors">
            <td class="px-gutter py-5 font-bold text-on-surface">${p.nombre}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${p.nom_productor}</td>
            <td class="px-gutter py-5 font-bold text-primary">$${parseFloat(p.precio).toFixed(2)}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${p.promedio} ★ (${p.ratings.length})</td>
            <td class="px-gutter py-5"><span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase ${Number(p.estado) === 1 ? 'bg-primary-fixed/60 text-on-primary-fixed' : 'bg-error-container text-error'}">${Number(p.estado) === 1 ? 'activo' : 'bajado'}</span></td>
            <td class="px-gutter py-5 text-center flex gap-2 justify-center">
                <button class="px-3 py-2 rounded-full text-label-sm font-bold bg-surface-container-high text-on-surface" onclick="toggleProductStatus(${p.id}, ${Number(p.estado)})">${Number(p.estado) === 1 ? 'Bajar' : 'Activar'}</button>
                <button class="px-3 py-2 rounded-full text-label-sm font-bold bg-error-container text-error" onclick="deleteProductAdmin(${p.id})">Eliminar</button>
            </td>
        </tr>`).join('') || '<tr><td class="px-gutter py-5 text-on-surface-variant" colspan="6">No hay productos registrados.</td></tr>';
        }
        async function toggleProductStatus(id, estadoActual) {
            const nuevoEstado = estadoActual === 1 ? 0 : 1;
            const body = new URLSearchParams({ accion: 'toggle_producto', id, estado: nuevoEstado });
            await fetch('panel.php', { method: 'POST', body });
            location.reload();
        }
        async function deleteProductAdmin(id) {
            if (!confirm('¿Eliminar este producto de la plataforma?')) return;
            const body = new URLSearchParams({ accion: 'eliminar_producto', id });
            await fetch('panel.php', { method: 'POST', body });
            location.reload();
        }

        // BLOQUE ADMIN: COMENTARIOS
        function renderAdminComments() {
            const list = document.getElementById('commentsAdminList');
            list.innerHTML = DB_COMENTARIOS.map(c => `
        <div class="bg-white rounded-xl p-4 border border-outline-variant/30 flex justify-between items-start gap-4">
            <div>
                <p class="font-bold text-on-surface">${c.consumidor} <span class="text-label-sm text-on-surface-variant font-normal">sobre "${c.producto}" · ${c.calificacion} ★</span></p>
                <p class="text-on-surface-variant">${c.comentario}</p>
            </div>
            <button class="px-3 py-2 rounded-full text-label-sm font-bold bg-error-container text-error whitespace-nowrap" onclick="deleteComment(${c.id})">Eliminar</button>
        </div>
    `).join('') || '<p class="text-on-surface-variant">No hay comentarios registrados.</p>';
        }
        async function deleteComment(id) {
            if (!confirm('¿Eliminar este comentario?')) return;
            const body = new URLSearchParams({ accion: 'eliminar_comentario', id });
            await fetch('panel.php', { method: 'POST', body });
            location.reload();
        }

        // BLOQUE ADMIN: LOGISTICA
        function renderAdminLogistics() {
            const list = document.getElementById('logisticaAdminList');
            list.innerHTML = DB_PEDIDOS.map(o => `
        <div class="bg-white rounded-xl p-4 border border-outline-variant/30 flex justify-between items-center flex-wrap gap-2">
            <div>
                <p class="font-bold text-primary">#${o.id} · ${o.comprador_nombre}</p>
                <p class="text-label-sm text-on-surface-variant">${o.repartidor_nombre ? 'Repartidor: ' + o.repartidor_nombre : 'Sin asignar'}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase bg-surface-container-high text-on-surface-variant">${o.estado_label}</span>
        </div>
    `).join('') || '<p class="text-on-surface-variant">No hay entregas registradas.</p>';
        }

        // BLOQUE REPARTIDOR: DISPONIBLES
        function renderAvailableDeliveries() {
            const pending = DB_PEDIDOS.filter(o => Number(o.estado) === 0 && !o.id_repartidor);
            const container = document.getElementById('availableDeliveriesList');
            const noAvailable = document.getElementById('noAvailable');
            if (pending.length === 0) { container.innerHTML = ''; noAvailable.classList.remove('hidden'); return; }
            noAvailable.classList.add('hidden');
            container.innerHTML = pending.map(o => {
                const itemsSummary = o.items.map(i => `${i.cantidad}x ${i.producto_nombre}`).join(', ');
                return `
        <div class="glass-card border-l-4 border-l-secondary p-gutter rounded-[28px] shadow-forest hover:shadow-forest-active transition-all">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <span class="text-[10px] font-bold text-on-secondary-container bg-secondary-fixed px-3 py-1 rounded-full uppercase tracking-widest">Pedido</span>
                    <h4 class="font-headline-sm mt-3 text-[20px]">#${o.id}</h4>
                </div>
                <div class="text-right">
                    <span class="block text-label-sm text-on-surface-variant mb-1">Pago</span>
                    <span class="font-bold text-primary text-headline-sm">$${parseFloat(o.total_compra).toFixed(2)}</span>
                </div>
            </div>
            <div class="space-y-4 mb-8">
                <div class="flex items-start gap-4 text-on-surface-variant bg-surface-container-low/30 p-3 rounded-xl">
                    <span class="material-symbols-outlined text-primary mt-0.5">location_on</span>
                    <div><p class="font-label-md text-on-surface leading-tight">Destino</p><p class="text-[12px]">${o.comprador_nombre} (${o.comprador_correo}) · ${o.zona || 'Sin zona registrada'}</p></div>
                </div>
                <div class="flex items-center gap-4 text-on-surface-variant p-3">
                    <span class="material-symbols-outlined text-primary">inventory</span>
                    <span class="font-label-md">${itemsSummary}</span>
                </div>
            </div>
            <button class="w-full py-4 bg-primary text-white rounded-2xl font-label-md hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2" onclick="assignDelivery(${o.id})">
                <span class="material-symbols-outlined">add_circle</span> Asignarme esta entrega
            </button>
        </div>`;
            }).join('');
        }
        async function assignDelivery(pedidoId) {
            const body = new URLSearchParams({ accion: 'asignar_entrega', id_pedido: pedidoId });
            const response = await fetch('panel.php', { method: 'POST', body });
            const data = await response.json();
            if (data.status === 'success') {
                location.reload();
                return;
            }
            alert(data.message || 'No se pudo asignar la entrega.');
        }

        // BLOQUE REPARTIDOR: ACTIVA
        function renderActiveDelivery() {
            const active = DB_PEDIDOS.find(o => Number(o.id_repartidor) === SESSION.id && Number(o.estado) === 1);
            const container = document.getElementById('activeDeliveryContainer');
            if (!active) {
                container.innerHTML = `
        <div class="text-center py-12 px-6 bg-surface-container/30 rounded-[28px] border-2 border-dashed border-outline-variant">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">task</span>
            <p class="font-headline-sm text-outline-variant">Sin entregas activas</p>
            <p class="text-label-md text-on-surface-variant/60">Selecciona una del listado para comenzar.</p>
        </div>`;
                return;
            }
            const itemsSummary = active.items.map(i => `${i.cantidad}x ${i.producto_nombre}`).join(', ');
            container.innerHTML = `
        <div class="glass-card border-2 border-primary p-gutter rounded-[28px] shadow-forest-active relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4"><span class="px-3 py-1 rounded-full bg-primary text-white text-[10px] font-bold uppercase tracking-widest">En curso</span></div>
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 bg-primary-fixed/40 rounded-2xl flex items-center justify-center shadow-inner"><span class="material-symbols-outlined text-primary text-[32px]">directions_bike</span></div>
                <div><h4 class="font-bold text-primary text-[20px]">#${active.id}</h4><p class="text-label-md text-on-surface-variant">Cliente: ${active.comprador_nombre}</p></div>
            </div>
            <div class="bg-surface-container-low/30 p-5 rounded-2xl mb-4 border border-outline-variant/30">
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Destino (entrega)</p>
                <p class="font-body-md font-bold text-primary">${active.comprador_nombre} — ${active.comprador_correo} · ${active.zona || 'Sin zona registrada'}</p>
            </div>
            <div class="bg-surface-container-low/30 p-5 rounded-2xl mb-8 border border-outline-variant/30">
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Contenido del pedido</p>
                <p class="font-body-md font-bold text-primary">${itemsSummary}</p>
            </div>
            <button class="w-full py-3.5 bg-primary text-white rounded-2xl font-label-md shadow-lg shadow-primary/20 hover:opacity-90 transition-all flex items-center justify-center gap-2" onclick="confirmDelivery(${active.id})">
                <span class="material-symbols-outlined text-[20px]">check</span> Confirmar Entrega
            </button>
        </div>`;
        }
        async function confirmDelivery(pedidoId) {
            const body = new URLSearchParams({ accion: 'confirmar_entrega', id_pedido: pedidoId });
            const response = await fetch('panel.php', { method: 'POST', body });
            const data = await response.json();
            if (data.status === 'success') {
                location.reload();
                return;
            }
            alert(data.message || 'No se pudo completar la entrega.');
        }

        // BLOQUE REPARTIDOR: HISTORIAL
        function renderHistory() {
            const orders = DB_PEDIDOS.filter(o => Number(o.id_repartidor) === SESSION.id && Number(o.estado) === 2);
            const list = document.getElementById('historyList');
            list.innerHTML = orders.map(o => `
        <div class="bg-white rounded-xl p-4 border border-outline-variant/30 flex justify-between items-center">
            <div><p class="font-bold text-primary">#${o.id} · ${o.comprador_nombre}</p><p class="text-label-sm text-on-surface-variant">${o.fecha}</p></div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase bg-primary-fixed/60 text-on-primary-fixed">Entregado</span>
        </div>
    `).join('') || '<p class="text-on-surface-variant">Aún no has completado entregas.</p>';
        }

        // BLOQUE DISTRIBUIDOR: DASHBOARD
        function renderDistribuidorDashboard() {
            const productosActivos = DB_PRODUCTOS.filter(p => Number(p.estado) === 1);
            const pedidosProceso = DB_PEDIDOS.filter(o => Number(o.estado) === 0 || Number(o.estado) === 1);
            const productores = new Set(productosActivos.map(p => p.nom_productor));
            const valorInventario = productosActivos.reduce((a, p) => a + (parseFloat(p.precio) * p.cantidad), 0);

            document.getElementById('kpiInventario').textContent = productosActivos.length;
            document.getElementById('kpiProductores').textContent = productores.size;
            document.getElementById('kpiPedidosProceso').textContent = pedidosProceso.length;
            document.getElementById('kpiValorInventario').textContent = `$${valorInventario.toFixed(2)}`;

            const statusColor = { pendiente: 'bg-surface-container-highest/80 text-on-surface-variant', 'en camino': 'bg-secondary-container/80 text-on-secondary-container' };
            const tbody = document.getElementById('distribuidorOrdersBody');
            tbody.innerHTML = pedidosProceso.slice(0, 8).map(o => `
        <tr class="table-row-hover transition-colors">
            <td class="px-gutter py-5 font-bold text-primary">#${o.id}</td>
            <td class="px-gutter py-5">${o.zona || '—'}</td>
            <td class="px-gutter py-5">${o.comprador_nombre}</td>
            <td class="px-gutter py-5"><span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase ${statusColor[o.estado_label] || statusColor.pendiente}">${o.estado_label}</span></td>
            <td class="px-gutter py-5 font-bold text-primary">$${parseFloat(o.total_compra).toFixed(2)}</td>
        </tr>`).join('') || '<tr><td class="px-gutter py-5 text-on-surface-variant" colspan="5">Sin pedidos en proceso.</td></tr>';
        }

        // BLOQUE DISTRIBUIDOR: INVENTARIO
        function renderInventario() {
            const productos = DB_PRODUCTOS.filter(p => Number(p.estado) === 1);
            const tbody = document.getElementById('inventarioBody');
            tbody.innerHTML = productos.map(p => `
        <tr class="table-row-hover transition-colors">
            <td class="px-gutter py-5 font-bold text-on-surface">${p.nombre}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${p.nom_productor}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${p.cantidad}</td>
            <td class="px-gutter py-5 font-bold text-primary">$${parseFloat(p.precio).toFixed(2)}</td>
            <td class="px-gutter py-5 font-bold text-primary">$${(parseFloat(p.precio) * p.cantidad).toFixed(2)}</td>
        </tr>
    `).join('') || '<tr><td class="px-gutter py-5 text-on-surface-variant" colspan="5">No hay inventario disponible.</td></tr>';
        }

        // BLOQUE DISTRIBUIDOR: PRODUCTORES
        function renderProductores() {
            const productos = DB_PRODUCTOS.filter(p => Number(p.estado) === 1);
            const byProducer = {};
            productos.forEach(p => {
                if (!byProducer[p.nom_productor]) byProducer[p.nom_productor] = { count: 0, unidades: 0 };
                byProducer[p.nom_productor].count++;
                byProducer[p.nom_productor].unidades += p.cantidad;
            });
            const tbody = document.getElementById('productoresBody');
            tbody.innerHTML = Object.entries(byProducer).map(([nombre, d]) => `
        <tr class="table-row-hover transition-colors">
            <td class="px-gutter py-5 font-bold text-on-surface">${nombre}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${d.count}</td>
            <td class="px-gutter py-5 text-on-surface-variant">${d.unidades}</td>
        </tr>
    `).join('') || '<tr><td class="px-gutter py-5 text-on-surface-variant" colspan="3">No hay productores con inventario activo.</td></tr>';
        }

        // BLOQUE DISTRIBUIDOR: PEDIDOS EN MOVIMIENTO
        function renderPedidosDistribuidor() {
            const orders = DB_PEDIDOS.filter(o => Number(o.estado) === 0 || Number(o.estado) === 1);
            const container = document.getElementById('pedidosDistribuidorList');
            const noPedidos = document.getElementById('noPedidosDistribuidor');
            if (orders.length === 0) { container.innerHTML = ''; noPedidos.classList.remove('hidden'); return; }
            noPedidos.classList.add('hidden');
            container.innerHTML = orders.map(o => {
                const itemsSummary = o.items.map(i => `${i.cantidad}x ${i.producto_nombre}`).join(', ');
                const statusStyle = o.estado_label === 'pendiente' ? 'bg-surface-container-highest text-on-surface-variant' : 'bg-secondary-container text-on-secondary-container';
                return `
        <div class="glass-card border-l-4 border-l-primary p-gutter rounded-[28px] shadow-forest">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="text-[10px] font-bold text-on-secondary-container bg-secondary-fixed px-3 py-1 rounded-full uppercase tracking-widest">Pedido</span>
                    <h4 class="font-headline-sm mt-3 text-[20px] text-primary">#${o.id}</h4>
                </div>
                <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase ${statusStyle}">${o.estado_label}</span>
            </div>
            <div class="space-y-2 text-label-sm text-on-surface-variant">
                <p><span class="font-bold text-on-surface">Zona:</span> ${o.zona || '—'}</p>
                <p><span class="font-bold text-on-surface">Destino:</span> ${o.comprador_nombre}</p>
                <p><span class="font-bold text-on-surface">Contenido:</span> ${itemsSummary}</p>
                <p><span class="font-bold text-on-surface">Total:</span> $${parseFloat(o.total_compra).toFixed(2)}</p>
            </div>
        </div>`;
            }).join('');
        }

        window.onload = init;
    </script>
</body>

</html>
