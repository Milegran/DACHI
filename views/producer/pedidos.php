<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/productor_panel_data.php';

if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$rolSesion = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
if (!in_array($rolSesion, ['productor', 'administrador', 'admin'], true)) {
    header('Location: ../../panel.php');
    exit;
}

$contexto = obtenerContextoProductor($conn, $usuarioActual);
$pedidosData = obtenerPedidosProductor($conn, $usuarioActual);
$conn->close();

$estadosLabel = [
    0 => 'Nuevo',
    1 => 'En Preparación',
    2 => 'Enviado',
    3 => 'Completado',
    4 => 'Cancelado',
];

$estadosClase = [
    0 => 'bg-primary-fixed text-on-primary-fixed',
    1 => 'bg-secondary-container text-on-secondary-container',
    2 => 'bg-tertiary-fixed text-on-tertiary-fixed',
    3 => 'bg-green-100 text-green-800 border border-green-200',
    4 => 'bg-error-container text-error',
];

$totalPedidos = count($pedidosData['pedidos']);
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DACHI | Pedidos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="../../css/dachi-brand.css" rel="stylesheet" />
    <link href="../../css/dachi-botanical.css" rel="stylesheet" />
</head>
<body class="dachi-app bg-background text-on-background font-body-md min-h-screen glass-container">
<?php require_once __DIR__ . '/producer_layout_start.php'; ?>
            <header class="mb-8 rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#404942]">Gestión de Pedidos</p>
                        <h2 class="text-3xl font-bold text-[#004528]">Pedidos de <?= htmlspecialchars(($contexto['usuario']['nombre'] ?? 'Productor') . ' ' . ($contexto['usuario']['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <div class="rounded-full border border-[#004528] bg-[#f3f4f5] px-4 py-2 text-sm font-semibold text-[#004528]">
                        <?= htmlspecialchars($contexto['usuario']['correo'] ?? 'correo@dachi.com', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </header>

            <section class="mb-8 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Pedidos Hoy</p>
                        <span class="material-symbols-outlined text-[#004528]">shopping_basket</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#004528]"><?= (int) $pedidosData['pedidosHoy'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Pendientes</p>
                        <span class="material-symbols-outlined text-[#5f2f00]">pending_actions</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#7c2d12]"><?= (int) $pedidosData['pendientes'] ?></p>
                    <p class="text-xs text-[#404942] mt-1">Requieren atención</p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Ingresos Mes</p>
                        <span class="material-symbols-outlined text-[#004528]">payments</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#004528]">$<?= number_format($pedidosData['ingresosMes'], 2, ',', '.') ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Incidencias</p>
                        <span class="material-symbols-outlined text-[#ba1a1a]">warning</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#ba1a1a]"><?= (int) $pedidosData['incidencias'] ?></p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-[#e1e3e4] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-[#e1e3e4] bg-[#f3f4f5]">
                            <tr>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">ID Pedido</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Fecha</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Cliente</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Productos</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942] text-right">Total</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pedidosData['pedidos'])): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-[#404942]">No hay pedidos registrados para esta cuenta.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pedidosData['pedidos'] as $pedido): ?>
                                    <?php
                                    $estado = $pedido['estado'];
                                    $label = $estadosLabel[$estado] ?? 'Desconocido';
                                    $clase = $estadosClase[$estado] ?? 'bg-gray-100 text-gray-800';
                                    $iniciales = strtoupper(substr($pedido['comprador_nombre'] ?? '', 0, 1) . substr($pedido['comprador_apellido'] ?? '', 0, 1));
                                    $listaProductos = [];
                                    foreach ($pedido['productos'] as $prod) {
                                        $listaProductos[] = htmlspecialchars($prod['producto_nombre'], ENT_QUOTES, 'UTF-8') . ' (' . $prod['cantidad'] . ')';
                                    }
                                    $productosTexto = implode(', ', $listaProductos);
                                    if (strlen($productosTexto) > 80) {
                                        $productosTexto = mb_substr($productosTexto, 0, 80, 'UTF-8') . '...';
                                    }
                                    ?>
                                    <tr class="border-b border-[#f3f4f5]">
                                        <td class="px-4 py-4 font-semibold text-[#004528]">#<?= (int) $pedido['id'] ?></td>
                                        <td class="px-4 py-4 text-sm text-[#191c1d]"><?= htmlspecialchars(date('d M Y, H:i', strtotime($pedido['fecha'])), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-[#cce6d0] flex items-center justify-center text-[#506856] font-bold text-xs"><?= $iniciales ?></div>
                                                <div>
                                                    <p class="font-semibold text-[#191c1d]"><?= htmlspecialchars(($pedido['comprador_nombre'] ?? '') . ' ' . ($pedido['comprador_apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-[#191c1d]"><?= htmlspecialchars($productosTexto, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="px-4 py-4 text-right font-semibold text-[#191c1d]">$<?= number_format($pedido['total_compra'], 2, ',', '.') ?></td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $clase ?>">
                                                <?= $label ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-[#f3f4f5] px-4 py-3 border-t border-[#e1e3e4] flex items-center justify-between">
                    <p class="text-sm text-[#404942]">Mostrando <?= $totalPedidos ?> pedido<?= $totalPedidos !== 1 ? 's' : '' ?></p>
                </div>
            </section>
<?php require_once __DIR__ . '/producer_layout_end.php'; ?>
