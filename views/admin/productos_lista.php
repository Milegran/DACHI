<style>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number] { -moz-appearance: textfield; }
</style>
<div class="flex items-end justify-between mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Catálogo</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Gestión de Productos</h2>
    </div>
    <button onclick="openCreateProductModal()" class="px-5 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-all flex items-center gap-2 shadow-sm active:scale-[0.97]">
        <span class="material-symbols-outlined text-[20px]">add</span>
        Agregar Producto
    </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #11663C;">
        <div class="w-10 h-10 rounded-2xl bg-success-badge-bg flex items-center justify-center text-primary mb-4">
            <span class="material-symbols-outlined font-bold">inventory_2</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Total</p>
        <h2 class="font-headline-md text-headline-md font-bold text-primary mt-1"><?= (int)($stats['total'] ?? 0) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #e6a700;">
        <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-4">
            <span class="material-symbols-outlined font-bold">pending</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Pendientes</p>
        <h2 class="font-headline-md text-headline-md font-bold text-amber-600 mt-1"><?= (int)($stats['pendiente'] ?? 0) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #2e7d32;">
        <div class="w-10 h-10 rounded-2xl bg-green-50 flex items-center justify-center text-green-700 mb-4">
            <span class="material-symbols-outlined font-bold">check_circle</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Aprobados</p>
        <h2 class="font-headline-md text-headline-md font-bold text-green-700 mt-1"><?= (int)($stats['aprobado'] ?? 0) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #d32f2f;">
        <div class="w-10 h-10 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 mb-4">
            <span class="material-symbols-outlined font-bold">cancel</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Rechazados</p>
        <h2 class="font-headline-md text-headline-md font-bold text-red-600 mt-1"><?= (int)($stats['rechazado'] ?? 0) ?></h2>
    </div>
</div>

