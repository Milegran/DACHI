<div class="mb-6">
    <a href="admin.php?accion=listar_productores" class="inline-flex items-center gap-1.5 text-sm font-semibold text-muted hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Volver al listado
    </a>
</div>

<div class="bg-primary text-white rounded-xl p-8 mb-gutter relative overflow-hidden flex flex-col md:flex-row items-center md:items-start gap-6 botanical-shadow">
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary-container opacity-50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
    <div class="w-24 h-24 rounded-full border-2 border-primary-fixed flex items-center justify-center bg-transparent z-10 shrink-0">
        <span class="font-headline-md text-headline-md text-white"><?= mb_strtoupper(mb_substr($productor['nombre'] ?? '?', 0, 1)) . mb_strtoupper(mb_substr($productor['apellido'] ?? '', 0, 1)) ?></span>
    </div>
    <div class="flex-1 z-10 text-center md:text-left">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <h1 class="font-headline-lg text-headline-lg text-white"><?= htmlspecialchars(($productor['nombre'] ?? '') . ' ' . ($productor['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
            <span class="bg-success-badge-bg text-success-badge-text font-pill-text text-pill-text px-3 py-1 rounded-full uppercase tracking-widest self-center md:self-auto">
                <?= ($productor['estado'] ?? 'activo') === 'activo' ? 'ACTIVO' : 'INACTIVO' ?>
            </span>
        </div>
        <div class="flex flex-col md:flex-row gap-4 md:gap-8 text-primary-fixed font-body-sm text-body-sm mt-4">
            <div class="flex items-center justify-center md:justify-start gap-2">
                <span class="material-symbols-outlined text-sm">mail</span>
                <?= htmlspecialchars($productor['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php if (!empty($productor['telefono'])): ?>
            <div class="flex items-center justify-center md:justify-start gap-2">
                <span class="material-symbols-outlined text-sm">phone</span>
                <?= htmlspecialchars($productor['telefono'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-4 text-primary-fixed-dim font-label-sm text-label-sm text-right">
            Miembro desde <?= $productor['fecha_registro'] ? date('M Y', strtotime($productor['fecha_registro'])) : '—' ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter mb-stack-lg">
    <div class="lg:col-span-4 flex flex-col gap-gutter">
        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow" style="border-left:4px solid #11663C;">
            <div class="flex items-center gap-2 mb-4 text-secondary">
                <span class="material-symbols-outlined text-[18px]">contact_page</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">INFORMACIÓN PERSONAL</h3>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="font-label-sm text-label-sm text-secondary mb-1">Nombre Completo</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars(($productor['nombre'] ?? '') . ' ' . ($productor['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-secondary mb-1">Correo</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars($productor['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-secondary mb-1">Teléfono</p>
                    <p class="font-body-md text-body-md text-on-surface font-medium"><?= htmlspecialchars($productor['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow" style="border-left:4px solid #57615b;">
            <div class="flex items-center gap-2 mb-4 text-secondary">
                <span class="material-symbols-outlined text-[18px]">location_on</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">UBICACIÓN DE LA FINCA</h3>
            </div>
            <p class="font-body-md text-body-md text-on-surface font-medium"><?= nl2br(htmlspecialchars($productor['ubicacion_finca'] ?? 'No registrada', ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow" style="border-left:4px solid #57615b;">
            <div class="flex items-center gap-2 mb-4 text-secondary">
                <span class="material-symbols-outlined text-[18px]">account_balance</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">DATOS BANCARIOS</h3>
            </div>
            <p class="font-body-md text-body-md text-on-surface font-medium"><?= nl2br(htmlspecialchars($productor['datos_bancarios'] ?? 'No registrados', ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow" style="border-left:4px solid #57615b;">
            <div class="flex items-center gap-2 mb-4 text-secondary">
                <span class="material-symbols-outlined text-[18px]">eco</span>
                <h3 class="font-label-bold text-label-bold uppercase tracking-wider">PRÁCTICAS DE PRODUCCIÓN</h3>
            </div>
            <p class="font-body-md text-body-md text-on-surface font-medium"><?= nl2br(htmlspecialchars($productor['practicas_produccion'] ?? 'No especificadas', ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>

    <div class="lg:col-span-8 flex flex-col gap-gutter">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow flex flex-col justify-between" style="border-left:4px solid #11663C;">
                <h4 class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-2">PRODUCTOS</h4>
                <div class="text-headline-lg font-headline-lg text-primary mb-2"><?= (int)($productor['total_productos'] ?? 0) ?></div>
                <span class="bg-success-badge-bg text-success-badge-text font-pill-text text-pill-text px-2 py-1 rounded-full w-fit">Registrados</span>
            </div>
            <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow flex flex-col justify-between" style="border-left:4px solid #bfc9c1;">
                <h4 class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-2">VENTAS</h4>
                <div class="text-headline-lg font-headline-lg text-primary mb-2"><?= (int)($productor['total_ventas'] ?? 0) ?></div>
                <span class="bg-surface-container text-secondary font-pill-text text-pill-text px-2 py-1 rounded-full w-fit">Completadas</span>
            </div>
            <div class="bg-surface-lowest rounded-xl p-6 botanical-shadow flex flex-col justify-between" style="border-left:4px solid #bfc9c1;">
                <h4 class="font-label-sm text-label-sm text-secondary uppercase tracking-wider mb-2">CALIFICACIÓN</h4>
                <div class="text-headline-lg font-headline-lg <?= ($productor['calificacion_promedio'] ?? 0) >= 4 ? 'text-primary' : 'text-muted' ?> mb-2">
                    <?= ($productor['calificacion_promedio'] ?? 0) > 0 ? number_format($productor['calificacion_promedio'], 1) : '—' ?>
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
                                <span class="material-symbols-outlined text-[14px]"><?= $h['tipo'] === 'ultimo_acceso' ? 'login' : ($h['tipo'] === 'pedido' ? 'shopping_bag' : 'inventory_2') ?></span>
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
        <span class="material-symbols-outlined text-primary text-[24px]">inventory_2</span>
        <h2 class="font-headline-sm text-headline-sm text-primary font-bold">Productos Asociados <span class="text-body-sm font-normal text-secondary ml-2">(<?= count($productos) ?> productos)</span></h2>
    </div>
    <div class="bg-surface-lowest rounded-xl botanical-shadow overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-muted bg-surface-container-low">
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">PRODUCTO</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">CATEGORÍA</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-right">PRECIO</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">STOCK</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">ESTADO</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider">PUBLICACIÓN</th>
                        <th class="py-4 px-6 font-label-bold text-label-bold text-secondary uppercase tracking-wider text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) === 0): ?>
                        <tr>
                            <td class="py-12 px-6 text-center" colspan="7">
                                <div class="flex flex-col items-center justify-center text-secondary">
                                    <span class="material-symbols-outlined text-[48px] mb-4 opacity-50">inventory_2</span>
                                    <p class="font-body-md text-body-md">Este productor no tiene productos registrados.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $prod): ?>
                            <tr class="border-b border-outline-muted hover:bg-surface-low/50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($prod['imagen'])): ?>
                                            <img src="<?= htmlspecialchars($prod['imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                class="w-10 h-10 rounded-lg object-cover border border-outline-variant/30 flex-none" />
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-lg bg-surface-container-low flex items-center justify-center text-muted flex-none">
                                                <span class="material-symbols-outlined text-[20px]">image</span>
                                            </div>
                                        <?php endif; ?>
                                        <p class="font-label-bold text-label-bold text-on-surface"><?= htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-body-sm text-secondary"><?= htmlspecialchars($prod['categoria'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-6 text-right font-bold text-on-surface">$<?= htmlspecialchars($prod['precio'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="py-4 px-6 text-center">
                                    <span class="font-bold text-sm <?= (int)($prod['stock'] ?? 0) <= 0 ? 'text-error' : ((int)($prod['stock'] ?? 0) <= 5 ? 'text-secondary' : 'text-on-surface') ?>">
                                        <?= (int)($prod['stock'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php $estado = $prod['estado_aprobacion'] ?? 'pendiente'; ?>
                                    <span class="estado-badge <?= $estado === 'aprobado' ? 'estado-aprobado' : ($estado === 'rechazado' ? 'estado-rechazado' : ($estado === 'inactivo' ? 'estado-inactivo' : 'estado-pendiente')) ?>">
                                        <?= $estado === 'aprobado' ? 'Aprobado' : ($estado === 'pendiente' ? 'Pendiente' : ($estado === 'rechazado' ? 'Rechazado' : 'Inactivo')) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-body-sm text-secondary"><?= $prod['created_at'] ? date('d/m/Y', strtotime($prod['created_at'])) : '—' ?></td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <?php if ($estado !== 'aprobado'): ?>
                                            <button onclick="cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'aprobado')"
                                                class="p-2 rounded-lg hover:bg-primary/90 text-white transition-colors" title="Aprobar">
                                                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($estado !== 'rechazado'): ?>
                                            <button onclick="cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'rechazado')"
                                                class="p-2 rounded-lg hover:bg-error-container/40 text-error transition-colors" title="Rechazar">
                                                <span class="material-symbols-outlined text-[20px]">cancel</span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($estado !== 'inactivo'): ?>
                                            <button onclick="cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'inactivo')"
                                                class="p-2 rounded-lg hover:bg-surface-container-low text-muted transition-colors" title="Deshabilitar">
                                                <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($estado === 'inactivo' || $estado === 'rechazado'): ?>
                                            <button onclick="cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'pendiente')"
                                                class="p-2 rounded-lg hover:bg-secondary-fixed/40 text-secondary transition-colors" title="Poner en pendiente">
                                                <span class="material-symbols-outlined text-[20px]">pending</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
