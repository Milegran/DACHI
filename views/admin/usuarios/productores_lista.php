<?php
$total = count($productores);
$activos = count(array_filter($productores, fn($p) => ($p['estado'] ?? 'activo') === 'activo'));
$inactivos = $total - $activos;
$ratings = array_filter(array_column($productores, 'calificacion_promedio'), fn($v) => $v > 0);
$promRating = count($ratings) > 0 ? number_format(array_sum($ratings) / count($ratings), 1) : '—';
?>

<div class="flex items-end justify-between mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Gestión de Usuarios</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Proveedores Agrícolas</h2>
    </div>
    <button onclick="openCreateUserModal(2)" class="px-5 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Nuevo Usuario
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Total Productores</p>
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
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-error/50">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Inactivos</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-error"><?= $inactivos ?></h3>
            <span class="text-on-error-container bg-error-container px-2.5 py-1 rounded-lg text-pill-text font-bold">Requiere atención</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary/30">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Calificación Promedio</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $promRating ?></h3>
            <span class="text-success-badge-text bg-success-badge-bg px-2.5 py-1 rounded-lg text-pill-text font-bold">Overall</span>
        </div>
    </div>
</div>

<form method="get" action="admin.php" class="mb-stack-md">
    <input type="hidden" name="accion" value="listar_productores" />
    <div class="flex gap-3">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Buscar por nombre, apellido o correo..."
                class="w-full pl-11 pr-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        </div>
        <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Buscar
        </button>
        <?php if (!empty($busqueda)): ?>
            <a href="admin.php?accion=listar_productores<?= ($mostrandoInactivos ?? false) ? '&inactivos=1' : '' ?>" class="px-4 py-2.5 rounded-full border border-outline-variant text-muted hover:bg-surface-container-low transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">close</span>
                Limpiar
            </a>
        <?php endif; ?>
        <a href="admin.php?accion=listar_productores<?= ($mostrandoInactivos ?? false) ? '' : '&inactivos=1' ?>"
            class="px-4 py-2.5 rounded-full border transition-all flex items-center gap-1.5 text-sm font-semibold <?= ($mostrandoInactivos ?? false) ? 'bg-primary text-white border-primary' : 'border-outline-variant text-secondary hover:bg-surface-container-low' ?>">
            <span class="material-symbols-outlined text-[18px]">block</span>
            Inactivos
        </a>
    </div>
</form>

<?php if ($total === 0): ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-12 text-center">
        <span class="material-symbols-outlined text-[64px] text-outline/40">agriculture</span>
        <h2 class="text-lg font-semibold text-muted mt-4">No hay proveedores agrícolas registrados</h2>
        <p class="text-sm text-muted/60 mt-1">Los productores aparecerán aquí cuando se registren en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low/50">
                    <tr>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Productor</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Correo</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Teléfono</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Productos</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Calificación</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Estado</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Registro</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($productores as $p): ?>
                        <tr class="group hover:bg-surface transition-colors">
                            <td class="px-6 py-stack-md">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-container/40 flex items-center justify-center text-primary font-bold text-sm flex-none">
                                        <?= mb_strtoupper(mb_substr($p['nombre'] ?? '?', 0, 1)) . mb_strtoupper(mb_substr($p['apellido'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php if (!empty($p['ubicacion_finca'])): ?>
                                            <p class="text-body-sm text-secondary"><?= htmlspecialchars(mb_substr($p['ubicacion_finca'], 0, 40), ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary"><?= htmlspecialchars($p['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary"><?= htmlspecialchars($p['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-container/30 text-primary font-bold text-sm"><?= (int)($p['total_productos'] ?? 0) ?></span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <?php $rating = (float)($p['calificacion_promedio'] ?? 0); ?>
                                <div class="flex items-center justify-center gap-1">
                                    <span class="font-bold text-on-surface text-sm"><?= $rating > 0 ? number_format($rating, 1) : '—' ?></span>
                                    <?php if ($rating > 0): ?>
                                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings:'FILL' 1">star</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="estado-badge <?= ($p['estado'] ?? 'activo') === 'activo' ? 'estado-aprobado' : 'estado-inactivo' ?>">
                                    <?= ($p['estado'] ?? 'activo') === 'activo' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary">
                                <?= $p['fecha_registro'] ? date('d/m/Y', strtotime($p['fecha_registro'])) : '—' ?>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="admin.php?accion=ver_productor&id=<?= (int)$p['id'] ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-primary hover:text-white transition-all"
                                        title="Ver perfil">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    <button onclick='openEditUserModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_AMP) ?>)'
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-primary hover:text-white transition-all"
                                        title="Editar usuario">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <?php if (($p['estado'] ?? 'activo') === 'activo'): ?>
                                        <button onclick="openDeleteUserModal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-error hover:text-white transition-all"
                                            title="Desactivar usuario">
                                            <span class="material-symbols-outlined text-[18px]">block</span>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="openActivateUserModal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-green-700 hover:text-white transition-all"
                                            title="Activar usuario">
                                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-6 flex items-center justify-between border-t border-outline-muted bg-surface-container-low/30">
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $total ?> productores</p>
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