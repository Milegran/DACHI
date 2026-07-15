<?php
/** @var array $usuarioActual */
/** @var bool $darkMode */
/** @var string $fontSize */
/** @var array $fontSizesPx */
/** @var int $notificationCount */
/** @var array $pedidos */
?>
<!DOCTYPE html>
<html class="light<?= $darkMode ? ' dark' : '' ?>" lang="es" style="font-size:<?= $fontSizesPx[$fontSize] ?>">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Panel de Logística</title>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        try {
            tailwind.config = {
                darkMode: "class",
                theme: {
                    extend: {
                        "colors": {
                            "primary": "#26402f", "primary-container": "#33513e", "on-primary-container": "#a3c2ae",
                            "primary-fixed": "#b9efc7", "primary-fixed-dim": "#9ed3ac", "on-primary-fixed": "#00210e",
                            "on-primary-fixed-variant": "#1f5032", "secondary": "#f6be39", "secondary-container": "#ffc641",
                            "on-secondary-container": "#4a3a00", "secondary-fixed": "#ffdfa0", "secondary-fixed-dim": "#f6be39",
                            "on-secondary-fixed": "#261a00", "tertiary": "#491a21", "tertiary-container": "#642f36",
                            "on-tertiary-container": "#e0979f", "surface": "#f7faf5",
                            "surface-dim": "#d8dbd6", "surface-bright": "#f7faf5", "surface-container-lowest": "#ffffff",
                            "surface-container-low": "#f1f4f0", "surface-container": "#ecefea", "surface-container-high": "#e6e9e4",
                            "surface-container-highest": "#e0e3df", "on-surface": "#191c1a", "on-surface-variant": "#414942",
                            "outline": "#717971", "outline-variant": "#c1c9bf", "background": "#f7faf5", "on-background": "#191c1a",
                            "error": "#ba1a1a", "error-container": "#ffdad6", "on-error": "#ffffff"
                        },
                        "borderRadius": { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "3xl": "1.5rem", "full": "9999px" },
                        "spacing": { "stack-sm": "8px", "stack-md": "16px", "margin-mobile": "16px", "stack-lg": "32px", "stack-xl": "64px", "container-max": "1280px", "gutter": "24px", "base": "8px", "margin-desktop": "48px" },
                        "fontFamily": { "headline-sm": ["\"Source Serif 4\""], "label-md": ["Hanken Grotesk"], "display-lg-mobile": ["\"Source Serif 4\""], "body-lg": ["Hanken Grotesk"], "headline-md": ["\"Source Serif 4\""], "display-lg": ["\"Source Serif 4\""], "body-md": ["Hanken Grotesk"], "label-sm": ["Hanken Grotesk"] },
                        "fontSize": { "headline-sm": ["24px", { "lineHeight": "32px", "fontWeight": "600" }], "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "600" }], "display-lg-mobile": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }], "headline-md": ["32px", { "lineHeight": "40px", "fontWeight": "600" }], "display-lg": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }], "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }], "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }] }
                    }
                }
            }
        } catch (_e) { }
    </script>
    <link href="css/sky.css" rel="stylesheet" />
    <style>
        html.dark body { background-color: #10140f !important; color: #e5e9e2 !important; }
        html.dark .glass-card { background: #1a1f18 !important; border-color: rgba(255,255,255,0.08) !important; }
        html.dark .text-on-surface { color: #e5e9e2 !important; }
        html.dark .text-on-surface-variant { color: #a7b0a4 !important; }
        html.dark .bg-surface-container-low,
        html.dark .bg-surface-container,
        html.dark .bg-surface-container-high,
        html.dark .bg-surface-container-lowest,
        html.dark .bg-white { background-color: #1c211b !important; }
        html.dark input,
        html.dark textarea,
        html.dark select { background-color: #20261f !important; color: #e5e9e2 !important; border-color: rgba(148,158,143,0.2) !important; }
        html.dark .border-outline-variant\/20,
        html.dark .border-outline-variant\/30,
        html.dark .border-outline-variant\/40,
        html.dark .border-outline-variant\/50 { border-color: rgba(148,158,143,0.15) !important; }
        html.dark .hover\:bg-surface-container-low:hover { background-color: #262c24 !important; }
        html.dark ::-webkit-scrollbar-thumb { background: #33513e !important; }
        html.dark #sidebar { background-color: #12160f !important; border-color: rgba(255,255,255,0.06) !important; }
        html.dark #sidebarBrandText { color: #9ed3ac !important; }
        html.dark #sidebarNav .nav-link,
        html.dark #sidebarFooter .nav-link:not(.text-error) { color: #cfd6cb !important; }
        html.dark #sidebarNav .nav-link:hover { background: rgba(255,255,255,0.06) !important; color: #fff !important; }
        html.dark #sidebarNav .nav-link.nav-item-active { background: rgba(158,211,172,0.18) !important; color: #9ed3ac !important; }
        html.dark #sidebarCollapseBtn,
        html.dark #sidebarExpandBtn { border-color: rgba(255,255,255,0.15) !important; color: #cfd6cb !important; }
        html.dark .bg-surface\/60 { background-color: rgba(16,20,15,0.75) !important; }
        html.dark .text-primary { color: #9ed3ac !important; }
        html.dark .text-primary\/60,
        html.dark .text-primary\/50 { color: rgba(158,211,172,0.6) !important; }
        html.dark .bg-primary\/10 { background-color: rgba(158,211,172,0.12) !important; }
        html.dark .bg-outline-variant\/30,
        html.dark .bg-outline-variant\/50 { background-color: rgba(148,158,143,0.15) !important; }
        html.dark .bg-outline-variant { background-color: #4a544c !important; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen transition-colors duration-300">
    <!-- BLOQUE SIDEBAR OVERLAY -->
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[60] hidden transition-opacity" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <!-- BLOQUE SIDEBAR -->
    <aside class="fixed left-0 top-0 h-full w-72 bg-white border-r border-outline-variant/20 z-[70] -translate-x-full lg:translate-x-0 sidebar-transition flex flex-col shadow-2xl" id="sidebar">
        <div class="p-6 flex items-center justify-between gap-2" id="sidebarHeader">
            <div class="flex items-center gap-2 min-w-0" id="sidebarLogoWrap">
                <img alt="Logo DACHI" class="h-10 object-contain shrink-0" src="img/Navbar.png" />
                <p class="text-primary font-bold text-[10px] uppercase tracking-widest opacity-80 leading-tight" id="sidebarBrandText">Panel de<br>Logística</p>
            </div>
            <button class="hidden lg:flex w-8 h-8 items-center justify-center rounded-lg border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container-low transition-all shrink-0" id="sidebarCollapseBtn" onclick="toggleSidebarCollapse()" title="Ocultar menú" type="button">
                <span class="material-symbols-outlined text-[18px]">keyboard_double_arrow_left</span>
            </button>
        </div>
        <nav class="flex-grow overflow-y-auto custom-scrollbar space-y-0.5 mt-2" id="sidebarNav">
            <!-- Populado por JS -->
        </nav>
        <div class="p-4 border-t border-outline-variant/10" id="sidebarFooter">
            <a class="nav-link w-full flex items-center gap-3 group" href="logistica.php?logout=1">
                <span class="material-symbols-outlined text-[19px]">logout</span>
                <span class="font-label-md font-bold text-[13px] nav-label">Cerrar Sesión</span>
            </a>
        </div>
    </aside>
    <!-- WRAPPER -->
    <div class="lg:ml-72 transition-all duration-300" id="mainWrapper">
        <!-- BLOQUE NAVBAR -->
        <nav class="bg-surface/60 backdrop-blur-xl sticky top-0 z-50 border-b border-outline-variant/30 h-[80px] flex items-center gap-3 px-6 lg:px-12">
            <button class="lg:hidden p-2 rounded-2xl bg-primary-container/10 text-primary flex-none" onclick="toggleSidebar()">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <button class="w-9 h-9 items-center justify-center rounded-xl border border-outline-variant/30 text-on-surface-variant hover:bg-primary-container hover:text-white transition-all flex-none" id="sidebarExpandBtn" onclick="toggleSidebarCollapse()" title="Mostrar menú" type="button">
                <span class="material-symbols-outlined text-[18px]">keyboard_double_arrow_right</span>
            </button>

            <img alt="DACHI" class="h-[52px] w-auto object-contain flex-none" src="img/LG.png" />

            <div class="hidden flex-col flex-none" style="display:none">
                <h2 class="text-on-surface-variant text-[11px] font-bold uppercase tracking-[0.2em]" id="navBreadcrumb">Panel de Control</h2>
                <p class="text-primary font-headline-sm text-[20px]" id="navPageTitle">Resumen General</p>
            </div>

            <div class="relative flex-1 max-w-xl mx-auto hidden md:block">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline z-10">search</span>
                <input class="w-full h-11 pl-12 pr-4 rounded-full border border-transparent bg-surface-container-low focus:border-primary focus:bg-surface-container-lowest text-sm transition-all outline-none focus:ring-2 focus:ring-primary/20" placeholder="Buscar en el panel..." type="search" />
            </div>

            <div class="flex-1 md:hidden"></div>

            <div class="flex items-center gap-2 sm:gap-3 flex-none">
                <button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-all" onclick="toggleDarkModeQuick()" title="Modo oscuro" type="button">
                    <span class="material-symbols-outlined">dark_mode</span>
                </button>

                <div class="relative">
                    <button class="relative w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-all" id="notificationButton" onclick="toggleNotificationMenu()">
                        <span class="material-symbols-outlined">notifications</span>
                        <?php if ($notificationCount > 0): ?>
                        <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-error text-white text-[10px] font-bold flex items-center justify-center rounded-full px-1 ring-2 ring-surface"><?= $notificationCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="user-menu absolute right-0 top-14 w-80 bg-white dark:bg-surface-container-highest rounded-3xl border border-outline-variant/30 shadow-2xl p-4 z-[200]" id="notificationMenu">
                        <div class="flex justify-between items-center mb-4 pb-2 border-b border-outline-variant/30">
                            <h4 class="font-bold text-primary">Notificaciones</h4>
                            <span class="text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-bold" id="notifCountTag"><?= $notificationCount ?> NUEVAS</span>
                        </div>
                        <div class="space-y-3" id="notificationList">
                            <!-- Populado por JS -->
                        </div>
                    </div>
                </div>

                <button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-all" onclick="openSettingsModal()" title="Configuración" type="button">
                    <span class="material-symbols-outlined">settings</span>
                </button>

                <div class="h-8 w-[1px] bg-outline-variant/30 mx-1 hidden sm:block"></div>

                <div class="hidden md:block text-right mr-1">
                    <span class="font-semibold text-sm text-on-surface block" id="panelUserName"><?= htmlspecialchars($usuarioActual['nombre'] ?? '') ?></span>
                    <small class="text-on-surface-variant text-xs">Logístico</small>
                </div>

                <div class="relative">
                    <button class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden flex items-center justify-center font-bold text-primary cursor-pointer" id="userAvatarBtn" onclick="toggleUserMenu()" title="Abrir perfil" type="button">
                        <span class="avatar-initial"><?= strtoupper(substr($usuarioActual['nombre'] ?? '', 0, 1)) ?></span>
                    </button>
                    <div class="user-menu absolute right-0 top-12 w-72 bg-white dark:bg-surface-container-highest rounded-2xl border border-outline-variant/50 shadow-xl p-2 z-[200]" id="userMenu">
                        <div class="flex items-center gap-3 p-3 border-b border-outline-variant/30 mb-2">
                            <div class="w-12 h-12 rounded-full bg-primary-fixed overflow-hidden border border-outline-variant flex items-center justify-center font-bold text-primary flex-none">
                                <span class="avatar-initial"><?= strtoupper(substr($usuarioActual['nombre'] ?? '', 0, 1)) ?></span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-on-surface truncate" id="userMenuName"><?= htmlspecialchars(trim(($usuarioActual['nombre'] ?? '') . ' ' . ($usuarioActual['apellido'] ?? ''))) ?></p>
                                <p class="text-xs text-on-surface-variant truncate" id="userMenuEmail"><?= htmlspecialchars($usuarioActual['correo'] ?? '') ?></p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide">LOGÍSTICO</span>
                            </div>
                        </div>
                        <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-on-surface flex items-center gap-3" onclick="location.href='logistica.php?logout=1'">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">swap_horiz</span>
                            Cambiar Cuenta
                        </button>
                        <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-on-surface flex items-center gap-3" onclick="openSettingsModal()">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">tune</span>
                            Configuración
                        </button>
                        <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-on-surface flex items-center gap-3" onclick="openProfileModal()">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
                            Editar Perfil
                        </button>
                        <hr class="border-outline-variant/50 my-1" />
                        <a class="flex items-center gap-3 w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-error transition-all" href="logistica.php?logout=1">
                            <span class="material-symbols-outlined text-[18px]">logout</span> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <main class="p-6 lg:p-12 max-w-7xl mx-auto">
            <header class="mb-8 flex items-center gap-4">
                <div class="w-12 h-12 shrink-0">
                    <img alt="Logo DACHI" class="w-full h-full object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCpzWcUspRS3urrbnxJ3LQ2H1OvRCAA8UQ_pJV1p7pty2Mc8YE-S7y_oc_weYfVU85XEkSaFeXSqLj-NJ1dyR90GnPz0TJT36fw_9GpmArQ6lQvhII292m8L90op4F1SGMZI0JZm2xJfyOEb5ZFLEoHtCh0TAr7wCMTJBOtdx2lm3nGBiElUlEEZ1NLtMGm29oOWEY2ueqRfGJJY3AsmIhAPxYSHqT_y4RGAzOLKY6HnKeB2PmlKp0hj5cHiqSVZPyCyZxTJEWzmrE" />
                </div>
                <div>
                    <h1 class="font-headline-md text-[26px] leading-tight text-primary" id="panelTitle">Resumen General</h1>
                    <p class="text-on-surface-variant text-[13px] font-medium" id="roleSubtitle">Bienvenido al corazón de tu gestión logística agrícola.</p>
                </div>
            </header>

            <!-- DASHBOARD -->
            <section class="space-y-10" id="sec-dashboard">
                <!-- BLOQUE BANNER RESUMEN -->
                <div class="rounded-[40px] p-10 bg-primary relative overflow-hidden shadow-xl">
                    <div class="absolute -right-10 -top-10 w-52 h-52 rounded-full bg-white/5"></div>
                    <div class="absolute right-16 bottom-[-40px] w-32 h-32 rounded-full bg-secondary/20"></div>
                    <div class="relative flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                        <div>
                            <p class="text-secondary font-bold text-[11px] uppercase tracking-widest mb-2" id="bannerFecha">—</p>
                            <h3 class="text-white font-headline-sm text-[26px] mb-1">Hola, <?= htmlspecialchars($usuarioActual['nombre'] ?? 'Logístico') ?></h3>
                            <p class="text-white/60 text-[14px]" id="bannerResumen">Cargando resumen del día...</p>
                        </div>
                        <div class="flex gap-8">
                            <div class="text-center sm:text-right">
                                <p class="text-white/50 text-[10px] font-bold uppercase tracking-widest">Valor Activo</p>
                                <p class="text-white font-bold text-[24px]" id="bannerValorActivo">$0.00</p>
                            </div>
                            <div class="text-center sm:text-right">
                                <p class="text-white/50 text-[10px] font-bold uppercase tracking-widest">Total Pedidos</p>
                                <p class="text-white font-bold text-[24px]" id="bannerTotalPedidos">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE KPIS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="glass-card p-7 rounded-[32px] shadow-sm border-b-4 border-secondary">
                        <div class="w-14 h-14 rounded-[20px] bg-secondary-fixed/30 flex items-center justify-center text-secondary mb-5"><span class="material-symbols-outlined text-[26px]">local_shipping</span></div>
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">En Ruta</p>
                        <h2 class="text-[32px] font-bold text-primary leading-none mb-1" id="kpiEnRuta">0</h2>
                        <p class="text-[11px] text-on-surface-variant" id="kpiEnRutaCaption">Sin pedidos en tránsito.</p>
                    </div>
                    <div class="glass-card p-7 rounded-[32px] shadow-sm border-b-4 border-outline-variant">
                        <div class="w-14 h-14 rounded-[20px] bg-primary-container/10 flex items-center justify-center text-primary mb-5"><span class="material-symbols-outlined text-[26px]">pending_actions</span></div>
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Pendientes</p>
                        <h2 class="text-[32px] font-bold text-primary leading-none mb-1" id="kpiPendientesLog">0</h2>
                        <p class="text-[11px] text-on-surface-variant" id="kpiPendientesCaption">Al día.</p>
                    </div>
                    <div class="glass-card p-7 rounded-[32px] shadow-sm border-b-4 border-primary">
                        <div class="w-14 h-14 rounded-[20px] bg-tertiary-fixed/30 flex items-center justify-center text-tertiary mb-5"><span class="material-symbols-outlined text-[26px]">task_alt</span></div>
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Entregados Hoy</p>
                        <h2 class="text-[32px] font-bold text-primary leading-none mb-1" id="kpiEntregadosHoy">0</h2>
                        <p class="text-[11px] text-on-surface-variant" id="kpiEntregadosCaption">Nada entregado aún hoy.</p>
                    </div>
                    <div class="glass-card p-7 rounded-[32px] shadow-sm border-b-4 border-error/40">
                        <div class="w-14 h-14 rounded-[20px] bg-error-container/30 flex items-center justify-center text-error mb-5"><span class="material-symbols-outlined text-[26px]">cancel</span></div>
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Cancelados</p>
                        <h2 class="text-[32px] font-bold text-primary leading-none mb-1" id="kpiCancelados">0</h2>
                        <p class="text-[11px] text-on-surface-variant" id="kpiCanceladosCaption">Sin cancelaciones.</p>
                    </div>
                </div>

                <!-- BLOQUE DISTRIBUCION Y ZONAS -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="glass-card rounded-[40px] p-8 shadow-sm border border-outline-variant/20 lg:col-span-3">
                        <h3 class="font-headline-sm text-primary text-[18px] mb-6">Distribución de Pedidos por Estado</h3>
                        <div class="space-y-5" id="distribucionEstados"></div>
                    </div>
                    <div class="glass-card rounded-[40px] p-8 shadow-sm border border-outline-variant/20 lg:col-span-2">
                        <h3 class="font-headline-sm text-primary text-[18px] mb-6">Pedidos por Zona</h3>
                        <div class="space-y-4" id="distribucionZonas"></div>
                    </div>
                </div>

                <div class="glass-card rounded-[40px] shadow-sm overflow-hidden border border-outline-variant/20">
                    <div class="p-8 flex justify-between items-center border-b border-outline-variant/10">
                        <h3 class="font-headline-sm text-primary">Pedidos Recientes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-surface-container-low/50">
                                <tr class="text-[11px] font-bold uppercase tracking-widest text-on-surface-variant">
                                    <th class="px-8 py-5">ID Pedido</th><th class="px-8 py-5">Cliente</th><th class="px-8 py-5">Estado</th><th class="px-8 py-5">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10" id="recentOrdersBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- LOGISTICA -->
            <section class="space-y-8 hidden" id="sec-logistica-admin">
                <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-3">
                    <h3 class="font-headline-sm text-primary text-[24px]">Tablero de Entregas</h3>
                    <p class="text-[13px] text-on-surface-variant">Haz clic en un pedido para ver el detalle y cambiar su estado.</p>
                </div>

                <!-- BLOQUE FRANJA DE ESTADISTICAS RAPIDAS -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Total Pedidos</p>
                        <p class="text-[24px] font-bold text-primary" id="statTotalPedidos">0</p>
                    </div>
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Valor Total</p>
                        <p class="text-[24px] font-bold text-primary" id="statValorTotal">$0.00</p>
                    </div>
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Activos</p>
                        <p class="text-[24px] font-bold text-secondary" id="statActivos">0</p>
                    </div>
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Completados</p>
                        <p class="text-[24px] font-bold text-primary" id="statCompletados">0</p>
                    </div>
                </div>

                <!-- BLOQUE TABLERO KANBAN -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 px-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-outline-variant"></span>
                            <h4 class="font-bold text-on-surface-variant text-[12px] uppercase tracking-widest">Pendiente</h4>
                            <span class="text-[11px] font-bold text-on-surface-variant bg-surface-container-high px-2 py-0.5 rounded-full ml-auto" id="countPendiente">0</span>
                        </div>
                        <div class="space-y-4 min-h-[80px]" id="kanbanPendiente"></div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 px-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-outline-variant"></span>
                            <h4 class="font-bold text-on-surface-variant text-[12px] uppercase tracking-widest">En Preparación</h4>
                            <span class="text-[11px] font-bold text-on-surface-variant bg-surface-container-high px-2 py-0.5 rounded-full ml-auto" id="countPreparacion">0</span>
                        </div>
                        <div class="space-y-4 min-h-[80px]" id="kanbanPreparacion"></div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 px-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
                            <h4 class="font-bold text-on-surface-variant text-[12px] uppercase tracking-widest">En Tránsito</h4>
                            <span class="text-[11px] font-bold text-secondary bg-secondary/10 px-2 py-0.5 rounded-full ml-auto" id="countTransito">0</span>
                        </div>
                        <div class="space-y-4 min-h-[80px]" id="kanbanTransito"></div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 px-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                            <h4 class="font-bold text-on-surface-variant text-[12px] uppercase tracking-widest">Entregado</h4>
                            <span class="text-[11px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full ml-auto" id="countEntregado">0</span>
                        </div>
                        <div class="space-y-4 min-h-[80px]" id="kanbanEntregado"></div>
                    </div>
                </div>

            </section>

            <!-- MIS ENTREGAS -->
            <section class="space-y-8 hidden" id="sec-mis-entregas">
                <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-3">
                    <h3 class="font-headline-sm text-primary text-[24px]">Historial de Entregas</h3>
                    <p class="text-[13px] text-on-surface-variant">Pedidos entregados y cancelados, con su fecha de entrega.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Total</p>
                        <p class="text-[24px] font-bold text-primary" id="meStatTotal">0</p>
                    </div>
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Entregados</p>
                        <p class="text-[24px] font-bold text-primary" id="meStatEntregados">0</p>
                    </div>
                    <div class="glass-card rounded-3xl p-5 border border-outline-variant/20">
                        <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mb-1">Cancelados</p>
                        <p class="text-[24px] font-bold text-error" id="meStatCancelados">0</p>
                    </div>
                </div>

                <div class="glass-card rounded-[32px] p-6 border border-outline-variant/20 flex flex-col md:flex-row gap-4 md:items-center">
                    <div class="flex gap-2" id="meFiltroEstado">
                        <button class="me-filtro-btn px-4 py-2 rounded-2xl text-[12px] font-bold uppercase tracking-wide bg-primary text-white" data-filtro="todos" onclick="filtrarMisEntregas('todos', this)" type="button">Todos</button>
                        <button class="me-filtro-btn px-4 py-2 rounded-2xl text-[12px] font-bold uppercase tracking-wide bg-surface-container-high text-on-surface-variant" data-filtro="entregado" onclick="filtrarMisEntregas('entregado', this)" type="button">Entregados</button>
                        <button class="me-filtro-btn px-4 py-2 rounded-2xl text-[12px] font-bold uppercase tracking-wide bg-surface-container-high text-on-surface-variant" data-filtro="cancelado" onclick="filtrarMisEntregas('cancelado', this)" type="button">Cancelados</button>
                    </div>
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                        <input class="w-full h-11 pl-11 pr-4 rounded-2xl border border-outline-variant/40 bg-surface-container-low/50 focus:ring-2 focus:ring-primary outline-none text-[13px]" id="meBuscar" oninput="filtrarMisEntregas(null, null)" placeholder="Buscar por cliente o número de pedido..." type="search" />
                    </div>
                </div>

                <div class="glass-card rounded-[40px] shadow-sm overflow-hidden border border-outline-variant/20">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-surface-container-low/50">
                                <tr class="text-[11px] font-bold uppercase tracking-widest text-on-surface-variant">
                                    <th class="px-8 py-5">ID Pedido</th><th class="px-8 py-5">Cliente</th><th class="px-8 py-5">Zona</th><th class="px-8 py-5">Estado</th><th class="px-8 py-5">Fecha de Entrega</th><th class="px-8 py-5 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10" id="misEntregasBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ZONAS DE COBERTURA -->
            <section class="space-y-8 hidden" id="sec-zonas-cobertura">
                <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-3">
                    <h3 class="font-headline-sm text-primary text-[24px]">Zonas de Cobertura</h3>
                    <p class="text-[13px] text-on-surface-variant">Volumen de pedidos por zona, ordenado de mayor a menor.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="zonasCoberturaGrid"></div>
            </section>
        </main>
    </div>

    <!-- MODAL DETALLE DE PEDIDO -->
    <div class="fixed inset-0 bg-primary/60 backdrop-blur-md z-[300] hidden items-center justify-center p-6" id="pedidoModal">
        <div class="bg-white dark:bg-surface-container-highest rounded-[40px] w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar p-10 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest">Detalle del Pedido</p>
                    <h3 class="font-headline-sm text-primary text-[32px]" id="pedidoModalTitulo">#0</h3>
                </div>
                <button class="w-12 h-12 rounded-2xl hover:bg-surface-container-high transition-colors" onclick="cerrarDetallePedido()" type="button"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div class="p-5 rounded-3xl bg-surface-container-low/50 min-w-0">
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Cliente</p>
                    <p class="font-bold text-on-surface break-words" id="pedidoModalCliente">—</p>
                    <p class="text-[13px] text-on-surface-variant break-words" id="pedidoModalCorreo">—</p>
                    <p class="text-[13px] text-on-surface-variant break-words" id="pedidoModalTelefono">—</p>
                </div>
                <div class="p-5 rounded-3xl bg-surface-container-low/50 min-w-0">
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Dirección de Entrega</p>
                    <p class="text-[13px] text-on-surface break-words" id="pedidoModalDireccion">—</p>
                </div>
                <div class="p-5 rounded-3xl bg-surface-container-low/50 min-w-0">
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Método de Pago</p>
                    <p class="text-[13px] text-on-surface break-words" id="pedidoModalPago">—</p>
                </div>
                <div class="p-5 rounded-3xl bg-surface-container-low/50 min-w-0">
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-2">Fecha del Pedido</p>
                    <p class="text-[13px] text-on-surface break-words" id="pedidoModalFecha">—</p>
                </div>
            </div>

            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-widest mb-3">Productos</p>
            <div class="rounded-3xl border border-outline-variant/20 overflow-hidden mb-6">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-surface-container-low/50">
                        <tr class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">
                            <th class="px-5 py-3">Producto</th><th class="px-5 py-3">Cant.</th><th class="px-5 py-3">Precio</th><th class="px-5 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10" id="pedidoModalItems"></tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mb-8 px-2">
                <p class="font-bold text-on-surface-variant uppercase text-[12px] tracking-widest">Total</p>
                <p class="font-bold text-primary text-[24px]" id="pedidoModalTotal">$0.00</p>
            </div>

            <div class="space-y-3">
                <label class="text-[12px] font-bold text-on-surface-variant uppercase ml-2">Estado del Pedido</label>
                <select class="w-full px-5 py-4 rounded-3xl border border-outline-variant/40 bg-surface-container-low/30 focus:ring-2 focus:ring-primary outline-none font-bold text-primary" id="pedidoModalEstado">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_preparacion">En Preparación</option>
                    <option value="en_transito">En Tránsito</option>
                    <option value="entregado">Entregado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <button class="w-full py-5 bg-primary text-white rounded-[32px] font-bold shadow-xl hover:shadow-2xl transition-all" onclick="guardarEstadoPedido()" type="button">GUARDAR CAMBIO DE ESTADO</button>
            </div>
        </div>
    </div>

    <!-- MODAL PERFIL -->
    <div class="fixed inset-0 bg-primary/60 backdrop-blur-md z-[300] hidden items-center justify-center p-6" id="profileModal">
        <div class="bg-white rounded-[40px] w-full max-w-xl p-10 shadow-2xl">
            <div class="flex justify-between items-center mb-8">
                <h3 class="font-headline-sm text-primary text-[32px]">Editar Perfil</h3>
                <button class="w-12 h-12 rounded-2xl hover:bg-surface-container-high transition-colors" onclick="closeProfileModal()"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form class="space-y-6" id="profileForm">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[12px] font-bold text-on-surface-variant uppercase ml-2">Nombre</label>
                        <input class="w-full px-5 py-4 rounded-3xl border border-outline-variant/40 bg-surface-container-low/30 focus:ring-2 focus:ring-primary outline-none" id="profileNombre" required type="text" value="<?= htmlspecialchars($usuarioActual['nombre'] ?? '') ?>" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[12px] font-bold text-on-surface-variant uppercase ml-2">Apellido</label>
                        <input class="w-full px-5 py-4 rounded-3xl border border-outline-variant/40 bg-surface-container-low/30 focus:ring-2 focus:ring-primary outline-none" id="profileApellido" type="text" value="<?= htmlspecialchars($usuarioActual['apellido'] ?? '') ?>" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[12px] font-bold text-on-surface-variant uppercase ml-2">Teléfono</label>
                    <input class="w-full px-5 py-4 rounded-3xl border border-outline-variant/40 bg-surface-container-low/30 focus:ring-2 focus:ring-primary outline-none" id="profileTelefono" type="tel" value="<?= htmlspecialchars($usuarioActual['telefono'] ?? '') ?>" />
                </div>
                <button class="w-full py-5 bg-primary text-white rounded-[32px] font-bold shadow-xl hover:shadow-2xl transition-all" type="submit">ACTUALIZAR PERFIL</button>
            </form>
        </div>
    </div>

    <!-- MODAL AJUSTES -->
    <div class="fixed inset-0 bg-primary/60 backdrop-blur-md z-[300] hidden items-center justify-center p-6" id="settingsModal">
        <div class="bg-white rounded-[40px] w-full max-w-md p-10 shadow-2xl">
            <div class="flex justify-between items-center mb-8">
                <h3 class="font-headline-sm text-primary text-[32px]">Ajustes</h3>
                <button class="w-12 h-12 rounded-2xl hover:bg-surface-container-high transition-colors" onclick="closeSettingsModal()"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form class="space-y-10" id="settingsForm">
                <div class="flex items-center justify-between p-6 bg-surface-container-low/50 rounded-3xl">
                    <div>
                        <p class="font-bold text-primary">Modo Oscuro</p>
                        <p class="text-[12px] text-on-surface-variant">Estilo tecnológico y nocturno.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input class="sr-only peer" id="settingsDarkMode" type="checkbox" <?= $darkMode ? 'checked' : '' ?> />
                        <div class="w-14 h-8 bg-outline-variant/50 rounded-full peer peer-checked:bg-secondary transition-all after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:after:translate-x-6"></div>
                    </label>
                </div>
                <button class="w-full py-5 bg-primary text-white rounded-[32px] font-bold shadow-xl" type="submit">GUARDAR AJUSTES</button>
            </form>
        </div>
    </div>

    <script>
        // BLOQUE DATOS DESDE PHP (nada hardcodeado, todo viene de la BD)
        const SESSION = <?= json_encode([
            'id' => (int) $usuarioActual['id'],
            'nombre' => $usuarioActual['nombre'] ?? '',
            'apellido' => $usuarioActual['apellido'] ?? '',
            'correo' => $usuarioActual['correo'] ?? '',
            'telefono' => $usuarioActual['telefono'] ?? '',
        ]) ?>;
        const AJUSTES_ACTUALES = <?= json_encode(['fontSize' => $fontSize, 'darkMode' => $darkMode]) ?>;
        const DB_PEDIDOS = <?= json_encode($pedidos) ?>;

        const NAV_LOGISTICA = [
            { id: 'sec-dashboard', label: 'Dashboard', icon: 'grid_view' },
            { id: 'sec-logistica-admin', label: 'Logística', icon: 'local_shipping' },
            { id: 'sec-mis-entregas', label: 'Mis Entregas', icon: 'task_alt' },
            { id: 'sec-zonas-cobertura', label: 'Zonas de Cobertura', icon: 'map' }
        ];

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }

        function showSection(id, el) {
            document.querySelectorAll('main section').forEach(s => s.classList.add('hidden'));
            document.getElementById(id).classList.remove('hidden');
            sessionStorage.setItem('dachiSeccionActiva', id);

            const navItem = NAV_LOGISTICA.find(n => n.id === id);
            if (navItem) {
                document.getElementById('navPageTitle').textContent = navItem.label;
                document.getElementById('navBreadcrumb').textContent = `PANEL DE CONTROL / ${navItem.label.toUpperCase()}`;
                document.getElementById('panelTitle').textContent = navItem.label === 'Dashboard' ? 'Resumen General' : navItem.label;
            }
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('nav-item-active'));
            if (el) el.classList.add('nav-item-active');
            if (window.innerWidth < 1024) toggleSidebar();
        }

        function buildSidebar() {
            document.getElementById('sidebarNav').innerHTML = NAV_LOGISTICA.map((item, i) => `
                <button onclick="showSection('${item.id}', this)" title="${item.label}" class="nav-link w-full flex items-center gap-3 transition-all group ${i === 0 ? 'nav-item-active' : ''}">
                    <span class="material-symbols-outlined text-[19px] transition-transform group-hover:scale-110">${item.icon}</span>
                    <span class="font-label-md font-bold text-[13px] nav-label">${item.label}</span>
                </button>
            `).join('');
        }

        // BLOQUE COLAPSAR/MOSTRAR SIDEBAR (solo escritorio)
        function toggleSidebarCollapse() {
            const colapsado = document.documentElement.classList.toggle('sidebar-mini');
            localStorage.setItem('dachiSidebarMini', colapsado ? '1' : '0');
        }

        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('open');
            document.getElementById('notificationMenu').classList.remove('open');
        }
        function toggleNotificationMenu() {
            document.getElementById('notificationMenu').classList.toggle('open');
            document.getElementById('userMenu').classList.remove('open');
        }

        function openProfileModal() { document.getElementById('profileModal').classList.remove('hidden'); document.getElementById('profileModal').classList.add('flex'); toggleUserMenu(); }
        function closeProfileModal() { document.getElementById('profileModal').classList.add('hidden'); document.getElementById('profileModal').classList.remove('flex'); }
        function openSettingsModal() { document.getElementById('settingsModal').classList.remove('hidden'); document.getElementById('settingsModal').classList.add('flex'); toggleUserMenu(); }
        function closeSettingsModal() { document.getElementById('settingsModal').classList.add('hidden'); document.getElementById('settingsModal').classList.remove('flex'); }

        // BLOQUE MODO OSCURO RAPIDO (boton de luna en el navbar, misma cookie que Ajustes)
        function toggleDarkModeQuick() {
            const nuevoDarkMode = !document.documentElement.classList.contains('dark');
            const ajustes = { fontSize: AJUSTES_ACTUALES.fontSize || 'mediano', darkMode: nuevoDarkMode };
            document.cookie = 'dachi_ajustes=' + encodeURIComponent(JSON.stringify(ajustes)) + ';path=/;max-age=' + (60 * 60 * 24 * 365);
            document.documentElement.classList.toggle('dark', nuevoDarkMode);
            const checkboxAjustes = document.getElementById('settingsDarkMode');
            if (checkboxAjustes) checkboxAjustes.checked = nuevoDarkMode;
        }

        // BLOQUE PERFIL - conectado a accion=guardar_perfil
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const body = new URLSearchParams({
                accion: 'guardar_perfil',
                nombre: document.getElementById('profileNombre').value.trim(),
                apellido: document.getElementById('profileApellido').value.trim(),
                telefono: document.getElementById('profileTelefono').value.trim()
            });
            await fetch('logistica.php', { method: 'POST', body });
            location.reload();
        });

        // BLOQUE AJUSTES - cookie
        document.getElementById('settingsForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const a = { fontSize: AJUSTES_ACTUALES.fontSize || 'mediano', darkMode: document.getElementById('settingsDarkMode').checked };
            document.cookie = 'dachi_ajustes=' + encodeURIComponent(JSON.stringify(a)) + ';path=/;max-age=' + (60 * 60 * 24 * 365);
            document.documentElement.classList.toggle('dark', a.darkMode);
            closeSettingsModal();
            location.reload();
        });

        // BLOQUE ESTADO ACTUAL DEL MODAL (pedido abierto)
        let pedidoModalActualId = null;

        function renderData() {
            const enRuta = DB_PEDIDOS.filter(o => o.estado_detallado === 'en_transito').length;
            const pendientes = DB_PEDIDOS.filter(o => o.estado_detallado === 'pendiente' || o.estado_detallado === 'en_preparacion').length;
            const hoy = new Date().toISOString().slice(0, 10);
            const entregadosHoy = DB_PEDIDOS.filter(o => o.estado_detallado === 'entregado' && (o.fecha || '').startsWith(hoy)).length;
            const cancelados = DB_PEDIDOS.filter(o => o.estado_detallado === 'cancelado').length;
            const entregadosTotal = DB_PEDIDOS.filter(o => o.estado_detallado === 'entregado').length;
            const activos = DB_PEDIDOS.filter(o => ['pendiente', 'en_preparacion', 'en_transito'].includes(o.estado_detallado)).length;
            const valorActivo = DB_PEDIDOS.filter(o => ['pendiente', 'en_preparacion', 'en_transito'].includes(o.estado_detallado)).reduce((s, o) => s + parseFloat(o.total_compra), 0);
            const valorTotal = DB_PEDIDOS.reduce((s, o) => s + parseFloat(o.total_compra), 0);

            document.getElementById('kpiEnRuta').textContent = enRuta;
            document.getElementById('kpiPendientesLog').textContent = pendientes;
            document.getElementById('kpiEntregadosHoy').textContent = entregadosHoy;
            document.getElementById('kpiCancelados').textContent = cancelados;

            document.getElementById('kpiEnRutaCaption').textContent = enRuta > 0 ? `${enRuta} pedido(s) camino al cliente.` : 'Sin pedidos en tránsito.';
            document.getElementById('kpiPendientesCaption').textContent = pendientes > 0 ? `${pendientes} esperando preparación o despacho.` : 'Al día.';
            document.getElementById('kpiEntregadosCaption').textContent = entregadosHoy > 0 ? `${entregadosHoy} completado(s) hoy.` : 'Nada entregado aún hoy.';
            document.getElementById('kpiCanceladosCaption').textContent = cancelados > 0 ? `${cancelados} pedido(s) cancelado(s).` : 'Sin cancelaciones.';

            document.getElementById('bannerFecha').textContent = new Date().toLocaleDateString('es-PA', { weekday: 'long', day: 'numeric', month: 'long' });
            document.getElementById('bannerResumen').textContent = `Tienes ${activos} pedido(s) activo(s) por gestionar hoy.`;
            document.getElementById('bannerValorActivo').textContent = `$${valorActivo.toFixed(2)}`;
            document.getElementById('bannerTotalPedidos').textContent = DB_PEDIDOS.length;

            document.getElementById('statTotalPedidos').textContent = DB_PEDIDOS.length;
            document.getElementById('statValorTotal').textContent = `$${valorTotal.toFixed(2)}`;
            document.getElementById('statActivos').textContent = activos;
            document.getElementById('statCompletados').textContent = entregadosTotal;

            renderDistribucionEstados();
            renderDistribucionZonas();

            document.getElementById('recentOrdersBody').innerHTML = DB_PEDIDOS.slice(0, 8).map(o => `
                <tr class="hover:bg-surface-container-low transition-colors">
                    <td class="px-8 py-5 font-bold text-primary">#${o.id}</td>
                    <td class="px-8 py-5 text-on-surface-variant">${o.comprador_nombre}</td>
                    <td class="px-8 py-5">${estadoBadge(o.estado_detallado)}</td>
                    <td class="px-8 py-5 font-bold text-primary">$${parseFloat(o.total_compra).toFixed(2)}</td>
                </tr>
            `).join('') || '<tr><td class="px-8 py-5 text-on-surface-variant" colspan="4">Sin pedidos registrados.</td></tr>';

            renderKanban();
            renderMisEntregas();
            renderZonasCobertura();

            const notifs = [];
            DB_PEDIDOS.filter(o => o.estado_detallado === 'pendiente' || o.estado_detallado === 'en_preparacion').slice(0, 5).forEach(o => notifs.push(`
                <div class="p-3 rounded-2xl hover:bg-surface-container-low transition-colors flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-secondary text-[18px]">local_shipping</span></div>
                    <div><p class="text-[13px] font-bold">Pedido pendiente</p><p class="text-[11px] text-on-surface-variant">Pedido #${o.id} de ${o.comprador_nombre} sin entregar.</p></div>
                </div>`));
            document.getElementById('notificationList').innerHTML = notifs.join('') || '<p class="text-on-surface-variant text-[13px] p-3">Sin notificaciones nuevas.</p>';
            document.getElementById('notifCountTag').textContent = `${notifs.length} NUEVAS`;
        }

        // BLOQUE DISTRIBUCION DE PEDIDOS POR ESTADO (barras)
        function renderDistribucionEstados() {
            const etiquetas = { pendiente: 'Pendiente', en_preparacion: 'En Preparación', en_transito: 'En Tránsito', entregado: 'Entregado', cancelado: 'Cancelado' };
            const colores = { pendiente: 'bg-outline-variant', en_preparacion: 'bg-outline-variant', en_transito: 'bg-secondary', entregado: 'bg-primary', cancelado: 'bg-error' };
            const total = DB_PEDIDOS.length || 1;

            document.getElementById('distribucionEstados').innerHTML = Object.keys(etiquetas).map(estado => {
                const cantidad = DB_PEDIDOS.filter(o => (o.estado_detallado || 'pendiente') === estado).length;
                const porcentaje = Math.round((cantidad / total) * 100);
                return `
                    <div>
                        <div class="flex justify-between items-center mb-2 text-[12px]">
                            <span class="font-bold text-on-surface-variant">${etiquetas[estado]}</span>
                            <span class="font-bold text-primary">${cantidad}</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-surface-container-high overflow-hidden">
                            <div class="h-full rounded-full ${colores[estado]} transition-all" style="width: ${porcentaje}%"></div>
                        </div>
                    </div>`;
            }).join('');
        }

        // BLOQUE PEDIDOS POR ZONA
        function renderDistribucionZonas() {
            const conteoZonas = {};
            DB_PEDIDOS.forEach(o => {
                const zona = o.zona || 'Sin zona registrada';
                conteoZonas[zona] = (conteoZonas[zona] || 0) + 1;
            });
            const zonasOrdenadas = Object.entries(conteoZonas).sort((a, b) => b[1] - a[1]);

            document.getElementById('distribucionZonas').innerHTML = zonasOrdenadas.map(([zona, cantidad]) => `
                <div class="flex items-center justify-between p-3 rounded-2xl bg-surface-container-low/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-primary-container/10 flex items-center justify-center text-primary"><span class="material-symbols-outlined text-[18px]">location_on</span></div>
                        <span class="text-[13px] font-semibold text-on-surface">${zona}</span>
                    </div>
                    <span class="text-[12px] font-bold text-primary bg-primary/10 px-3 py-1 rounded-full">${cantidad}</span>
                </div>
            `).join('') || '<p class="text-on-surface-variant text-[13px]">Sin datos de zona.</p>';
        }

        // BLOQUE TABLERO KANBAN
        function renderKanban() {
            const columnas = {
                pendiente: 'kanbanPendiente',
                en_preparacion: 'kanbanPreparacion',
                en_transito: 'kanbanTransito',
                entregado: 'kanbanEntregado'
            };
            const contadores = {
                pendiente: 'countPendiente',
                en_preparacion: 'countPreparacion',
                en_transito: 'countTransito',
                entregado: 'countEntregado'
            };

            Object.keys(columnas).forEach(estado => {
                const grupo = DB_PEDIDOS.filter(o => (o.estado_detallado || 'pendiente') === estado);
                document.getElementById(columnas[estado]).innerHTML = grupo.map(tarjetaPedido).join('') || '<p class="text-on-surface-variant text-[12px] px-2">Sin pedidos.</p>';
                document.getElementById(contadores[estado]).textContent = grupo.length;
            });
        }

        function tarjetaPedido(o) {
            const productos = (o.items || []).map(it => `${it.producto_nombre} x${it.cantidad}`).join(', ') || 'Sin productos';
            const coloresBorde = {
                pendiente: 'border-l-outline-variant',
                en_preparacion: 'border-l-outline-variant',
                en_transito: 'border-l-secondary',
                entregado: 'border-l-primary',
                cancelado: 'border-l-error'
            };
            const borde = coloresBorde[o.estado_detallado] || coloresBorde.pendiente;
            return `
                <div class="glass-card rounded-3xl p-5 shadow-sm border border-outline-variant/20 border-l-4 ${borde} cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all" onclick="abrirDetallePedido(${o.id})">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-bold text-primary text-[15px]">#${o.id}</span>
                        <span class="font-bold text-on-surface text-[13px]">$${parseFloat(o.total_compra).toFixed(2)}</span>
                    </div>
                    <p class="text-[13px] font-semibold text-on-surface truncate">${o.comprador_nombre} ${o.comprador_apellido || ''}</p>
                    <p class="text-[11px] text-on-surface-variant mb-2 flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">location_on</span>${o.zona || 'Zona sin registrar'}</p>
                    <p class="text-[11px] text-on-surface-variant line-clamp-2">${productos}</p>
                </div>`;
        }

        function estadoBadge(estado) {
            const estilos = {
                pendiente: 'bg-outline-variant/30 text-on-surface-variant',
                en_preparacion: 'bg-outline-variant/30 text-on-surface-variant',
                en_transito: 'bg-secondary/20 text-secondary',
                entregado: 'bg-primary/10 text-primary',
                cancelado: 'bg-error/10 text-error'
            };
            return `<span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase ${estilos[estado] || estilos.pendiente}">${(estado || 'pendiente').replace('_', ' ')}</span>`;
        }

        // BLOQUE MIS ENTREGAS (entregados + cancelados en una sola pantalla)
        let meFiltroActual = 'todos';

        function filtrarMisEntregas(filtro, boton) {
            if (filtro !== null) {
                meFiltroActual = filtro;
                document.querySelectorAll('.me-filtro-btn').forEach(b => {
                    b.classList.remove('bg-primary', 'text-white');
                    b.classList.add('bg-surface-container-high', 'text-on-surface-variant');
                });
                if (boton) {
                    boton.classList.remove('bg-surface-container-high', 'text-on-surface-variant');
                    boton.classList.add('bg-primary', 'text-white');
                }
            }
            renderMisEntregas();
        }

        function renderMisEntregas() {
            const busqueda = (document.getElementById('meBuscar')?.value || '').trim().toLowerCase();
            const base = DB_PEDIDOS.filter(o => o.estado_detallado === 'entregado' || o.estado_detallado === 'cancelado');

            document.getElementById('meStatTotal').textContent = base.length;
            document.getElementById('meStatEntregados').textContent = base.filter(o => o.estado_detallado === 'entregado').length;
            document.getElementById('meStatCancelados').textContent = base.filter(o => o.estado_detallado === 'cancelado').length;

            let filtrados = meFiltroActual === 'todos' ? base : base.filter(o => o.estado_detallado === meFiltroActual);
            if (busqueda) {
                filtrados = filtrados.filter(o =>
                    String(o.id).includes(busqueda) ||
                    `${o.comprador_nombre || ''} ${o.comprador_apellido || ''}`.toLowerCase().includes(busqueda)
                );
            }
            filtrados = filtrados.slice().sort((a, b) => new Date(b.fecha_entrega || b.fecha || 0) - new Date(a.fecha_entrega || a.fecha || 0));

            document.getElementById('misEntregasBody').innerHTML = filtrados.map(o => `
                <tr class="hover:bg-surface-container-low transition-colors cursor-pointer" onclick="abrirDetallePedido(${o.id})">
                    <td class="px-8 py-5 font-bold text-primary">#${o.id}</td>
                    <td class="px-8 py-5 text-on-surface-variant">${o.comprador_nombre || ''} ${o.comprador_apellido || ''}</td>
                    <td class="px-8 py-5 text-on-surface-variant">${o.zona || 'Sin zona registrada'}</td>
                    <td class="px-8 py-5">${estadoBadge(o.estado_detallado)}</td>
                    <td class="px-8 py-5 text-on-surface-variant">${o.fecha_entrega ? new Date(o.fecha_entrega).toLocaleDateString('es-PA', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Sin registrar'}</td>
                    <td class="px-8 py-5 text-right font-bold text-primary">$${parseFloat(o.total_compra).toFixed(2)}</td>
                </tr>
            `).join('') || '<tr><td class="px-8 py-5 text-on-surface-variant" colspan="6">No hay entregas que coincidan con el filtro.</td></tr>';
        }

        // BLOQUE ZONAS DE COBERTURA
        function renderZonasCobertura() {
            const zonas = {};
            DB_PEDIDOS.forEach(o => {
                const zona = o.zona || 'Sin zona registrada';
                if (!zonas[zona]) zonas[zona] = { total: 0, pendiente: 0, en_preparacion: 0, en_transito: 0, entregado: 0, cancelado: 0 };
                zonas[zona].total++;
                const estado = o.estado_detallado || 'pendiente';
                if (zonas[zona][estado] !== undefined) zonas[zona][estado]++;
            });
            const ordenadas = Object.entries(zonas).sort((a, b) => b[1].total - a[1].total);

            const etiquetas = { pendiente: 'Pendiente', en_preparacion: 'En Prep.', en_transito: 'En Tránsito', entregado: 'Entregado', cancelado: 'Cancelado' };
            const colores = { pendiente: 'bg-outline-variant/20 text-on-surface-variant', en_preparacion: 'bg-outline-variant/20 text-on-surface-variant', en_transito: 'bg-secondary/15 text-secondary', entregado: 'bg-primary/10 text-primary', cancelado: 'bg-error/10 text-error' };

            document.getElementById('zonasCoberturaGrid').innerHTML = ordenadas.map(([zona, datos]) => `
                <div class="glass-card rounded-[32px] p-7 border border-outline-variant/20">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-primary-container/10 flex items-center justify-center text-primary"><span class="material-symbols-outlined text-[20px]">location_on</span></div>
                            <h4 class="font-bold text-on-surface text-[15px]">${zona}</h4>
                        </div>
                        <span class="text-[13px] font-bold text-primary bg-primary/10 px-3 py-1.5 rounded-full">${datos.total} pedido${datos.total === 1 ? '' : 's'}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${Object.keys(etiquetas).filter(e => datos[e] > 0).map(e => `
                            <span class="px-3 py-1.5 rounded-full text-[11px] font-bold ${colores[e]}">${etiquetas[e]}: ${datos[e]}</span>
                        `).join('') || '<span class="text-[12px] text-on-surface-variant">Sin pedidos registrados.</span>'}
                    </div>
                </div>
            `).join('') || '<p class="text-on-surface-variant text-[13px]">Sin datos de zona todavía.</p>';
        }

        function abrirDetallePedido(idPedido) {
            const pedido = DB_PEDIDOS.find(o => String(o.id) === String(idPedido));
            if (!pedido) return;
            pedidoModalActualId = pedido.id;

            document.getElementById('pedidoModalTitulo').textContent = `#${pedido.id}`;
            document.getElementById('pedidoModalCliente').textContent = `${pedido.comprador_nombre} ${pedido.comprador_apellido || ''}`.trim();
            document.getElementById('pedidoModalCorreo').textContent = pedido.comprador_correo || 'Sin correo registrado';
            document.getElementById('pedidoModalTelefono').textContent = pedido.comprador_telefono || 'Sin teléfono registrado';

            const partesDireccion = [pedido.zona, pedido.distrito, pedido.corregimiento, pedido.direccion_detalle].filter(Boolean);
            document.getElementById('pedidoModalDireccion').textContent = partesDireccion.length ? partesDireccion.join(', ') : 'Sin dirección registrada';

            document.getElementById('pedidoModalPago').textContent = pedido.metodo_pago_nombre || 'No especificado';
            document.getElementById('pedidoModalFecha').textContent = pedido.fecha || '—';

            document.getElementById('pedidoModalItems').innerHTML = (pedido.items || []).map(it => `
                <tr>
                    <td class="px-5 py-3">${it.producto_nombre}</td>
                    <td class="px-5 py-3">${it.cantidad}</td>
                    <td class="px-5 py-3">$${parseFloat(it.precio_unitario).toFixed(2)}</td>
                    <td class="px-5 py-3 text-right font-bold text-primary">$${parseFloat(it.subtotal).toFixed(2)}</td>
                </tr>
            `).join('') || '<tr><td class="px-5 py-3 text-on-surface-variant" colspan="4">Sin productos registrados.</td></tr>';

            document.getElementById('pedidoModalTotal').textContent = `$${parseFloat(pedido.total_compra).toFixed(2)}`;
            document.getElementById('pedidoModalEstado').value = pedido.estado_detallado || 'pendiente';

            document.getElementById('pedidoModal').classList.remove('hidden');
            document.getElementById('pedidoModal').classList.add('flex');
        }

        function cerrarDetallePedido() {
            document.getElementById('pedidoModal').classList.add('hidden');
            document.getElementById('pedidoModal').classList.remove('flex');
            pedidoModalActualId = null;
        }

        // BLOQUE GUARDAR CAMBIO DE ESTADO - conectado a accion=registrar_entrega
        async function guardarEstadoPedido() {
            if (!pedidoModalActualId) return;
            const nuevoEstado = document.getElementById('pedidoModalEstado').value;
            const body = new URLSearchParams({ accion: 'registrar_entrega', id_pedido: pedidoModalActualId, estado: nuevoEstado });
            await fetch('logistica.php', { method: 'POST', body });
            location.reload();
        }

        window.onload = () => {
            if (localStorage.getItem('dachiSidebarMini') === '1') {
                document.documentElement.classList.add('sidebar-mini');
            }
            buildSidebar();
            renderData();

            const seccionGuardada = sessionStorage.getItem('dachiSeccionActiva');
            if (seccionGuardada && document.getElementById(seccionGuardada)) {
                const boton = document.querySelector(`#sidebarNav button[onclick*="'${seccionGuardada}'"]`);
                showSection(seccionGuardada, boton);
            }

            window.addEventListener('click', (e) => {
                if (!document.getElementById('userAvatarBtn').contains(e.target) && !document.getElementById('userMenu').contains(e.target)) {
                    document.getElementById('userMenu').classList.remove('open');
                }
                if (!document.getElementById('notificationButton').contains(e.target) && !document.getElementById('notificationMenu').contains(e.target)) {
                    document.getElementById('notificationMenu').classList.remove('open');
                }
            });
        };
    </script>
</body>
</html>