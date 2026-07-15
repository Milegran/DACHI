<?php
$total = count($consumidores);
$activos = count(array_filter($consumidores, fn($c) => ($c['estado'] ?? 'activo') === 'activo'));
$inactivos = $total - $activos;
$comprasTotales = array_sum(array_column($consumidores, 'total_compras'));
$montoTotal = array_sum(array_column($consumidores, 'monto_total'));
?>

<div class="flex items-end justify-between mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Gestión de Usuarios</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Consumidores</h2>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Total Consumidores</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $total ?></h3>
            <span class="text-success-badge-text bg-success-badge-bg px-2.5 py-1 rounded-lg text-pill-text font-bold">Registrados</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Activos</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $activos ?></h3>
            <span class="text-secondary bg-surface-container px-2.5 py-1 rounded-lg text-pill-text font-bold">En plataforma</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary/30">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Compras Totales</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $comprasTotales ?></h3>
            <span class="text-secondary bg-surface-container px-2.5 py-1 rounded-lg text-pill-text font-bold">Pedidos</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary/30">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Monto Total</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface">$<?= number_format($montoTotal, 2) ?></h3>
            <span class="text-success-badge-text bg-success-badge-bg px-2.5 py-1 rounded-lg text-pill-text font-bold">Facturado</span>
        </div>
    </div>
</div>

<form method="get" action="admin.php" class="mb-stack-md">
    <input type="hidden" name="accion" value="listar_consumidores" />
    <div class="flex gap-3">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Buscar consumidor por nombre, apellido o correo..."
                class="w-full pl-11 pr-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        </div>
        <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Buscar
        </button>
        <?php if (!empty($busqueda)): ?>
            <a href="admin.php?accion=listar_consumidores" class="px-4 py-2.5 rounded-full border border-outline-variant text-muted hover:bg-surface-container-low transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">close</span>
                Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if ($total === 0): ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-12 text-center">
        <span class="material-symbols-outlined text-[64px] text-outline/40">person</span>
        <h2 class="text-lg font-semibold text-muted mt-4">No hay consumidores registrados</h2>
        <p class="text-sm text-muted/60 mt-1">Los consumidores aparecerán aquí cuando se registren en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low/50">
                    <tr>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Consumidor</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Correo</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Teléfono</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Direcciones</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Compras</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Estado</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($consumidores as $c): ?>
                        <tr class="group hover:bg-surface transition-colors">
                            <td class="px-6 py-stack-md">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-container/40 flex items-center justify-center text-primary font-bold text-sm flex-none">
                                        <?= mb_strtoupper(mb_substr($c['nombre'] ?? '?', 0, 1)) . mb_strtoupper(mb_substr($c['apellido'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary"><?= htmlspecialchars($c['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary"><?= htmlspecialchars($c['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-container/30 text-primary font-bold text-sm"><?= (int)($c['total_direcciones'] ?? 0) ?></span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="font-bold text-sm text-on-surface"><?= (int)($c['total_compras'] ?? 0) ?></span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="estado-badge <?= ($c['estado'] ?? 'activo') === 'activo' ? 'estado-aprobado' : 'estado-inactivo' ?>">
                                    <?= ($c['estado'] ?? 'activo') === 'activo' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <a href="admin.php?accion=ver_consumidor&id=<?= (int)$c['id'] ?>"
                                    class="px-4 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    Ver perfil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-6 flex items-center justify-between border-t border-outline-muted bg-surface-container-low/30">
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $total ?> consumidores</p>
            <div class="flex items-center gap-2">
                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary transition-colors" disabled>
                    <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                </button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-white font-label-bold shadow-sm">1</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary transition-colors" disabled>
                    <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>