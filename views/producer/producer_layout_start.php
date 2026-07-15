<?php
$producerUserName = htmlspecialchars(trim((($contexto['usuario']['nombre'] ?? '') . ' ' . ($contexto['usuario']['apellido'] ?? ''))), ENT_QUOTES, 'UTF-8');
$producerUserEmail = htmlspecialchars($contexto['usuario']['correo'] ?? '', ENT_QUOTES, 'UTF-8');
$producerUserRole = htmlspecialchars(ucfirst($contexto['rolActual'] ?? 'Productor'), ENT_QUOTES, 'UTF-8');
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<div class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="fixed left-0 top-0 h-full w-72 bg-surface-container-lowest border-r border-outline-variant z-[70] -translate-x-full sidebar-transition flex flex-col" id="sidebar">
    <div class="p-gutter flex items-center justify-between border-b border-outline-variant h-16">
        <div class="dachi-sidebar-brand flex items-center gap-3">
            <img src="../../img/LG.png" alt="DACHI" class="h-8" />
            <span class="font-semibold text-on-surface">Gestión Productor</span>
        </div>
        <button class="dachi-sidebar-close p-2 hover:bg-surface-container-low rounded-full" onclick="toggleSidebar()" title="Compactar menú" type="button">
            <span class="material-symbols-outlined dachi-sidebar-close-icon">left_panel_close</span>
        </button>
    </div>
    <nav class="p-4 space-y-2 flex-grow" id="sidebarNav">
        <a href="productor_tablero.php" class="flex items-center gap-3 rounded-xl px-4 py-3 <?= $currentFile === 'productor_tablero.php' ? 'bg-primary-container text-on-primary-container' : 'text-on-surface hover:bg-surface-container-low' ?>">
            <span class="material-symbols-outlined">dashboard</span>
            Tablero
        </a>
        <a href="productor_prod.php" class="flex items-center gap-3 rounded-xl px-4 py-3 <?= $currentFile === 'productor_prod.php' ? 'bg-primary-container text-on-primary-container' : 'text-on-surface hover:bg-surface-container-low' ?>">
            <span class="material-symbols-outlined">inventory_2</span>
            Mis productos
        </a>
        <a href="pedidos.php" class="flex items-center gap-3 rounded-xl px-4 py-3 <?= $currentFile === 'pedidos.php' ? 'bg-primary-container text-on-primary-container' : 'text-on-surface hover:bg-surface-container-low' ?>">
            <span class="material-symbols-outlined">shopping_cart</span>
            Pedidos
        </a>
        <a href="reclamos.php" class="flex items-center gap-3 rounded-xl px-4 py-3 <?= $currentFile === 'reclamos.php' ? 'bg-primary-container text-on-primary-container' : 'text-on-surface hover:bg-surface-container-low' ?>">
            <span class="material-symbols-outlined">report_problem</span>
            Reclamaciones
        </a>
    </nav>
    <div class="p-4 border-t border-outline-variant">
        <button class="w-full flex items-center gap-4 p-3 rounded-xl text-error hover:bg-error-container/40 transition-all" onclick="logout()" type="button">
            <span class="material-symbols-outlined">logout</span>
            <span class="font-label-md">Cerrar Sesión</span>
        </button>
    </div>
</aside>
<nav class="dachi-topbar bg-surface/80 backdrop-blur-md w-full top-0 sticky z-50 border-b border-outline-variant shadow-sm">
    <div class="flex items-center gap-3 px-gutter max-w-container-max mx-auto h-[72px]">
        <div class="dachi-mobile-brand flex items-center gap-stack-md flex-none">
            <button class="dachi-sidebar-toggle p-2 -ml-2 rounded-full hover:bg-surface-container-low transition-colors" onclick="toggleSidebar()" title="Abrir menú" type="button">
                <span class="material-symbols-outlined text-primary dachi-sidebar-toggle-icon">menu</span>
            </button>
            <img alt="DACHI" class="dachi-topbar-logo" src="../../img/LG.png" />
        </div>
        <div class="flex-1"></div>
        <div class="dachi-topbar-actions flex items-center gap-1 sm:gap-2 relative flex-none">
            <span class="dachi-topbar-divider hidden sm:block"></span>
            <div class="hidden md:block text-right mr-1">
                <span class="font-semibold text-sm text-on-surface block" id="panelUserName"><?= $producerUserName ?></span>
                <small class="text-on-surface-variant"><?= $producerUserRole ?></small>
            </div>
            <button class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden flex items-center justify-center font-bold text-primary cursor-pointer" id="userAvatarBtn" onclick="toggleUserMenu()" title="Abrir perfil" type="button">
                <span class="avatar-initial" id="panelUserAvatar"><?= strtoupper(substr($producerUserName, 0, 1)) ?></span>
            </button>
            <div class="user-menu absolute right-0 top-12 w-72 bg-white rounded-2xl border border-outline-variant/50 shadow-xl p-2 z-[200] hidden" id="userMenu">
                <div class="flex items-center gap-3 p-3 border-b border-outline-variant/30 mb-2">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed overflow-hidden border border-outline-variant flex items-center justify-center font-bold text-primary flex-none">
                        <span class="avatar-initial"><?= strtoupper(substr($producerUserName, 0, 1)) ?></span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-label-md text-label-md text-on-surface truncate"><?= $producerUserName ?></p>
                        <p class="text-label-sm text-on-surface-variant truncate"><?= $producerUserEmail ?></p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide"><?= $producerUserRole ?></span>
                    </div>
                </div>
                <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-on-surface flex items-center gap-3" onclick="openProfileModal()" type="button">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
                    Perfil
                </button>
                <button class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-label-md font-label-md text-error flex items-center gap-3" onclick="logout()" type="button">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </div>
</nav>
<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-stack-lg relative z-10">