<?php
// VISTA MVC: este archivo conserva el HTML del catalogo.
session_start();

require_once __DIR__ . "/../conexion.php";
require_once __DIR__ . "/../app/Controllers/CatalogoController.php";

//BLOQUE VALIDAR SESION ACTIVA
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$totalCarrito = array_sum(is_array($_SESSION['carrito'] ?? null) ? $_SESSION['carrito'] : []);
$rolSesion = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
$rolActual = match ($rolSesion) {
    'administrador' => 'admin',
    default => $rolSesion
};

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $rolActual === 'consumidor') {
    header('Location: ../panel.php#productos');
    exit;
}

$controller = new CatalogoController(new SistemaDachiFacade($conn));

//BLOQUE PROCESAR PETICIONES AJAX (busqueda y detalle)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: application/json');
    $respuesta = $controller->handle($_POST);

    echo json_encode($respuesta);
    $conn->close();
    exit();
}

// BLOQUE AJUSTES (APARIENCIA) - GUARDADO EN COOKIES, misma cookie que panel.php
$ajustes = isset($_COOKIE['dachi_ajustes']) ? json_decode($_COOKIE['dachi_ajustes'], true) : [];
$fontSize = $ajustes['fontSize'] ?? 'mediano';
$darkMode = !empty($ajustes['darkMode']);
$fontSizesPx = ['pequeno' => '14px', 'mediano' => '16px', 'grande' => '18px'];

