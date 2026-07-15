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
$reclamosData = obtenerReclamosProductor($conn, $usuarioActual);
$conn->close();
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DACHI | Reclamaciones</title>
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
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#404942]">Centro de Reclamaciones</p>
                        <h2 class="text-3xl font-bold text-[#004528]">Reclamaciones de <?= htmlspecialchars(($contexto['usuario']['nombre'] ?? 'Productor') . ' ' . ($contexto['usuario']['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <div class="rounded-full border border-[#004528] bg-[#f3f4f5] px-4 py-2 text-sm font-semibold text-[#004528]">
                        <?= htmlspecialchars($contexto['usuario']['correo'] ?? 'correo@dachi.com', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </header>

            <section class="mb-8 grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Promedio General</p>
                        <span class="material-symbols-outlined text-[#004528]">star</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#004528]"><?= number_format($reclamosData['promedioGeneral'], 1, ',', '.') ?> <span class="text-lg text-[#404942]">/ 5</span></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Total Reseñas</p>
                        <span class="material-symbols-outlined text-[#506856]">reviews</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#004528]"><?= (int) $reclamosData['totalCalificaciones'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Reclamos</p>
                        <span class="material-symbols-outlined text-[#ba1a1a]">warning</span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-[#ba1a1a]"><?= (int) $reclamosData['totalReclamos'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-sm text-[#404942]">Satisfacción</p>
                        <span class="material-symbols-outlined text-[#004528]">thumb_up</span>
                    </div>
                    <?php
                    $satisfaccion = $reclamosData['totalCalificaciones'] > 0
                        ? round(($reclamosData['totalCalificaciones'] - $reclamosData['totalReclamos']) / $reclamosData['totalCalificaciones'] * 100)
                        : 0;
                    ?>
                    <p class="mt-2 text-3xl font-bold text-[#004528]"><?= $satisfaccion ?>%</p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-[#004528]">Reclamaciones y Quejas</h3>
                        <span class="bg-[#ffdad6] text-[#93000a] text-xs font-bold px-2 py-1 rounded-full"><?= (int) $reclamosData['totalReclamos'] ?> críticas</span>
                    </div>
                    <?php if (empty($reclamosData['reclamos'])): ?>
                        <div class="text-center py-12">
                            <span class="material-symbols-outlined text-[64px] text-[#cce6d0]">check_circle</span>
                            <p class="mt-4 text-[#404942]">No hay reclamaciones registradas.</p>
                            <p class="text-sm text-[#404942]">¡Tus clientes están satisfechos!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($reclamosData['reclamos'] as $reclamo): ?>
                                <?php
                                $estrellas = (int) $reclamo['calificacion'];
                                $urgencia = $estrellas <= 1 ? 'bg-[#ffdad6] text-[#93000a] border-l-4 border-[#ba1a1a]' : 'bg-[#ffdcc4] text-[#6f3800] border-l-4 border-[#5f2f00]';
                                $urgenciaLabel = $estrellas <= 1 ? 'URGENCIA ALTA' : 'URGENCIA MEDIA';
                                $urgenciaBadge = $estrellas <= 1 ? 'bg-[#ba1a1a] text-white' : 'bg-[#5f2f00] text-white';
                                ?>
                                <div class="rounded-xl p-4 <?= $urgencia ?> transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-semibold text-[#191c1d]"><?= htmlspecialchars($reclamo['consumidor_nombre'] . ' ' . $reclamo['consumidor_apellido'], ENT_QUOTES, 'UTF-8') ?></p>
                                            <p class="text-sm text-[#404942]">Producto: <?= htmlspecialchars($reclamo['producto_nombre'], ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $urgenciaBadge ?>"><?= $urgenciaLabel ?></span>
                                    </div>
                                    <div class="flex items-center gap-1 mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="material-symbols-outlined text-sm <?= $i <= $estrellas ? 'text-[#5f2f00]' : 'text-[#e1e3e4]' ?>" <?= $i <= $estrellas ? 'style="font-variation-settings: \'FILL\' 1;"' : '' ?>>star</span>
                                        <?php endfor; ?>
                                        <span class="text-xs text-[#404942] ml-1"><?= $estrellas ?>/5</span>
                                    </div>
                                    <?php if (!empty($reclamo['comentario'])): ?>
                                        <p class="text-sm text-[#191c1d] bg-white/60 p-3 rounded-lg italic">"<?= htmlspecialchars($reclamo['comentario'], ENT_QUOTES, 'UTF-8') ?>"</p>
                                    <?php else: ?>
                                        <p class="text-sm text-[#404942] italic">Sin comentario adicional.</p>
                                    <?php endif; ?>
                                    <p class="text-xs text-[#404942] mt-2"><?= date('d M Y, H:i', strtotime($reclamo['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                        <h3 class="mb-3 text-xl font-bold text-[#004528]">Reseñas Positivas</h3>
                        <?php if (empty($reclamosData['buenasCalificaciones'])): ?>
                            <p class="text-sm text-[#404942]">No hay reseñas positivas recientes.</p>
                        <?php else: ?>
                            <ul class="space-y-3">
                                <?php foreach ($reclamosData['buenasCalificaciones'] as $resena): ?>
                                    <li class="rounded-lg bg-[#f3f4f5] p-3">
                                        <div class="flex items-center gap-1 mb-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="material-symbols-outlined text-xs <?= $i <= (int) $resena['calificacion'] ? 'text-[#004528]' : 'text-[#e1e3e4]' ?>" <?= $i <= (int) $resena['calificacion'] ? 'style="font-variation-settings: \'FILL\' 1;"' : '' ?>>star</span>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="font-semibold text-[#191c1d] text-sm"><?= htmlspecialchars($resena['consumidor_nombre'] . ' ' . $resena['consumidor_apellido'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-xs text-[#404942]">sobre <?= htmlspecialchars($resena['producto_nombre'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php if (!empty($resena['comentario'])): ?>
                                            <p class="text-sm text-[#404942] mt-1">"<?= htmlspecialchars(mb_substr($resena['comentario'], 0, 100, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($resena['comentario'], 'UTF-8') > 100 ? '...' : '' ?>"</p>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="rounded-2xl border border-[#e1e3e4] bg-[#f3f4f5] p-6">
                        <h3 class="mb-3 text-xl font-bold text-[#004528]">Consejos</h3>
                        <ul class="space-y-2 text-sm text-[#404942]">
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[#004528] text-sm mt-0.5">check_circle</span>
                                Responde a las reclamaciones dentro de 24 horas.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[#004528] text-sm mt-0.5">check_circle</span>
                                Ofrece soluciones claras y justas.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[#004528] text-sm mt-0.5">check_circle</span>
                                Mantén la calma y sé profesional.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[#004528] text-sm mt-0.5">check_circle</span>
                                Usa las reseñas positivas como testimonios.
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
<?php require_once __DIR__ . '/producer_layout_end.php'; ?>
