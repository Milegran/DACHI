<?php
$paginaActiva = $paginaActiva ?? '';
$rolNavegacion = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
$nombreNavegacion = trim(($usuarioActual['nombre'] ?? 'Usuario') . ' ' . ($usuarioActual['apellido'] ?? ''));
$inicialNavegacion = mb_strtoupper(mb_substr($usuarioActual['nombre'] ?? 'U', 0, 1));
$totalCarritoNavegacion = array_sum(is_array($_SESSION['carrito'] ?? null) ? $_SESSION['carrito'] : []);
$itemsNavegacion = [
    ['id' => 'inicio', 'href' => '../panel.php#productos', 'label' => 'Inicio', 'icon' => 'dashboard'],
    ['id' => 'carrito', 'href' => 'carrito.php', 'label' => 'Carrito', 'icon' => 'shopping_cart']
];
if ($rolNavegacion === 'consumidor') {
    $itemsNavegacion[] = ['id' => 'seguimiento', 'href' => 'seguimiento.php', 'label' => 'Mis pedidos', 'icon' => 'package_2'];
}
?>
<div class="fixed inset-0 bg-black/50 z-[60] hidden" id="dachiSidebarOverlay" onclick="toggleDachiSidebar()"></div>

<aside class="fixed left-0 top-0 h-full w-72 bg-surface-container-lowest border-r border-outline-variant z-[70] -translate-x-full dachi-sidebar-transition flex flex-col dachi-shell-surface dachi-shell-border"
    id="dachiSidebar">
    <div class="h-16 px-6 flex items-center justify-between border-b border-outline-variant dachi-shell-border">
        <div class="dachi-sidebar-brand">
            <img src="../img/LG.png" alt="DACHI" />
            <span>Gesti&oacute;n agr&iacute;cola</span>
        </div>
        <button class="dachi-sidebar-close w-10 h-10 inline-flex items-center justify-center rounded-lg hover:bg-surface-container-low dark-surface-low" onclick="toggleDachiSidebar()" title="Compactar menu" type="button">
            <span class="material-symbols-outlined dachi-sidebar-close-icon">left_panel_close</span>
        </button>
    </div>
    <nav class="p-4 space-y-2 flex-1">
        <?php foreach ($itemsNavegacion as $item): ?>
            <?php $activo = $paginaActiva === $item['id']; ?>
            <a class="flex items-center gap-4 p-3 rounded-lg transition-colors <?= $activo ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary-fixed/20 hover:text-primary dark-muted' ?>"
                href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="material-symbols-outlined"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="font-semibold text-sm"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-outline-variant dachi-shell-border">
        <a class="flex items-center gap-4 p-3 rounded-lg text-error hover:bg-error-container/40" href="../panel.php?logout=1">
            <span class="material-symbols-outlined">logout</span>
            <span class="font-semibold text-sm">Cerrar sesion</span>
        </a>
    </div>
</aside>

