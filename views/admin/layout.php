<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Administración</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#11663C",
                        "primary-dark": "#001a15",
                        "primary-container": "#1a3c34",
                        "on-primary-container": "#83a69c",
                        "primary-fixed": "#c5eadf",
                        "primary-fixed-dim": "#aacec3",
                        surface: "#f9faf7",
                        "surface-low": "#f3f4f1",
                        "surface-container": "#edeeeb",
                        "surface-high": "#e7e8e6",
                        "surface-highest": "#e2e3e0",
                        "surface-lowest": "#ffffff",
                        "on-surface": "#191c1b",
                        "surface-dim": "#d9dad8",
                        "surface-bright": "#f9faf7",
                        "surface-variant": "#e2e3e0",
                        muted: "#57615b",
                        outline: "#717976",
                        "outline-variant": "#c1c8c4",
                        "outline-muted": "rgba(113,121,118,0.2)",
                        error: "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a",
                        secondary: "#57615b",
                        "secondary-container": "#dbe5dd",
                        "success-badge-bg": "#E8F2EA",
                        "success-badge-text": "#1a3c34",
                        tertiary: "#1a231e",
                        "tertiary-container": "#2f3833",
                    },
                    borderRadius: {
                        DEFAULT: "0.375rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        "2xl": "1rem",
                        full: "9999px",
                    },
                    fontFamily: {
                        headline: ['"Playfair Display"', "Georgia", "serif"],
                        "headline-lg": ['"Playfair Display"', "Georgia", "serif"],
                        "headline-md": ['"Playfair Display"', "Georgia", "serif"],
                        "headline-sm": ['"Playfair Display"', "Georgia", "serif"],
                        body: ["Plus Jakarta Sans", "sans-serif"],
                        "body-md": ["Plus Jakarta Sans", "sans-serif"],
                        "body-sm": ["Plus Jakarta Sans", "sans-serif"],
                        "label-sm": ["Plus Jakarta Sans", "sans-serif"],
                        "label-bold": ["Plus Jakarta Sans", "sans-serif"],
                        "pill-text": ["Plus Jakarta Sans", "sans-serif"],
                    },
                    fontSize: {
                        "headline-lg": ["40px", { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "headline-md": ["28px", { lineHeight: "1.3", fontWeight: "600" }],
                        "headline-sm": ["22px", { lineHeight: "1.4", fontWeight: "600" }],
                        "body-md": ["16px", { lineHeight: "1.5", fontWeight: "400" }],
                        "body-sm": ["14px", { lineHeight: "1.5", fontWeight: "400" }],
                        "label-sm": ["12px", { lineHeight: "1.2", fontWeight: "600" }],
                        "label-bold": ["14px", { lineHeight: "1.2", letterSpacing: "0.02em", fontWeight: "600" }],
                        "pill-text": ["11px", { lineHeight: "1.0", letterSpacing: "0.05em", fontWeight: "700" }],
                    },
                    spacing: {
                        gutter: "24px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "stack-xl": "64px",
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-width: 260px; }
        body.sidebar-collapsed { --sidebar-width: 80px; }
        body {
            background: #f9faf7;
            color: #191c1b;
            font-family: "Plus Jakarta Sans", sans-serif;
            overflow-x: hidden;
        }
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
        }
        #sidebar {
            width: var(--sidebar-width) !important;
            transition: width 0.24s cubic-bezier(0.4, 0, 0.2, 1), transform 0.24s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #main-content {
            margin-left: var(--sidebar-width) !important;
            width: calc(100% - var(--sidebar-width)) !important;
            transition: margin-left 0.24s cubic-bezier(0.4, 0, 0.2, 1), width 0.24s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-label {
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }
        .sidebar-collapsed .sidebar-label {
            opacity: 0;
            width: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .sidebar-collapsed .sidebar-chevron {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .sidebar-collapsed .nav-toggle-wrapper {
            position: relative;
        }
        .sidebar-collapsed .sidebar-submenu {
            position: absolute;
            left: calc(100% - 20px);
            top: -8px;
            background: #11663C;
            border-radius: 12px;
            padding: 8px 8px 8px 28px;
            min-width: 230px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.08);
            opacity: 0;
            pointer-events: none;
            transform: translateX(-8px);
            transition: all 0.15s ease;
            z-index: 100;
        }
        .sidebar-collapsed .nav-toggle-wrapper:hover .sidebar-submenu {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }
        .sidebar-collapsed .sidebar-submenu::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 28px;
        }
        .sidebar-collapsed .sidebar-submenu .sidebar-label {
            opacity: 1;
            width: auto;
            overflow: visible;
            pointer-events: auto;
        }
        .sidebar-collapsed #sidebar .nav-item {
            justify-content: center;
            gap: 0 !important;
            padding: 12px !important;
            border-radius: 12px !important;
        }
        .sidebar-collapsed #sidebar .nav-item .nav-icon {
            margin: 0 !important;
        }
        .sidebar-collapsed #sidebar .nav-toggle {
            justify-content: center;
            gap: 0 !important;
            padding: 12px !important;
        }
        .sidebar-collapsed #sidebar .brand-area {
            justify-content: center !important;
            padding: 12px !important;
            min-height: 72px;
            border-bottom: none !important;
        }
        .sidebar-collapsed #sidebar .brand-area .brand-text {
            display: none;
        }
        .sidebar-collapsed #sidebar .sidebar-footer {
            padding: 10px !important;
        }
        .sidebar-collapsed #sidebar .sidebar-footer a {
            justify-content: center;
            gap: 0 !important;
            padding: 12px !important;
            border-radius: 12px !important;
        }
        .sidebar-collapsed #users-submenu.hidden {
            display: flex !important;
            flex-direction: column;
        }
        .sidebar-collapsed #sidebar nav {
            overflow: visible !important;
        }
        .sidebar-collapsed #sidebar .sidebar-submenu .nav-item {
            justify-content: flex-start !important;
            gap: 12px !important;
            padding: 10px 14px !important;
            border-radius: 10px !important;
        }
        html.dark #detailsCategoriaPanel { background: #1c211b !important; }
        html.dark #detailsCategoriaPanel .text-on-surface { color: #e5e9e2 !important; }
        html.dark #detailsCategoriaPanel .text-secondary { color: #a5aea7 !important; }
        html.dark #deleteCategoriaPanel { background: #1c211b !important; }
        .topbar-logo { display: none; }
        @media (max-width: 1023px) {
            .topbar-logo { display: block !important; }
        }
        .sidebar-collapsed .topbar-logo { display: block !important; }
        .sidebar-collapsed #sidebar .nav-item:hover {
            background: rgba(131, 166, 156, 0.12) !important;
            color: #c5eadf !important;
        }
        .sidebar-collapsed #sidebar .nav-item:hover .material-symbols-outlined {
            color: #c5eadf !important;
        }
        .rotate-180 { transform: rotate(180deg); }
        .user-menu {
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.15s ease;
        }
        .user-menu.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .estado-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .estado-aprobado { background: #E8F2EA; color: #1a3c34; }
        .estado-pendiente { background: #ffdfa0; color: #5c4300; }
        .estado-rechazado { background: #ffdad6; color: #93000a; }
        .estado-inactivo { background: #e2e3e0; color: #57615b; }
        .botanical-shadow { box-shadow: 0 10px 30px -5px rgba(1, 38, 31, 0.05); }
        .table-row-hover:hover { background-color: rgba(241, 245, 242, 0.5); }
        .nav-item.active-pill {
            background: rgba(131, 166, 156, 0.12) !important;
            color: #c5eadf !important;
        }
        .nav-item.active-pill .material-symbols-outlined {
            font-variation-settings: "FILL" 1, "wght" 400, "GRAD" 0, "opsz" 24;
            color: #c5eadf !important;
        }
        #sidebar .nav-item:hover,
        #sidebar .nav-toggle:hover {
            background: rgba(131, 166, 156, 0.12) !important;
            color: #c5eadf !important;
        }
        #sidebar .nav-item:hover .material-symbols-outlined,
        #sidebar .nav-toggle:hover .material-symbols-outlined {
            color: #c5eadf !important;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d9d4; border-radius: 10px; }
        html.dark body {
            background-color: #10140f;
            color: #e2e3e0;
        }
        html.dark .topbar-blur {
            background: rgba(15, 21, 18, 0.8) !important;
            border-color: rgba(174, 187, 179, 0.14) !important;
        }
        html.dark .estado-aprobado { background: rgba(197, 234, 223, 0.2); color: #c5eadf; }
        html.dark .estado-pendiente { background: rgba(255, 223, 160, 0.2); color: #ffdfa0; }
        html.dark .estado-rechazado { background: rgba(255, 218, 214, 0.15); color: #ffb4ab; }
        html.dark .estado-inactivo { background: rgba(226, 227, 224, 0.15); color: #aeb7ad; }
        html.dark .user-menu#userMenu,
        html.dark .user-menu#notificationMenu {
            background: #1c211b !important;
            border-color: rgba(51, 60, 48, 0.5) !important;
        }
        html.dark .user-menu#userMenu .text-on-surface,
        html.dark .user-menu#notificationMenu .text-on-surface {
            color: #e5e9e2 !important;
        }
        html.dark .user-menu .border-outline-variant\/30,
        html.dark .user-menu .border-outline-variant\/50 {
            border-color: rgba(51, 60, 48, 0.5) !important;
        }
        html.dark .hover\:bg-surface-container-low:hover {
            background-color: #20261f !important;
        }
        .modal-overlay {
            transition: opacity .2s ease;
        }
        html.dark .modal-overlay > div {
            background: #1c211b !important;
        }
        html.dark #confirmModal .text-on-surface,
        html.dark #detailsPanel .text-on-surface { color: #e5e9e2 !important; }
        html.dark #confirmModal .text-secondary,
        html.dark #detailsPanel .text-secondary { color: #a5aea7 !important; }
        html.dark #detailsPanel { background: #1c211b !important; }

        /* --- DARK MODE: Admin Dashboard (clases duplicadas de dachi-botanical.css) --- */

        /* Backgrounds */
        html.dark .bg-surface { background-color: #0f1512 !important; }
        html.dark .bg-surface\/80 { background-color: rgba(15,21,18,0.85) !important; }
        html.dark .bg-surface-low { background-color: #151d19 !important; }
        html.dark .bg-surface-low\/50 { background-color: rgba(21,29,25,0.5) !important; }
        html.dark .bg-surface-lowest { background-color: #18211c !important; }
        html.dark .bg-surface-container { background-color: #1a231e !important; }
        html.dark .bg-surface-container-low { background-color: #1c211b !important; }
        html.dark .bg-surface-container-low\/50 { background-color: rgba(26,35,30,0.5) !important; }
        html.dark .bg-surface-container-low\/60 { background-color: rgba(26,35,30,0.6) !important; }
        html.dark .bg-surface-container-highest { background-color: #28342d !important; }
        html.dark .bg-primary-fixed { background-color: #245f45 !important; }
        html.dark .bg-primary-container { background-color: #1a4d38 !important; }
        html.dark .bg-primary-container\/40 { background-color: rgba(26,77,56,0.4) !important; }
        html.dark .bg-primary-container\/30 { background-color: rgba(26,77,56,0.3) !important; }
        html.dark .bg-success-badge-bg { background-color: rgba(197,234,223,0.2) !important; }
        html.dark .bg-outline-muted { background-color: rgba(174,187,179,0.14) !important; }
        html.dark .bg-error-container { background-color: rgba(147,0,10,0.25) !important; }
        html.dark .bg-error-container\/20 { background-color: rgba(147,0,10,0.2) !important; }
        html.dark .bg-error-container\/40 { background-color: rgba(147,0,10,0.4) !important; }
        html.dark .bg-error\/20 { background-color: rgba(255,180,171,0.2) !important; }
        html.dark .bg-secondary-fixed\/40 { background-color: rgba(174,187,179,0.4) !important; }
        html.dark .bg-white { background-color: #18211c !important; }

        /* Text */
        html.dark .text-primary { color: #83db9f !important; }
        html.dark .text-primary-fixed { color: #c5eadf !important; }
        html.dark .text-primary-fixed-dim { color: #aacec3 !important; }
        html.dark .text-primary-fixed-dim\/70 { color: rgba(170,206,195,0.7) !important; }
        html.dark .text-primary-fixed-dim\/80 { color: rgba(170,206,195,0.8) !important; }
        html.dark .text-secondary { color: #aebbb3 !important; }
        html.dark .text-on-surface { color: #e7eee9 !important; }
        html.dark .text-on-surface-variant { color: #9fab9a !important; }
        html.dark .text-muted { color: #aebbb3 !important; }
        html.dark .text-muted\/60 { color: rgba(174,187,179,0.6) !important; }
        html.dark .text-outline { color: #8c9a92 !important; }
        html.dark .text-outline\/40 { color: rgba(140,154,146,0.4) !important; }
        html.dark .text-error { color: #ffb4ab !important; }
        html.dark .text-on-error-container { color: #ffb4ab !important; }
        html.dark .text-success-badge-text { color: #c5eadf !important; }
        html.dark .text-on-primary-container { color: #c5eadf !important; }

        /* Borders */
        html.dark .border-outline-muted { border-color: rgba(174,187,179,0.14) !important; }
        html.dark .border-outline-variant { border-color: #34443b !important; }
        html.dark .border-outline-variant\/30 { border-color: rgba(52,68,59,0.3) !important; }
        html.dark .border-outline-variant\/50 { border-color: rgba(52,68,59,0.5) !important; }
        html.dark .border-primary { border-color: #83db9f !important; }
        html.dark .border-primary\/10 { border-color: rgba(131,219,159,0.1) !important; }
        html.dark .border-primary\/20 { border-color: rgba(131,219,159,0.2) !important; }
        html.dark .border-primary\/30 { border-color: rgba(131,219,159,0.3) !important; }
        html.dark .border-primary-fixed { border-color: #245f45 !important; }
        html.dark .border-error\/20 { border-color: rgba(255,180,171,0.2) !important; }
        html.dark .border-error\/50 { border-color: rgba(255,180,171,0.5) !important; }

        /* Divide */
        html.dark .divide-outline-muted > * + * { border-color: rgba(174,187,179,0.14) !important; }

        /* Hover backgrounds */
        html.dark .hover\:bg-surface:hover { background-color: #0f1512 !important; }
        html.dark .hover\:bg-surface-low:hover { background-color: #151d19 !important; }
        html.dark .hover\:bg-surface-low\/50:hover { background-color: rgba(21,29,25,0.5) !important; }
        html.dark .hover\:bg-surface-container:hover { background-color: #1a231e !important; }
        html.dark .hover\:bg-primary:hover { background-color: #3a7a55 !important; }
        html.dark .hover\:bg-primary\/90:hover { background-color: rgba(58,122,85,0.9) !important; }
        html.dark .hover\:bg-primary\/10:hover { background-color: rgba(131,219,159,0.1) !important; }
        html.dark .hover\:bg-primary-container:hover { background-color: #1a4d38 !important; }
        html.dark .hover\:bg-error-container\/20:hover { background-color: rgba(147,0,10,0.2) !important; }
        html.dark .hover\:bg-error-container\/40:hover { background-color: rgba(147,0,10,0.4) !important; }
        html.dark .hover\:bg-secondary-fixed\/40:hover { background-color: rgba(174,187,179,0.4) !important; }

        /* Hover text */
        html.dark .hover\:text-primary:hover { color: #83db9f !important; }
        html.dark .hover\:text-error:hover { color: #ffb4ab !important; }

        /* Inputs */
        html.dark input,
        html.dark select,
        html.dark textarea {
            background-color: #18211c !important;
            color: #e7eee9 !important;
            border-color: #34443b !important;
        }
        html.dark input::placeholder,
        html.dark textarea::placeholder { color: #8c9a92 !important; }

        /* Scrollbar */
        html.dark ::-webkit-scrollbar-track { background: #0f1512 !important; }
        html.dark ::-webkit-scrollbar-thumb { background: #34443b !important; }

        /* Table row hover */
        html.dark .table-row-hover:hover { background-color: rgba(255,255,255,0.04) !important; }

        /* Botanical shadow */
        html.dark .botanical-shadow { box-shadow: 0 10px 30px -5px rgba(0,0,0,0.35) !important; }

        /* Additional backgrounds for user sub-views */
        html.dark .bg-primary { background-color: #1a4d38 !important; }
        html.dark .bg-primary-container { background-color: #1a4d38 !important; }
        html.dark .bg-surface-container-low\/30 { background-color: rgba(28,33,27,0.3) !important; }

        /* Hover variants for user sub-views */
        html.dark .hover\:bg-primary-dark:hover { background-color: #3a7a55 !important; }
        html.dark .hover\:text-white:hover { color: #e7eee9 !important; }

        /* Border variants */
        html.dark .border-secondary { border-color: #aebbb3 !important; }

        /* Focus ring */
        html.dark .focus\:ring-primary\/20:focus { --tw-ring-color: rgba(131,219,159,0.2) !important; }
    </style>
</head>
<body class="min-h-screen" style="font-family:'Hanken Grotesk',sans-serif;">
    <script>
if(localStorage.getItem('dachiAdminSidebarCollapsed')==='1')document.body.classList.add('sidebar-collapsed');
if(localStorage.getItem('dachiAdminDarkMode')==='1')document.documentElement.classList.add('dark');
</script>
    <div class="fixed inset-0 bg-black/50 z-[60] hidden" id="sidebarOverlay" onclick="toggleSidebarMobile()"></div>

    <aside class="fixed left-0 top-0 h-full z-50 flex flex-col -translate-x-full lg:translate-x-0" id="sidebar" style="background:#11663C;">
        <div class="brand-area h-16 px-6 flex items-center justify-between border-b border-white/10 flex-none">
            <span class="brand-text text-xs font-semibold text-white/80 sidebar-label">Administración</span>
            <button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-primary-container hover:text-on-primary-container transition-all text-white flex-none hidden lg:flex" onclick="toggleSidebarDesktop()" title="Colapsar menú" type="button">
                <span class="material-symbols-outlined" id="toggle-icon">left_panel_close</span>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="admin.php" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 transition-all hover:translate-x-0.5 <?= !isset($submodulo) ? 'active-pill' : '' ?>">
                <span class="material-symbols-outlined nav-icon transition-all group-hover:scale-105">dashboard</span>
                <span class="text-sm font-medium sidebar-label">Dashboard</span>
            </a>

            <div class="nav-toggle-wrapper space-y-1" id="navToggleWrapper">
                <button class="nav-toggle w-full flex items-center justify-between p-3 rounded-xl text-white/80 transition-all <?= in_array($submodulo ?? '', ['productores', 'logistica', 'consumidores']) ? 'active-pill' : '' ?>" onclick="toggleSubmenu()" type="button">
                    <div class="flex items-center gap-4">
                        <span class="material-symbols-outlined nav-icon transition-all">group</span>
                        <span class="text-sm font-medium sidebar-label">Gestión de Usuarios</span>
                    </div>
                    <span class="material-symbols-outlined text-[18px] transition-transform duration-300 sidebar-chevron <?= in_array($submodulo ?? '', ['productores', 'logistica', 'consumidores']) ? 'rotate-180' : '' ?>">expand_more</span>
                </button>
                <div class="sidebar-submenu space-y-1 <?= in_array($submodulo ?? '', ['productores', 'logistica', 'consumidores']) ? '' : 'hidden' ?>" id="users-submenu">
                    <a href="admin.php?accion=listar_productores" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 hover:translate-x-0.5 transition-all <?= ($submodulo ?? '') === 'productores' ? 'active-pill' : '' ?>">
                        <span class="material-symbols-outlined nav-icon text-[20px] transition-all">agriculture</span>
                        <span class="text-sm sidebar-label">Proveedores Agrícolas</span>
                    </a>
                    <a href="admin.php?accion=listar_logisticos" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 hover:translate-x-0.5 transition-all <?= ($submodulo ?? '') === 'logistica' ? 'active-pill' : '' ?>">
                        <span class="material-symbols-outlined nav-icon text-[20px] transition-all">local_shipping</span>
                        <span class="text-sm sidebar-label">Proveedores Logísticos</span>
                    </a>
                    <a href="admin.php?accion=listar_consumidores" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 hover:translate-x-0.5 transition-all <?= ($submodulo ?? '') === 'consumidores' ? 'active-pill' : '' ?>">
                        <span class="material-symbols-outlined nav-icon text-[20px] transition-all">person</span>
                        <span class="text-sm sidebar-label">Consumidores</span>
                    </a>
                </div>
            </div>

            <div class="pt-2"><div class="border-t border-white/10"></div></div>

            <a href="admin.php?accion=listar_productos" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 transition-all hover:translate-x-0.5 <?= ($submodulo ?? '') === 'productos' ? 'active-pill' : '' ?>">
                <span class="material-symbols-outlined nav-icon">inventory_2</span>
                <span class="text-sm font-medium sidebar-label">Gestión de Productos</span>
            </a>
            <a href="admin.php?accion=listar_categorias" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 transition-all hover:translate-x-0.5 <?= ($submodulo ?? '') === 'categorias' ? 'active-pill' : '' ?>">
                <span class="material-symbols-outlined nav-icon">category</span>
                <span class="text-sm font-medium sidebar-label">Gestión de Categorías</span>
            </a>
            <a href="admin.php?accion=listar_pedidos" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 transition-all hover:translate-x-0.5 <?= ($submodulo ?? '') === 'pedidos' ? 'active-pill' : '' ?>">
                <span class="material-symbols-outlined nav-icon">shopping_cart</span>
                <span class="text-sm font-medium sidebar-label">Gestión de Pedidos</span>
            </a>
            <a href="#" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/30 pointer-events-none opacity-40">
                <span class="material-symbols-outlined nav-icon">payments</span>
                <span class="text-sm font-medium sidebar-label">Pagos y Comisiones</span>
            </a>
            <a href="admin.php?accion=logistica_dashboard" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 transition-all hover:translate-x-0.5 <?= ($submodulo ?? '') === 'logistica_dashboard' ? 'active-pill' : '' ?>">
                <span class="material-symbols-outlined nav-icon">map</span>
                <span class="text-sm font-medium sidebar-label">Panel de Logística</span>
            </a>
            <a href="admin.php?accion=listar_calificaciones" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/80 transition-all hover:translate-x-0.5 <?= ($submodulo ?? '') === 'calificaciones' ? 'active-pill' : '' ?>">
                <span class="material-symbols-outlined nav-icon">star</span>
                <span class="text-sm font-medium sidebar-label">Calificaciones y Reputación</span>
            </a>

            <div class="pt-2"><div class="border-t border-white/10"></div></div>

            <a href="#" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/30 pointer-events-none opacity-40">
                <span class="material-symbols-outlined nav-icon">analytics</span>
                <span class="text-sm font-medium sidebar-label">Reports</span>
            </a>
            <a href="#" class="nav-item flex items-center gap-4 p-3 rounded-xl text-white/30 pointer-events-none opacity-40">
                <span class="material-symbols-outlined nav-icon">verified_user</span>
                <span class="text-sm font-medium sidebar-label">Auditoría</span>
            </a>
        </nav>

        <div class="sidebar-footer p-4 border-t border-white/10"></div>
    </aside>

    <div id="main-content" class="min-h-screen transition-all duration-300">
        <nav class="bg-surface/80 backdrop-blur-md w-full top-0 sticky z-40 border-b border-outline-variant shadow-sm">
            <div class="flex items-center gap-3 px-6 h-[72px]">
                <div class="flex items-center gap-4 flex-none">
                    <button class="w-10 h-10 inline-flex items-center justify-center rounded-full hover:bg-surface-container-low transition-colors lg:hidden flex-none" onclick="toggleSidebarMobile()" title="Abrir menú" type="button">
                        <span class="material-symbols-outlined text-primary">menu</span>
                    </button>
                    <img src="img/LG.png" alt="DACHI" class="topbar-logo h-[64px] w-auto object-contain flex-none" />
                </div>

                <div class="relative flex-1 max-w-2xl mx-auto hidden md:block">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline z-10">search</span>
                    <input type="search" placeholder="Buscar en el panel..."
                        class="w-full h-11 pl-12 pr-4 rounded-full border border-transparent bg-surface-low focus:border-primary focus:bg-surface-lowest text-sm transition-all outline-none focus:ring-2 focus:ring-primary/20" />
                </div>

                <div class="flex items-center gap-1 sm:gap-2 relative flex-none">
                    <button class="relative w-10 h-10 inline-flex items-center justify-center rounded-full text-muted hover:bg-surface-low hover:text-primary transition-all" onclick="toggleDarkMode()" title="Modo oscuro" type="button">
                        <span class="material-symbols-outlined" id="darkModeIcon">dark_mode</span>
                    </button>

                    <div class="relative">
                        <button class="relative w-10 h-10 inline-flex items-center justify-center rounded-full text-muted hover:bg-surface-low hover:text-primary transition-all" id="notificationBtn" onclick="toggleNotificationMenu()" title="Notificaciones" type="button">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 bg-error text-white text-[10px] font-bold flex items-center justify-center rounded-full px-1 ring-2 ring-surface hidden" id="notificationDot">0</span>
                        </button>
                        <div class="user-menu absolute right-0 top-12 w-80 bg-white rounded-2xl border border-outline-variant/50 shadow-xl p-2 z-[200]" id="notificationMenu">
                            <div class="flex items-center justify-between p-3 border-b border-outline-variant/30 mb-2">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Notificaciones</p>
                                    <span class="text-xs text-on-surface-variant">Actividad reciente</span>
                                </div>
                                <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
                            </div>
                            <div class="p-6 text-center text-on-surface-variant text-sm">
                                <span class="material-symbols-outlined text-[32px] block mb-2">task_alt</span>
                                <p>Todo está al día.</p>
                            </div>
                        </div>
                    </div>

                    <button class="relative w-10 h-10 inline-flex items-center justify-center rounded-full text-muted hover:bg-surface-low hover:text-primary transition-all" title="Configuración" type="button">
                        <span class="material-symbols-outlined cursor-pointer" onclick="openSettingsModal()">settings</span>
                    </button>

                    <span class="w-px h-8 bg-outline-muted mx-2 hidden sm:block"></span>

                    <div class="hidden md:block text-right mr-1">
                        <span class="font-semibold text-sm text-on-surface block"><?= htmlspecialchars($usuarioAdmin['nombre'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
                        <small class="text-on-surface-variant text-xs">Administrador</small>
                    </div>

                    <div class="relative">
                        <button class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden flex items-center justify-center font-bold text-primary cursor-pointer" onclick="toggleUserMenu()" title="Abrir perfil" type="button">
                            <span><?= mb_strtoupper(mb_substr($usuarioAdmin['nombre'] ?? 'A', 0, 1)) ?></span>
                        </button>
                        <div class="user-menu absolute right-0 top-12 w-72 bg-white rounded-2xl border border-outline-variant/50 shadow-xl p-2 z-[200]" id="userMenu">
                            <div class="flex items-center gap-3 p-3 border-b border-outline-variant/30 mb-2">
                                <div class="w-12 h-12 rounded-full bg-primary-fixed overflow-hidden border border-outline-variant flex items-center justify-center font-bold text-primary flex-none">
                                    <span><?= mb_strtoupper(mb_substr($usuarioAdmin['nombre'] ?? 'A', 0, 1)) ?></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-on-surface truncate"><?= htmlspecialchars($usuarioAdmin['nombre'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="text-xs text-on-surface-variant truncate"><?= htmlspecialchars($usuarioAdmin['correo'] ?? 'admin@dachi.com', ENT_QUOTES, 'UTF-8') ?></p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide">ADMINISTRADOR</span>
                                </div>
                            </div>
                            <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-on-surface flex items-center gap-3" onclick="location.href='panel.php'">
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
                            <a href="admin.php?logout=1" class="flex items-center gap-3 w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-error transition-all">
                                <span class="material-symbols-outlined text-[18px]">logout</span> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="p-6 lg:p-8">
            <?php if (isset($error)): ?>
                <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-8 text-center">
                    <span class="material-symbols-outlined text-[48px] text-error mb-4">error</span>
                    <p class="text-error font-semibold text-lg"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="admin.php?accion=listar_productores" class="inline-block mt-4 px-6 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-all">Volver al listado</a>
                </div>
            <?php else: ?>
                <?php require $contenido; ?>
            <?php endif; ?>
        </main>
    </div>

    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="profileModal">
        <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-primary">Editar Perfil</h3>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide">ADMINISTRADOR</span>
                </div>
                <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeProfileModal()"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form class="space-y-stack-md" id="profileForm">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="profileNombre" required type="text" />
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Apellido</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="profileApellido" type="text" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md">
                    <div>
                        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Correo</label>
                        <input class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant outline-none" disabled id="profileCorreo" type="email" />
                    </div>
                    <div>
                        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Teléfono</label>
                        <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="profileTelefono" placeholder="+507 6000-0000" type="tel" />
                    </div>
                </div>
                <button class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98] mt-2" type="submit">GUARDAR CAMBIOS</button>
            </form>
        </div>
    </div>

    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="settingsModal">
        <div class="bg-white rounded-[24px] w-full max-w-md p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline-sm text-headline-sm text-primary">Configuración</h3>
                <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeSettingsModal()"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form class="space-y-stack-lg" id="settingsForm">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">Tamaño de Fuente</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary outline-none bg-white" id="settingsFontSize">
                        <option value="pequeno">Pequeño</option>
                        <option value="mediano" selected>Mediano</option>
                        <option value="grande">Grande</option>
                    </select>
                </div>
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-label-bold text-label-bold text-on-surface mb-1">Modo Oscuro</h4>
                        <p class="text-on-surface-variant text-label-sm">Reduce el brillo de la interfaz.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input class="sr-only peer" id="settingsDarkMode" type="checkbox" />
                        <div class="w-12 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                <button class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98]" type="submit">GUARDAR</button>
            </form>
        </div>
    </div>

    <script>
        const SIDEBAR_STATE_KEY = 'dachiAdminSidebarCollapsed';

        function toggleSidebarDesktop() {
            const collapsed = document.body.classList.toggle('sidebar-collapsed');
            const icon = document.getElementById('toggle-icon');
            if (icon) icon.textContent = collapsed ? 'right_panel_close' : 'left_panel_close';
            localStorage.setItem(SIDEBAR_STATE_KEY, collapsed ? '1' : '0');
        }

        function toggleSidebarMobile() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isHidden = sidebar.classList.contains('-translate-x-full');
            sidebar.classList.toggle('-translate-x-full', !isHidden);
            overlay.classList.toggle('hidden', !isHidden);
            document.body.style.overflow = isHidden ? 'hidden' : '';
        }

        function toggleSubmenu() {
            const submenu = document.getElementById('users-submenu');
            const chevron = document.querySelector('.nav-toggle .sidebar-chevron');
            submenu.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }

        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('open');
            document.getElementById('notificationMenu').classList.remove('open');
        }

        function toggleNotificationMenu() {
            document.getElementById('notificationMenu').classList.toggle('open');
            document.getElementById('userMenu').classList.remove('open');
            renderNotifications();
        }

        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            const icon = document.getElementById('darkModeIcon');
            icon.textContent = isDark ? 'light_mode' : 'dark_mode';
            localStorage.setItem('dachiAdminDarkMode', isDark ? '1' : '0');
            const cb = document.getElementById('settingsDarkMode');
            if (cb) cb.checked = isDark;
        }

        const SESSION = {
            id: <?= (int)($usuarioAdmin['id'] ?? 0) ?>,
            nombre: <?= json_encode($usuarioAdmin['nombre'] ?? 'Admin', JSON_UNESCAPED_UNICODE) ?>,
            apellido: <?= json_encode($usuarioAdmin['apellido'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
            correo: <?= json_encode($usuarioAdmin['correo'] ?? 'admin@dachi.com', JSON_UNESCAPED_UNICODE) ?>,
            telefono: <?= json_encode($usuarioAdmin['telefono'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
            rol: 'admin'
        };
        const ROLE_LABELS = { admin: 'ADMINISTRADOR' };

        function logout() { window.location.href = 'admin.php?logout=1'; }

        function openProfileModal() {
            document.getElementById('userMenu').classList.remove('open');
            document.getElementById('profileNombre').value = SESSION.nombre;
            document.getElementById('profileApellido').value = SESSION.apellido || '';
            document.getElementById('profileCorreo').value = SESSION.correo;
            document.getElementById('profileTelefono').value = SESSION.telefono || '';
            document.getElementById('profileModal').classList.remove('hidden');
            document.getElementById('profileModal').classList.add('flex');
        }
        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
            document.getElementById('profileModal').classList.remove('flex');
        }
        document.getElementById('profileForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const body = new URLSearchParams({
                accion: 'guardar_perfil',
                nombre: document.getElementById('profileNombre').value.trim(),
                apellido: document.getElementById('profileApellido').value.trim(),
                telefono: document.getElementById('profileTelefono').value.trim()
            });
            try {
                await fetch('admin.php', { method: 'POST', body });
                SESSION.nombre = document.getElementById('profileNombre').value.trim();
                SESSION.apellido = document.getElementById('profileApellido').value.trim();
                SESSION.telefono = document.getElementById('profileTelefono').value.trim();
                location.reload();
            } catch { alert('Error al guardar'); }
        });

        function openSettingsModal() {
            document.getElementById('userMenu').classList.remove('open');
            const saved = JSON.parse(localStorage.getItem('dachiAdminSettings') || '{}');
            document.getElementById('settingsFontSize').value = saved.fontSize || 'mediano';
            document.getElementById('settingsDarkMode').checked = document.documentElement.classList.contains('dark');
            document.getElementById('settingsModal').classList.remove('hidden');
            document.getElementById('settingsModal').classList.add('flex');
        }
        function closeSettingsModal() {
            document.getElementById('settingsModal').classList.add('hidden');
            document.getElementById('settingsModal').classList.remove('flex');
        }
        document.getElementById('settingsForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const fs = document.getElementById('settingsFontSize').value;
            const dm = document.getElementById('settingsDarkMode').checked;
            const sizes = { pequeno: '14px', mediano: '16px', grande: '18px' };
            document.documentElement.style.fontSize = sizes[fs] || '16px';
            if (dm !== document.documentElement.classList.contains('dark')) toggleDarkMode();
            localStorage.setItem('dachiAdminSettings', JSON.stringify({ fontSize: fs }));
            closeSettingsModal();
        });

        (function applySavedSettings() {
            const saved = JSON.parse(localStorage.getItem('dachiAdminSettings') || '{}');
            const sizes = { pequeno: '14px', mediano: '16px', grande: '18px' };
            if (saved.fontSize) document.documentElement.style.fontSize = sizes[saved.fontSize] || '16px';
        })();

        document.addEventListener('click', function(e) {
            ['userMenu', 'notificationMenu'].forEach(id => {
                const menu = document.getElementById(id);
                const btn = id === 'userMenu'
                    ? document.querySelector('[onclick="toggleUserMenu()"]')
                    : document.getElementById('notificationBtn');
                if (menu && menu.classList.contains('open') && !menu.contains(e.target) && !btn?.contains(e.target)) {
                    menu.classList.remove('open');
                }
            });
            const overlay = e.target.closest('.modal-overlay');
            if (overlay && e.target === overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        });

        function applySidebarState() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth >= 1024) {
                const collapsed = localStorage.getItem(SIDEBAR_STATE_KEY) === '1';
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                const icon = document.getElementById('toggle-icon');
                if (icon) icon.textContent = collapsed ? 'right_panel_close' : 'left_panel_close';
            } else {
                document.body.classList.remove('sidebar-collapsed');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function applyDarkMode() {
            const saved = localStorage.getItem('dachiAdminDarkMode');
            if (saved === '1') {
                document.documentElement.classList.add('dark');
                const icon = document.getElementById('darkModeIcon');
                if (icon) icon.textContent = 'light_mode';
            }
        }

        function checkPendingNotification() {
            const raw = sessionStorage.getItem('dachiNotifications');
            if (raw) {
                try {
                    const list = JSON.parse(raw);
                    list.forEach(function (n) {
                        if (!n._toasted) { showToast(n.message, n.type); n._toasted = true; }
                    });
                    sessionStorage.setItem('dachiNotifications', JSON.stringify(list));
                    renderNotifications();
                } catch (e) {}
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            applySidebarState();
            applyDarkMode();
            window.addEventListener('resize', applySidebarState);
            checkPendingNotification();
        });
    </script>

    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[400] hidden items-center justify-center p-4 modal-overlay" id="confirmModal">
        <div class="bg-white rounded-[24px] w-full max-w-md p-6 sm:p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-none" id="confirmIconBox">
                    <span class="material-symbols-outlined text-[28px]" id="confirmIcon"></span>
                </div>
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface" id="confirmTitle"></h3>
                    <p class="text-body-sm text-secondary mt-1" id="confirmMessage"></p>
                </div>
            </div>
            <div class="hidden mb-4" id="confirmReasonBox">
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Motivo del rechazo <span class="text-error">*</span></label>
                <textarea class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white min-h-[80px] resize-none" id="confirmReason" maxlength="300" placeholder="Indique por qué se rechaza este producto..."></textarea>
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Máximo 300 caracteres</p>
            </div>
            <div class="flex gap-3">
                <button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeConfirmModal()" type="button">Cancelar</button>
                <button class="flex-1 px-4 py-3 rounded-xl text-white font-semibold transition-all active:scale-[0.98]" id="confirmBtn" type="button"></button>
            </div>
        </div>
    </div>

    <script>
        let _confirmCallback = null;

        function openConfirmModal(title, message, icon, iconBg, btnText, btnBg, callback) {
            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmIcon').textContent = icon;
            document.getElementById('confirmIconBox').className = `w-12 h-12 rounded-2xl flex items-center justify-center flex-none ${iconBg}`;
            const btn = document.getElementById('confirmBtn');
            btn.textContent = btnText;
            btn.className = `flex-1 px-4 py-3 rounded-xl text-white font-semibold transition-all active:scale-[0.98] ${btnBg}`;
            document.getElementById('confirmReasonBox').classList.add('hidden');
            document.getElementById('confirmReason').value = '';
            _confirmCallback = callback;
            document.getElementById('confirmModal').classList.remove('hidden');
            document.getElementById('confirmModal').classList.add('flex');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.getElementById('confirmModal').classList.remove('flex');
            _confirmCallback = null;
        }

        document.getElementById('confirmBtn').addEventListener('click', function () {
            if (_confirmCallback) _confirmCallback();
        });

        function cambiarEstadoProducto(id, estado) {
            const labels = {
                aprobado:   { title: 'Aprobar producto',   msg: 'El producto quedará visible en el catálogo.',            icon: 'check_circle', iconBg: 'bg-green-50 text-green-700',   btn: 'APROBAR',          btnBg: 'bg-green-700 hover:bg-green-800' },
                rechazado:  { title: 'Rechazar producto',   msg: 'El producto no estará disponible en el catálogo.',        icon: 'cancel',        iconBg: 'bg-red-50 text-red-600',       btn: 'RECHAZAR',         btnBg: 'bg-red-600 hover:bg-red-700' },
                inactivo:   { title: 'Deshabilitar producto', msg: 'El producto se ocultará del catálogo temporalmente.',   icon: 'visibility_off', iconBg: 'bg-surface-container text-muted', btn: 'DESHABILITAR',     btnBg: 'bg-neutral-700 hover:bg-neutral-800' },
                pendiente:  { title: 'Restaurar producto',  msg: 'El producto volverá a estado pendiente de revisión.',     icon: 'pending',       iconBg: 'bg-amber-50 text-amber-600',  btn: 'RESTAURAR',        btnBg: 'bg-amber-600 hover:bg-amber-700' }
            };
            const cfg = labels[estado] || labels.pendiente;
            const reasonBox = document.getElementById('confirmReasonBox');

            openConfirmModal(cfg.title, cfg.msg, cfg.icon, cfg.iconBg, cfg.btn, cfg.btnBg, function () {
                if (estado === 'rechazado') {
                    reasonBox.classList.remove('hidden');
                    const reason = document.getElementById('confirmReason').value.trim();
                    if (!reason) {
                        document.getElementById('confirmReason').focus();
                        document.getElementById('confirmReason').classList.add('border-error', 'ring-1', 'ring-error');
                        setTimeout(() => document.getElementById('confirmReason').classList.remove('border-error', 'ring-1', 'ring-error'), 2000);
                        return;
                    }
                    closeConfirmModal();
                    confirmarCambio(id, estado, reason);
                } else {
                    closeConfirmModal();
                    confirmarCambio(id, estado, '');
                }
            });

            if (estado === 'rechazado') {
                setTimeout(() => reasonBox.classList.remove('hidden'), 100);
            }
        }

        function confirmarCambio(id, estado, motivo) {
            const formData = new FormData();
            formData.append('accion', 'cambiar_estado_producto');
            formData.append('id', id);
            formData.append('estado', estado);
            formData.append('motivo', motivo);
            fetch('admin.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        const msgs = { aprobado: 'Producto aprobado correctamente', rechazado: 'Producto rechazado correctamente', inactivo: 'Producto deshabilitado correctamente', pendiente: 'Producto restaurado correctamente' };
                        storeNotification(msgs[estado] || 'Estado actualizado correctamente', 'success');
                        location.reload();
                    } else {
                        openConfirmModal('Error', data.message || 'No se pudo completar la acción', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
                        document.getElementById('confirmBtn').onclick = closeConfirmModal;
                    }
                })
                .catch(() => {
                    openConfirmModal('Error de conexión', 'Verifique su conexión e intente nuevamente.', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
                    document.getElementById('confirmBtn').onclick = closeConfirmModal;
                });
        }
    </script>

<div class="fixed bottom-6 right-6 z-[500] space-y-3" id="toastContainer"></div>

<script>
const NOTIF_KEY = 'dachiNotifications';

function storeNotification(message, type) {
    const list = JSON.parse(sessionStorage.getItem(NOTIF_KEY) || '[]');
    list.push({ id: Date.now() + '_' + Math.random().toString(36).slice(2, 6), message, type, time: Date.now(), read: false });
    sessionStorage.setItem(NOTIF_KEY, JSON.stringify(list));
}

function showToast(message, type) {
    const icons = { success: 'check_circle', error: 'error', info: 'info', warning: 'warning' };
    const colors = { success: 'bg-green-700 text-white', error: 'bg-red-700 text-white', info: 'bg-primary text-white', warning: 'bg-amber-600 text-white' };
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl ' + (colors[type] || colors.info) + ' animate-slide-up cursor-pointer';
    toast.innerHTML = '<span class="material-symbols-outlined text-[22px]">' + (icons[type] || icons.info) + '</span><span class="text-sm font-semibold">' + message + '</span>';
    toast.addEventListener('click', function () { this.remove(); });
    container.appendChild(toast);
    setTimeout(() => { if (toast.parentNode) { toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)'; toast.style.transition = 'all 0.3s ease'; setTimeout(() => toast.remove(), 300); } }, 4000);
}

function renderNotifications() {
    const menu = document.getElementById('notificationMenu');
    const dot = document.getElementById('notificationDot');
    if (!menu) return;
    const list = JSON.parse(sessionStorage.getItem(NOTIF_KEY) || '[]');
    const headers = menu.querySelector('.flex.items-center.justify-between');
    const empty = menu.querySelector('.p-6.text-center');

    menu.querySelectorAll('.notif-item').forEach(el => el.remove());
    const clearBtn = menu.querySelector('.notif-clear');
    if (clearBtn) clearBtn.remove();

    if (list.length === 0) {
        if (empty) empty.classList.remove('hidden');
        if (dot) dot.classList.add('hidden');
        return;
    }
    if (empty) empty.classList.add('hidden');
    if (dot) { dot.textContent = list.length; dot.classList.remove('hidden'); }

    list.forEach(function (n) {
        const item = document.createElement('div');
        item.className = 'notif-item flex items-start gap-3 p-3 rounded-xl hover:bg-surface-container-low transition-colors group';
        const icons = { success: 'check_circle', error: 'cancel', info: 'info', warning: 'warning' };
        item.innerHTML = '<span class="material-symbols-outlined text-[20px] text-primary mt-0.5 flex-none">' + (icons[n.type] || 'notifications') + '</span>'
            + '<div class="flex-1 min-w-0"><p class="text-sm text-on-surface">' + n.message + '</p><span class="text-xs text-on-surface-variant">'
            + new Date(n.time).toLocaleString('es-PA') + '</span></div>'
            + '<button class="opacity-0 group-hover:opacity-100 transition-opacity text-on-surface-variant hover:text-error flex-none" onclick="dismissNotification(\'' + n.id + '\')"><span class="material-symbols-outlined text-[18px]">close</span></button>';
        menu.appendChild(item);
    });

    const clr = document.createElement('div');
    clr.className = 'notif-clear border-t border-outline-variant/30 pt-2 mt-2 px-1';
    clr.innerHTML = '<button class="w-full text-center text-xs font-semibold text-on-surface-variant hover:text-primary py-2 transition-colors" onclick="dismissAllNotifications()">Marcar todas como leídas</button>';
    menu.appendChild(clr);
}

function dismissNotification(id) {
    let list = JSON.parse(sessionStorage.getItem(NOTIF_KEY) || '[]');
    list = list.filter(function (n) { return n.id !== id; });
    sessionStorage.setItem(NOTIF_KEY, JSON.stringify(list));
    renderNotifications();
}

function dismissAllNotifications() {
    sessionStorage.removeItem(NOTIF_KEY);
    renderNotifications();
}
</script>

<style>
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-slide-up { animation: slideUp 0.3s ease-out; }
</style>

</body>
</html>
