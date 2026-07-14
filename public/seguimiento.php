<?php
session_start();

require_once __DIR__ . "/../conexion.php";
require_once __DIR__ . "/../app/Controllers/SeguimientoController.php";

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$rolActual = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
if ($rolActual !== 'consumidor') {
    header('Location: ../panel.php');
    exit;
}

$idUsuario = (int) ($usuarioActual['id'] ?? 0);
$controller = new SeguimientoController(new SistemaDachiFacade($conn));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: application/json');
    $respuesta = $controller->handle($_POST, $idUsuario);

    echo json_encode($respuesta);
    $conn->close();
    exit;
}

$estadoInicial = $controller->initialState($idUsuario);
$pedidosIniciales = $estadoInicial['orders'];
$direccionInicial = $estadoInicial['address'];
$conn->close();

$ajustes = isset($_COOKIE['dachi_ajustes']) ? json_decode($_COOKIE['dachi_ajustes'], true) : [];
$fontSize = $ajustes['fontSize'] ?? 'mediano';
$darkMode = !empty($ajustes['darkMode']);
$fontSizesPx = ['pequeno' => '14px', 'mediano' => '16px', 'grande' => '18px'];
$fontSizePx = $fontSizesPx[$fontSize] ?? $fontSizesPx['mediano'];

