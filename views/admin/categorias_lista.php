<style>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
input[type=number] { -moz-appearance: textfield; }
</style>
<div class="flex items-end justify-between mb-stack-lg">
    <div>
        <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Taxonomía</p>
        <h2 class="font-headline-lg text-headline-lg text-primary">Gesti&oacute;n de Categor&iacute;as</h2>
    </div>
    <button onclick="openCreateCategoriaModal()" class="px-5 py-2.5 bg-primary text-white rounded-full font-semibold hover:bg-primary-dark transition-all flex items-center gap-2 shadow-sm active:scale-[0.97]">
        <span class="material-symbols-outlined text-[20px]">add</span>
        Agregar Categor&iacute;a
    </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-gutter mb-stack-lg">
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #e6a700;">
        <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-4">
            <span class="material-symbols-outlined font-bold">category</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Total</p>
        <h2 class="font-headline-md text-headline-md font-bold text-amber-600 mt-1"><?= (int)($stats['total'] ?? 0) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #0288d1;">
        <div class="w-10 h-10 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-700 mb-4">
            <span class="material-symbols-outlined font-bold">check_circle</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Activas</p>
        <h2 class="font-headline-md text-headline-md font-bold text-sky-700 mt-1"><?= (int)($stats['activo'] ?? 0) ?></h2>
    </div>
    <div class="bg-surface-lowest rounded-[24px] p-6 botanical-shadow" style="border-left:4px solid #f59e0b;">
        <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 mb-4">
            <span class="material-symbols-outlined font-bold">visibility_off</span>
        </div>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Inactivas</p>
        <h2 class="font-headline-md text-headline-md font-bold text-amber-600 mt-1"><?= (int)($stats['inactivo'] ?? 0) ?></h2>
    </div>
</div>

