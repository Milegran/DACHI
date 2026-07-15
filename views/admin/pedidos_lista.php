<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Operaciones</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Gestión de Pedidos</h2>
        <p class="text-body-sm text-secondary mt-1 max-w-2xl">Supervise todas las órdenes realizadas dentro de DACHI, desde la compra hasta la entrega al consumidor.</p>
    </div>
    <div class="flex items-center gap-2 flex-none">
        <a href="admin.php?accion=exportar_pdf_pedidos<?= htmlspecialchars($_SERVER['QUERY_STRING'] ? '&' . $_SERVER['QUERY_STRING'] : '', ENT_QUOTES, 'UTF-8') ?>"
           class="px-4 py-2.5 rounded-full border border-outline-variant text-secondary hover:bg-surface-container-low transition-colors flex items-center gap-2 text-sm font-semibold">
            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
            Exportar PDF
        </a>
        <a href="admin.php?accion=exportar_excel_pedidos<?= htmlspecialchars($_SERVER['QUERY_STRING'] ? '&' . $_SERVER['QUERY_STRING'] : '', ENT_QUOTES, 'UTF-8') ?>"
           class="px-4 py-2.5 rounded-full border border-outline-variant text-secondary hover:bg-surface-container-low transition-colors flex items-center gap-2 text-sm font-semibold">
            <span class="material-symbols-outlined text-[18px]">table_chart</span>
            Exportar Excel
        </a>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-left:4px solid #11663C;">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-2xl bg-success-badge-bg flex items-center justify-center text-primary flex-none">
                <span class="material-symbols-outlined text-[20px]">receipt_long</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Pedidos Totales</p>
        </div>
        <p class="font-headline-md text-headline-md font-bold text-primary"><?= number_format((int)($statsExpandidas['total'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-left:4px solid #e6a700;">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 flex-none">
                <span class="material-symbols-outlined text-[20px]">pending</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Pendientes</p>
        </div>
        <p class="font-headline-md text-headline-md font-bold text-amber-600"><?= number_format((int)($statsExpandidas['pendiente'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-left:4px solid #0288d1;">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-700 flex-none">
                <span class="material-symbols-outlined text-[20px]">pending_actions</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">En preparación</p>
        </div>
        <p class="font-headline-md text-headline-md font-bold text-sky-700"><?= number_format((int)($statsExpandidas['en_preparacion'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-left:4px solid #7b1fa2;">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-700 flex-none">
                <span class="material-symbols-outlined text-[20px]">local_shipping</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">En tránsito</p>
        </div>
        <p class="font-headline-md text-headline-md font-bold text-purple-700"><?= number_format((int)($statsExpandidas['en_transito'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-left:4px solid #2e7d32;">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-2xl bg-green-50 flex items-center justify-center text-green-700 flex-none">
                <span class="material-symbols-outlined text-[20px]">today</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Entregados Hoy</p>
        </div>
        <p class="font-headline-md text-headline-md font-bold text-green-700"><?= number_format((int)($statsExpandidas['entregados_hoy'] ?? 0)) ?></p>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-5 botanical-shadow" style="border-left:4px solid #d32f2f;">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 flex-none">
                <span class="material-symbols-outlined text-[20px]">report</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Con incidencias</p>
        </div>
        <p class="font-headline-md text-headline-md font-bold text-red-600"><?= number_format((int)($statsExpandidas['con_incidencias'] ?? 0)) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-2xl p-5 botanical-shadow">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-4">Pedidos por estado</p>
        <div class="h-[280px] flex items-center justify-center">
            <canvas id="donaChart"></canvas>
        </div>
    </div>
    <div class="bg-surface-lowest rounded-2xl p-5 botanical-shadow">
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-4">Pedidos por día</p>
        <div class="h-[280px] flex items-center justify-center">
            <canvas id="lineaChart"></canvas>
        </div>
    </div>
</div>

<form method="get" action="admin.php" class="mb-stack-md">
    <input type="hidden" name="accion" value="listar_pedidos" />
    <input type="hidden" name="orden" value="<?= htmlspecialchars($orden ?? 'id', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="direccion" value="<?= htmlspecialchars($direccion ?? 'DESC', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="pagina" value="1" />
    <div class="bg-surface-lowest rounded-2xl p-5 botanical-shadow border border-outline-muted">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Buscar pedido..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
            </div>
            <input type="text" name="consumidor" value="<?= htmlspecialchars($_GET['consumidor'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Consumidor"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
            <select name="agricultor"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all">
                <option value="">Agricultor</option>
                <?php foreach ($agricultores as $ag): ?>
                    <option value="<?= (int)$ag['id'] ?>" <?= ($filtro_agricultor ?? '') === (string)$ag['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(trim($ag['nombre'] . ' ' . $ag['apellido']), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="logistica"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all">
                <option value="">Empresa logística</option>
                <?php foreach ($logisticos as $lg): ?>
                    <option value="<?= (int)$lg['id'] ?>" <?= ($filtro_logistica ?? '') === (string)$lg['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(trim($lg['nombre'] . ' ' . $lg['apellido']), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="estado"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all">
                <option value="">Estado</option>
                <option value="pendiente" <?= ($filtro_estado ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="en_preparacion" <?= ($filtro_estado ?? '') === 'en_preparacion' ? 'selected' : '' ?>>En preparación</option>
                <option value="en_transito" <?= ($filtro_estado ?? '') === 'en_transito' ? 'selected' : '' ?>>En tránsito</option>
                <option value="entregado" <?= ($filtro_estado ?? '') === 'entregado' ? 'selected' : '' ?>>Entregado</option>
                <option value="cancelado" <?= ($filtro_estado ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <select name="metodo_pago"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all">
                <option value="">Método de pago</option>
                <?php foreach ($metodosPago as $mp): ?>
                    <option value="<?= (int)$mp['id'] ?>" <?= ($filtro_metodo_pago ?? '') === (string)$mp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mp['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="provincia"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all">
                <option value="">Provincia</option>
                <?php foreach ($provincias as $prov): ?>
                    <option value="<?= htmlspecialchars($prov, ENT_QUOTES, 'UTF-8') ?>" <?= ($filtro_provincia ?? '') === $prov ? 'selected' : '' ?>>
                        <?= htmlspecialchars($prov, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($filtro_fecha_inicio ?? '', ENT_QUOTES, 'UTF-8') ?>"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
            <input type="date" name="fecha_fin" value="<?= htmlspecialchars($filtro_fecha_fin ?? '', ENT_QUOTES, 'UTF-8') ?>"
                class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-black text-white rounded-xl font-semibold hover:bg-neutral-800 transition-colors flex items-center justify-center gap-1.5 text-sm">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Buscar
                </button>
                <?php
                $hasFilters = !empty($busqueda) || !empty($filtro_estado) || !empty($filtro_metodo_pago)
                    || !empty($filtro_agricultor) || !empty($filtro_logistica) || !empty($filtro_provincia)
                    || !empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)
                    || !empty($_GET['consumidor']);
                ?>
                <?php if ($hasFilters): ?>
                    <a href="admin.php?accion=listar_pedidos&pagina=1" class="px-3 py-2 rounded-xl border border-outline-variant text-muted hover:bg-surface-container-low transition-colors flex items-center gap-1 text-sm">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                        Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<?php if (count($pedidos) === 0): ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-12 text-center">
        <span class="material-symbols-outlined text-[64px] text-outline/40">receipt_long</span>
        <h2 class="text-lg font-semibold text-muted mt-4">No hay pedidos registrados</h2>
        <p class="text-sm text-muted/60 mt-1">Los pedidos aparecerán aquí cuando los consumidores realicen compras en la plataforma.</p>
    </div>
<?php else: ?>
    <?php
    $ESTADOS_LABEL = [
        'pendiente' => 'Pendiente',
        'en_preparacion' => 'En preparación',
        'en_transito' => 'En tránsito',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado'
    ];
    $ESTADOS_CLASS = [
        'pendiente' => 'estado-pendiente',
        'en_preparacion' => 'estado-pendiente',
        'en_transito' => 'estado-pendiente',
        'entregado' => 'estado-aprobado',
        'cancelado' => 'estado-rechazado'
    ];
    $sortUrl = function ($col) use ($orden, $direccion, $busqueda, $filtro_estado, $filtro_metodo_pago, $filtro_agricultor, $filtro_logistica, $filtro_provincia, $filtro_fecha_inicio, $filtro_fecha_fin, $pagina) {
        $dir = ($orden === $col && $direccion === 'ASC') ? 'DESC' : 'ASC';
        $qs = http_build_query([
            'accion' => 'listar_pedidos',
            'orden' => $col,
            'direccion' => $dir,
            'pagina' => $pagina ?? 1,
            'busqueda' => $busqueda,
            'estado' => $filtro_estado,
            'metodo_pago' => $filtro_metodo_pago,
            'agricultor' => $filtro_agricultor,
            'logistica' => $filtro_logistica,
            'provincia' => $filtro_provincia,
            'fecha_inicio' => $filtro_fecha_inicio,
            'fecha_fin' => $filtro_fecha_fin
        ]);
        return "admin.php?$qs";
    };
    ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low/50">
                    <tr>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('id') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Pedido</a>
                        </th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('fecha') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Fecha</a>
                        </th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('cliente') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Consumidor</a>
                        </th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase">Agricultor</th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase">Logística</th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">
                            <a href="<?= $sortUrl('estado') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">Estado</a>
                        </th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('metodo_pago') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">Pago</a>
                        </th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase text-right">
                            <a href="<?= $sortUrl('total') ?>" class="flex items-center gap-1 justify-end hover:text-primary transition-colors">Total</a>
                        </th>
                        <th class="px-4 py-3.5 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($pedidos as $ped): ?>
                        <?php $estadoPed = $ped['estado_detallado'] ?? 'pendiente'; ?>
                        <tr class="cursor-pointer group hover:bg-surface transition-colors" onclick='openDetailsPedido(<?= json_encode($ped, JSON_UNESCAPED_UNICODE) ?>)'>
                            <td class="px-4 py-3">
                                <span class="font-label-bold text-label-bold text-on-surface font-mono">#<?= (int)$ped['id'] ?></span>
                            </td>
                            <td class="px-4 py-3 text-body-sm text-secondary whitespace-nowrap">
                                <?= $ped['fecha'] ? date('d/m/Y', strtotime($ped['fecha'])) : '—' ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars(trim(($ped['consumer_nombre'] ?? '') . ' ' . ($ped['consumer_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?: '—' ?></p>
                                <p class="text-body-sm text-secondary"><?= htmlspecialchars($ped['consumer_correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td class="px-4 py-3 text-body-sm text-secondary max-w-[140px] truncate" title="<?= htmlspecialchars($ped['agricultores'] ?? '—', ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($ped['agricultores'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-4 py-3 text-body-sm text-secondary max-w-[120px] truncate" title="<?= htmlspecialchars($ped['logisticos'] ?? '—', ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($ped['logisticos'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="estado-badge whitespace-nowrap <?= $ESTADOS_CLASS[$estadoPed] ?? 'estado-pendiente' ?>">
                                    <?= $ESTADOS_LABEL[$estadoPed] ?? 'Pendiente' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-surface-container text-secondary whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[14px]">payments</span>
                                    <?= htmlspecialchars($ped['metodo_pago_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-on-surface whitespace-nowrap">$<?= number_format((float)($ped['total_compra'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button onclick='event.stopPropagation();openDetailsPedido(<?= json_encode($ped, JSON_UNESCAPED_UNICODE) ?>)'
                                        class="px-2.5 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">search</span>
                                        Detalles
                                    </button>
                                    <span class="w-px h-5 bg-outline-muted mx-0.5"></span>
                                    <?php if ($estadoPed === 'pendiente'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoPedido(<?= (int)$ped['id'] ?>, 'en_preparacion')"
                                            class="p-1.5 rounded-lg hover:bg-sky-100 text-sky-700 transition-all" title="Poner en preparación">
                                            <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($estadoPed === 'en_preparacion'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoPedido(<?= (int)$ped['id'] ?>, 'en_transito')"
                                            class="p-1.5 rounded-lg hover:bg-purple-100 text-purple-700 transition-all" title="Enviar a tránsito">
                                            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($estadoPed === 'en_transito'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoPedido(<?= (int)$ped['id'] ?>, 'entregado')"
                                            class="p-1.5 rounded-lg hover:bg-green-100 text-green-700 transition-all" title="Marcar como entregado">
                                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (in_array($estadoPed, ['pendiente', 'en_preparacion', 'en_transito'])): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoPedido(<?= (int)$ped['id'] ?>, 'cancelado')"
                                            class="p-1.5 rounded-lg hover:bg-error-container/20 text-error transition-colors" title="Cancelar pedido">
                                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 flex items-center justify-between border-t border-outline-muted bg-surface-container-low/30">
            <?php
            $from = ($pagina - 1) * $limite + 1;
            $to = min($pagina * $limite, $total);
            $pageUrl = function ($p) use ($orden, $direccion, $busqueda, $filtro_estado, $filtro_metodo_pago, $filtro_agricultor, $filtro_logistica, $filtro_provincia, $filtro_fecha_inicio, $filtro_fecha_fin) {
                return 'admin.php?' . http_build_query([
                    'accion' => 'listar_pedidos',
                    'orden' => $orden,
                    'direccion' => $direccion,
                    'pagina' => $p,
                    'busqueda' => $busqueda,
                    'estado' => $filtro_estado,
                    'metodo_pago' => $filtro_metodo_pago,
                    'agricultor' => $filtro_agricultor,
                    'logistica' => $filtro_logistica,
                    'provincia' => $filtro_provincia,
                    'fecha_inicio' => $filtro_fecha_inicio,
                    'fecha_fin' => $filtro_fecha_fin
                ]);
            };
            ?>
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> pedidos</p>
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

<div class="fixed inset-0 bg-black/40 z-[300] hidden" id="detailsOverlay" onclick="closeDetailsPedido()"></div>
<div class="fixed top-0 right-0 h-full w-full max-w-2xl bg-white z-[310] shadow-2xl translate-x-full transition-transform duration-300 ease-out overflow-y-auto" id="detailsPanel">
    <div class="sticky top-0 bg-white border-b border-outline-variant/50 z-10 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-none">
                <span class="material-symbols-outlined text-[24px]">receipt_long</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary" id="dpTitulo">Pedido</h3>
                <p class="text-body-sm text-secondary mt-0.5" id="dpFecha"></p>
            </div>
        </div>
        <button class="p-2 hover:bg-surface-container-low rounded-full flex-none" onclick="closeDetailsPedido()"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Estado</p>
                <p id="dpEstado"></p>
            </div>
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Método de pago</p>
                <p class="font-label-bold text-label-bold text-on-surface" id="dpPago"></p>
            </div>
        </div>

        <div class="bg-primary/5 rounded-2xl p-5 border border-primary/10">
            <p class="font-label-sm text-label-sm text-primary uppercase tracking-wider mb-3">Cliente</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold flex-none">
                    <span id="dpClienteInicial"></span>
                </div>
                <div>
                    <p class="font-label-bold text-label-bold text-on-surface" id="dpClienteNombre"></p>
                    <p class="text-body-sm text-secondary" id="dpClienteCorreo"></p>
                    <p class="text-body-sm text-secondary" id="dpClienteTelefono"></p>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Productos</p>
                <span class="text-label-sm text-secondary" id="dpItemsCount"></span>
            </div>
            <div class="space-y-3" id="dpProductosList"></div>
            <div class="flex justify-between items-center mt-4 pt-4 border-t border-outline-variant/30">
                <p class="font-label-bold text-label-bold text-on-surface">Total</p>
                <p class="font-headline-sm text-headline-sm font-bold text-primary" id="dpTotal"></p>
            </div>
        </div>

        <div id="dpNotasBox" class="bg-surface-container-low/60 rounded-2xl p-4 hidden">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Notas</p>
            <p class="text-body-md text-on-surface leading-relaxed" id="dpNotas"></p>
        </div>

        <div id="dpEntregaBox" class="bg-surface-container-low/60 rounded-2xl p-4 hidden">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-2">Información de entrega</p>
            <div class="space-y-2" id="dpEntregaContent"></div>
        </div>

        <div class="border-t border-outline-variant/30 pt-4 flex gap-3 flex-wrap" id="dpActions">
            <button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsPedido()" type="button">Cerrar</button>
        </div>
    </div>
</div>

<script>
const ESTADOS_PEDIDO = {
    pendiente: 'Pendiente',
    en_preparacion: 'En preparación',
    en_transito: 'En tránsito',
    entregado: 'Entregado',
    cancelado: 'Cancelado'
};
const ESTADOS_CLASS = {
    pendiente: 'estado-pendiente',
    en_preparacion: 'estado-pendiente',
    en_transito: 'estado-pendiente',
    entregado: 'estado-aprobado',
    cancelado: 'estado-rechazado'
};

function cambiarEstadoPedido(id, estado) {
    const labels = {
        en_preparacion: { title: 'Iniciar preparación', msg: 'El pedido pasará a estar en preparación.', icon: 'pending_actions', iconBg: 'bg-sky-50 text-sky-700', btn: 'INICIAR', btnBg: 'bg-sky-700 hover:bg-sky-800' },
        en_transito:    { title: 'Enviar a tránsito',   msg: 'El pedido será marcado como en tránsito.',      icon: 'local_shipping', iconBg: 'bg-purple-50 text-purple-700', btn: 'ENVIAR', btnBg: 'bg-purple-700 hover:bg-purple-800' },
        entregado:      { title: 'Marcar como entregado', msg: 'El pedido se marcará como entregado.',          icon: 'check_circle',   iconBg: 'bg-green-50 text-green-700',   btn: 'ENTREGAR', btnBg: 'bg-green-700 hover:bg-green-800' },
        cancelado:      { title: 'Cancelar pedido',     msg: 'El pedido será cancelado.',                       icon: 'cancel',         iconBg: 'bg-red-50 text-red-600',       btn: 'CANCELAR', btnBg: 'bg-red-600 hover:bg-red-700' }
    };
    const cfg = labels[estado] || labels.en_preparacion;
    openConfirmModal(cfg.title, cfg.msg, cfg.icon, cfg.iconBg, cfg.btn, cfg.btnBg, function () {
        closeConfirmModal();
        ejecutarCambioEstado(id, estado);
    });
}

function ejecutarCambioEstado(id, estado) {
    const formData = new FormData();
    formData.append('accion', 'cambiar_estado_pedido');
    formData.append('id', id);
    formData.append('estado', estado);
    fetch('admin.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                storeNotification('Pedido #' + id + ' actualizado a "' + (ESTADOS_PEDIDO[estado] || estado) + '"', 'success');
                location.reload();
            } else {
                openConfirmModal('Error', data.message || 'No se pudo completar la acción', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
                document.getElementById('confirmBtn').onclick = closeConfirmModal;
            }
        })
        .catch(() => {
            openConfirmModal('Error de conexión', 'Verifique su conexión e intente nuevamente.', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
            document.getElementById('confirmBtn').onclick = closeConfirmModal;
        });
}

function closeDetailsPedido() {
    document.getElementById('detailsPanel').classList.add('translate-x-full');
    setTimeout(() => document.getElementById('detailsOverlay').classList.add('hidden'), 300);
}

function openDetailsPedido(ped) {
    document.getElementById('dpTitulo').textContent = 'Pedido #' + ped.id;
    document.getElementById('dpFecha').textContent = ped.fecha ? new Date(ped.fecha + 'T00:00:00').toLocaleDateString('es-PA', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';
    const est = ped.estado_detallado || 'pendiente';
    const badge = document.getElementById('dpEstado');
    badge.className = 'estado-badge ' + (ESTADOS_CLASS[est] || 'estado-pendiente');
    badge.textContent = ESTADOS_PEDIDO[est] || 'Pendiente';
    document.getElementById('dpPago').textContent = ped.metodo_pago_nombre || '—';

    const clienteNombre = ((ped.consumer_nombre || '') + ' ' + (ped.consumer_apellido || '')).trim() || '—';
    document.getElementById('dpClienteNombre').textContent = clienteNombre;
    document.getElementById('dpClienteInicial').textContent = (ped.consumer_nombre || '?')[0].toUpperCase();
    document.getElementById('dpClienteCorreo').textContent = ped.consumer_correo || '';
    document.getElementById('dpClienteTelefono').textContent = ped.consumer_telefono ? 'Tel: ' + ped.consumer_telefono : '';

    document.getElementById('dpTotal').textContent = '$' + (parseFloat(ped.total_compra) || 0).toFixed(2);

    const notasBox = document.getElementById('dpNotasBox');
    const notasEl = document.getElementById('dpNotas');
    if (ped.notas) {
        notasBox.classList.remove('hidden');
        notasEl.textContent = ped.notas;
    } else {
        notasBox.classList.add('hidden');
    }

    fetch('admin.php?accion=listar_pedidos&ajax_pedido_detalle=' + ped.id)
        .then(r => r.json())
        .then(data => {
            if (data.productos) {
                const list = document.getElementById('dpProductosList');
                const count = document.getElementById('dpItemsCount');
                list.innerHTML = '';
                data.productos.forEach(function (item) {
                    const div = document.createElement('div');
                    div.className = 'flex items-center gap-3 p-3 rounded-xl bg-surface-container-low/60';
                    div.innerHTML = '<div class="w-10 h-10 rounded-lg overflow-hidden flex-none border border-outline-variant/30 bg-surface-container flex items-center justify-center text-outline">'
                        + (item.producto_imagen ? '<img src="' + item.producto_imagen + '" class="w-full h-full object-cover" />' : '<span class="material-symbols-outlined text-[18px]">inventory_2</span>')
                        + '</div>'
                        + '<div class="flex-1 min-w-0"><p class="font-label-bold text-label-bold text-on-surface truncate">' + item.producto_nombre + '</p>'
                        + '<p class="text-body-sm text-secondary">' + item.cantidad + ' x $' + parseFloat(item.precio_unitario).toFixed(2) + '</p>'
                        + (item.agricultor_nombre ? '<p class="text-body-sm text-secondary">Agricultor: ' + item.agricultor_nombre + '</p>' : '')
                        + '</div>'
                        + '<p class="font-bold text-on-surface">$' + parseFloat(item.subtotal).toFixed(2) + '</p>';
                    list.appendChild(div);
                });
                count.textContent = data.productos.length + ' producto' + (data.productos.length !== 1 ? 's' : '');
            }
            if (data.entrega) {
                const e = data.entrega;
                const entregaBox = document.getElementById('dpEntregaBox');
                const content = document.getElementById('dpEntregaContent');
                entregaBox.classList.remove('hidden');
                const repNombre = ((e.repartidor_nombre || '') + ' ' + (e.repartidor_apellido || '')).trim();
                const dirParts = [e.provincia, e.distrito, e.corregimiento].filter(Boolean);
                const dirStr = dirParts.length > 0 ? dirParts.join(', ') : (e.direccion_detalle || '—');
                content.innerHTML = '<div class="grid grid-cols-2 gap-3">'
                    + '<div><p class="text-label-sm text-outline">Repartidor</p><p class="font-label-bold text-label-bold text-on-surface">' + (repNombre || '—') + '</p></div>'
                    + '<div><p class="text-label-sm text-outline">Tarifa envío</p><p class="font-label-bold text-label-bold text-on-surface">$' + parseFloat(e.tarifa_envio || 0).toFixed(2) + '</p></div>'
                    + '<div class="col-span-2"><p class="text-label-sm text-outline">Dirección</p><p class="font-label-bold text-label-bold text-on-surface">' + dirStr + '</p></div>'
                    + (e.notas ? '<div class="col-span-2"><p class="text-label-sm text-outline">Notas</p><p class="text-body-sm text-secondary">' + e.notas + '</p></div>' : '')
                    + '</div>';
            } else {
                document.getElementById('dpEntregaBox').classList.add('hidden');
            }
        })
        .catch(() => {});

    const actionsDiv = document.getElementById('dpActions');
    let html = '<button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsPedido()" type="button">Cerrar</button>';
    if (est === 'pendiente') {
        html += '<button class="flex-1 px-4 py-3 rounded-xl bg-sky-700 text-white font-semibold hover:bg-sky-800 transition-all" onclick="closeDetailsPedido();setTimeout(function(){cambiarEstadoPedido(' + ped.id + ',\'en_preparacion\')},350)" type="button">Iniciar preparación</button>';
    } else if (est === 'en_preparacion') {
        html += '<button class="flex-1 px-4 py-3 rounded-xl bg-purple-700 text-white font-semibold hover:bg-purple-800 transition-all" onclick="closeDetailsPedido();setTimeout(function(){cambiarEstadoPedido(' + ped.id + ',\'en_transito\')},350)" type="button">Enviar a tránsito</button>';
    } else if (est === 'en_transito') {
        html += '<button class="flex-1 px-4 py-3 rounded-xl bg-green-700 text-white font-semibold hover:bg-green-800 transition-all" onclick="closeDetailsPedido();setTimeout(function(){cambiarEstadoPedido(' + ped.id + ',\'entregado\')},350)" type="button">Marcar como entregado</button>';
    }
    if (['pendiente', 'en_preparacion', 'en_transito'].indexOf(est) !== -1) {
        html += '<button class="px-4 py-3 rounded-xl border border-red-300 text-red-700 font-semibold hover:bg-red-50 transition-all" onclick="closeDetailsPedido();setTimeout(function(){cambiarEstadoPedido(' + ped.id + ',\'cancelado\')},350)" type="button">Cancelar</button>';
    }
    actionsDiv.innerHTML = html;

    document.getElementById('detailsOverlay').classList.remove('hidden');
    document.getElementById('detailsPanel').classList.remove('translate-x-full');
}

document.addEventListener('DOMContentLoaded', function () {
    const donaCtx = document.getElementById('donaChart');
    const lineaCtx = document.getElementById('lineaChart');
    if (!donaCtx || !lineaCtx) return;

    const donaData = <?= json_encode($datosDona ?? []) ?>;
    const lineaData = <?= json_encode($datosLinea ?? []) ?>;

    const estadoColors = {
        pendiente: '#e6a700',
        en_preparacion: '#0288d1',
        en_transito: '#7b1fa2',
        entregado: '#2e7d32',
        cancelado: '#d32f2f'
    };
    const estadoLabels = {
        pendiente: 'Pendientes',
        en_preparacion: 'Preparación',
        en_transito: 'En tránsito',
        entregado: 'Entregados',
        cancelado: 'Cancelados'
    };

    if (donaData.length > 0) {
        new Chart(donaCtx, {
            type: 'doughnut',
            data: {
                labels: donaData.map(function (d) { return estadoLabels[d.estado] || d.estado; }),
                datasets: [{
                    data: donaData.map(function (d) { return parseInt(d.total); }),
                    backgroundColor: donaData.map(function (d) { return estadoColors[d.estado] || '#999'; }),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, usePointStyle: true, font: { size: 12 } }
                    }
                },
                cutout: '65%'
            }
        });
    }

    if (lineaData.length > 0) {
        new Chart(lineaCtx, {
            type: 'line',
            data: {
                labels: lineaData.map(function (d) { return d.dia; }),
                datasets: [{
                    label: 'Pedidos',
                    data: lineaData.map(function (d) { return d.total; }),
                    borderColor: '#11663C',
                    backgroundColor: 'rgba(17, 102, 60, 0.08)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#11663C',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
