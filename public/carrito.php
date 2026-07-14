<?php
session_start();

require_once __DIR__ . "/../conexion.php";
require_once __DIR__ . "/../app/Controllers/CarritoController.php";

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$rolActual = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
$esConsumidor = $rolActual === 'consumidor';
$controller = new CarritoController(new SistemaDachiFacade($conn));
$carritoSesion = is_array($_SESSION['carrito'] ?? null) ? $_SESSION['carrito'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: application/json');
    $resultado = $controller->handle($_POST, $usuarioActual, $carritoSesion, $esConsumidor);
    $respuesta = $resultado['response'];
    $_SESSION['carrito'] = $resultado['cart'];

    echo json_encode($respuesta);
    $conn->close();
    exit;
}

$estadoInicial = $controller->initialState($carritoSesion);
$resumenInicial = $estadoInicial['summary'];
$_SESSION['carrito'] = $resumenInicial['carrito'];
$metodosIniciales = $estadoInicial['paymentMethods'];
$conn->close();

$ajustes = isset($_COOKIE['dachi_ajustes']) ? json_decode($_COOKIE['dachi_ajustes'], true) : [];
$fontSize = $ajustes['fontSize'] ?? 'mediano';
$darkMode = !empty($ajustes['darkMode']);
$fontSizesPx = ['pequeno' => '14px', 'mediano' => '16px', 'grande' => '18px'];
$fontSizePx = $fontSizesPx[$fontSize] ?? $fontSizesPx['mediano'];
?>
<!DOCTYPE html>
<html class="light<?= $darkMode ? ' dark' : '' ?>" lang="es" style="font-size:<?= $fontSizePx ?>">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>DACHI | Carrito</title>
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
                        'surface-container': '#ecefea', 'surface-container-highest': '#e0e3df',
                        'on-surface': '#191c1a', 'on-surface-variant': '#414942', outline: '#717971',
                        'outline-variant': '#c1c9bf', error: '#ba1a1a', 'error-container': '#ffdad6'
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
        html.dark select { color-scheme: dark; }
    </style>
    <link href="../css/dachi-botanical.css" rel="stylesheet" />
</head>