<?php if (count($categorias) === 0 && $busqueda === ''): ?>
    <div class="bg-white rounded-2xl border border-outline-muted botanical-shadow p-8 text-center">
        <span class="material-symbols-outlined text-[48px] text-outline mb-4">category</span>
        <p class="text-on-surface-variant text-lg mb-2">No hay categor&iacute;as registradas</p>
        <p class="text-outline text-sm">Crea la primera categor&iacute;a usando el bot&oacute;n &quot;Agregar Categor&iacute;a&quot;</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-2xl border border-outline-muted botanical-shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-outline-muted bg-surface-low/50">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1 max-w-sm">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline z-10 text-[18px]">search</span>
                    <form method="GET" action="admin.php" class="m-0">
                        <input type="hidden" name="accion" value="listar_categorias" />
                        <input type="search" name="busqueda" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Buscar categor&iacute;as..."
                            class="w-full h-10 pl-10 pr-4 rounded-xl border border-outline-variant bg-white focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm transition-all" />
                    </form>
                </div>
                <?php if ($busqueda !== ''): ?>
                    <a href="admin.php?accion=listar_categorias" class="px-4 py-2 rounded-xl bg-black text-white text-sm font-semibold hover:bg-black/80 transition-all flex items-center gap-1.5 w-fit">
                        <span class="material-symbols-outlined text-[16px]">close</span> Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-outline-muted bg-surface-low/30">
                        <th class="px-6 py-4 text-left">
                            <a href="admin.php?accion=listar_categorias&orden=id&direccion=<?= $orden === 'id' && $direccion === 'ASC' ? 'DESC' : 'ASC' ?>&busqueda=<?= urlencode($busqueda) ?>" class="font-label-sm text-label-sm text-outline uppercase tracking-wider hover:text-primary transition-colors flex items-center gap-1<?= $orden === 'id' ? ' text-primary' : '' ?>">
                                ID
                                <?php if ($orden === 'id'): ?><span class="material-symbols-outlined text-[14px]"><?= $direccion === 'ASC' ? 'expand_less' : 'expand_more' ?></span><?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-left">
                            <a href="admin.php?accion=listar_categorias&orden=nombre&direccion=<?= $orden === 'nombre' && $direccion === 'ASC' ? 'DESC' : 'ASC' ?>&busqueda=<?= urlencode($busqueda) ?>" class="font-label-sm text-label-sm text-outline uppercase tracking-wider hover:text-primary transition-colors flex items-center gap-1<?= $orden === 'nombre' ? ' text-primary' : '' ?>">
                                Nombre
                                <?php if ($orden === 'nombre'): ?><span class="material-symbols-outlined text-[14px]"><?= $direccion === 'ASC' ? 'expand_less' : 'expand_more' ?></span><?php endif; ?>
                            </a>
                        </th>
                        <th class="px-6 py-4 text-left font-label-sm text-label-sm text-outline uppercase tracking-wider">Descripci&oacute;n</th>
                        <th class="px-6 py-4 text-center font-label-sm text-label-sm text-outline uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-center font-label-sm text-label-sm text-outline uppercase tracking-wider">Productos</th>
                        <th class="px-6 py-4 text-center font-label-sm text-label-sm text-outline uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $cat):
                        $catEstado = $cat['estado'] ?? 'activo';
                    ?>
                        <tr class="cursor-pointer group hover:bg-surface transition-colors border-b border-outline-variant/30" onclick='openDetailsCategoria(<?= json_encode($cat, JSON_UNESCAPED_UNICODE) ?>)'>
                            <td class="px-6 py-stack-md">
                                <span class="font-label-bold text-label-bold text-on-surface font-mono">#<?= (int)$cat['id'] ?></span>
                            </td>
                            <td class="px-6 py-stack-md">
                                <span class="font-label-bold text-label-bold text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars($cat['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="px-6 py-stack-md text-body-sm text-secondary max-w-[200px] truncate">
                                <?= htmlspecialchars($cat['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?: '<span class="text-outline">&mdash;</span>' ?>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="estado-badge <?= $catEstado === 'activo' ? 'estado-aprobado' : 'estado-inactivo' ?>">
                                    <?= $catEstado === 'activo' ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold <?= ((int)($cat['total_productos'] ?? 0)) > 0 ? 'bg-primary/10 text-primary' : 'bg-surface-container text-muted' ?>">
                                    <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                                    <?= (int)($cat['total_productos'] ?? 0) ?>
                                </span>
                            </td>
                            <td class="px-6 py-stack-md text-center">
                                <div class="flex items-center justify-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button onclick='event.stopPropagation();openDetailsCategoria(<?= json_encode($cat, JSON_UNESCAPED_UNICODE) ?>)'
                                        class="px-3 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">search</span>
                                        Detalles
                                    </button>
                                    <button onclick='event.stopPropagation();openEditCategoriaModal(<?= json_encode($cat, JSON_UNESCAPED_UNICODE) ?>)'
                                        class="px-3 py-1.5 rounded-lg bg-surface-container text-secondary font-label-bold text-label-sm hover:bg-primary hover:text-white transition-all inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        Editar
                                    </button>
                                    <span class="w-px h-6 bg-outline-muted mx-1"></span>
                                    <?php if ($catEstado === 'activo'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoCategoria(<?= (int)$cat['id'] ?>, 'inactivo')"
                                            class="p-2 rounded-lg hover:bg-surface-container-low text-muted transition-colors" title="Deshabilitar">
                                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($catEstado === 'inactivo'): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoCategoria(<?= (int)$cat['id'] ?>, 'activo')"
                                            class="p-2 rounded-lg hover:bg-primary/10 text-primary transition-colors" title="Activar">
                                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ((int)($cat['total_productos'] ?? 0) === 0): ?>
                                        <button onclick="event.stopPropagation();cambiarEstadoCategoria(<?= (int)$cat['id'] ?>, 'eliminar')"
                                            class="p-2 rounded-lg hover:bg-error-container/20 text-error transition-colors" title="Eliminar">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
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
            $pageUrl = function ($p) use ($orden, $direccion, $busqueda) {
                return 'admin.php?' . http_build_query([
                    'accion' => 'listar_categorias',
                    'orden' => $orden,
                    'direccion' => $direccion,
                    'pagina' => $p,
                    'busqueda' => $busqueda
                ]);
            };
            ?>
            <p class="text-label-sm text-outline font-medium">Mostrando <?= $from ?>&ndash;<?= $to ?> de <?= $total ?> categor&iacute;as</p>
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
                    <?php if ($start > 2): ?><span class="text-outline px-1">&middot;&middot;&middot;</span><?php endif; ?>
                <?php endif; ?>
                <?php for ($p = $start; $p <= $end; $p++): ?>
                    <a href="<?= $pageUrl($p) ?>"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-label-sm font-bold transition-colors <?= $p === $pagina ? 'bg-primary text-white shadow-sm' : 'border border-outline-muted text-secondary hover:bg-surface-container hover:text-primary' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($end < $totalPaginas): ?>
                    <?php if ($end < $totalPaginas - 1): ?><span class="text-outline px-1">&middot;&middot;&middot;</span><?php endif; ?>
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

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="createCategoriaModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary">Nueva Categor&iacute;a</h3>
                <p class="text-body-sm text-secondary mt-1">Registra una nueva categor&iacute;a para productos</p>
            </div>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeCreateCategoriaModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="space-y-stack-md" id="createCategoriaForm" onsubmit="return handleCreateCategoria(event)">
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre <span class="text-error">*</span></label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="ccNombre" required maxlength="20" type="text" placeholder="Ej: Frutas tropicales" />
                <p class="text-xs text-on-surface-variant mt-1 ml-1">M&aacute;ximo 20 caracteres</p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Descripci&oacute;n</label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="ccDescripcion" maxlength="50" type="text" placeholder="Breve descripci&oacute;n de la categor&iacute;a" />
                <p class="text-xs text-on-surface-variant mt-1 ml-1">M&aacute;ximo 50 caracteres</p>
            </div>
            <div id="ccError" class="hidden flex items-center gap-2 p-3 rounded-xl bg-error-container/20 text-error text-sm">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span id="ccErrorText"></span>
            </div>
            <button class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98]" type="submit">
                <span class="material-symbols-outlined text-[20px] inline-block mr-2">add_circle</span>
                CREAR CATEGOR&Iacute;A
            </button>
        </form>
    </div>
</div>

<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="editCategoriaModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary">Editar Categor&iacute;a</h3>
                <p class="text-body-sm text-secondary mt-1">Actualiza los datos de la categor&iacute;a</p>
            </div>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeEditCategoriaModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="space-y-stack-md" id="editCategoriaForm" onsubmit="return handleEditCategoria(event)">
            <input id="ecId" type="hidden" />
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">C&oacute;digo</label>
                <div class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-low text-on-surface-variant font-mono font-bold text-sm" id="ecCodigoDisplay">#&mdash;</div>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Nombre <span class="text-error">*</span></label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="ecNombre" required maxlength="20" type="text" placeholder="Ej: Frutas tropicales" />
                <p class="text-xs text-on-surface-variant mt-1 ml-1">M&aacute;ximo 20 caracteres</p>
            </div>
            <div>
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1 ml-1">Descripci&oacute;n</label>
                <input class="w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white" id="ecDescripcion" maxlength="50" type="text" placeholder="Breve descripci&oacute;n de la categor&iacute;a" />
                <p class="text-xs text-on-surface-variant mt-1 ml-1">M&aacute;ximo 50 caracteres</p>
            </div>
            <div id="ecError" class="hidden flex items-center gap-2 p-3 rounded-xl bg-error-container/20 text-error text-sm">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span id="ecErrorText"></span>
            </div>
            <button class="w-full bg-primary text-white py-4 rounded-xl font-label-bold text-label-bold hover:bg-primary-dark transition-all active:scale-[0.98]" type="submit">
                <span class="material-symbols-outlined text-[20px] inline-block mr-2">save</span>
                GUARDAR CAMBIOS
            </button>
        </form>
    </div>
</div>

<div class="fixed inset-0 bg-black/40 z-[300] hidden" id="detailsCategoriaOverlay" onclick="closeDetailsCategoria()"></div>
<div class="fixed top-0 right-0 h-full w-full max-w-lg bg-white z-[310] shadow-2xl translate-x-full transition-transform duration-300 ease-out overflow-y-auto" id="detailsCategoriaPanel">
    <div class="sticky top-0 bg-white border-b border-outline-variant/50 z-10 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-none">
                <span class="material-symbols-outlined text-[24px]">category</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary" id="dcNombre"></h3>
                <p class="text-body-sm text-secondary mt-0.5" id="dcCodigo"></p>
            </div>
        </div>
        <button class="p-2 hover:bg-surface-container-low rounded-full flex-none" onclick="closeDetailsCategoria()"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="p-6 space-y-5">
        <div class="bg-surface-container-low/60 rounded-2xl p-4">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Descripci&oacute;n</p>
            <p class="text-body-md text-on-surface leading-relaxed" id="dcDescripcion">Sin descripci&oacute;n</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-primary/5 rounded-2xl p-4 border border-primary/10">
                <p class="font-label-sm text-label-sm text-primary uppercase tracking-wider mb-1">Productos</p>
                <p class="font-headline-md text-headline-md font-bold text-primary" id="dcProductos">0</p>
            </div>
            <div class="bg-surface-container-low/60 rounded-2xl p-4">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">C&oacute;digo</p>
                <p class="font-label-bold text-label-bold text-on-surface font-mono" id="dcId">#&mdash;</p>
            </div>
        </div>
        <div class="bg-surface-container-low/60 rounded-2xl p-4" id="dcEstadoBox">
            <p class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1">Estado</p>
            <p id="dcEstado"></p>
        </div>
        <div class="border-t border-outline-variant/30 pt-4 flex gap-3">
            <button class="flex-1 px-4 py-3 rounded-xl border border-outline-variant text-on-surface font-semibold hover:bg-surface-container-low transition-all" onclick="closeDetailsCategoria()" type="button">Cerrar</button>
            <button class="flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition-all" id="dcActionBtn" type="button"></button>
        </div>
    </div>
</div>

<script>
function openCreateCategoriaModal() {
    document.getElementById('createCategoriaForm').reset();
    document.getElementById('ccError').classList.add('hidden');
    document.getElementById('createCategoriaModal').classList.remove('hidden');
    document.getElementById('createCategoriaModal').classList.add('flex');
}
function closeCreateCategoriaModal() {
    document.getElementById('createCategoriaModal').classList.add('hidden');
    document.getElementById('createCategoriaModal').classList.remove('flex');
}
async function handleCreateCategoria(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const errBox = document.getElementById('ccError');
    const errText = document.getElementById('ccErrorText');
    errBox.classList.add('hidden');
    const nombre = document.getElementById('ccNombre').value.trim();
    const descripcion = document.getElementById('ccDescripcion').value.trim();
    if (!nombre) { showCcError('El nombre de la categor\u00eda es obligatorio'); return; }
    if (nombre.length > 20) { showCcError('El nombre no puede exceder 20 caracteres'); return; }
    if (descripcion.length > 50) { showCcError('La descripci\u00f3n no puede exceder 50 caracteres'); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2 animate-spin">refresh</span> GUARDANDO...';
    const body = new URLSearchParams();
    body.append('accion', 'crear_categoria');
    body.append('nombre', nombre);
    body.append('descripcion', descripcion);
    try {
        const res = await fetch('admin.php', { method: 'POST', body });
        const data = await res.json();
        if (data.status === 'success') {
            storeNotification('Categor\u00eda creada correctamente', 'success');
            location.reload();
        } else {
            showCcError(data.message || 'Error al crear la categor\u00eda');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2">add_circle</span> CREAR CATEGOR\u00cdA';
        }
    } catch {
        showCcError('Error de conexi\u00f3n');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2">add_circle</span> CREAR CATEGOR\u00cdA';
    }
}
function showCcError(msg) {
    document.getElementById('ccErrorText').textContent = msg;
    document.getElementById('ccError').classList.remove('hidden');
}

function openEditCategoriaModal(cat) {
    document.getElementById('editCategoriaForm').reset();
    document.getElementById('ecId').value = cat.id;
    document.getElementById('ecCodigoDisplay').textContent = '#' + cat.id;
    document.getElementById('ecNombre').value = cat.nombre || '';
    document.getElementById('ecDescripcion').value = cat.descripcion || '';
    document.getElementById('ecError').classList.add('hidden');
    document.getElementById('editCategoriaModal').classList.remove('hidden');
    document.getElementById('editCategoriaModal').classList.add('flex');
}
function closeEditCategoriaModal() {
    document.getElementById('editCategoriaModal').classList.add('hidden');
    document.getElementById('editCategoriaModal').classList.remove('flex');
}
async function handleEditCategoria(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const errBox = document.getElementById('ecError');
    const errText = document.getElementById('ecErrorText');
    errBox.classList.add('hidden');
    const id = document.getElementById('ecId').value;
    const nombre = document.getElementById('ecNombre').value.trim();
    const descripcion = document.getElementById('ecDescripcion').value.trim();
    if (!nombre) { showEcError('El nombre de la categor\u00eda es obligatorio'); return; }
    if (nombre.length > 20) { showEcError('El nombre no puede exceder 20 caracteres'); return; }
    if (descripcion.length > 50) { showEcError('La descripci\u00f3n no puede exceder 50 caracteres'); return; }
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2 animate-spin">refresh</span> GUARDANDO...';
    const body = new URLSearchParams();
    body.append('accion', 'editar_categoria');
    body.append('id', id);
    body.append('nombre', nombre);
    body.append('descripcion', descripcion);
    try {
        const res = await fetch('admin.php', { method: 'POST', body });
        const data = await res.json();
        if (data.status === 'success') {
            storeNotification('Categor\u00eda actualizada correctamente', 'success');
            location.reload();
        } else {
            showEcError(data.message || 'Error al editar la categor\u00eda');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2">save</span> GUARDAR CAMBIOS';
        }
    } catch {
        showEcError('Error de conexi\u00f3n');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[20px] inline-block mr-2">save</span> GUARDAR CAMBIOS';
    }
}
function showEcError(msg) {
    document.getElementById('ecErrorText').textContent = msg;
    document.getElementById('ecError').classList.remove('hidden');
}

function cambiarEstadoCategoria(id, accion) {
    if (accion === 'eliminar') {
        openConfirmModal(
            'Eliminar categor\u00eda',
            'Esta acci\u00f3n no se puede deshacer.',
            'delete',
            'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600',
            'ELIMINAR',
            'flex-1 px-4 py-3 rounded-xl bg-red-600 text-white font-semibold transition-all hover:bg-red-700',
            function () {
                closeConfirmModal();
                ejecutarCategoriaAction(id, 'eliminar');
            }
        );
    } else if (accion === 'inactivo') {
        openConfirmModal(
            'Deshabilitar categor\u00eda',
            'La categor\u00eda quedar\u00e1 oculta para nuevos productos.',
            'visibility_off',
            'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-surface-container text-muted',
            'DESHABILITAR',
            'flex-1 px-4 py-3 rounded-xl bg-neutral-700 text-white font-semibold transition-all hover:bg-neutral-800',
            function () {
                closeConfirmModal();
                ejecutarCategoriaAction(id, 'inactivo');
            }
        );
    } else if (accion === 'activo') {
        openConfirmModal(
            'Activar categor\u00eda',
            'La categor\u00eda volver\u00e1 a estar disponible.',
            'check_circle',
            'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-green-50 text-green-700',
            'ACTIVAR',
            'flex-1 px-4 py-3 rounded-xl bg-green-700 text-white font-semibold transition-all hover:bg-green-800',
            function () {
                closeConfirmModal();
                ejecutarCategoriaAction(id, 'activo');
            }
        );
    }
}

function ejecutarCategoriaAction(id, accion) {
    const formData = new FormData();
    if (accion === 'eliminar') {
        formData.append('accion', 'eliminar_categoria');
    } else {
        formData.append('accion', 'cambiar_estado_categoria');
        formData.append('estado', accion);
    }
    formData.append('id', id);
    fetch('admin.php', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === 'success') {
                storeNotification(data.message, 'success');
                location.reload();
            } else {
                openConfirmModal('Error', data.message || 'No se pudo completar la acci\u00f3n', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
                document.getElementById('confirmBtn').onclick = closeConfirmModal;
            }
        })
        .catch(function () {
            openConfirmModal('Error de conexi\u00f3n', 'Verifique su conexi\u00f3n e intente nuevamente.', 'error', 'w-12 h-12 rounded-2xl flex items-center justify-center flex-none bg-red-50 text-red-600', 'CERRAR', 'flex-1 px-4 py-3 rounded-xl bg-primary text-white font-semibold transition-all hover:bg-primary-dark', closeConfirmModal);
            document.getElementById('confirmBtn').onclick = closeConfirmModal;
        });
}

function closeDetailsCategoria() {
    document.getElementById('detailsCategoriaPanel').classList.add('translate-x-full');
    setTimeout(function () { document.getElementById('detailsCategoriaOverlay').classList.add('hidden'); }, 300);
}
function openDetailsCategoria(cat) {
    document.getElementById('dcNombre').textContent = cat.nombre || '\u2014';
    document.getElementById('dcCodigo').textContent = '#' + cat.id;
    document.getElementById('dcDescripcion').textContent = cat.descripcion || 'Sin descripci\u00f3n';
    document.getElementById('dcProductos').textContent = cat.total_productos ?? 0;
    document.getElementById('dcId').textContent = '#' + cat.id;

    var est = cat.estado || 'activo';
    var badge = document.getElementById('dcEstado');
    badge.className = 'estado-badge ' + (est === 'activo' ? 'estado-aprobado' : 'estado-inactivo');
    badge.textContent = est === 'activo' ? 'Activa' : 'Inactiva';

    var btn = document.getElementById('dcActionBtn');
    btn.textContent = 'Editar categor\u00eda';
    btn.onclick = function () { closeDetailsCategoria(); setTimeout(function () { openEditCategoriaModal(cat); }, 350); };
    btn.classList.remove('hidden');

    document.getElementById('detailsCategoriaOverlay').classList.remove('hidden');
    document.getElementById('detailsCategoriaPanel').classList.remove('translate-x-full');
}
</script>
