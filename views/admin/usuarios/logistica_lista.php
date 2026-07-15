<?php
$total = count($logisticos);
$activos = count(array_filter($logisticos, fn($l) => ($l['estado'] ?? 'activo') === 'activo'));
$inactivos = $total - $activos;
$exitosasTotales = array_sum(array_column($logisticos, 'entregas_exitosas'));
$entregasTotales = array_sum(array_column($logisticos, 'total_entregas'));
$pctExito = $entregasTotales > 0 ? round(($exitosasTotales / $entregasTotales) * 100, 1) . '%' : '—';
?>

<div class="flex items-end justify-between mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Gestión de Usuarios</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Proveedores Logísticos</h2>
    </div>
    <button onclick="openCreateUserModal(3)" class="px-5 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Nuevo Usuario
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Total Transportistas</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $total ?></h3>
            <span class="text-success-badge-text bg-success-badge-bg px-2.5 py-1 rounded-lg text-pill-text font-bold">Registrados</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Activos</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $activos ?></h3>
            <span class="text-secondary bg-surface-container px-2.5 py-1 rounded-lg text-pill-text font-bold">Disponibles</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-error/50">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Inactivos</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-error"><?= $inactivos ?></h3>
            <span class="text-on-error-container bg-error-container px-2.5 py-1 rounded-lg text-pill-text font-bold">Sin disponibilidad</span>
        </div>
    </div>
    <div class="bg-surface-lowest p-stack-md botanical-shadow rounded-2xl border-b-4 border-primary/30">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">% Entregas Exitosas</p>
        <div class="flex items-end justify-between mt-2">
            <h3 class="font-headline-md text-headline-md text-on-surface"><?= $pctExito ?></h3>
            <span class="text-success-badge-text bg-success-badge-bg px-2.5 py-1 rounded-lg text-pill-text font-bold">Overall</span>
        </div>
    </div>
</div>

<form method="get" action="admin.php" class="mb-stack-md">
    <input type="hidden" name="accion" value="listar_logisticos" />
    <div class="flex gap-3">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Buscar transportista por nombre, empresa o correo..."
                class="w-full pl-11 pr-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        </div>
        <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Buscar
        </button>
        <?php if (!empty($busqueda)): ?>
            <a href="admin.php?accion=listar_logisticos" class="px-4 py-2.5 rounded-full border border-outline-variant text-muted hover:bg-surface-container-low transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">close</span>
                Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if ($total === 0): ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-12 text-center">
        <span class="material-symbols-outlined text-[64px] text-outline/40">local_shipping</span>
        <h2 class="text-lg font-semibold text-muted mt-4">No hay proveedores logísticos registrados</h2>
        <p class="text-sm text-muted/60 mt-1">Los transportistas aparecerán aquí cuando se registren en la plataforma.</p>
    </div>
<?php else: ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low/50">
                    <tr>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Transportista</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">Contacto</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Entregas</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">% Éxito</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Calificación</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Estado</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($logisticos as $l): ?>
                        <tr class="group hover:bg-surface transition-colors">
                            <td class="px-6 py-stack-md">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-container/40 flex items-center justify-center text-primary font-bold text-sm flex-none">
                                        <?= mb_strtoupper(mb_substr($l['nombre'] ?? '?', 0, 1)) . mb_strtoupper(mb_substr($l['apellido'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars(($l['nombre'] ?? '') . ' ' . ($l['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md">
                                <p class="text-body-sm text-secondary"><?= htmlspecialchars($l['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-body-sm text-secondary"><?= htmlspecialchars($l['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-container/30 text-primary font-bold text-sm"><?= (int)($l['total_entregas'] ?? 0) ?></span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <?php $pct = (int)($l['total_entregas'] ?? 0) > 0 ? round(((int)($l['entregas_exitosas'] ?? 0) / (int)($l['total_entregas'] ?? 1)) * 100) : 0; ?>
                                <span class="font-bold text-sm <?= $pct >= 80 ? 'text-primary' : ($pct >= 50 ? 'text-secondary' : 'text-error') ?>"><?= $pct ?>%</span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <?php $rating = (float)($l['calificacion_promedio'] ?? 0); ?>
                                <div class="flex items-center justify-center gap-1">
                                    <span class="font-bold text-on-surface text-sm"><?= $rating > 0 ? number_format($rating, 1) : '—' ?></span>
                                    <?php if ($rating > 0): ?>
                                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings:'FILL' 1">star</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="estado-badge <?= ($l['estado'] ?? 'activo') === 'activo' ? 'estado-aprobado' : 'estado-inactivo' ?>">
                                    <?= ($l['estado'] ?? 'activo') === 'activo' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="admin.php?accion=ver_logistico&id=<?= (int)$l['id'] ?>"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-primary hover:text-white transition-all"
                                        title="Ver perfil">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
                                    <button onclick='openEditUserModal(<?= json_encode($l, JSON_HEX_TAG | JSON_HEX_AMP) ?>)'
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-primary hover:text-white transition-all"
                                        title="Editar usuario">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button onclick="openDeleteUserModal(<?= (int)$l['id'] ?>, '<?= htmlspecialchars(($l['nombre'] ?? '') . ' ' . ($l['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-surface-container text-secondary hover:bg-error hover:text-white transition-all"
                                        title="Eliminar usuario">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-6 flex items-center justify-between border-t border-outline-muted bg-surface-container-low/30">
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $total ?> transportistas</p>
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