<nav class="dachi-topbar sticky top-0 z-40 border-b border-outline-variant bg-surface/95 backdrop-blur dachi-shell-top dachi-shell-border">
    <div class="h-[72px] max-w-content mx-auto px-6 flex items-center gap-3">
        <div class="dachi-mobile-brand flex items-center gap-4 flex-none">
            <button class="dachi-sidebar-toggle w-10 h-10 inline-flex items-center justify-center rounded-lg hover:bg-surface-container-low dark-surface-low" onclick="toggleDachiSidebar()" title="Abrir menu" type="button">
                <span class="material-symbols-outlined text-primary dachi-shell-brand dachi-sidebar-toggle-icon">menu</span>
            </button>
            <img alt="DACHI" class="dachi-topbar-logo" src="../img/LG.png" />
        </div>

        <?php if ($rolNavegacion === 'consumidor'): ?>
            <form action="../panel.php#productos" class="dachi-global-search relative flex-1 max-w-2xl mx-auto" method="get">
                <span class="material-symbols-outlined">search</span>
                <input aria-label="Buscar productos" maxlength="60" name="buscar" placeholder="Buscar productos o productores..." type="search" />
            </form>
        <?php else: ?>
            <div class="flex-1"></div>
        <?php endif; ?>

        <div class="dachi-topbar-actions relative flex items-center gap-1 sm:gap-2 flex-none">
            <?php if ($rolNavegacion === 'consumidor'): ?>
                <a class="dachi-icon-button relative" href="carrito.php" title="Ver carrito">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span class="<?= $totalCarritoNavegacion > 0 ? '' : 'hidden ' ?>dachi-action-badge" id="contadorNav"><?= $totalCarritoNavegacion ?></span>
                </a>
            <?php endif; ?>
            <div class="relative">
                <button class="dachi-icon-button relative" id="dachiNotificationButton" onclick="toggleDachiNotifications()" title="Notificaciones" type="button">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="dachi-action-badge">1</span>
                </button>
                <div class="dachi-notification-menu hidden" id="dachiNotificationMenu">
                    <div class="dachi-popover-heading"><div><p>Notificaciones</p><span>Actividad de tu cuenta</span></div><span class="material-symbols-outlined">notifications</span></div>
                    <div class="dachi-notification-list">
                        <?php if ($totalCarritoNavegacion > 0): ?>
                            <a href="carrito.php"><span class="material-symbols-outlined">shopping_bag</span><div><strong>Carrito pendiente</strong><small>Tienes <?= $totalCarritoNavegacion ?> unidad<?= $totalCarritoNavegacion === 1 ? '' : 'es' ?> por confirmar.</small></div></a>
                        <?php else: ?>
                            <a href="../panel.php#productos"><span class="material-symbols-outlined">eco</span><div><strong>Productos disponibles</strong><small>Explora la selecci&oacute;n local desde tu inicio.</small></div></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a class="dachi-icon-button" href="../panel.php?config=1" title="Configuraci&oacute;n"><span class="material-symbols-outlined">settings</span></a>
            <span class="dachi-topbar-divider hidden sm:block"></span>
            <span class="hidden md:inline text-sm font-semibold text-on-surface-variant dachi-shell-muted">
                <?= htmlspecialchars($usuarioActual['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <button class="w-10 h-10 rounded-full border-2 border-primary text-primary dark:text-primary-fixed font-bold inline-flex items-center justify-center" id="dachiUserButton" onclick="toggleDachiUserMenu()" type="button">
                <?= htmlspecialchars($inicialNavegacion, ENT_QUOTES, 'UTF-8') ?>
            </button>
            <div class="dachi-user-menu absolute right-0 top-12 w-72 bg-surface-container-lowest rounded-lg border border-outline-variant shadow-xl p-2 z-50 dachi-shell-surface dachi-shell-border" id="dachiUserMenu">
                <div class="px-3 py-3 border-b border-outline-variant/50 dachi-shell-border">
                    <p class="font-semibold truncate"><?= htmlspecialchars($nombreNavegacion, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-on-surface-variant truncate dachi-shell-muted"><?= htmlspecialchars($usuarioActual['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <a class="mt-2 px-3 py-2 rounded-lg flex items-center gap-3 hover:bg-surface-container-low dark-surface-low" href="../panel.php">
                    <span class="material-symbols-outlined text-lg">dashboard</span>
                    <span class="text-sm font-semibold">Ir al panel</span>
                </a>
                <a class="px-3 py-2 rounded-lg flex items-center gap-3 text-error hover:bg-error-container/40" href="../panel.php?logout=1">
                    <span class="material-symbols-outlined text-lg">logout</span>
                    <span class="text-sm font-semibold">Cerrar sesion</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    const DACHI_SIDEBAR_STATE_KEY = 'dachiSidebarCollapsed';

    function updateDachiSidebarControls() {
        const collapsed = document.body.classList.contains('dachi-sidebar-collapsed');
        document.querySelectorAll('.dachi-sidebar-toggle-icon').forEach(icon => {
            icon.textContent = collapsed ? 'left_panel_open' : 'menu';
        });
    }

    function applyDachiSidebarState() {
        const sidebar = document.getElementById('dachiSidebar');
        const overlay = document.getElementById('dachiSidebarOverlay');
        if (window.innerWidth >= 1024) {
            document.body.classList.toggle('dachi-sidebar-collapsed', localStorage.getItem(DACHI_SIDEBAR_STATE_KEY) === '1');
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            document.body.classList.remove('dachi-sidebar-collapsed');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
        updateDachiSidebarControls();
    }

    function toggleDachiSidebar() {
        const sidebar = document.getElementById('dachiSidebar');
        const overlay = document.getElementById('dachiSidebarOverlay');
        if (window.innerWidth >= 1024) {
            const collapsed = document.body.classList.toggle('dachi-sidebar-collapsed');
            localStorage.setItem(DACHI_SIDEBAR_STATE_KEY, collapsed ? '1' : '0');
            updateDachiSidebarControls();
            return;
        }
        const estaCerrado = sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', !estaCerrado);
        overlay.classList.toggle('hidden', !estaCerrado);
        document.body.style.overflow = estaCerrado ? 'hidden' : '';
    }

    function toggleDachiUserMenu() {
        document.getElementById('dachiNotificationMenu').classList.add('hidden');
        document.getElementById('dachiUserMenu').classList.toggle('open');
    }

    function toggleDachiNotifications() {
        document.getElementById('dachiUserMenu').classList.remove('open');
        document.getElementById('dachiNotificationMenu').classList.toggle('hidden');
    }

    document.addEventListener('click', event => {
        const menu = document.getElementById('dachiUserMenu');
        const boton = document.getElementById('dachiUserButton');
        if (menu?.classList.contains('open') && !menu.contains(event.target) && !boton.contains(event.target)) {
            menu.classList.remove('open');
        }
        const notifications = document.getElementById('dachiNotificationMenu');
        const notificationButton = document.getElementById('dachiNotificationButton');
        if (!notifications.contains(event.target) && !notificationButton.contains(event.target)) {
            notifications.classList.add('hidden');
        }
    });

    let dachiSidebarUsesDesktopLayout = window.innerWidth >= 1024;
    window.addEventListener('resize', () => {
        const usesDesktopLayout = window.innerWidth >= 1024;
        if (usesDesktopLayout !== dachiSidebarUsesDesktopLayout) {
            dachiSidebarUsesDesktopLayout = usesDesktopLayout;
            applyDachiSidebarState();
        }
    });

    applyDachiSidebarState();
</script>