//BLOQUE CARGA INICIAL (server-side) para que el catalogo no dependa de JS para el primer render
$catalogoInicial = $controller->initialCatalog();
$conn->close();
?>
<!DOCTYPE html>
<html class="light<?= $darkMode ? ' dark' : '' ?>" lang="es" style="font-size:<?= $fontSizesPx[$fontSize] ?>">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Catálogo de Productos</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="../css/dachi-brand.css" rel="stylesheet" />
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

        html.dark .bg-secondary-container\/40 {
            background-color: rgba(92, 66, 0, 0.4) !important;
            color: #ffdfa0 !important;
        }

        html.dark .border-secondary-container {
            border-color: #5c4200 !important;
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

        html.dark .bg-error-container {
            background-color: rgba(147, 0, 10, 0.25) !important;
            color: #ffb4ab !important;
        }

        html.dark .bg-error-container\/40 {
            background-color: rgba(147, 0, 10, 0.25) !important;
            color: #ffb4ab !important;
        }

        html.dark .text-on-error-container {
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
    <link href="../css/dachi-botanical.css" rel="stylesheet" />
</head>

<body class="dachi-app bg-background text-on-background font-body-md min-h-screen glass-container flex flex-col">
    <!-- BLOQUE SIDEBAR OVERLAY -->
    <div class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity" id="sidebarOverlay"
        onclick="toggleSidebar()"></div>
    <!-- BLOQUE SIDEBAR -->
    <aside
        class="fixed left-0 top-0 h-full w-72 bg-surface-container-lowest border-r border-outline-variant z-[70] -translate-x-full sidebar-transition flex flex-col"
        id="sidebar">
        <div class="p-gutter flex items-center justify-between border-b border-outline-variant h-16">
            <div class="dachi-sidebar-brand">
                <img src="../img/LG.png" alt="DACHI" />
                <span>Gesti&oacute;n agr&iacute;cola</span>
            </div>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="toggleSidebar()">
                <span class="material-symbols-outlined">close</span>
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
    <nav class="bg-surface/80 backdrop-blur-md w-full top-0 sticky z-50 border-b border-outline-variant shadow-sm">
        <div class="flex justify-between items-center px-gutter py-stack-sm max-w-container-max mx-auto h-16">
            <div class="flex items-center gap-stack-md">
                <button class="p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-colors"
                    onclick="toggleSidebar()">
                    <span class="material-symbols-outlined text-primary">menu</span>
                </button>
                <span class="dachi-wordmark text-primary hidden sm:inline">DACHI</span>
            </div>
            <div class="flex items-center gap-3 relative">
                <a class="p-2 rounded-full hover:bg-surface-container-low text-on-surface-variant relative transition-colors"
                    href="carrito.php" title="Ver carrito">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span class="<?= $totalCarrito > 0 ? '' : 'hidden ' ?>absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-secondary-container text-on-secondary-container text-[11px] font-bold flex items-center justify-center"
                        id="contadorCarrito"><?= $totalCarrito ?></span>
                </a>
                <span class="font-label-md text-label-md text-on-surface-variant hidden sm:inline"
                    id="panelUserName"></span>
                <button
                    class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden flex items-center justify-center font-bold text-primary cursor-pointer"
                    id="userAvatarBtn" onclick="toggleUserMenu()">
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
                    <a class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-on-surface flex items-center gap-3"
                        href="../panel.php">
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">dashboard</span>
                        Ir al Panel
                    </a>
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

    <main class="w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-stack-xl relative z-10 flex-1">
        <div class="mb-stack-xl">
            <div class="max-w-2xl">
                <h1 class="font-display-lg text-headline-md text-primary">Productos disponibles</h1>
                <p class="text-on-surface-variant font-body-md mt-1">Explora la selección activa de productores
                    panameños en DACHI.</p>
            </div>
            <div class="relative w-full max-w-2xl mt-stack-lg">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary text-[22px] pointer-events-none">search</span>
                <input class="w-full min-h-14 pl-12 pr-5 py-3 bg-surface-container-lowest border border-outline-variant/70 rounded-lg shadow-sm focus:ring-2 focus:ring-primary/25 focus:border-primary font-body-md text-body-md text-on-surface placeholder:text-on-surface-variant/80 outline-none transition-all"
                    id="buscador" oninput="buscarConDebounce()" placeholder="Buscar por nombre o productor..." type="text" />
            </div>
        </div>

        <div id="avisoCarrito" class="hidden mb-stack-md p-3 bg-secondary-container/40 border border-secondary-container rounded-xl text-on-secondary-container font-label-sm text-label-sm text-center"></div>

        <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter"></div>
        <p class="hidden text-center py-16 text-on-surface-variant" id="sinResultados">No se encontraron productos con esa búsqueda.</p>
    </main>

    <footer class="bg-surface-container-highest/40 backdrop-blur-md w-full mt-auto border-t border-outline-variant">
        <div
            class="flex flex-col md:flex-row justify-between items-center px-margin-mobile md:px-margin-desktop py-stack-md w-full max-w-container-max mx-auto">
            <div class="mb-stack-md md:mb-0">
                <span class="dachi-wordmark text-outline text-[14px]">DACHI</span>
                <p class="text-on-surface-variant font-body-md text-[13px]">© 2026 DACHI. Cultivando confianza.</p>
            </div>
        </div>
    </footer>

    <!-- BLOQUE DETALLE PRODUCTO -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay"
        id="detalleOverlay" onclick="if (event.target === this) cerrarDetalle()">
        <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
            <div class="flex justify-between items-start mb-4">
                <span class="inline-block px-3 py-1 bg-primary-container/10 text-primary font-label-sm text-label-sm rounded-full uppercase tracking-wide" id="detalleProductor"></span>
                <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="cerrarDetalle()" type="button">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <h2 class="font-headline-sm text-headline-sm text-primary mb-2" id="detalleNombre"></h2>
            <div class="flex items-center gap-4 mb-4">
                <span class="font-headline-md text-2xl text-secondary" id="detallePrecio"></span>
                <span class="font-label-sm text-label-sm text-on-surface-variant" id="detalleStock"></span>
            </div>
            <p class="text-on-surface-variant mb-stack-lg" id="detalleDescripcion"></p>
            <button class="w-full bg-primary text-on-primary py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                id="detalleBotonCarrito" type="button">
                <span class="material-symbols-outlined">shopping_cart</span>
                <span id="detalleBotonTexto">Agregar al carrito</span>
            </button>
        </div>
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
        //BLOQUE ESTADO INICIAL
        const RESPUESTA_INICIAL = <?= json_encode($catalogoInicial) ?>;
        const SESSION = <?= json_encode([
            'id' => $usuarioActual['id'],
            'nombre' => $usuarioActual['nombre'],
            'apellido' => $usuarioActual['apellido'] ?? '',
            'correo' => $usuarioActual['correo'],
            'telefono' => $usuarioActual['telefono'] ?? '',
            'rol' => $rolActual
        ]) ?>;
        const AJUSTES_ACTUALES = <?= json_encode(['fontSize' => $fontSize, 'darkMode' => $darkMode]) ?>;
        const ROLE_LABELS = {
            admin: 'PERFIL DE ADMIN',
            logistico: 'PERFIL LOGISTICO',
            productor: 'PERFIL DE PRODUCTOR',
            consumidor: 'PERFIL DE CONSUMIDOR'
        };
        let debounceTimer = null;

        function logout() {
            window.location.href = '../panel.php?logout=1';
        }

        // BLOQUE AJUSTES (identico a panel.php, comparten la misma cookie)
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
        function toggleUserMenu() { document.getElementById('userMenu').classList.toggle('open'); }
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('userMenu');
            const btn = document.getElementById('userAvatarBtn');
            if (menu && menu.classList.contains('open') && !menu.contains(e.target) && !btn.contains(e.target)) menu.classList.remove('open');
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
        // Reutiliza el mismo endpoint de panel.php (accion=guardar_perfil) en vez de
        // duplicar la logica de actualizacion de usuario en esta pagina.
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const body = new URLSearchParams({
                accion: 'guardar_perfil',
                nombre: document.getElementById('profileNombre').value.trim(),
                apellido: document.getElementById('profileApellido').value.trim(),
                telefono: document.getElementById('profileTelefono').value.trim()
            });
            await fetch('../panel.php', { method: 'POST', body });
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
        const NAV_CATALOGO = [
            { href: '../panel.php', label: 'Inicio', icon: 'dashboard' },
            { href: 'catalogo.php', label: 'Catálogo', icon: 'grid_view', activo: true }
        ];
        if (SESSION.rol === 'consumidor') {
            NAV_CATALOGO.push({ href: 'seguimiento.php', label: 'Mis pedidos', icon: 'package_2' });
        }
        function buildSidebar(items) {
            const nav = document.getElementById('sidebarNav');
            nav.innerHTML = items.map((it) => `
        <a class="nav-item flex items-center gap-4 p-3 rounded-xl transition-all cursor-pointer ${it.activo ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'hover:bg-primary-fixed/20 text-on-surface-variant hover:text-primary'}" href="${it.href}">
            <span class="material-symbols-outlined">${it.icon}</span>
            <span class="font-label-md">${it.label}</span>
        </a>
    `).join('');
        }
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
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

        //BLOQUE CATALOGO
        function renderGrid(productos) {
            const grid = document.getElementById('productGrid');
            const sinResultados = document.getElementById('sinResultados');

            if (!productos || productos.length === 0) {
                grid.innerHTML = '';
                sinResultados.classList.remove('hidden');
                return;
            }

            sinResultados.classList.add('hidden');
            grid.innerHTML = productos.map(renderCard).join('');
        }

        function iniciales(nombre) {
            return (nombre || '?').trim().charAt(0).toUpperCase();
        }

        function renderCard(p) {
            const sinStock = !p.disponible;
            const badge = sinStock
                ? '<span class="absolute top-3 right-3 bg-error-container text-on-error-container text-[11px] font-bold uppercase tracking-wide px-2 py-1 rounded-full">Sin stock</span>'
                : '';
            return `
                <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden hover:shadow-lg transition-shadow cursor-pointer relative"
                     onclick="verDetalle(${p.id})">
                    ${badge}
                    <div class="aspect-square bg-primary-container/10 flex items-center justify-center">
                        <span class="font-headline-md text-headline-md text-primary/40">${iniciales(p.nombre)}</span>
                    </div>
                    <div class="p-4">
                        <p class="font-label-sm text-label-sm text-on-surface-variant mb-1 truncate">${p.productor}</p>
                        <h3 class="font-label-md text-label-md text-on-surface mb-2 truncate" title="${p.nombre}">${p.nombre}</h3>
                        <div class="flex items-center justify-between">
                            <span class="font-headline-sm text-[18px] text-secondary">$${p.precio.toFixed(2)}</span>
                            <span class="text-[12px] text-on-surface-variant">${p.cantidad} disp.</span>
                        </div>
                    </div>
                </div>
            `;
        }

        //BLOQUE BUSQUEDA
        function buscarConDebounce() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(ejecutarBusqueda, 300);
        }

        function ejecutarBusqueda() {
            const termino = document.getElementById('buscador').value.trim();
            const datos = new FormData();
            datos.append('accion', 'buscar');
            datos.append('busqueda', termino);

            fetch('catalogo.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderGrid(data.productos);
                    }
                })
                .catch(() => {
                    document.getElementById('sinResultados').textContent = 'Error de conexión con el servidor.';
                    document.getElementById('sinResultados').classList.remove('hidden');
                });
        }

        //BLOQUE DETALLE
        function verDetalle(id) {
            const datos = new FormData();
            datos.append('accion', 'detalle');
            datos.append('id', id);

            fetch('catalogo.php', { method: 'POST', body: datos })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') {
                        alert(data.message);
                        return;
                    }
                    mostrarDetalle(data.producto);
                })
                .catch(() => {
                    alert('Error de conexión con el servidor.');
                });
        }

        function mostrarDetalle(p) {
            document.getElementById('detalleProductor').textContent = p.productor;
            document.getElementById('detalleNombre').textContent = p.nombre;
            document.getElementById('detallePrecio').textContent = `$${p.precio.toFixed(2)}`;
            document.getElementById('detalleStock').textContent = p.disponible
                ? `Disponible: ${p.cantidad}`
                : 'Sin stock disponible';
            document.getElementById('detalleDescripcion').textContent = p.descripcion;

            const boton = document.getElementById('detalleBotonCarrito');
            const textoBoton = document.getElementById('detalleBotonTexto');
            if (p.disponible) {
                boton.disabled = false;
                textoBoton.textContent = 'Agregar al carrito';
                boton.onclick = () => agregarProductoAlCarrito(p.id);
            } else {
                boton.disabled = true;
                textoBoton.textContent = 'Sin stock disponible';
                boton.onclick = null;
            }

            document.getElementById('detalleOverlay').classList.remove('hidden');
            document.getElementById('detalleOverlay').classList.add('flex');
        }

        function cerrarDetalle() {
            document.getElementById('detalleOverlay').classList.add('hidden');
            document.getElementById('detalleOverlay').classList.remove('flex');
        }

        //BLOQUE CARRITO
        async function agregarProductoAlCarrito(idProducto) {
            const datos = new FormData();
            datos.append('accion', 'agregar');
            datos.append('id_producto', idProducto);
            datos.append('cantidad', 1);

            try {
                const respuesta = await fetch('carrito.php', { method: 'POST', body: datos });
                const data = await respuesta.json();
                mostrarAvisoCarrito(data.message, data.status !== 'success');

                if (data.status === 'success') {
                    const contador = document.getElementById('contadorCarrito');
                    contador.textContent = data.total_unidades;
                    contador.classList.remove('hidden');
                    cerrarDetalle();
                }
            } catch (_error) {
                mostrarAvisoCarrito('No se pudo conectar con el carrito.', true);
            }
        }

        function mostrarAvisoCarrito(mensaje, esError = false) {
            const aviso = document.getElementById('avisoCarrito');
            aviso.textContent = mensaje;
            aviso.className = `mb-stack-md p-3 border rounded-lg font-label-sm text-label-sm text-center ${esError
                ? 'bg-error-container border-error/30 text-error'
                : 'bg-primary-fixed border-primary/20 text-primary'}`;
            aviso.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        //BLOQUE INIT
        function init() {
            applyAjustes(AJUSTES_ACTUALES);
            refreshAvatarUI();
            buildSidebar(NAV_CATALOGO);
            renderGrid(RESPUESTA_INICIAL.productos || []);
        }
        window.onload = init;
    </script>
</body>

</html>
