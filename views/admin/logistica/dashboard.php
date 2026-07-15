<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Monitoreo</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Panel de Logística</h2>
        <p class="text-body-sm text-secondary mt-1 max-w-2xl">Visibilidad completa de la operación de entregas en DACHI.</p>
    </div>
    <div class="flex items-center gap-2 flex-none">
        <span class="text-xs text-secondary bg-surface-container-low px-3 py-1.5 rounded-full">Actualizado en tiempo real</span>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow border-l-[3px] border-primary">
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">En Tránsito</p>
        <p class="font-headline-md text-headline-md font-bold text-primary mt-1"><?= number_format((int)($kpis['en_transito'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow border-l-[3px] border-amber-500">
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Pendientes</p>
        <p class="font-headline-md text-headline-md font-bold text-amber-600 mt-1"><?= number_format((int)($kpis['pendiente'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow border-l-[3px] border-green-600">
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Entregados</p>
        <p class="font-headline-md text-headline-md font-bold text-green-700 mt-1"><?= number_format((int)($kpis['entregado'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow border-l-[3px] border-red-500">
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Cancelados</p>
        <p class="font-headline-md text-headline-md font-bold text-red-600 mt-1"><?= number_format((int)($kpis['cancelado'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow border-l-[3px] border-purple-500">
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Tiempo Promedio</p>
        <p class="font-headline-md text-headline-md font-bold text-purple-700 mt-1"><?= (int)($kpis['tiempo_promedio'] ?? 0) ?> <span class="text-body-sm text-secondary font-normal">min</span></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border border-outline-muted">
        <h3 class="font-headline-sm text-headline-sm text-primary mb-4">Pedidos por Estado</h3>
        <div class="relative" style="max-height:260px">
            <canvas id="estadoDonutChart"></canvas>
        </div>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border border-outline-muted">
        <h3 class="font-headline-sm text-headline-sm text-primary mb-4">Rendimiento de Logistas</h3>
        <div class="relative" style="max-height:260px">
            <canvas id="rendimientoBarChart"></canvas>
        </div>
    </div>
</div>

<div class="bg-surface-lowest rounded-[24px] botanical-shadow border border-outline-muted mb-stack-lg overflow-hidden">
    <div class="px-6 py-5 border-b border-outline-muted flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">local_shipping</span>
            <h3 class="font-headline-sm text-headline-sm text-primary">Entregas Activas (En Tránsito)</h3>
        </div>
        <span class="bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full"><?= count($activas) ?> activas</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Pedido</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Consumidor</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Zona</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Logista</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Tiempo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-muted">
                <?php if (count($activas) === 0): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-secondary font-body-md">
                            <span class="material-symbols-outlined text-[48px] opacity-30 block mb-2">local_shipping</span>
                            No hay entregas en tránsito
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($activas as $a): ?>
                        <tr class="table-row-hover">
                            <td class="px-6 py-4 font-semibold text-on-surface">#<?= (int)$a['pedido_id'] ?></td>
                            <td class="px-6 py-4 text-on-surface"><?= htmlspecialchars($a['consumidor'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-4 text-secondary"><?= htmlspecialchars($a['zona'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-4">
                                <?php if ($a['logista']): ?>
                                    <span class="text-on-surface"><?= htmlspecialchars($a['logista'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php else: ?>
                                    <span class="text-error text-xs font-semibold">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($a['minutos_transcurridos'] !== null): ?>
                                    <span class="font-mono text-sm <?= ((int)$a['minutos_transcurridos'] > 60) ? 'text-error' : 'text-secondary' ?>">
                                        <?= (int)$a['minutos_transcurridos'] ?> min
                                    </span>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-surface-lowest rounded-[24px] botanical-shadow border border-outline-muted mb-stack-lg overflow-hidden">
    <div class="px-6 py-5 border-b border-outline-muted flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">social_leaderboard</span>
            <h3 class="font-headline-sm text-headline-sm text-primary">Rendimiento de Logistas</h3>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Logista</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">Entregas</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">% Éxito</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">Calificación</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">Tiempo Prom.</th>
                    <th class="px-6 py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">Incidencias</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-muted">
                <?php if (count($rendimiento) === 0): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-secondary font-body-md">
                            <span class="material-symbols-outlined text-[48px] opacity-30 block mb-2">social_leaderboard</span>
                            No hay datos de logistas
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rendimiento as $r): ?>
                        <tr class="table-row-hover">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-on-surface"><?= htmlspecialchars(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-on-surface"><?= (int)$r['total_entregas'] ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-semibold <?= ((float)$r['porcentaje_exito'] >= 90) ? 'text-green-700' : (((float)$r['porcentaje_exito'] >= 70) ? 'text-amber-600' : 'text-red-600') ?>">
                                    <?= (float)$r['porcentaje_exito'] ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php $cal = (float)($r['calificacion'] ?? 0); ?>
                                <span class="font-semibold <?= $cal >= 4 ? 'text-green-700' : ($cal >= 3 ? 'text-amber-600' : 'text-secondary') ?>">
                                    <?= $cal > 0 ? number_format($cal, 1) : '—' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-secondary font-mono text-sm"><?= $r['tiempo_promedio'] ? (int)$r['tiempo_promedio'] . ' min' : '—' ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="<?= ((int)($r['incidencias'] ?? 0) > 3) ? 'text-error font-semibold' : 'text-secondary' ?>">
                                    <?= (int)($r['incidencias'] ?? 0) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border border-outline-muted">
        <h3 class="font-headline-sm text-headline-sm text-primary mb-4">Distribución por Zonas</h3>
        <div class="space-y-3">
            <?php if (count($provincias) === 0): ?>
                <p class="text-secondary text-sm text-center py-8">Sin datos de zonas</p>
            <?php else: ?>
                <?php $maxProv = max(array_column($provincias, 'total_entregas')); ?>
                <?php foreach ($provincias as $p): ?>
                    <?php $pct = $maxProv > 0 ? ((int)$p['total_entregas'] / $maxProv) * 100 : 0; ?>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-on-surface"><?= htmlspecialchars($p['provincia'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-sm font-semibold text-primary"><?= (int)$p['total_entregas'] ?></span>
                        </div>
                        <div class="w-full h-2.5 bg-surface-container-highest rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all" style="width:<?= $pct ?>%"></div>
                        </div>
                        <div class="flex gap-3 mt-1 text-xs text-secondary">
                            <span>✔ <?= (int)$p['entregadas'] ?> entregadas</span>
                            <span>✘ <?= (int)$p['canceladas'] ?> canceladas</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-surface-lowest rounded-[24px] botanical-shadow border border-outline-muted overflow-hidden">
        <div class="px-6 py-5 border-b border-outline-muted flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-error">warning</span>
                <h3 class="font-headline-sm text-headline-sm text-primary">Incidencias</h3>
            </div>
            <span class="bg-error/20 text-error text-xs font-bold px-3 py-1 rounded-full"><?= count($incidencias) ?> registros</span>
        </div>
        <div class="overflow-x-auto" style="max-height:360px;overflow-y:auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low/50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-xs">Fecha</th>
                        <th class="px-4 py-3 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-xs">Pedido</th>
                        <th class="px-4 py-3 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-xs">Logista</th>
                        <th class="px-4 py-3 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-xs">Motivo</th>
                        <th class="px-4 py-3 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-xs">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php if (count($incidencias) === 0): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-secondary text-sm">
                                <span class="material-symbols-outlined text-[36px] opacity-30 block mb-1">check_circle</span>
                                Sin incidencias registradas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($incidencias as $inc): ?>
                            <tr class="table-row-hover">
                                <td class="px-4 py-3 text-xs text-secondary"><?= htmlspecialchars($inc['fecha'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 text-sm font-semibold text-on-surface">#<?= (int)$inc['pedido_id'] ?></td>
                                <td class="px-4 py-3 text-sm text-secondary"><?= htmlspecialchars($inc['logista'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 text-sm text-on-surface"><?= htmlspecialchars(mb_substr($inc['motivo'] ?? '', 0, 60), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3">
                                    <span class="estado-badge text-xs <?= $inc['estado'] === 'entregado' ? 'estado-aprobado' : ($inc['estado'] === 'cancelado' ? 'estado-rechazado' : 'estado-pendiente') ?>">
                                        <?= htmlspecialchars($inc['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var donutCtx = document.getElementById('estadoDonutChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Entregado', 'En tránsito', 'Pendiente', 'En preparación', 'Cancelado'],
                datasets: [{
                    data: [
                        <?= (int)($kpis['entregado'] ?? 0) ?>,
                        <?= (int)($kpis['en_transito'] ?? 0) ?>,
                        <?= (int)($kpis['pendiente'] ?? 0) ?>,
                        <?= (int)($kpis['en_preparacion'] ?? 0) ?>,
                        <?= (int)($kpis['cancelado'] ?? 0) ?>
                    ],
                    backgroundColor: ['#2e7d32', '#7b1fa2', '#e6a700', '#0288d1', '#dc2626'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, font: { family: 'Plus Jakarta Sans' } } }
                },
                cutout: '65%'
            }
        });
    }

    var barCtx = document.getElementById('rendimientoBarChart');
    if (barCtx) {
        var labels = <?= json_encode(array_map(function($r) { return ($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? ''); }, $rendimiento), JSON_UNESCAPED_UNICODE) ?>;
        var exitos = <?= json_encode(array_map(function($r) { return (int)($r['entregas_exitosas'] ?? 0); }, $rendimiento)) ?>;
        var cancelados = <?= json_encode(array_map(function($r) { return (int)($r['entregas_canceladas'] ?? 0); }, $rendimiento)) ?>;

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Exitosas', data: exitos, backgroundColor: '#2e7d32', borderRadius: 4 },
                    { label: 'Canceladas', data: cancelados, backgroundColor: '#dc2626', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, grid: { display: false } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, font: { family: 'Plus Jakarta Sans' } } }
                }
            }
        });
    }
});
</script>
