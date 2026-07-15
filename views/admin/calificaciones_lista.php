<div class="flex items-end justify-between mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Reputación</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Calificaciones y Reputación</h2>
    </div>
</div>

<?php
$estrellasLabel = ['1' => '★☆☆☆☆', '2' => '★★☆☆☆', '3' => '★★★☆☆', '4' => '★★★★☆', '5' => '★★★★★'];
$kpiIcon = fn($k) => match($k) {
    'promedio_global' => 'star',
    'mejor_agricultor' => 'agriculture',
    'mejor_logistico' => 'local_shipping',
    'consumidor_activo' => 'person',
    'total_reportes' => 'flag',
    'reportes_pendientes' => 'pending',
    default => 'star'
};
$kpiColor = fn($k) => match($k) {
    'promedio_global' => '#11663C',
    'mejor_agricultor' => '#2e7d32',
    'mejor_logistico' => '#0288d1',
    'consumidor_activo' => '#7b1fa2',
    'total_reportes' => '#e65100',
    'reportes_pendientes' => '#e6a700',
    default => '#11663C'
};
$ESTADOS_LABEL_CAL = ['visible' => 'Visible', 'oculto' => 'Oculto', 'investigacion' => 'En investigación'];
$ESTADOS_CLASS_CAL = ['visible' => 'estado-aprobado', 'oculto' => 'estado-inactivo', 'investigacion' => 'estado-rechazado'];
$ESTADOS_REPORTE = ['pendiente' => 'Pendiente', 'investigando' => 'Investigando', 'resuelto' => 'Resuelto', 'cerrado' => 'Cerrado'];
$ESTADOS_REPORTE_CLASS = ['pendiente' => 'estado-pendiente', 'investigando' => 'estado-pendiente', 'resuelto' => 'estado-aprobado', 'cerrado' => 'estado-inactivo'];
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid <?= $kpiColor('promedio_global') ?>;">
        <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-4">
            <span class="material-symbols-outlined font-bold"><?= $kpiIcon('promedio_global') ?></span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Promedio Global</p>
        <h2 class="font-headline-md text-headline-md font-bold text-primary mt-1"><?= number_format((float)($kpis['promedio_global'] ?? 0), 2) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid <?= $kpiColor('mejor_agricultor') ?>;">
        <div class="w-10 h-10 rounded-2xl bg-green-50 flex items-center justify-center text-green-700 mb-4">
            <span class="material-symbols-outlined font-bold"><?= $kpiIcon('mejor_agricultor') ?></span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Mejor Agricultor</p>
        <h2 class="font-headline-md text-headline-md font-bold text-green-700 mt-1 leading-tight">
            <?php if ($kpis['mejor_agricultor']): ?>
                <?= htmlspecialchars(trim(($kpis['mejor_agricultor']['nombre'] ?? '') . ' ' . ($kpis['mejor_agricultor']['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                —
            <?php endif; ?>
        </h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid <?= $kpiColor('mejor_logistico') ?>;">
        <div class="w-10 h-10 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-700 mb-4">
            <span class="material-symbols-outlined font-bold"><?= $kpiIcon('mejor_logistico') ?></span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Mejor Logístico</p>
        <h2 class="font-headline-md text-headline-md font-bold text-sky-700 mt-1 leading-tight">
            <?php if ($kpis['mejor_logistico']): ?>
                <?= htmlspecialchars(trim(($kpis['mejor_logistico']['nombre'] ?? '') . ' ' . ($kpis['mejor_logistico']['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                —
            <?php endif; ?>
        </h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid <?= $kpiColor('consumidor_activo') ?>;">
        <div class="w-10 h-10 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-700 mb-4">
            <span class="material-symbols-outlined font-bold"><?= $kpiIcon('consumidor_activo') ?></span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Consumidor Activo</p>
        <h2 class="font-headline-md text-headline-md font-bold text-purple-700 mt-1 leading-tight">
            <?php if ($kpis['consumidor_activo']): ?>
                <?= htmlspecialchars(trim(($kpis['consumidor_activo']['nombre'] ?? '') . ' ' . ($kpis['consumidor_activo']['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                —
            <?php endif; ?>
        </h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid <?= $kpiColor('total_reportes') ?>;">
        <div class="w-10 h-10 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-700 mb-4">
            <span class="material-symbols-outlined font-bold"><?= $kpiIcon('total_reportes') ?></span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Reportados</p>
        <h2 class="font-headline-md text-headline-md font-bold text-orange-700 mt-1"><?= (int)($kpis['total_reportes'] ?? 0) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid <?= $kpiColor('reportes_pendientes') ?>;">
        <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-4">
            <span class="material-symbols-outlined font-bold"><?= $kpiIcon('reportes_pendientes') ?></span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Pendientes</p>
        <h2 class="font-headline-md text-headline-md font-bold text-amber-600 mt-1"><?= (int)($kpis['reportes_pendientes'] ?? 0) ?></h2>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-gutter mb-stack-lg">
    <div class="lg:col-span-3 bg-surface-lowest rounded-[24px] p-6 botanical-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Tendencia</p>
                <h3 class="font-headline-sm text-headline-sm text-primary">Satisfacción Mensual</h3>
            </div>
        </div>
        <div class="h-[280px]"><canvas id="trendChart" class="w-full h-full"></canvas></div>
    </div>
    <div class="lg:col-span-2 bg-surface-lowest rounded-[24px] p-6 botanical-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Distribución</p>
                <h3 class="font-headline-sm text-headline-sm text-primary">Calificaciones</h3>
            </div>
        </div>
        <div class="h-[300px]"><canvas id="distChart" class="w-full h-full"></canvas></div>
    </div>
</div>

<form method="get" action="admin.php" class="mb-stack-md">
    <input type="hidden" name="accion" value="listar_calificaciones" />
    <input type="hidden" name="orden" value="<?= htmlspecialchars($orden ?? 'c.id', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="direccion" value="<?= htmlspecialchars($direccion ?? 'DESC', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="pagina" value="1" />
    <div class="flex flex-wrap gap-3 items-end">
        <div class="relative flex-1 min-w-[200px]">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Buscar por ID, evaluado, evaluador..."
                class="w-full pl-11 pr-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        </div>
        <select name="rol"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all min-w-[140px]">
            <option value="">Todos los roles</option>
            <option value="producto" <?= ($filtro_rol ?? '') === 'producto' ? 'selected' : '' ?>>Producto</option>
            <option value="productor" <?= ($filtro_rol ?? '') === 'productor' ? 'selected' : '' ?>>Productor</option>
            <option value="logistica" <?= ($filtro_rol ?? '') === 'logistica' ? 'selected' : '' ?>>Logística</option>
        </select>
        <select name="estado"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all min-w-[140px]">
            <option value="">Todos los estados</option>
            <option value="visible" <?= ($filtro_estado ?? '') === 'visible' ? 'selected' : '' ?>>Visible</option>
            <option value="oculto" <?= ($filtro_estado ?? '') === 'oculto' ? 'selected' : '' ?>>Oculto</option>
            <option value="investigacion" <?= ($filtro_estado ?? '') === 'investigacion' ? 'selected' : '' ?>>En investigación</option>
        </select>
        <select name="estrellas"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all min-w-[130px]">
            <option value="">Todas las estrellas</option>
            <?php for ($e = 5; $e >= 1; $e--): ?>
                <option value="<?= $e ?>" <?= ($filtro_estrellas ?? '') === (string)$e ? 'selected' : '' ?>><?= $e ?> ★</option>
            <?php endfor; ?>
        </select>
        <input type="date" name="fecha_desde" value="<?= htmlspecialchars($filtro_fecha_desde ?? '', ENT_QUOTES, 'UTF-8') ?>"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($filtro_fecha_hasta ?? '', ENT_QUOTES, 'UTF-8') ?>"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        <button type="submit" class="px-6 py-2.5 bg-black text-white rounded-full font-semibold hover:bg-neutral-800 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Buscar
        </button>
        <?php if (!empty($busqueda) || !empty($filtro_rol) || !empty($filtro_estado) || !empty($filtro_estrellas) || !empty($filtro_fecha_desde) || !empty($filtro_fecha_hasta)): ?>
            <a href="admin.php?accion=listar_calificaciones&pagina=1&orden=c.id&direccion=DESC" class="px-4 py-2.5 rounded-full border border-outline-variant text-muted hover:bg-surface-container-low transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">close</span>
                Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (count($calificaciones) === 0): ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-12 text-center">
        <span class="material-symbols-outlined text-[64px] text-outline/40">star</span>
        <h2 class="text-lg font-semibold text-muted mt-4">No hay calificaciones registradas</h2>
        <p class="text-sm text-muted/60 mt-1">Las calificaciones aparecerán aquí cuando los consumidores califiquen productos y servicios.</p>
    </div>
<?php else: ?>
    <?php
    $sortUrl = function ($col) use ($orden, $direccion, $busqueda, $filtro_rol, $filtro_estado, $filtro_estrellas, $filtro_fecha_desde, $filtro_fecha_hasta, $pagina) {
        $dir = ($orden === $col && $direccion === 'ASC') ? 'DESC' : 'ASC';
        $qs = http_build_query([
            'accion' => 'listar_calificaciones',
            'orden' => $col,
            'direccion' => $dir,
            'pagina' => $pagina ?? 1,
            'busqueda' => $busqueda,
            'rol' => $filtro_rol,
            'estado' => $filtro_estado,
            'estrellas' => $filtro_estrellas,
            'fecha_desde' => $filtro_fecha_desde,
            'fecha_hasta' => $filtro_fecha_hasta
        ]);
        return "admin.php?$qs";
    };
    ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low/50">
                    <tr>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider w-10">
                            <input type="checkbox" class="rounded border-outline-variant" />
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider">
                            <a href="<?= $sortUrl('evaluado') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Evaluado</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider">
                            <a href="<?= $sortUrl('evaluador') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Evaluador</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider text-center">
                            <a href="<?= $sortUrl('rol') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">Rol</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider text-center">
                            <a href="<?= $sortUrl('calificacion') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">Calif.</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider">
                            <a href="<?= $sortUrl('fecha') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Fecha</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider text-center">
                            <a href="<?= $sortUrl('reportes') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">Reportes</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider text-center">
                            <a href="<?= $sortUrl('estado') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">Estado</a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline uppercase tracking-wider text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($calificaciones as $cal): ?>
                        <?php $estCal = $cal['estado'] ?? 'visible'; ?>
                        <tr class="cursor-pointer group hover:bg-surface transition-colors" onclick="openDetailsCal(<?= (int)$cal['id'] ?>)">
                            <td class="px-6 py-stack-md" onclick="event.stopPropagation()">
                                <input type="checkbox" class="rounded border-outline-variant" />
                            </td>
                            <td class="px-6 py-stack-md">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-none overflow-hidden">
                                        <?php if (!empty($cal['evaluado_foto'])): ?>
                                            <img src="<?= htmlspecialchars($cal['evaluado_foto'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover" />
                                        <?php else: ?>
                                            <?= mb_strtoupper(mb_substr($cal['evaluado_nombre'] ?? '?', 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors">
                                            <?= htmlspecialchars(trim(($cal['evaluado_nombre'] ?? '') . ' ' . ($cal['evaluado_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?: '—' ?>
                                        </p>
                                        <p class="text-body-sm text-secondary"><?= htmlspecialchars($cal['evaluado_correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md">
                                <p class="font-label-bold text-label-bold text-on-surface">
                                    <?= htmlspecialchars(trim(($cal['consumer_nombre'] ?? '') . ' ' . ($cal['consumer_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?: '—' ?>
                                </p>
                                <p class="text-body-sm text-secondary"><?= htmlspecialchars($cal['consumer_correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <?php $rolBadge = $cal['tipo'] ?? 'producto'; ?>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold <?= $rolBadge === 'logistica' ? 'bg-sky-50 text-sky-700' : 'bg-green-50 text-green-700' ?>">
                                    <span class="material-symbols-outlined text-[14px]"><?= $rolBadge === 'logistica' ? 'local_shipping' : 'agriculture' ?></span>
                                    <?= $rolBadge === 'logistica' ? 'Logística' : 'Producto' ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary">
                                <?= htmlspecialchars($cal['producto_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="font-bold text-lg <?= (int)($cal['calificacion'] ?? 0) >= 4 ? 'text-green-700' : ((int)($cal['calificacion'] ?? 0) >= 3 ? 'text-amber-600' : 'text-red-600') ?>">
                                    <?= (int)($cal['calificacion'] ?? 0) ?>
                                </span>
                                <span class="text-xs text-outline block"><?= $estrellasLabel[(string)(int)($cal['calificacion'] ?? 0)] ?? '' ?></span>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary">
                                <?php $f = $cal['created_at'] ?? ($cal['updated_at'] ?? ''); ?>
                                <?= $f ? date('d/m/Y', strtotime($f)) : '—' ?>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <?php $repCount = (int)($cal['total_reportes'] ?? 0); ?>
                                <?php if ($repCount > 0): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700">
                                        <span class="material-symbols-outlined text-[14px]">flag</span>
                                        <?= $repCount ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-outline/40">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="estado-badge <?= $ESTADOS_CLASS_CAL[$estCal] ?? 'estado-visible' ?>">
                                    <?= $ESTADOS_LABEL_CAL[$estCal] ?? 'Visible' ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <div class="flex items-center justify-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button onclick='event.stopPropagation();openDetailsCal(<?= (int)$cal['id'] ?>)'
                                        class="p-2 rounded-lg hover:bg-surface-container text-secondary hover:text-primary transition-all" title="Ver detalles">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                    <button onclick="event.stopPropagation();abrirResponder(<?= (int)$cal['id'] ?>, '<?= htmlspecialchars(addslashes($cal['comentario'] ?? ''), ENT_QUOTES, 'UTF-8') ?>')"
                                        class="p-2 rounded-lg hover:bg-surface-container text-secondary hover:text-primary transition-all" title="Responder">
                                        <span class="material-symbols-outlined text-[20px]">reply</span>
                                    </button>
                                    <?php if ($estCal !== 'investigacion'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoCal(<?= (int)$cal['id'] ?>, 'investigacion')"
                                            class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition-all" title="Reportar">
                                            <span class="material-symbols-outlined text-[20px]">flag</span>
                                        </button>
                                    <?php endif; ?>
                                    <div class="relative inline-block" onclick="event.stopPropagation()">
                                        <button onclick="toggleAccionMenu(this)"
                                            class="p-2 rounded-lg hover:bg-surface-container text-secondary hover:text-primary transition-all">
                                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                        </button>
                                        <div class="absolute right-0 top-10 w-44 bg-white rounded-2xl border border-outline-variant/50 shadow-xl p-2 z-50 hidden accion-menu">
                                            <?php if ($estCal !== 'visible'): ?>
                                                <button onclick="cambiarEstadoCal(<?= (int)$cal['id'] ?>, 'visible')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-on-surface flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-[18px]">visibility</span> Mostrar
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($estCal !== 'oculto'): ?>
                                                <button onclick="cambiarEstadoCal(<?= (int)$cal['id'] ?>, 'oculto')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-surface-container-low text-sm text-on-surface flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-[18px]">visibility_off</span> Ocultar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-6 flex items-center justify-between border-t border-outline-muted bg-surface-container-low/30">
            <?php
            $from = ($pagina - 1) * $limite + 1;
            $to = min($pagina * $limite, $total);
            $pageUrl = function ($p) use ($orden, $direccion, $busqueda, $filtro_rol, $filtro_estado, $filtro_estrellas, $filtro_fecha_desde, $filtro_fecha_hasta) {
                return 'admin.php?' . http_build_query([
                    'accion' => 'listar_calificaciones',
                    'orden' => $orden,
                    'direccion' => $direccion,
                    'pagina' => $p,
                    'busqueda' => $busqueda,
                    'rol' => $filtro_rol,
                    'estado' => $filtro_estado,
                    'estrellas' => $filtro_estrellas,
                    'fecha_desde' => $filtro_fecha_desde,
                    'fecha_hasta' => $filtro_fecha_hasta
                ]);
            };
            ?>
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> calificaciones</p>
            <div class="flex items-center gap-2">
                <a href="<?= $pageUrl(max(1, $pagina - 1)) ?>"
                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary transition-colors <?= $pagina <= 1 ? 'pointer-events-none opacity-30' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                </a>
                <?php
                $maxLinks = 7;
                $start = max(1, $pagina - floor($maxLinks / 2));
                $end = min($totalPaginas, $start + $maxLinks - 1);
                if ($end - $start + 1 < $maxLinks) {
                    $start = max(1, $end - $maxLinks + 1);
                }
                if ($start > 1): ?>
                    <a href="<?= $pageUrl(1) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary transition-colors text-label-sm font-bold">1</a>
                    <?php if ($start > 2): ?><span class="text-outline px-1">···</span><?php endif; ?>
                <?php endif; ?>
                <?php for ($p = $start; $p <= $end; $p++): ?>
                    <a href="<?= $pageUrl($p) ?>"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-label-sm font-bold transition-colors <?= $p === $pagina ? 'bg-primary text-white shadow-sm' : 'border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($end < $totalPaginas): ?>
                    <?php if ($end < $totalPaginas - 1): ?><span class="text-outline px-1">···</span><?php endif; ?>
                    <a href="<?= $pageUrl($totalPaginas) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary transition-colors text-label-sm font-bold"><?= $totalPaginas ?></a>
                <?php endif; ?>
                <a href="<?= $pageUrl(min($totalPaginas, $pagina + 1)) ?>"
                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary transition-colors <?= $pagina >= $totalPaginas ? 'pointer-events-none opacity-30' : '' ?>">
                    <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mt-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-top:3px solid #d32f2f;">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-[#d32f2f]">gavel</span>
            <h3 class="font-headline-sm text-headline-sm text-[#d32f2f]">Casos en investigación</h3>
        </div>
        <?php if (count($casos) === 0): ?>
            <div class="text-center py-8">
                <span class="material-symbols-outlined text-[40px] text-outline/30">check_circle</span>
                <p class="text-sm text-secondary mt-2">No hay casos pendientes</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left">
                <thead>
                    <tr class="font-label-sm text-label-sm text-outline uppercase tracking-wider">
                        <th class="pb-2">Usuario</th>
                        <th class="pb-2 text-right">Prioridad</th>
                        <th class="pb-2 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($casos as $caso): ?>
                        <tr>
                            <td class="py-2.5">
                                <p class="font-label-bold text-label-bold text-on-surface">
                                    <?= htmlspecialchars(trim(($caso['reportado_nombre'] ?? '') . ' ' . ($caso['reportado_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <p class="text-body-sm text-secondary line-clamp-1"><?= htmlspecialchars($caso['motivo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="estado-badge <?= $caso['prioridad'] === 'alta' ? 'estado-rechazado' : ($caso['prioridad'] === 'media' ? 'estado-pendiente' : 'estado-inactivo') ?>">
                                    <?= ucfirst($caso['prioridad'] ?? 'media') ?>
                                </span>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="text-xs text-outline">
                                    <?php $horas = (int)($caso['horas_transcurridas'] ?? 0); ?>
                                    <?php if ($horas < 24): ?><?= $horas ?>h<?php else: ?><?= floor($horas / 24) ?>d <?= $horas % 24 ?>h<?php endif; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-top:3px solid #e65100;">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-[#e65100]">sentiment_very_dissatisfied</span>
            <h3 class="font-headline-sm text-headline-sm text-[#e65100]">Peor reputación</h3>
        </div>
        <?php if (count($peorReputacion) === 0): ?>
            <div class="text-center py-8">
                <span class="material-symbols-outlined text-[40px] text-outline/30">sentiment_satisfied</span>
                <p class="text-sm text-secondary mt-2">Todo en orden</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left">
                <thead>
                    <tr class="font-label-sm text-label-sm text-outline uppercase tracking-wider">
                        <th class="pb-2">#</th>
                        <th class="pb-2">Agricultor</th>
                        <th class="pb-2 text-right">Prom.</th>
                        <th class="pb-2 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($peorReputacion as $i => $usr): ?>
                        <tr>
                            <td class="py-2.5 w-8">
                                <span class="w-6 h-6 rounded-full bg-[#e65100]/10 text-[#e65100] flex items-center justify-center text-xs font-bold"><?= $i + 1 ?></span>
                            </td>
                            <td class="py-2.5">
                                <p class="font-label-bold text-label-bold text-on-surface truncate">
                                    <?= htmlspecialchars(trim(($usr['nombre'] ?? '') . ' ' . ($usr['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <span class="text-xs text-secondary"><?= (int)($usr['total_reportes'] ?? 0) ?> reportes</span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-[#e6a700]">★ <?= number_format((float)($usr['promedio'] ?? 0), 1) ?></td>
                            <td class="py-2.5 text-right">
                                <?php $estadoCuenta = $usr['estado_cuenta'] ?? 'activo'; ?>
                                <span class="estado-badge <?= $estadoCuenta === 'inactivo' ? 'estado-inactivo' : 'estado-aprobado' ?>">
                                    <?= ucfirst($estadoCuenta) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-top:3px solid #0288d1;">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-[#0288d1]">emoji_events</span>
            <h3 class="font-headline-sm text-headline-sm text-[#0288d1]">Mejores agricultores</h3>
        </div>
        <?php if (count($mejoresAgricultores) === 0): ?>
            <div class="text-center py-8">
                <span class="material-symbols-outlined text-[40px] text-outline/30">agriculture</span>
                <p class="text-sm text-secondary mt-2">Sin datos aún</p>
            </div>
        <?php else: ?>
            <table class="w-full text-left">
                <thead>
                    <tr class="font-label-sm text-label-sm text-outline uppercase tracking-wider">
                        <th class="pb-2">#</th>
                        <th class="pb-2">Agricultor</th>
                        <th class="pb-2 text-right">Prom.</th>
                        <th class="pb-2 text-right">Reseñas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($mejoresAgricultores as $i => $agr): ?>
                        <tr>
                            <td class="py-2.5 w-8">
                                <span class="w-7 h-7 rounded-full <?= $i === 0 ? 'bg-[#0288d1] text-white' : 'bg-[#0288d1]/10 text-[#0288d1]' ?> flex items-center justify-center text-xs font-bold"><?= $i + 1 ?></span>
                            </td>
                            <td class="py-2.5">
                                <p class="font-label-bold text-label-bold text-on-surface truncate">
                                    <?= htmlspecialchars(trim(($agr['nombre'] ?? '') . ' ' . ($agr['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <?php if (!empty($agr['ubicacion_finca'])): ?>
                                    <span class="text-xs text-secondary"><?= htmlspecialchars($agr['ubicacion_finca'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2.5 text-right font-bold text-[#e6a700]">★ <?= number_format((float)($agr['promedio'] ?? 0), 1) ?></td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#0288d1]/10 text-[#0288d1]">
                                    <?= (int)($agr['total_resenas'] ?? 0) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="fixed inset-0 bg-black/40 z-[300] hidden" id="detailsOverlay" onclick="closeDetailsCal()"></div>
<div class="fixed top-0 right-0 h-full w-full max-w-2xl bg-white z-[310] shadow-2xl translate-x-full transition-transform duration-300 ease-out overflow-y-auto" id="detailsPanel">
    <div class="sticky top-0 bg-white border-b border-outline-variant/50 z-10 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 flex-none">
                <span class="material-symbols-outlined text-[24px]">star</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary" id="dpTitulo">Calificación</h3>
                <p class="text-body-sm text-secondary mt-0.5" id="dpFecha"></p>
            </div>
        </div>
        <button class="p-2 hover:bg-surface-container-low rounded-full flex-none" onclick="closeDetailsCal()"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Calificación</p>
                <p id="dpCalificacion" class="text-2xl font-bold"></p>
            </div>
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Estado</p>
                <p id="dpEstado"></p>
            </div>
        </div>

        <div class="bg-primary/5 rounded-2xl p-5 border border-primary/10">
            <p class="font-label-sm text-label-sm text-primary uppercase tracking-wider mb-3">Evaluado</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold flex-none overflow-hidden" id="dpEvalFotoContainer">
                    <span id="dpEvalInicial"></span>
                </div>
                <div>
                    <p class="font-label-bold text-label-bold text-on-surface" id="dpEvalNombre"></p>
                    <p class="text-body-sm text-secondary" id="dpEvalCorreo"></p>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide" id="dpEvalRol"></span>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-low/60 rounded-2xl p-4">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Evaluador</p>
            <p class="font-label-bold text-label-bold text-on-surface" id="dpConsNombre"></p>
            <p class="text-body-sm text-secondary" id="dpConsCorreo"></p>
        </div>

        <div>
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Producto / Servicio</p>
            <p class="font-label-bold text-label-bold text-on-surface" id="dpProducto"></p>
        </div>

        <div>
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Comentario</p>
            <p class="text-body-md text-on-surface bg-surface-container-low/30 rounded-2xl p-4 leading-relaxed" id="dpComentario"></p>
        </div>

        <div id="dpRespuestaBox" class="hidden">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Tu respuesta</p>
            <p class="text-body-md text-primary bg-primary/5 rounded-2xl p-4 leading-relaxed border border-primary/10" id="dpRespuesta"></p>
        </div>

        <div id="dpReportesBox" class="hidden">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Reportes</p>
            <div class="space-y-3" id="dpReportesList"></div>
        </div>

        <div class="border-t border-outline-variant/30 pt-4 flex gap-3 flex-wrap" id="dpActions">
            <button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsCal()" type="button">Cerrar</button>
        </div>
    </div>
</div>

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[400] hidden items-center justify-center p-4 modal-overlay" id="responderModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg p-6 sm:p-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-headline-sm text-headline-sm text-primary">Responder comentario</h3>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeResponderModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="bg-surface-container-low/60 rounded-2xl p-4 mb-4">
            <p class="font-label-sm text-label-sm text-outline mb-1">Comentario original</p>
            <p class="text-body-sm text-secondary leading-relaxed" id="respComentarioOrig"></p>
        </div>
        <textarea id="respTexto" class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white min-h-[120px] resize-none" placeholder="Escribe tu respuesta como administrador..." maxlength="500"></textarea>
        <p class="text-xs text-on-surface-variant mt-1 ml-1">Máximo 500 caracteres</p>
        <button onclick="enviarRespuesta()" class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98] mt-4" type="button">ENVIAR RESPUESTA</button>
    </div>
</div>

<script>
const ESTRELLAS_VIEW = <?= json_encode($estrellasLabel) ?>;
const ESTADOS_CAL = <?= json_encode($ESTADOS_LABEL_CAL) ?>;
const ESTADOS_CAL_CLASS = <?= json_encode($ESTADOS_CLASS_CAL) ?>;

const trendCtx = document.getElementById('trendChart').getContext('2d');
const distCtx = document.getElementById('distChart').getContext('2d');

const trendData = <?= json_encode($tendencia) ?>;
const distData = <?= json_encode($distribucion) ?>;

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendData.map(function(d) { return d.mes; }),
        datasets: [{
            label: 'Promedio',
            data: trendData.map(function(d) { return parseFloat(d.promedio); }),
            borderColor: '#11663C',
            backgroundColor: 'rgba(17,102,60,0.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#11663C',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 5, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(distCtx, {
    type: 'bar',
    data: {
        labels: ['5 ★', '4 ★', '3 ★', '2 ★', '1 ★'],
        datasets: [{
            label: 'Calificaciones',
            data: (function() {
                const m = {5:0,4:0,3:0,2:0,1:0};
                distData.forEach(function(d) { m[parseInt(d.estrellas)] = parseInt(d.total); });
                return [m[5], m[4], m[3], m[2], m[1]];
            })(),
            backgroundColor: ['#11663C', '#2e7d32', '#e6a700', '#e65100', '#ba1a1a'],
            borderRadius: 4,
            barThickness: 28
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, beginAtZero: true },
            y: { grid: { display: false } }
        }
    }
});

function toggleAccionMenu(btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.accion-menu').forEach(function(m) { if (m !== menu) m.classList.add('hidden'); });
    menu.classList.toggle('hidden');
}
document.addEventListener('click', function() {
    document.querySelectorAll('.accion-menu').forEach(function(m) { m.classList.add('hidden'); });
});

function cambiarEstadoCal(id, estado) {
    const labels = {
        visible: { title: 'Mostrar calificación', msg: 'La calificación será visible para todos.', icon: 'visibility', iconBg: 'bg-green-50 text-green-700', btn: 'MOSTRAR', btnBg: 'bg-green-700 hover:bg-green-800' },
        oculto: { title: 'Ocultar calificación', msg: 'La calificación quedará oculta del público.', icon: 'visibility_off', iconBg: 'bg-surface-container text-muted', btn: 'OCULTAR', btnBg: 'bg-neutral-700 hover:bg-neutral-800' },
        investigacion: { title: 'Marcar en investigación', msg: 'Se iniciará un caso de revisión para esta calificación.', icon: 'flag', iconBg: 'bg-red-50 text-red-600', btn: 'INVESTIGAR', btnBg: 'bg-red-600 hover:bg-red-700' }
    };
    const cfg = labels[estado] || labels.visible;
    openConfirmModal(cfg.title, cfg.msg, cfg.icon, cfg.iconBg, cfg.btn, cfg.btnBg, function() {
        closeConfirmModal();
        const formData = new FormData();
        formData.append('accion', 'cambiar_estado_calificacion');
        formData.append('id', id);
        formData.append('estado', estado);
        fetch('admin.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    storeNotification('Calificación actualizada a "' + (ESTADOS_CAL[estado] || estado) + '"', 'success');
                    location.reload();
                } else {
                    openConfirmModal('Error', data.message || 'No se pudo completar la acción', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
                    document.getElementById('confirmBtn').onclick = closeConfirmModal;
                }
            })
            .catch(function() {
                openConfirmModal('Error de conexión', 'Verifique su conexión e intente nuevamente.', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
                document.getElementById('confirmBtn').onclick = closeConfirmModal;
            });
    });
}

function openDetailsCal(id) {
    fetch('admin.php?accion=listar_calificaciones&ajax_calificacion_detalle=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) return;
            const c = data.calificacion;
            document.getElementById('dpTitulo').textContent = 'Calificación #' + c.id;
            document.getElementById('dpFecha').textContent = (c.created_at || c.updated_at || '') ? new Date((c.created_at || c.updated_at).replace(' ', 'T')).toLocaleDateString('es-PA', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';
            const calVal = parseInt(c.calificacion) || 0;
            document.getElementById('dpCalificacion').innerHTML = '<span class="' + (calVal >= 4 ? 'text-green-700' : calVal >= 3 ? 'text-amber-600' : 'text-red-600') + '">' + calVal + '</span> <span class="text-sm text-outline">' + (ESTRELLAS_VIEW[String(calVal)] || '') + '</span>';
            const est = c.estado || 'visible';
            const badge = document.getElementById('dpEstado');
            badge.className = 'estado-badge ' + (ESTADOS_CAL_CLASS[est] || 'estado-aprobado');
            badge.textContent = ESTADOS_CAL[est] || 'Visible';

            const evalFoto = document.getElementById('dpEvalFotoContainer');
            const evalInicial = document.getElementById('dpEvalInicial');
            if (c.evaluado_foto) {
                evalFoto.innerHTML = '<img src="' + c.evaluado_foto + '" class="w-full h-full object-cover" />';
            } else {
                evalFoto.innerHTML = '';
                evalInicial.textContent = (c.evaluado_nombre || '?')[0].toUpperCase();
            }
            document.getElementById('dpEvalNombre').textContent = ((c.evaluado_nombre || '') + ' ' + (c.evaluado_apellido || '')).trim() || '—';
            document.getElementById('dpEvalCorreo').textContent = c.evaluado_correo || '';
            const rolSpan = document.getElementById('dpEvalRol');
            rolSpan.textContent = c.rol_evaluado === 'logistica' ? 'Logística' : 'Productor';
            rolSpan.className = 'inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide ' + (c.rol_evaluado === 'logistica' ? 'bg-sky-50 text-sky-700' : 'bg-green-50 text-green-700');

            document.getElementById('dpConsNombre').textContent = ((c.consumer_nombre || '') + ' ' + (c.consumer_apellido || '')).trim() || '—';
            document.getElementById('dpConsCorreo').textContent = c.consumer_correo || '';
            document.getElementById('dpProducto').textContent = c.producto_nombre || '—';
            document.getElementById('dpComentario').textContent = c.comentario || 'Sin comentario';

            const respBox = document.getElementById('dpRespuestaBox');
            if (c.respuesta_admin) {
                respBox.classList.remove('hidden');
                document.getElementById('dpRespuesta').textContent = c.respuesta_admin;
            } else {
                respBox.classList.add('hidden');
            }

            const repBox = document.getElementById('dpReportesBox');
            const repList = document.getElementById('dpReportesList');
            if (data.reportes && data.reportes.length > 0) {
                repBox.classList.remove('hidden');
                repList.innerHTML = '';
                data.reportes.forEach(function(r) {
                    const d = document.createElement('div');
                    d.className = 'p-3 rounded-xl bg-surface-container-low/60 border border-outline-muted';
                    d.innerHTML = '<div class="flex items-center justify-between"><span class="font-label-bold text-label-bold text-on-surface">Reportado por ' + (r.reporta_nombre || '') + ' ' + (r.reporta_apellido || '') + '</span>'
                        + '<span class="estado-badge ' + (ESTADOS_REPORTE_CLASS[r.estado] || 'estado-pendiente') + '">' + (ESTADOS_REPORTE[r.estado] || r.estado) + '</span></div>'
                        + '<p class="text-body-sm text-secondary mt-1">' + (r.motivo || '') + '</p>'
                        + '<p class="text-xs text-outline mt-1">' + (r.created_at ? new Date(r.created_at.replace(' ', 'T')).toLocaleString('es-PA') : '') + '</p></div>';
                    repList.appendChild(d);
                });
            } else {
                repBox.classList.add('hidden');
            }

            const actionsDiv = document.getElementById('dpActions');
            let html = '<button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsCal()" type="button">Cerrar</button>';
            if (est !== 'visible') {
                html += '<button class="flex-1 px-4 py-3 rounded-xl bg-green-700 text-white font-semibold hover:bg-green-800 transition-all" onclick="closeDetailsCal();setTimeout(function(){cambiarEstadoCal(' + c.id + ',\'visible\')},350)" type="button">Mostrar</button>';
            }
            if (est !== 'oculto') {
                html += '<button class="px-4 py-3 rounded-xl border border-neutral-300 text-neutral-700 font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsCal();setTimeout(function(){cambiarEstadoCal(' + c.id + ',\'oculto\')},350)" type="button">Ocultar</button>';
            }
            actionsDiv.innerHTML = html;

            document.getElementById('detailsOverlay').classList.remove('hidden');
            document.getElementById('detailsPanel').classList.remove('translate-x-full');
        });
}

function closeDetailsCal() {
    document.getElementById('detailsPanel').classList.add('translate-x-full');
    setTimeout(function() { document.getElementById('detailsOverlay').classList.add('hidden'); }, 300);
}

var _respCalId = null;
function abrirResponder(id, comentario) {
    _respCalId = id;
    document.getElementById('respComentarioOrig').textContent = comentario || 'Sin comentario';
    document.getElementById('respTexto').value = '';
    document.getElementById('responderModal').classList.remove('hidden');
    document.getElementById('responderModal').classList.add('flex');
}
function closeResponderModal() {
    document.getElementById('responderModal').classList.add('hidden');
    document.getElementById('responderModal').classList.remove('flex');
    _respCalId = null;
}
function enviarRespuesta() {
    const texto = document.getElementById('respTexto').value.trim();
    if (!texto) { document.getElementById('respTexto').focus(); return; }
    const formData = new FormData();
    formData.append('accion', 'responder_comentario');
    formData.append('id', _respCalId);
    formData.append('respuesta', texto);
    fetch('admin.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                storeNotification('Respuesta guardada correctamente', 'success');
                closeResponderModal();
                location.reload();
            } else {
                alert(data.message || 'Error al guardar respuesta');
            }
        })
        .catch(function() { alert('Error de conexión'); });
}

const ESTADOS_REPORTE = <?= json_encode($ESTADOS_REPORTE) ?>;
const ESTADOS_REPORTE_CLASS = <?= json_encode($ESTADOS_REPORTE_CLASS) ?>;
</script>
