</main>

<footer class="border-t border-outline-variant mt-auto">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8">
        <p class="font-semibold text-on-surface-variant text-sm">DACHI</p>
        <p class="text-on-surface-variant text-sm">&copy; 2026 DACHI. Cultivando confianza.</p>
    </div>
</footer>

<style>
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
</style>

<!-- MODAL PERFIL -->
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="profileModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary">Editar Perfil</h3>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wide">PRODUCTOR</span>
            </div>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeProfileModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="space-y-4" id="profileForm">
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre</label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="profileNombre" required type="text" />
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Apellido</label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="profileApellido" type="text" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Correo</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant outline-none" disabled id="profileCorreo" type="email" />
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Teléfono</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="profileTelefono" placeholder="+507 6000-0000" type="tel" />
                </div>
            </div>
            <button class="w-full bg-primary text-white py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98] mt-2" type="submit">GUARDAR CAMBIOS</button>
        </form>
    </div>
</div>

<!-- MODAL CONFIGURACION -->
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="settingsModal">
    <div class="bg-white rounded-[24px] w-full max-w-md p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-sm text-headline-sm text-primary">Configuración</h3>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeSettingsModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="space-y-6" id="settingsForm">
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">Tamaño de Fuente</label>
                <select class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary outline-none bg-white" id="settingsFontSize">
                    <option value="pequeno">Pequeño</option>
                    <option value="mediano" selected>Mediano</option>
                    <option value="grande">Grande</option>
                </select>
            </div>
            <button class="w-full bg-primary text-white py-4 rounded-xl font-label-md text-label-md hover:bg-primary-container transition-all active:scale-[0.98]" type="submit">GUARDAR</button>
        </form>
    </div>
</div>

<script>
    const SIDEBAR_STATE_KEY = 'dachiProducerSidebarCollapsed';
    const SESSION = {
        nombre: <?= json_encode($contexto['usuario']['nombre'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        apellido: <?= json_encode($contexto['usuario']['apellido'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        correo: <?= json_encode($contexto['usuario']['correo'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        telefono: <?= json_encode($contexto['usuario']['telefono'] ?? '', JSON_UNESCAPED_UNICODE) ?>
    };

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
    });

    function toggleUserMenu() {
        document.getElementById('userMenu').classList.toggle('open');
    }

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

    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = new URLSearchParams({
            accion: 'guardar_perfil',
            nombre: document.getElementById('profileNombre').value.trim(),
            apellido: document.getElementById('profileApellido').value.trim(),
            telefono: document.getElementById('profileTelefono').value.trim()
        });
        try {
            await fetch('productor_tablero.php', { method: 'POST', body });
            SESSION.nombre = document.getElementById('profileNombre').value.trim();
            SESSION.apellido = document.getElementById('profileApellido').value.trim();
            SESSION.telefono = document.getElementById('profileTelefono').value.trim();
            location.reload();
        } catch { alert('Error al guardar'); }
    });

    function openSettingsModal() {
        document.getElementById('userMenu').classList.remove('open');
        const saved = JSON.parse(localStorage.getItem('dachiProducerSettings') || '{}');
        document.getElementById('settingsFontSize').value = saved.fontSize || 'mediano';
        document.getElementById('settingsModal').classList.remove('hidden');
        document.getElementById('settingsModal').classList.add('flex');
    }

    function closeSettingsModal() {
        document.getElementById('settingsModal').classList.add('hidden');
        document.getElementById('settingsModal').classList.remove('flex');
    }

    document.getElementById('settingsForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const fs = document.getElementById('settingsFontSize').value;
        const sizes = { pequeno: '14px', mediano: '16px', grande: '18px' };
        document.documentElement.style.fontSize = sizes[fs] || '16px';
        localStorage.setItem('dachiProducerSettings', JSON.stringify({ fontSize: fs }));
        closeSettingsModal();
    });

    (function applySavedSettings() {
        const saved = JSON.parse(localStorage.getItem('dachiProducerSettings') || '{}');
        const sizes = { pequeno: '14px', mediano: '16px', grande: '18px' };
        if (saved.fontSize) document.documentElement.style.fontSize = sizes[saved.fontSize] || '16px';
    })();

    function toggleProductForm() {
        const form = document.getElementById('newProductForm');
        if (!form) return;
        form.classList.toggle('hidden');
    }

    function logout() {
        window.location.href = '../../panel.php?logout=1';
    }

    document.addEventListener('DOMContentLoaded', applySavedSidebarState);

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('userMenu');
        const btn = document.getElementById('userAvatarBtn');
        if (menu && menu.classList.contains('open') && !menu.contains(e.target) && !btn?.contains(e.target)) {
            menu.classList.remove('open');
        }
        const overlay = e.target.closest('.modal-overlay');
        if (overlay && e.target === overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    });
</script>
</body>
</html>