function valorDireccion(?array $direccion, string $campo): string
{
    return htmlspecialchars($direccion[$campo] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html class="light<?= $darkMode ? ' dark' : '' ?>" lang="es" style="font-size:<?= $fontSizePx ?>">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Seguimiento de pedidos</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Source+Serif+4:wght@600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet" />
    <link href="../css/dachi-brand.css" rel="stylesheet" />
    <link href="../css/dachi-shell.css" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#003118', 'primary-container': '#16482b', 'primary-fixed': '#b9efc7',
                        secondary: '#795900', 'secondary-container': '#ffc641', surface: '#f7faf5',
                        'surface-container-lowest': '#ffffff', 'surface-container-low': '#f1f4f0',
                        'surface-container-highest': '#e0e3df', 'on-surface': '#191c1a',
                        'on-surface-variant': '#414942', outline: '#717971', 'outline-variant': '#c1c9bf',
                        error: '#ba1a1a', 'error-container': '#ffdad6'
                    },
                    fontFamily: { sans: ['Hanken Grotesk'], serif: ['Source Serif 4'] },
                    maxWidth: { content: '1280px' }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { letter-spacing: 0; }
        html.dark body { background: #10130f; color: #dfe5dc; }
        html.dark .dark-surface { background: #1c211b !important; }
        html.dark .dark-surface-low { background: #20261f !important; }
        html.dark .dark-border { border-color: #414941 !important; }
        html.dark .dark-text { color: #dfe5dc !important; }
        html.dark .dark-muted { color: #aeb7ad !important; }
    </style>
    <link href="../css/dachi-botanical.css" rel="stylesheet" />
</head>

<body class="dachi-app bg-surface text-on-surface min-h-screen flex flex-col font-sans">
    <?php $paginaActiva = 'seguimiento'; require __DIR__ . '/partials/navigation.php'; ?>

    <main class="w-full max-w-content mx-auto px-4 md:px-12 py-10 flex-1">
        <div class="mb-10">
            <h1 class="font-serif text-[32px] leading-10 font-bold text-primary dark:text-primary-fixed">Seguimiento de pedidos</h1>
            <p class="mt-2 text-on-surface-variant dark-muted">Consulta el avance de tus compras y su historial de entrega.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-10 lg:gap-14 items-start">
            <section>
                <div class="flex items-center justify-between pb-4 border-b border-outline-variant/70 dark-border">
                    <h2 class="font-serif text-[24px] leading-8 font-semibold text-primary dark:text-primary-fixed">Mis pedidos</h2>
                    <span class="text-sm font-semibold text-on-surface-variant dark-muted" id="cantidadPedidos"></span>
                </div>
                <div class="space-y-6 mt-6" id="listaPedidos"></div>
                <div class="hidden py-20 text-center" id="sinPedidos">
                    <span class="material-symbols-outlined text-5xl text-outline">package_2</span>
                    <h3 class="font-serif text-2xl mt-4 dark-text">Aun no tienes pedidos</h3>
                    <p class="text-on-surface-variant dark-muted mt-2">Tus compras apareceran aqui despues de confirmarlas.</p>
                    <a class="inline-flex items-center gap-2 mt-6 px-5 py-3 bg-primary text-white rounded-lg font-semibold" href="../panel.php#productos">
                        <span class="material-symbols-outlined text-xl">grid_view</span>
                        Explorar productos
                    </a>
                </div>
            </section>

            <aside class="border border-outline-variant/70 rounded-lg p-6 bg-surface-container-lowest dark-surface dark-border lg:sticky lg:top-24">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary dark:text-primary-fixed">location_on</span>
                    <h2 class="font-serif text-[24px] leading-8 font-semibold dark-text">Direccion de entrega</h2>
                </div>
                <form class="mt-6 space-y-4" id="direccionForm" onsubmit="guardarDireccion(event)">
                    <div>
                        <label class="block text-sm font-semibold dark-text" for="provincia">Provincia</label>
                        <input class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="provincia" maxlength="50" required type="text" value="<?= valorDireccion($direccionInicial, 'provincia') ?>" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark-text" for="distrito">Distrito</label>
                        <input class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="distrito" maxlength="20" required type="text" value="<?= valorDireccion($direccionInicial, 'distrito') ?>" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark-text" for="corregimiento">Corregimiento</label>
                        <input class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="corregimiento" maxlength="50" required type="text" value="<?= valorDireccion($direccionInicial, 'corregimiento') ?>" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold dark-text" for="detalle">Calle, casa o referencia</label>
                        <textarea class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary resize-none" id="detalle" maxlength="250" required rows="3"><?= valorDireccion($direccionInicial, 'detalle') ?></textarea>
                    </div>
                    <p class="hidden text-sm rounded-lg p-3" id="mensajeDireccion"></p>
                    <button class="w-full h-11 bg-primary text-white rounded-lg font-bold inline-flex items-center justify-center gap-2" type="submit">
                        <span class="material-symbols-outlined text-xl">save</span>
                        Guardar direccion
                    </button>
                </form>
            </aside>
        </div>
    </main>

    <footer class="mt-auto border-t border-outline-variant/70 bg-surface-container-highest/50 dark-surface dark-border">
        <div class="max-w-content mx-auto px-4 md:px-12 py-5 text-sm text-on-surface-variant dark-muted">
            <span class="dachi-wordmark">DACHI</span>
            <span class="ml-2">© 2026. Cultivando confianza.</span>
        </div>
    </footer>

    <script>
        const RESPUESTA_INICIAL = <?= json_encode($pedidosIniciales, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        let pedidos = RESPUESTA_INICIAL.pedidos || [];

        function escapar(texto) {
            return String(texto ?? '').replace(/[&<>'"]/g, caracter => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
            })[caracter]);
        }

        function dinero(valor) {
            return `$${Number(valor || 0).toFixed(2)}`;
        }

        function fechaLegible(fecha) {
            if (!fecha) return '';
            return new Intl.DateTimeFormat('es-PA', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' }).format(new Date(`${fecha}T00:00:00Z`));
        }

        function renderEtapas(estado) {
            const etapas = [
                { etiqueta: 'Pedido recibido', icono: 'receipt_long' },
                { etiqueta: 'En camino', icono: 'local_shipping' },
                { etiqueta: 'Entregado', icono: 'task_alt' }
            ];

            return `<div class="grid grid-cols-3 gap-2 mt-6">${etapas.map((etapa, indice) => {
                const activa = indice <= estado;
                return `<div class="relative text-center">
                    ${indice < 2 ? `<span class="absolute h-0.5 top-5 left-1/2 w-full ${indice < estado ? 'bg-primary' : 'bg-outline-variant'}"></span>` : ''}
                    <span class="relative z-10 mx-auto w-10 h-10 rounded-full inline-flex items-center justify-center ${activa ? 'bg-primary text-white' : 'bg-surface-container-highest text-outline'}">
                        <span class="material-symbols-outlined text-xl">${etapa.icono}</span>
                    </span>
                    <p class="mt-2 text-xs font-semibold ${activa ? 'text-primary dark:text-primary-fixed' : 'text-on-surface-variant dark-muted'}">${etapa.etiqueta}</p>
                </div>`;
            }).join('')}</div>`;
        }

        function renderPedidos() {
            const lista = document.getElementById('listaPedidos');
            const vacio = document.getElementById('sinPedidos');
            document.getElementById('cantidadPedidos').textContent = `${pedidos.length} ${pedidos.length === 1 ? 'pedido' : 'pedidos'}`;

            if (pedidos.length === 0) {
                lista.innerHTML = '';
                vacio.classList.remove('hidden');
                return;
            }

            vacio.classList.add('hidden');
            lista.innerHTML = pedidos.map(pedido => `
                <article class="border border-outline-variant/70 rounded-lg bg-surface-container-lowest dark-surface dark-border overflow-hidden">
                    <header class="px-5 sm:px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-outline-variant/50 dark-border">
                        <div>
                            <p class="text-xs uppercase font-bold text-on-surface-variant dark-muted">Pedido #${pedido.id}</p>
                            <h3 class="font-serif text-xl font-semibold dark-text mt-1">${fechaLegible(pedido.fecha)}</h3>
                        </div>
                        <div class="sm:text-right">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold uppercase ${pedido.estado === 2 ? 'bg-primary-fixed text-primary' : pedido.estado === 1 ? 'bg-secondary-container text-secondary' : 'bg-surface-container-highest text-on-surface-variant'}">${escapar(pedido.estado_label)}</span>
                            <p class="font-serif text-2xl font-bold text-primary dark:text-primary-fixed mt-2">${dinero(pedido.total)}</p>
                        </div>
                    </header>
                    <div class="p-5 sm:p-6">
                        ${renderEtapas(pedido.estado)}
                        <div class="mt-7 pt-5 border-t border-outline-variant/50 dark-border space-y-3">
                            ${pedido.items.map(item => `<div class="flex items-start justify-between gap-4 text-sm"><span class="dark-text"><strong>${item.cantidad}x</strong> ${escapar(item.nombre)}</span><span class="font-semibold dark-text">${dinero(item.subtotal)}</span></div>`).join('')}
                        </div>
                        <div class="order-delivery-meta mt-5 pt-5 border-t grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-on-surface-variant dark-muted">
                            <p><strong class="dark-text">Pago:</strong> ${escapar(pedido.metodo_pago)}</p>
                            <p><strong class="dark-text">Repartidor:</strong> ${pedido.repartidor ? escapar(pedido.repartidor) : 'Por asignar'}</p>
                            <p><strong class="dark-text">Origen:</strong> ${escapar(pedido.origen || 'Productor local')}</p>
                            <p><strong class="dark-text">Entrega estimada:</strong> ${escapar(pedido.tiempo_estimado || 'Por calcular')}</p>
                        </div>
                    </div>
                </article>
            `).join('');
        }

        async function guardarDireccion(evento) {
            evento.preventDefault();
            const cuerpo = new URLSearchParams({
                accion: 'guardar_direccion',
                provincia: document.getElementById('provincia').value.trim(),
                distrito: document.getElementById('distrito').value.trim(),
                corregimiento: document.getElementById('corregimiento').value.trim(),
                detalle: document.getElementById('detalle').value.trim()
            });
            const respuesta = await fetch('seguimiento.php', { method: 'POST', body: cuerpo });
            const data = await respuesta.json();
            const mensaje = document.getElementById('mensajeDireccion');
            mensaje.textContent = data.message;
            mensaje.className = `text-sm rounded-lg p-3 ${data.status === 'success' ? 'bg-primary-fixed text-primary' : 'bg-error-container text-error'}`;
            if (data.status === 'success') {
                const listaRespuesta = await fetch('seguimiento.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'listar' })
                });
                const listaData = await listaRespuesta.json();
                if (listaData.status === 'success') {
                    pedidos = listaData.pedidos || [];
                    renderPedidos();
                }
            }
        }

        renderPedidos();
    </script>
</body>

</html>