<form method="get" action="admin.php" class="mb-stack-md">
    <input type="hidden" name="accion" value="listar_productos" />
    <input type="hidden" name="orden" value="<?= htmlspecialchars($orden ?? 'id', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="direccion" value="<?= htmlspecialchars($direccion ?? 'DESC', ENT_QUOTES, 'UTF-8') ?>" />
    <input type="hidden" name="pagina" value="1" />
    <div class="flex flex-wrap gap-3 items-end">
        <div class="relative flex-1 min-w-[200px]">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Buscar productos..."
                class="w-full pl-11 pr-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all" />
        </div>
        <select name="estado"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all min-w-[140px]">
            <option value="">Todos los estados</option>
            <option value="pendiente" <?= ($filtro_estado ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="aprobado" <?= ($filtro_estado ?? '') === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
            <option value="rechazado" <?= ($filtro_estado ?? '') === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
            <option value="inactivo" <?= ($filtro_estado ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <select name="categoria"
            class="px-4 py-2.5 rounded-full border border-outline-variant bg-surface-container text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-surface-lowest transition-all min-w-[160px]">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ($filtro_categoria ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="px-6 py-2.5 bg-black text-white rounded-full font-semibold hover:bg-neutral-800 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Filtrar
        </button>
        <?php if (!empty($busqueda) || !empty($filtro_estado) || !empty($filtro_categoria)): ?>
            <a href="admin.php?accion=listar_productos&pagina=1" class="px-4 py-2.5 rounded-full border border-outline-variant text-muted hover:bg-surface-container-low transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">close</span>
                Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (count($productos) === 0): ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl border border-outline-muted p-12 text-center">
        <span class="material-symbols-outlined text-[64px] text-outline/40">inventory_2</span>
        <h2 class="text-lg font-semibold text-muted mt-4">No hay productos registrados</h2>
        <p class="text-sm text-muted/60 mt-1">Los productos aparecerán aquí cuando los productores los registren en la plataforma.</p>
    </div>
<?php else: ?>
    <?php
    $sortUrl = function ($col) use ($orden, $direccion, $busqueda, $filtro_estado, $filtro_categoria, $pagina) {
        $dir = ($orden === $col && $direccion === 'ASC') ? 'DESC' : 'ASC';
        $qs = http_build_query([
            'accion' => 'listar_productos',
            'orden' => $col,
            'direccion' => $dir,
            'pagina' => $pagina ?? 1,
            'busqueda' => $busqueda,
            'estado' => $filtro_estado,
            'categoria' => $filtro_categoria
        ]);
        return "admin.php?$qs";
    };
    ?>
    <div class="bg-surface-lowest botanical-shadow rounded-2xl overflow-hidden border border-outline-muted">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low/50">
                    <tr>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('id') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Código
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('nombre') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Producto
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('categoria') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Categoría
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('productor') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Productor
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-right">
                            <a href="<?= $sortUrl('precio') ?>" class="flex items-center gap-1 justify-end hover:text-primary transition-colors">
                                Precio
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">
                            <a href="<?= $sortUrl('stock') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">
                                Stock
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">
                            <a href="<?= $sortUrl('estado') ?>" class="flex items-center gap-1 justify-center hover:text-primary transition-colors">
                                Estado
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase">
                            <a href="<?= $sortUrl('created_at') ?>" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Publicación
                            </a>
                        </th>
                        <th class="px-6 py-4 font-label-bold text-label-bold text-outline tracking-wider uppercase text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-muted">
                    <?php foreach ($productos as $prod): ?>
                        <?php $estado = $prod['estado_aprobacion'] ?? 'pendiente'; ?>
                        <tr class="cursor-pointer group hover:bg-surface transition-colors" onclick='openDetailsModal(<?= json_encode($prod, JSON_UNESCAPED_UNICODE) ?>)'>
                            <td class="px-6 py-stack-md">
                                <span class="font-label-bold text-label-bold text-on-surface font-mono">#<?= (int)$prod['id'] ?></span>
                            </td>
                            <td class="px-6 py-stack-md">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($prod['imagen'])): ?>
                                        <img src="<?= htmlspecialchars($prod['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            class="w-10 h-10 rounded-lg object-cover border border-outline-variant/30 flex-none" />
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-lg bg-surface-container-low flex items-center justify-center text-muted flex-none">
                                            <span class="material-symbols-outlined text-[20px]">image</span>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php if (!empty($prod['descripcion'])): ?>
                                            <p class="text-body-sm text-secondary truncate max-w-[200px]"><?= htmlspecialchars(mb_substr($prod['descripcion'], 0, 60), ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary"><?= htmlspecialchars($prod['categoria'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary">
                                <?php
                                    $nomProd = trim(($prod['productor_nombre'] ?? '') . ' ' . ($prod['productor_apellido'] ?? ''));
                                    echo htmlspecialchars($nomProd ?: ($prod['nom_productor'] ?? '—'), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                            <td class="px-6 py-stack-md text-right font-bold text-on-surface">$<?= number_format((float)($prod['precio'] ?? 0), 2) ?></td>
                            <td class="px-6 py-stack-md text-center">
                                <?php $stock = (int)($prod['stock'] ?? 0); ?>
                                <span class="font-bold text-sm <?= $stock <= 0 ? 'text-error' : ($stock <= 5 ? 'text-secondary' : 'text-on-surface') ?>">
                                    <?= $stock ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="estado-badge <?= $estado === 'aprobado' ? 'estado-aprobado' : ($estado === 'rechazado' ? 'estado-rechazado' : ($estado === 'inactivo' ? 'estado-inactivo' : 'estado-pendiente')) ?>">
                                    <?= $estado === 'aprobado' ? 'Aprobado' : ($estado === 'pendiente' ? 'Pendiente' : ($estado === 'rechazado' ? 'Rechazado' : 'Inactivo')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary">
                                <?= $prod['created_at'] ? date('d/m/Y', strtotime($prod['created_at'])) : '—' ?>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <div class="flex items-center justify-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button onclick='event.stopPropagation();openDetailsModal(<?= json_encode($prod, JSON_UNESCAPED_UNICODE) ?>)'
                                        class="px-3 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">search</span>
                                        Detalles
                                    </button>
                                    <button onclick='event.stopPropagation();openEditProductModal(<?= json_encode($prod, JSON_UNESCAPED_UNICODE) ?>)'
                                        class="px-3 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        Editar
                                    </button>
                                    <span class="w-px h-6 bg-outline-muted mx-1"></span>
                                    <?php if ($estado !== 'aprobado'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'aprobado')"
                                            class="p-2 rounded-lg hover:bg-primary/90 text-primary hover:text-white transition-all" title="Aprobar">
                                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($estado !== 'rechazado'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'rechazado')"
                                            class="p-2 rounded-lg hover:bg-error-container/40 text-error transition-colors" title="Rechazar">
                                            <span class="material-symbols-outlined text-[20px]">cancel</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($estado !== 'inactivo'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'inactivo')"
                                            class="p-2 rounded-lg hover:bg-surface-container-low text-muted transition-colors" title="Deshabilitar">
                                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($estado === 'inactivo' || $estado === 'rechazado'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoProducto(<?= (int)$prod['id'] ?>, 'pendiente')"
                                            class="p-2 rounded-lg hover:bg-surface-container-low text-muted transition-colors" title="Poner en pendiente">
                                            <span class="material-symbols-outlined text-[20px]">pending</span>
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
            <?php
            $from = ($pagina - 1) * $limite + 1;
            $to = min($pagina * $limite, $total);
            $pageUrl = function ($p) use ($orden, $direccion, $busqueda, $filtro_estado, $filtro_categoria) {
                return 'admin.php?' . http_build_query([
                    'accion' => 'listar_productos',
                    'orden' => $orden,
                    'direccion' => $direccion,
                    'pagina' => $p,
                    'busqueda' => $busqueda,
                    'estado' => $filtro_estado,
                    'categoria' => $filtro_categoria
                ]);
            };
            ?>
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $from ?>–<?= $to ?> de <?= $total ?> productos</p>
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

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="createProductModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary">Nuevo Producto</h3>
                <p class="text-body-sm text-secondary mt-1">Completa los campos para registrar un producto</p>
            </div>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeCreateProductModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="space-y-stack-md" id="createProductForm" enctype="multipart/form-data" onsubmit="return handleCreateProduct(event)">
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre <span class="text-error">*</span></label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="cpNombre" required maxlength="100" type="text" placeholder="Ej: Café orgánico premium" />
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Máximo 100 caracteres</p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Descripción</label>
                <textarea class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white min-h-[80px] resize-y" id="cpDescripcion" maxlength="500" placeholder="Breve descripción del producto"></textarea>
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Máximo 500 caracteres</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Precio <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-semibold">$</span>
                        <input class="w-full pl-8 pr-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="cpPrecio" required min="0.01" max="999999.99" step="0.01" type="number" placeholder="0.00" onkeydown="return event.key==='Backspace'||event.key==='Tab'||event.key==='ArrowLeft'||event.key==='ArrowRight'||event.key==='ArrowUp'||event.key==='ArrowDown'||event.key==='Home'||event.key==='End'||event.key==='Delete'||event.key==='Enter'||event.key===','||event.key==='.'||(event.key>='0'&&event.key<='9')||event.ctrlKey||event.metaKey" />
                    </div>
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Stock <span class="text-error">*</span></label>
                    <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="cpStock" required min="0" max="999999" step="1" type="number" placeholder="0" onkeydown="return event.key==='Backspace'||event.key==='Tab'||event.key==='ArrowLeft'||event.key==='ArrowRight'||event.key==='ArrowUp'||event.key==='ArrowDown'||event.key==='Home'||event.key==='End'||event.key==='Delete'||event.key==='Enter'||(event.key>='0'&&event.key<='9')||event.ctrlKey||event.metaKey" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Categoría</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="cpCategoria">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Estado</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="cpEstado">
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Imagen</label>
                <div class="flex items-center gap-4">
                    <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border border-outline-variant border-dashed bg-surface-container-low cursor-pointer hover:bg-surface-container transition-colors" id="cpImageLabel">
                        <span class="material-symbols-outlined text-outline">add_photo_alternate</span>
                        <span class="text-sm text-on-surface-variant" id="cpImageText">Seleccionar archivo</span>
                        <input class="hidden" id="cpImagen" type="file" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewProductImage(this)" />
                    </label>
                    <div class="w-16 h-16 rounded-xl border border-outline-variant bg-surface-container-low overflow-hidden flex-none flex items-center justify-center text-outline hidden" id="cpImagePreview">
                        <img class="w-full h-full object-cover" id="cpImagePreviewImg" />
                    </div>
                </div>
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Formatos: JPG, PNG, WebP, GIF. Máximo 2 MB.</p>
            </div>
            <div id="cpError" class="hidden flex items-center gap-2 p-3 rounded-xl bg-error-container/20 text-error text-sm">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span id="cpErrorText"></span>
            </div>
            <button class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98]" type="submit">
                <span class="material-symbols-outlined text-[20px] inline-block mr-2">add_circle</span>
                CREAR PRODUCTO
            </button>
        </form>
    </div>
</div>

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="editProductModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary">Editar Producto</h3>
                <p class="text-body-sm text-secondary mt-1">Actualiza los campos del producto</p>
            </div>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeEditProductModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="space-y-stack-md" id="editProductForm" enctype="multipart/form-data" onsubmit="return handleEditProduct(event)">
            <input id="epId" type="hidden" />
            <input id="epImagenActual" type="hidden" />
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Código</label>
                <div class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant font-mono font-bold text-sm" id="epCodigoDisplay">#—</div>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre <span class="text-error">*</span></label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="epNombre" required maxlength="100" type="text" placeholder="Ej: Café orgánico premium" />
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Máximo 100 caracteres</p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Descripción</label>
                <textarea class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white min-h-[80px] resize-y" id="epDescripcion" maxlength="500" placeholder="Breve descripción del producto"></textarea>
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Máximo 500 caracteres</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Precio <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-semibold">$</span>
                        <input class="w-full pl-8 pr-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="epPrecio" required min="0.01" max="999999.99" step="0.01" type="number" placeholder="0.00" onkeydown="return event.key==='Backspace'||event.key==='Tab'||event.key==='ArrowLeft'||event.key==='ArrowRight'||event.key==='ArrowUp'||event.key==='ArrowDown'||event.key==='Home'||event.key==='End'||event.key==='Delete'||event.key==='Enter'||event.key===','||event.key==='.'||(event.key>='0'&&event.key<='9')||event.ctrlKey||event.metaKey" />
                    </div>
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Stock <span class="text-error">*</span></label>
                    <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="epStock" required min="0" max="999999" step="1" type="number" placeholder="0" onkeydown="return event.key==='Backspace'||event.key==='Tab'||event.key==='ArrowLeft'||event.key==='ArrowRight'||event.key==='ArrowUp'||event.key==='ArrowDown'||event.key==='Home'||event.key==='End'||event.key==='Delete'||event.key==='Enter'||(event.key>='0'&&event.key<='9')||event.ctrlKey||event.metaKey" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md">
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Categoría</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="epCategoria">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Estado</label>
                    <select class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="epEstado">
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Imagen</label>
                <div class="flex items-center gap-4">
                    <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border border-outline-variant border-dashed bg-surface-container-low cursor-pointer hover:bg-surface-container transition-colors" id="epImageLabel">
                        <span class="material-symbols-outlined text-outline">add_photo_alternate</span>
                        <span class="text-sm text-on-surface-variant" id="epImageText">Seleccionar archivo</span>
                        <input class="hidden" id="epImagen" type="file" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewEditImage(this)" />
                    </label>
                    <div class="w-16 h-16 rounded-xl border border-outline-variant bg-surface-container-low overflow-hidden flex-none flex items-center justify-center text-outline hidden" id="epImagePreview">
                        <img class="w-full h-full object-cover" id="epImagePreviewImg" />
                    </div>
                </div>
                <p class="text-xs text-on-surface-variant mt-1 ml-1">Formatos: JPG, PNG, WebP, GIF. Máximo 2 MB. Dejar vacío para mantener la imagen actual.</p>
            </div>
            <div id="epError" class="hidden flex items-center gap-2 p-3 rounded-xl bg-error-container/20 text-error text-sm">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span id="epErrorText"></span>
            </div>
            <button class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98]" type="submit">
                <span class="material-symbols-outlined text-[20px] inline-block mr-2">save</span>
                GUARDAR CAMBIOS
            </button>
        </form>
    </div>
</div>

<div class="fixed inset-0 bg-black/40 z-[300] hidden" id="detailsOverlay" onclick="closeDetailsModal()"></div>
<div class="fixed top-0 right-0 h-full w-full max-w-lg bg-white z-[310] shadow-2xl translate-x-full transition-transform duration-300 ease-out overflow-y-auto" id="detailsPanel">
    <div class="sticky top-0 bg-white border-b border-outline-variant/50 z-10 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-surface-container-low overflow-hidden flex items-center justify-center text-outline flex-none border border-outline-variant/30" id="detailsImageBox">
                <span class="material-symbols-outlined text-[24px]">image</span>
                <img class="w-full h-full object-cover hidden" id="detailsImage" />
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary" id="detailsName"></h3>
                <p class="text-body-sm text-secondary mt-0.5" id="detailsCategory"></p>
            </div>
        </div>
        <button class="p-2 hover:bg-surface-container-low rounded-full flex-none" onclick="closeDetailsModal()"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Código</p>
                <p class="font-label-bold text-label-bold text-on-surface font-mono" id="detailsCode"></p>
            </div>
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Estado</p>
                <p id="detailsStatus"></p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-primary/5 rounded-2xl p-4 border border-primary/10">
                <p class="font-label-sm text-label-sm text-primary uppercase tracking-wider mb-1">Precio</p>
                <p class="font-headline-md text-headline-md font-bold text-primary" id="detailsPrice"></p>
            </div>
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Stock</p>
                <p class="font-headline-md text-headline-md font-bold text-on-surface" id="detailsStock"></p>
            </div>
        </div>
        <div class="bg-surface-container-low/60 rounded-2xl p-4">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Descripción</p>
            <p class="text-body-md text-on-surface leading-relaxed" id="detailsDescription">Sin descripción</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Productor</p>
                <p class="font-label-bold text-label-bold text-on-surface" id="detailsProducer"></p>
            </div>
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Publicación</p>
                <p class="font-label-bold text-label-bold text-on-surface" id="detailsDate"></p>
            </div>
        </div>
        <div class="bg-error-container/20 rounded-2xl p-4 hidden border border-error/20" id="detailsReasonBox">
            <p class="font-label-sm text-label-sm text-error uppercase tracking-wider mb-1">Motivo de rechazo</p>
            <p class="text-body-sm text-error" id="detailsReason"></p>
        </div>
        <div class="border-t border-outline-variant/30 pt-4 flex gap-3" id="detailsActions">
            <button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsModal()" type="button">Cerrar</button>
            <button class="flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition-all" id="detailsActionBtn" type="button"></button>
        </div>
    </div>
</div>

<script>
function previewProductImage(input) {
    const label = document.getElementById('cpImageLabel');
    const text = document.getElementById('cpImageText');
    const preview = document.getElementById('cpImagePreview');
    const img = document.getElementById('cpImagePreviewImg');
    if (input.files && input.files[0]) {
        text.textContent = input.files[0].name;
        preview.classList.remove('hidden');
        const reader = new FileReader();
        reader.onload = function (e) { img.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    } else {
        text.textContent = 'Seleccionar archivo';
        preview.classList.add('hidden');
    }
}
function openCreateProductModal() {
    document.getElementById('createProductForm').reset();
    document.getElementById('cpImagePreview').classList.add('hidden');
    document.getElementById('cpImageText').textContent = 'Seleccionar archivo';
    document.getElementById('createProductModal').classList.remove('hidden');
    document.getElementById('createProductModal').classList.add('flex');
    document.getElementById('cpError').classList.add('hidden');
}
function closeCreateProductModal() {
    document.getElementById('createProductModal').classList.add('hidden');
    document.getElementById('createProductModal').classList.remove('flex');
}
async function handleCreateProduct(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const errBox = document.getElementById('cpError');
    const errText = document.getElementById('cpErrorText');
    errBox.classList.add('hidden');

    const nombre = document.getElementById('cpNombre').value.trim();
    const descripcion = document.getElementById('cpDescripcion').value.trim();
    const precio = document.getElementById('cpPrecio').value;
    const stock = document.getElementById('cpStock').value;
    const idCategoria = document.getElementById('cpCategoria').value;
    const estado = document.getElementById('cpEstado').value;
    const fileInput = document.getElementById('cpImagen');

    if (!nombre) { showCpError('El nombre del producto es obligatorio'); return; }
    if (nombre.length > 100) { showCpError('El nombre no puede exceder 100 caracteres'); return; }
    if (!precio || parseFloat(precio) <= 0) { showCpError('El precio debe ser mayor a 0'); return; }
    if (parseFloat(precio) > 999999.99) { showCpError('El precio no puede exceder 999,999.99'); return; }
    if (stock === '' || parseInt(stock) < 0) { showCpError('El stock no puede ser negativo'); return; }
    if (parseInt(stock) > 999999) { showCpError('El stock no puede exceder 999,999'); return; }
    if (fileInput.files && fileInput.files[0] && fileInput.files[0].size > 2 * 1024 * 1024) {
        showCpError('La imagen no puede superar los 2 MB'); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2 animate-spin">refresh</span> GUARDANDO...';

    const body = new FormData();
    body.append('accion', 'crear_producto');
    body.append('nombre', nombre);
    body.append('descripcion', descripcion);
    body.append('precio', precio);
    body.append('stock', stock);
    body.append('id_categoria', idCategoria);
    body.append('estado', estado);
    if (fileInput.files && fileInput.files[0]) {
        body.append('imagen', fileInput.files[0]);
    }
    try {
        const res = await fetch('admin.php', { method: 'POST', body });
        const data = await res.json();
        if (data.status === 'success') {
            storeNotification('Producto creado correctamente', 'success');
            location.reload();
        } else {
            showCpError(data.message || 'Error al crear el producto');
            btn.disabled = false;
            btn.innerHTML = 'CREAR PRODUCTO';
        }
    } catch {
        showCpError('Error de conexión');
        btn.disabled = false;
        btn.innerHTML = 'CREAR PRODUCTO';
    }
}
function showCpError(msg) {
    document.getElementById('cpErrorText').textContent = msg;
    document.getElementById('cpError').classList.remove('hidden');
}
function previewEditImage(input) {
    const text = document.getElementById('epImageText');
    const preview = document.getElementById('epImagePreview');
    const img = document.getElementById('epImagePreviewImg');
    if (input.files && input.files[0]) {
        text.textContent = input.files[0].name;
        preview.classList.remove('hidden');
        const reader = new FileReader();
        reader.onload = function (e) { img.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    } else {
        text.textContent = 'Seleccionar archivo';
        preview.classList.add('hidden');
    }
}
function openEditProductModal(prod) {
    document.getElementById('editProductForm').reset();
    document.getElementById('epImagePreview').classList.add('hidden');
    document.getElementById('epImageText').textContent = 'Seleccionar archivo';
    document.getElementById('epId').value = prod.id;
    document.getElementById('epImagenActual').value = prod.imagen || '';
    document.getElementById('epCodigoDisplay').textContent = '#' + prod.id;
    document.getElementById('epNombre').value = prod.nombre || '';
    document.getElementById('epDescripcion').value = prod.descripcion || '';
    document.getElementById('epPrecio').value = prod.precio || '';
    document.getElementById('epStock').value = prod.stock ?? prod.cantidad ?? 0;
    document.getElementById('epCategoria').value = prod.id_categoria || '';
    const est = prod.estado_aprobacion || 'pendiente';
    if (['pendiente','aprobado','rechazado','inactivo'].includes(est)) {
        document.getElementById('epEstado').value = est;
    }
    document.getElementById('epError').classList.add('hidden');
    document.getElementById('editProductModal').classList.remove('hidden');
    document.getElementById('editProductModal').classList.add('flex');
}
function closeEditProductModal() {
    document.getElementById('editProductModal').classList.add('hidden');
    document.getElementById('editProductModal').classList.remove('flex');
}
async function handleEditProduct(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const errBox = document.getElementById('epError');
    const errText = document.getElementById('epErrorText');
    errBox.classList.add('hidden');

    const id = document.getElementById('epId').value;
    const nombre = document.getElementById('epNombre').value.trim();
    const descripcion = document.getElementById('epDescripcion').value.trim();
    const precio = document.getElementById('epPrecio').value;
    const stock = document.getElementById('epStock').value;
    const idCategoria = document.getElementById('epCategoria').value;
    const estado = document.getElementById('epEstado').value;
    const fileInput = document.getElementById('epImagen');

    if (!nombre) { showEpError('El nombre del producto es obligatorio'); return; }
    if (nombre.length > 100) { showEpError('El nombre no puede exceder 100 caracteres'); return; }
    if (!precio || parseFloat(precio) <= 0) { showEpError('El precio debe ser mayor a 0'); return; }
    if (parseFloat(precio) > 999999.99) { showEpError('El precio no puede exceder 999,999.99'); return; }
    if (stock === '' || parseInt(stock) < 0) { showEpError('El stock no puede ser negativo'); return; }
    if (parseInt(stock) > 999999) { showEpError('El stock no puede exceder 999,999'); return; }
    if (fileInput.files && fileInput.files[0] && fileInput.files[0].size > 2 * 1024 * 1024) {
        showEpError('La imagen no puede superar los 2 MB'); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2 animate-spin">refresh</span> GUARDANDO...';

    const body = new FormData();
    body.append('accion', 'editar_producto');
    body.append('id', id);
    body.append('nombre', nombre);
    body.append('descripcion', descripcion);
    body.append('precio', precio);
    body.append('stock', stock);
    body.append('id_categoria', idCategoria);
    body.append('estado', estado);
    body.append('imagen_actual', document.getElementById('epImagenActual').value);
    if (fileInput.files && fileInput.files[0]) {
        body.append('imagen', fileInput.files[0]);
    }
    try {
        const res = await fetch('admin.php', { method: 'POST', body });
        const data = await res.json();
        if (data.status === 'success') {
            storeNotification('Producto actualizado correctamente', 'success');
            location.reload();
        } else {
            showEpError(data.message || 'Error al editar el producto');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2">save</span> GUARDAR CAMBIOS';
        }
    } catch {
        showEpError('Error de conexión');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2">save</span> GUARDAR CAMBIOS';
    }
}
function showEpError(msg) {
    document.getElementById('epErrorText').textContent = msg;
    document.getElementById('epError').classList.remove('hidden');
}
function closeDetailsModal() {
    document.getElementById('detailsPanel').classList.add('translate-x-full');
    setTimeout(() => document.getElementById('detailsOverlay').classList.add('hidden'), 300);
}
function openDetailsModal(prod) {
    document.getElementById('detailsName').textContent = prod.nombre || '—';
    document.getElementById('detailsCategory').textContent = prod.categoria || 'Sin categoría';
    document.getElementById('detailsCode').textContent = '#' + prod.id;
    document.getElementById('detailsPrice').textContent = '$' + (parseFloat(prod.precio) || 0).toFixed(2);
    document.getElementById('detailsStock').textContent = prod.stock ?? prod.cantidad ?? 0;
    document.getElementById('detailsDescription').textContent = prod.descripcion || 'Sin descripción';
    const prodName = ((prod.productor_nombre || '') + ' ' + (prod.productor_apellido || '')).trim();
    document.getElementById('detailsProducer').textContent = prodName || prod.nom_productor || '—';
    document.getElementById('detailsDate').textContent = prod.created_at ? new Date(prod.created_at).toLocaleDateString('es-PA', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';
    const imgBox = document.getElementById('detailsImageBox');
    const img = document.getElementById('detailsImage');
    if (prod.imagen) {
        img.src = prod.imagen;
        img.classList.remove('hidden');
        imgBox.querySelector('span')?.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        imgBox.querySelector('span')?.classList.remove('hidden');
    }
    const est = prod.estado_aprobacion || 'pendiente';
    const badge = document.getElementById('detailsStatus');
    const labels = { aprobado: 'Aprobado', pendiente: 'Pendiente', rechazado: 'Rechazado', inactivo: 'Inactivo' };
    const classes = { aprobado: 'estado-aprobado', pendiente: 'estado-pendiente', rechazado: 'estado-rechazado', inactivo: 'estado-inactivo' };
    badge.className = 'estado-badge ' + (classes[est] || 'estado-pendiente');
    badge.textContent = labels[est] || 'Pendiente';
    const reasonBox = document.getElementById('detailsReasonBox');
    const reasonText = document.getElementById('detailsReason');
    if (est === 'rechazado' && prod.motivo_rechazo) {
        reasonBox.classList.remove('hidden');
        reasonText.textContent = prod.motivo_rechazo;
    } else {
        reasonBox.classList.add('hidden');
    }
    const actionBtn = document.getElementById('detailsActionBtn');
    if (est === 'pendiente') {
        actionBtn.textContent = 'Aprobar producto';
        actionBtn.onclick = () => { closeDetailsModal(); showConfirmModal('aprobar', prod.id); };
        actionBtn.classList.remove('hidden');
    } else if (est === 'inactivo') {
        actionBtn.textContent = 'Restaurar producto';
        actionBtn.onclick = () => { closeDetailsModal(); showConfirmModal('restaurar', prod.id); };
        actionBtn.classList.remove('hidden');
    } else {
        actionBtn.classList.add('hidden');
    }
    document.getElementById('detailsOverlay').classList.remove('hidden');
    document.getElementById('detailsPanel').classList.remove('translate-x-full');
}
</script>
