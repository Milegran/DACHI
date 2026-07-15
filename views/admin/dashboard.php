<div class="space-y-stack-lg">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border-l-4 border-primary">
            <div class="w-12 h-12 rounded-2xl bg-success-badge-bg flex items-center justify-center text-primary mb-6">
                <span class="material-symbols-outlined font-bold">inventory_2</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Productos</p>
            <h2 class="font-headline-md text-headline-md font-bold text-primary mt-1"><?= (int)($stats['total_productos'] ?? 0) ?></h2>
        </div>
        <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border-l-4 border-outline-variant">
            <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-secondary mb-6">
                <span class="material-symbols-outlined font-bold">group</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Usuarios</p>
            <h2 class="font-headline-md text-headline-md font-bold text-primary mt-1"><?= (int)($stats['total_usuarios'] ?? 0) ?></h2>
        </div>
        <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border-l-4 border-outline-variant">
            <div class="w-12 h-12 rounded-2xl bg-outline-muted flex items-center justify-center text-muted mb-6">
                <span class="material-symbols-outlined font-bold">local_shipping</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Pedidos Totales</p>
            <h2 class="font-headline-md text-headline-md font-bold text-primary mt-1"><?= (int)($stats['total_pedidos'] ?? 0) ?></h2>
        </div>
        <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow border-l-4 border-primary">
            <div class="w-12 h-12 rounded-2xl border border-primary/20 flex items-center justify-center text-primary mb-6">
                <span class="material-symbols-outlined font-bold">payments</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Ingresos</p>
            <h2 class="font-headline-md text-headline-md font-bold text-primary mt-1">$<?= number_format($stats['total_ingresos'] ?? 0, 2) ?></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
        <div class="lg:col-span-2 bg-surface-lowest rounded-[24px] botanical-shadow overflow-hidden flex flex-col border border-outline-muted">
            <div class="px-gutter py-6 flex justify-between items-center">
                <h3 class="font-headline-sm text-headline-sm text-primary">Pedidos Recientes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container-low/50">
                        <tr>
                            <th class="px-gutter py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Pedido</th>
                            <th class="px-gutter py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Cliente</th>
                            <th class="px-gutter py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider">Estado</th>
                            <th class="px-gutter py-4 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-muted">
                        <?php if (count($stats['pedidos_recientes']) === 0): ?>
                            <tr>
                                <td colspan="4" class="px-gutter py-12 text-center text-secondary font-body-md">
                                    <span class="material-symbols-outlined text-[48px] opacity-30 block mb-2">receipt_long</span>
                                    No hay pedidos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $ESTADOS = [0 => 'pendiente', 1 => 'en_camino', 2 => 'entregado']; ?>
                            <?php $ESTADOS_LABEL = [0 => 'Pendiente', 1 => 'En camino', 2 => 'Entregado']; ?>
                            <?php foreach ($stats['pedidos_recientes'] as $ped): ?>
                                <tr class="hover:bg-surface-low/50 transition-colors">
                                    <td class="px-gutter py-4 font-label-bold text-label-bold text-on-surface">#<?= (int)$ped['id'] ?></td>
                                    <td class="px-gutter py-4">
                                        <p class="font-body-md text-body-md text-on-surface"><?= htmlspecialchars(($ped['nombre'] ?? '') . ' ' . ($ped['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="font-label-sm text-label-sm text-secondary"><?= htmlspecialchars($ped['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                    </td>
                                    <td class="px-gutter py-4">
                                        <?php $estadoN = (int)($ped['estado'] ?? 0); ?>
                                        <span class="estado-badge <?= $estadoN === 2 ? 'estado-aprobado' : ($estadoN === 1 ? 'estado-pendiente' : 'estado-pendiente') ?>">
                                            <?= $ESTADOS_LABEL[$estadoN] ?>
                                        </span>
                                    </td>
                                    <td class="px-gutter py-4 text-right font-bold text-on-surface">$<?= number_format((float)($ped['total_compra'] ?? 0), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-primary text-white rounded-[24px] p-gutter flex flex-col botanical-shadow border border-primary relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
                <span class="material-symbols-outlined text-[120px]">monitoring</span>
            </div>
            <div class="relative z-10">
                <h3 class="font-headline-sm text-headline-sm text-white mb-1">Calidad y Reputación</h3>
                <p class="font-body-sm text-body-sm text-primary-fixed-dim/80">Basado en calificaciones de consumidores</p>
            </div>
            <div class="mt-8 space-y-6 relative z-10">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-label-bold text-label-bold text-white">Calificación promedio</span>
                        <span class="font-bold text-primary-fixed" id="avgRatingValue"><?= number_format($stats['calificacion_promedio'] ?? 0, 1) ?></span>
                    </div>
                    <div class="w-full h-2.5 bg-black/30 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-fixed transition-all duration-700" style="width:<?= min(100, ($stats['calificacion_promedio'] ?? 0) * 20) ?>%"></div>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white/10 p-4 rounded-2xl border border-white/20">
                    <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">forum</span>
                    </div>
                    <div class="text-[13px]">
                        <p class="font-bold text-white"><?= (int)($stats['total_comentarios'] ?? 0) ?> comentarios</p>
                        <p class="text-primary-fixed-dim/70">Registrados en el catálogo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
