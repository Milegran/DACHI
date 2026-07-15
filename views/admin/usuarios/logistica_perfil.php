<div class="mb-6">
    <a href="admin.php?accion=listar_logisticos" class="inline-flex items-center gap-1.5 text-sm font-semibold text-muted hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Volver al listado
    </a>
</div>

<div class="bg-primary text-white rounded-xl p-8 mb-gutter relative overflow-hidden flex flex-col md:flex-row items-center md:items-start gap-6 botanical-shadow">
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary-container opacity-50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
    <div class="w-24 h-24 rounded-full border-2 border-primary-fixed flex items-center justify-center bg-transparent z-10 shrink-0">
        <span class="font-headline-md text-headline-md text-white"><?= mb_strtoupper(mb_substr($logistico['nombre'] ?? '?', 0, 1)) . mb_strtoupper(mb_substr($logistico['apellido'] ?? '', 0, 1)) ?></span>
    </div>
    <div class="flex-1 z-10 text-center md:text-left">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <h1 class="font-headline-lg text-headline-lg text-white"><?= htmlspecialchars(($logistico['nombre'] ?? '') . ' ' . ($logistico['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
            <span class="bg-success-badge-bg text-success-badge-text font-pill-text text-pill-text px-3 py-1 rounded-full uppercase tracking-widest self-center md:self-auto">
                <?= ($logistico['estado'] ?? 'activo') === 'activo' ? 'ACTIVO' : 'INACTIVO' ?>
            </span>
        </div>
        <div class="flex flex-col md:flex-row gap-4 md:gap-8 text-primary-fixed font-body-sm text-body-sm mt-4">
            <div class="flex items-center justify-center md:justify-start gap-2">
                <span class="material-symbols-outlined text-sm">mail</span>
                <?= htmlspecialchars($logistico['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php if (!empty($logistico['telefono'])): ?>
            <div class="flex items-center justify-center md:justify-start gap-2">
                <span class="material-symbols-outlined text-sm">phone</span>
                <?= htmlspecialchars($logistico['telefono'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-4 text-primary-fixed-dim font-label-sm text-label-sm text-right">
            Miembro desde <?= $logistico['fecha_registro'] ? date('M Y', strtotime($logistico['fecha_registro'])) : '—' ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter mb-stack-lg">
    <div class="lg:col-span-4 flex flex-col gap-gutter">
        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow border-l-4 border-primary">
            <div class="flex items-center gap-2 mb-4 text-secondary">
                <span class="material-symbols-outlined text-[18px]">badge</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">DATOS GENERALES</h3>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="font-label-sm text-label-sm text-secondary mb-1">Nombre Completo</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars(($logistico['nombre'] ?? '') . ' ' . ($logistico['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-secondary mb-1">Correo</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars($logistico['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-secondary mb-1">Teléfono</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars($logistico['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow border-l-4 border-secondary">
            <div class="flex items-center gap-2 mb-4 text-secondary">
                <span class="material-symbols-outlined text-[18px]">info</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">INFORMACIÓN ADICIONAL</h3>
            </div>
            <p class="font-body-md text-body-md text-secondary">Los datos de cobertura, tipo de vehículo y disponibilidad se configuran desde el perfil del transportista.</p>
        </div>
    </div>

    <div class="lg:col-span-8 flex flex-col gap-gutter">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow border-l-4 border-primary flex flex-col justify-between">
                <h4 class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-2">ENTREGAS TOTALES</h4>
                <div class="text-headline-lg font-headline-lg text-primary mb-2"><?= (int)($logistico['total_entregas'] ?? 0) ?></div>
                <span class="bg-success-badge-bg text-success-badge-text font-pill-text text-pill-text px-2 py-1 rounded-full w-fit">Registradas</span>
            </div>
            <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow border-l-4 border-outline-variant flex flex-col justify-between">
                <h4 class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-2">% ÉXITO</h4>
                <div class="text-headline-lg font-headline-lg text-primary mb-2"><?= number_format($logistico['porcentaje_exito'] ?? 0, 1) ?>%</div>
                <span class="bg-surface-container text-secondary font-pill-text text-pill-text px-2 py-1 rounded-full w-fit">Efectividad</span>
            </div>
            <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow border-l-4 border-outline-variant flex flex-col justify-between">
                <h4 class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-2">CALIFICACIÓN</h4>
                <div class="text-headline-lg font-headline-lg <?= ($logistico['calificacion_promedio'] ?? 0) >= 4 ? 'text-primary' : 'text-muted' ?> mb-2">
                    <?= ($logistico['calificacion_promedio'] ?? 0) > 0 ? number_format($logistico['calificacion_promedio'], 1) : '—' ?>
                </div>
                <span class="bg-success-badge-bg text-success-badge-text font-pill-text text-pill-text px-2 py-1 rounded-full w-fit">Promedio</span>
            </div>
        </div>

        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow flex-1 border border-outline-muted">
            <div class="flex items-center gap-2 mb-6 text-secondary">
                <span class="material-symbols-outlined text-[18px]">history</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">HISTORIAL DE ACTIVIDAD</h3>
            </div>
            <?php if (count($historial) === 0): ?>
                <div class="relative pl-6 border-l border-outline-muted ml-3">
                    <div class="relative">
                        <div class="absolute -left-9 top-1 bg-surface-lowest w-6 h-6 rounded-full border border-outline-muted flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[14px]">login</span>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface font-medium">Sin actividad registrada</p>
                        <p class="font-label-sm text-label-sm text-outline">—</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="relative pl-6 border-l border-outline-muted ml-3 space-y-6">
                    <?php foreach ($historial as $h): ?>
                        <div class="relative">
                            <div class="absolute -left-9 top-1 bg-surface-lowest w-6 h-6 rounded-full border border-outline-muted flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[14px]"><?= $h['tipo'] === 'ultimo_acceso' ? 'login' : 'local_shipping' ?></span>
                            </div>
                            <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars($h['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="font-label-sm text-label-sm text-outline"><?= $h['fecha'] ? date('d/m/Y H:i', strtotime($h['fecha'])) : '—' ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="mt-stack-lg">
    <div class="flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined text-primary text-[24px]">local_shipping</span>
        <h2 class="font-headline-sm text-headline-sm text-primary font-bold">Entregas Asociadas <span class="text-body-sm font-normal text-secondary ml-2">(<?= count($entregas) ?> entregas)</span></h2>
    </div>
    <div class="bg-surface-lowest rounded-xl botanical-shadow overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-muted bg-surface-container-low">
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">PEDIDO #</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">PRODUCTOR</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">CONSUMIDOR</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">FECHA</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">ESTADO</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">EVIDENCIA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($entregas) === 0): ?>
                        <tr>
                            <td class="py-12 px-6 text-center" colspan="6">
                                <div class="flex flex-col items-center justify-center text-secondary">
                                    <span class="material-symbols-outlined text-[48px] mb-4 opacity-50">local_shipping</span>
                                    <p class="font-body-md text-body-md">Este transportista no tiene entregas registradas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entregas as $e): ?>
                            <tr class="border-b border-outline-muted hover:bg-surface-low/50 transition-colors">
                                <td class="py-4 px-6 font-label-bold text-label-bold text-on-surface">#<?= (int)$e['id_pedidos'] ?></td>
                                <td class="py-4 px-6 text-body-sm text-secondary"><?= htmlspecialchars($e['productor_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-6 text-body-sm text-secondary"><?= htmlspecialchars($e['consumidor_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-6 text-body-sm text-secondary"><?= $e['fecha'] ? date('d/m/Y', strtotime($e['fecha'])) : '—' ?></td>
                                <td class="py-4 px-6 text-center">
                                    <?php $estadoEntrega = $e['estado_detallado'] ?? 'pendiente'; ?>
                                    <span class="estado-badge <?= $estadoEntrega === 'entregado' ? 'estado-aprobado' : ($estadoEntrega === 'cancelado' ? 'estado-rechazado' : 'estado-pendiente') ?>">
                                        <?= $estadoEntrega === 'entregado' ? 'Entregado' : ($estadoEntrega === 'cancelado' ? 'Cancelado' : ($estadoEntrega === 'en_transito' ? 'En tránsito' : 'Pendiente')) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if (!empty($e['evidencia'])): ?>
                                        <a href="<?= htmlspecialchars($e['evidencia'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"
                                            class="px-3 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">image</span>
                                            Ver
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted/50 text-sm">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
