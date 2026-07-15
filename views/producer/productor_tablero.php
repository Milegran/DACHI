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
$conn->close();
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DACHI | Panel del Productor</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link href="../../css/dachi-brand.css" rel="stylesheet" />
    <link href="../../css/dachi-botanical.css" rel="stylesheet" />
</head>
<body class="dachi-app bg-background text-on-background font-body-md min-h-screen glass-container">
<?php require_once __DIR__ . '/producer_layout_start.php'; ?>
            <header class="mb-8 flex items-center justify-between rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#404942]">Bienvenido</p>
                    <h2 class="text-3xl font-bold text-[#004528]">
                        <?= htmlspecialchars(($contexto['usuario']['nombre'] ?? 'Productor') . ' ' . ($contexto['usuario']['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p class="mt-1 text-[#506856]">Cuenta activa: <?= htmlspecialchars(ucfirst($contexto['rolActual']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-full border border-[#004528] bg-[#f3f4f5] px-4 py-2 text-sm font-semibold text-[#004528]">
                    <?= htmlspecialchars($contexto['usuario']['correo'] ?? 'correo@dachi.com', ENT_QUOTES, 'UTF-8') ?>
                </div>
            </header>

            <section class="mb-8 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Productos</p>
                    <p class="mt-2 text-3xl font-bold text-[#004528]"><?= (int) $contexto['totalProductos'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Stock bajo</p>
                    <p class="mt-2 text-3xl font-bold text-[#b91c1c]"><?= (int) $contexto['stockBajo'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Pedidos pendientes</p>
                    <p class="mt-2 text-3xl font-bold text-[#7c2d12]"><?= (int) $contexto['pedidosPendientes'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Valor inventario</p>
                    <p class="mt-2 text-3xl font-bold text-[#004528]">$<?= number_format($contexto['valorInventario'], 2, ',', '.') ?></p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-[#004528]">Productos recientes</h3>
                        <a href="productor_prod.php" class="text-sm font-semibold text-[#004528]">Ver todos</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="border-b border-[#e1e3e4] bg-[#f3f4f5]">
                                <tr>
                                    <th class="px-3 py-3 text-sm font-semibold uppercase text-[#404942]">Producto</th>
                                    <th class="px-3 py-3 text-sm font-semibold uppercase text-[#404942]">Precio</th>
                                    <th class="px-3 py-3 text-sm font-semibold uppercase text-[#404942]">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($contexto['productos'])): ?>
                                    <tr><td colspan="3" class="px-3 py-6 text-[#404942]">No hay productos registrados para esta cuenta.</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($contexto['productos'], 0, 5) as $producto): ?>
                                        <tr class="border-b border-[#f3f4f5]">
                                            <td class="px-3 py-3">
                                                <div class="font-semibold text-[#191c1d]"><?= htmlspecialchars($producto['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8') ?></div>
                                                <div class="text-sm text-[#404942]"><?= htmlspecialchars($producto['nom_productor'] ?? $contexto['usuario']['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                                            </td>
                                            <td class="px-3 py-3">$<?= number_format((float) ($producto['precio'] ?? 0), 2, ',', '.') ?></td>
                                            <td class="px-3 py-3"><?= (int) ($producto['cantidad'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                        <h3 class="mb-3 text-xl font-bold text-[#004528]">Actividad</h3>
                        <ul class="space-y-3 text-sm text-[#404942]">
                            <li class="rounded-lg bg-[#f3f4f5] p-3">Ventas registradas: $<?= number_format($contexto['ventasTotales'], 2, ',', '.') ?></li>
                            <li class="rounded-lg bg-[#f3f4f5] p-3">Reseñas promedio: <?= number_format($contexto['promedioResenas'], 1, ',', '.') ?> / 5</li>
                            <li class="rounded-lg bg-[#f3f4f5] p-3">Total reseñas: <?= (int) $contexto['totalResenas'] ?></li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                        <h3 class="mb-3 text-xl font-bold text-[#004528]">Pedidos recientes</h3>
                        <?php if (empty($contexto['pedidosRecientes'])): ?>
                            <p class="text-sm text-[#404942]">No hay pedidos recientes para esta cuenta.</p>
                        <?php else: ?>
                            <ul class="space-y-3">
                                <?php foreach ($contexto['pedidosRecientes'] as $pedido): ?>
                                    <li class="rounded-lg border border-[#e1e3e4] p-3">
                                        <p class="font-semibold text-[#191c1d]">#<?= (int) $pedido['id'] ?> · <?= htmlspecialchars($pedido['producto_nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-sm text-[#404942]">Cliente: <?= htmlspecialchars(($pedido['comprador_nombre'] ?? '') . ' ' . ($pedido['comprador_apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
<?php require_once __DIR__ . '/producer_layout_end.php'; ?>