<body class="dachi-app bg-surface text-on-surface min-h-screen flex flex-col font-sans">
    <?php $paginaActiva = 'carrito'; require __DIR__ . '/partials/navigation.php'; ?>

    <main class="w-full max-w-content mx-auto px-4 md:px-12 py-10 flex-1">
        <div class="mb-10">
            <h1 class="font-serif text-[32px] leading-10 font-bold text-primary dark:text-primary-fixed">Tu carrito</h1>
            <p class="mt-2 text-on-surface-variant dark-muted">Revisa cantidades y confirma el metodo de pago antes de crear el pedido.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-10 lg:gap-14 items-start">
            <section aria-labelledby="productosCarritoTitulo">
                <div class="flex items-center justify-between pb-4 border-b border-outline-variant/70 dark-border">
                    <h2 class="font-serif text-[24px] leading-8 font-semibold dark-text" id="productosCarritoTitulo">Productos</h2>
                    <button class="hidden text-sm font-semibold text-error hover:underline" id="vaciarBtn" onclick="vaciarCarrito()" type="button">Vaciar carrito</button>
                </div>
                <div id="listaCarrito"></div>
                <div class="hidden py-20 text-center" id="carritoVacio">
                    <span class="material-symbols-outlined text-5xl text-outline">shopping_cart</span>
                    <h3 class="font-serif text-2xl mt-4 dark-text">Tu carrito esta vacio</h3>
                    <p class="text-on-surface-variant dark-muted mt-2">Agrega productos disponibles desde el inicio.</p>
                    <a class="inline-flex items-center gap-2 mt-6 px-5 py-3 bg-primary text-white rounded-lg font-semibold" href="../panel.php#productos">
                        <span class="material-symbols-outlined text-xl">grid_view</span>
                        Explorar productos
                    </a>
                </div>
            </section>

            <aside class="border border-outline-variant/70 rounded-lg p-6 bg-surface-container-lowest dark-surface dark-border lg:sticky lg:top-24">
                <h2 class="font-serif text-[24px] leading-8 font-semibold dark-text">Resumen</h2>
                <div class="mt-6 space-y-3 text-on-surface-variant dark-muted">
                    <div class="flex justify-between gap-4"><span>Unidades</span><span class="font-semibold dark-text" id="resumenUnidades">0</span></div>
                    <div class="flex justify-between gap-4"><span>Envio</span><span class="font-semibold text-primary dark:text-primary-fixed">Por coordinar</span></div>
                </div>
                <div class="mt-6 pt-5 border-t border-outline-variant/70 dark-border flex justify-between items-end gap-4">
                    <span class="font-semibold">Total</span>
                    <span class="font-serif text-3xl font-bold text-primary dark:text-primary-fixed" id="resumenTotal">$0.00</span>
                </div>

                <label class="block mt-7 text-sm font-semibold dark-text" for="metodoPago">Metodo de pago</label>
                <select class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="metodoPago" onchange="actualizarFormaPago()">
                    <option value="">Selecciona una opcion</option>
                </select>

                <div class="hidden mt-6 pt-6 border-t border-outline-variant/70 dark-border" id="datosTarjeta">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="material-symbols-outlined text-primary dark:text-primary-fixed">credit_card</span>
                        <div>
                            <h3 class="font-semibold dark-text">Datos de la tarjeta</h3>
                            <p class="text-xs text-on-surface-variant dark-muted">Pago de demostracion. No uses una tarjeta real.</p>
                        </div>
                    </div>

                    <label class="block text-sm font-semibold dark-text" for="titularTarjeta">Titular de la tarjeta</label>
                    <input autocomplete="off" class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary uppercase" id="titularTarjeta" maxlength="80" placeholder="NOMBRE EN LA TARJETA" type="text" />

                    <label class="block mt-4 text-sm font-semibold dark-text" for="numeroTarjeta">Numero de tarjeta</label>
                    <div class="relative mt-2">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant dark-muted">credit_card</span>
                        <input autocomplete="off" class="w-full pl-11 pr-4 rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="numeroTarjeta" inputmode="numeric" maxlength="23" oninput="formatearNumeroTarjeta(this)" placeholder="0000 0000 0000 0000" type="text" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-semibold dark-text" for="vencimientoTarjeta">Vencimiento</label>
                            <input autocomplete="off" class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="vencimientoTarjeta" inputmode="numeric" maxlength="5" oninput="formatearVencimiento(this)" placeholder="MM/AA" type="text" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold dark-text" for="cvvTarjeta">CVV</label>
                            <input autocomplete="off" class="mt-2 w-full rounded-lg border-outline-variant bg-surface-container-lowest dark-surface dark-border focus:border-primary focus:ring-primary" id="cvvTarjeta" inputmode="numeric" maxlength="4" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 4)" placeholder="123" type="password" />
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4 text-xs text-on-surface-variant dark-muted">
                        <span class="material-symbols-outlined text-base">shield_lock</span>
                        <p>Los datos se validan para esta operacion y no se almacenan en DACHI.</p>
                    </div>
                </div>

                <p class="hidden mt-4 text-sm text-error" id="errorStock">Actualiza las cantidades: uno de los productos supera el stock disponible.</p>
                <p class="hidden mt-4 text-sm rounded-lg p-3" id="mensajeCompra"></p>

                <button class="w-full h-12 mt-6 bg-primary text-white rounded-lg font-bold inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" id="confirmarBtn" onclick="confirmarCompra()" type="button">
                    <span class="material-symbols-outlined">lock</span>
                    Confirmar pedido
                </button>
                <?php if (!$esConsumidor): ?>
                    <p class="mt-4 text-sm text-error">Solo una cuenta consumidora puede confirmar compras.</p>
                <?php endif; ?>
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
        const ESTADO_INICIAL = <?= json_encode($resumenInicial, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const METODOS = <?= json_encode($metodosIniciales['metodos'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const ES_CONSUMIDOR = <?= $esConsumidor ? 'true' : 'false' ?>;
        let estadoCarrito = ESTADO_INICIAL;

        function escapar(texto) {
            return String(texto ?? '').replace(/[&<>'"]/g, caracter => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
            })[caracter]);
        }

        function dinero(valor) {
            return `$${Number(valor || 0).toFixed(2)}`;
        }

        async function enviar(accion, datos = {}) {
            const cuerpo = new URLSearchParams({ accion, ...datos });
            const respuesta = await fetch('carrito.php', { method: 'POST', body: cuerpo });
            return respuesta.json();
        }

        function cargarMetodos() {
            const select = document.getElementById('metodoPago');
            METODOS.forEach(metodo => {
                const opcion = document.createElement('option');
                opcion.value = metodo.id;
                opcion.textContent = metodo.nombre;
                select.appendChild(opcion);
            });
        }

        function obtenerMetodoSeleccionado() {
            const idMetodo = Number(document.getElementById('metodoPago').value);
            return METODOS.find(metodo => Number(metodo.id) === idMetodo) || null;
        }

        function actualizarFormaPago() {
            const metodo = obtenerMetodoSeleccionado();
            const requiereTarjeta = Boolean(metodo?.requiere_tarjeta);
            document.getElementById('datosTarjeta').classList.toggle('hidden', !requiereTarjeta);

            ['titularTarjeta', 'numeroTarjeta', 'vencimientoTarjeta', 'cvvTarjeta'].forEach(id => {
                document.getElementById(id).required = requiereTarjeta;
            });
        }

        function formatearNumeroTarjeta(input) {
            const digitos = input.value.replace(/\D/g, '').slice(0, 19);
            input.value = digitos.replace(/(.{4})/g, '$1 ').trim();
        }

        function formatearVencimiento(input) {
            const digitos = input.value.replace(/\D/g, '').slice(0, 4);
            input.value = digitos.length > 2 ? `${digitos.slice(0, 2)}/${digitos.slice(2)}` : digitos;
        }

        function renderCarrito() {
            const lista = document.getElementById('listaCarrito');
            const vacio = document.getElementById('carritoVacio');
            const vaciar = document.getElementById('vaciarBtn');
            const items = estadoCarrito.items || [];

            const contadorNav = document.getElementById('contadorNav');
            if (contadorNav) {
                contadorNav.textContent = estadoCarrito.total_unidades || 0;
                contadorNav.classList.toggle('hidden', !estadoCarrito.total_unidades);
            }
            document.getElementById('resumenUnidades').textContent = estadoCarrito.total_unidades || 0;
            document.getElementById('resumenTotal').textContent = dinero(estadoCarrito.total);
            document.getElementById('errorStock').classList.toggle('hidden', estadoCarrito.puede_comprar || items.length === 0);
            document.getElementById('confirmarBtn').disabled = !ES_CONSUMIDOR || !estadoCarrito.puede_comprar;

            if (items.length === 0) {
                lista.innerHTML = '';
                vacio.classList.remove('hidden');
                vaciar.classList.add('hidden');
                return;
            }

            vacio.classList.add('hidden');
            vaciar.classList.remove('hidden');
            lista.innerHTML = items.map(item => `
                <article class="py-6 border-b border-outline-variant/70 dark-border grid grid-cols-[72px_minmax(0,1fr)] sm:grid-cols-[80px_minmax(0,1fr)_auto] gap-4 items-center">
                    <div class="w-[72px] h-[72px] sm:w-20 sm:h-20 bg-primary-fixed/40 dark-surface-low rounded-lg flex items-center justify-center font-serif text-2xl font-bold text-primary dark:text-primary-fixed">
                        ${escapar(item.nombre).charAt(0).toUpperCase()}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase font-bold text-on-surface-variant dark-muted truncate">${escapar(item.productor)}</p>
                        <h3 class="font-semibold text-lg dark-text truncate">${escapar(item.nombre)}</h3>
                        <p class="text-sm text-on-surface-variant dark-muted">${dinero(item.precio)} · Stock ${item.stock}</p>
                        ${item.stock_suficiente ? '' : '<p class="text-sm text-error mt-1">Cantidad superior al stock</p>'}
                    </div>
                    <div class="col-span-2 sm:col-span-1 flex sm:flex-col items-center sm:items-end justify-between gap-3">
                        <strong class="text-lg dark-text">${dinero(item.subtotal)}</strong>
                        <div class="flex items-center gap-1">
                            <button class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-outline-variant dark-border" onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})" title="Reducir cantidad" type="button"><span class="material-symbols-outlined text-lg">remove</span></button>
                            <span class="w-10 text-center font-semibold">${item.cantidad}</span>
                            <button class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-outline-variant dark-border" onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})" title="Aumentar cantidad" type="button"><span class="material-symbols-outlined text-lg">add</span></button>
                            <button class="w-9 h-9 ml-2 inline-flex items-center justify-center rounded-lg text-error hover:bg-error-container" onclick="eliminarProducto(${item.id})" title="Eliminar producto" type="button"><span class="material-symbols-outlined text-lg">delete</span></button>
                        </div>
                    </div>
                </article>
            `).join('');
        }

        async function recargarResumen() {
            estadoCarrito = await enviar('resumen');
            renderCarrito();
        }

        async function cambiarCantidad(idProducto, cantidad) {
            const respuesta = cantidad <= 0
                ? await enviar('eliminar', { id_producto: idProducto })
                : await enviar('actualizar', { id_producto: idProducto, cantidad });
            if (respuesta.status === 'success') {
                estadoCarrito = respuesta;
                renderCarrito();
            } else {
                mostrarMensaje(respuesta.message, true);
                await recargarResumen();
            }
        }

        async function eliminarProducto(idProducto) {
            const respuesta = await enviar('eliminar', { id_producto: idProducto });
            if (respuesta.status === 'success') {
                estadoCarrito = respuesta;
                renderCarrito();
            }
        }

        async function vaciarCarrito() {
            const respuesta = await enviar('vaciar');
            if (respuesta.status === 'success') {
                estadoCarrito = respuesta;
                renderCarrito();
            }
        }

        async function confirmarCompra() {
            const idMetodoPago = document.getElementById('metodoPago').value;
            if (!idMetodoPago) {
                mostrarMensaje('Selecciona un metodo de pago.', true);
                return;
            }

            const metodo = obtenerMetodoSeleccionado();
            const datosPago = { id_metodo_pago: idMetodoPago };
            if (metodo?.requiere_tarjeta) {
                datosPago.titular_tarjeta = document.getElementById('titularTarjeta').value.trim();
                datosPago.numero_tarjeta = document.getElementById('numeroTarjeta').value;
                datosPago.vencimiento_tarjeta = document.getElementById('vencimientoTarjeta').value;
                datosPago.cvv_tarjeta = document.getElementById('cvvTarjeta').value;

                if (!datosPago.titular_tarjeta || !datosPago.numero_tarjeta || !datosPago.vencimiento_tarjeta || !datosPago.cvv_tarjeta) {
                    mostrarMensaje('Completa todos los datos de la tarjeta.', true);
                    return;
                }
            }

            const boton = document.getElementById('confirmarBtn');
            boton.disabled = true;
            const respuesta = await enviar('confirmar', datosPago);

            if (respuesta.status === 'success') {
                estadoCarrito = { status: 'success', carrito: {}, items: [], total: 0, total_unidades: 0, puede_comprar: false };
                renderCarrito();
                mostrarMensaje(`Pedido #${respuesta.pedido.id_pedido} registrado por ${dinero(respuesta.pedido.total)}.`, false);
                return;
            }

            mostrarMensaje(respuesta.message, true);
            await recargarResumen();
        }

        function mostrarMensaje(mensaje, esError) {
            const elemento = document.getElementById('mensajeCompra');
            elemento.textContent = mensaje;
            elemento.className = `mt-4 text-sm rounded-lg p-3 ${esError ? 'bg-error-container text-error' : 'bg-primary-fixed text-primary'}`;
        }

        cargarMetodos();
        renderCarrito();
    </script>
</body>

</html>
