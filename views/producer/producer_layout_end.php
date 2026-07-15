</main>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const isHidden = sidebar.classList.contains('-translate-x-full');
        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        menu.classList.toggle('hidden');
    }

    function openProfileModal() {
        alert('Abrir edición de perfil');
    }

    function toggleProductForm() {
        const form = document.getElementById('newProductForm');
        if (!form) return;
        form.classList.toggle('hidden');
    }

    function logout() {
        window.location.href = '../../panel.php?logout=1';
    }
</script>
</body>
</html>
