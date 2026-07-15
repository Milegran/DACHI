<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/productor_panel_data.php';

if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$rolSesion = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
if (!in_array($rolSesion, ['productor', 'administrador', 'admin'], true)) {
    header('Location: ../../panel.php');
    exit;
}

$successMessage = $_GET['success'] ?? '';
$errorMessage = '';
$categories = [];

$categoryResult = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
if ($categoryResult) {
    while ($category = $categoryResult->fetch_assoc()) {
        $categories[] = $category;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_producto'])) {
    $idProducto = (int)($_POST['id_producto'] ?? 0);
    $idUsuario = (int)($usuarioActual['id'] ?? 0);
    if ($idProducto > 0 && $idUsuario > 0) {
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ? AND id_usuario = ?");
        $stmt->bind_param('ii', $idProducto, $idUsuario);
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();
    header('Location: productor_prod.php?success=Producto+eliminado+correctamente');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_producto'])) {
    $idProducto = (int)($_POST['id_producto'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $estado = isset($_POST['estado']) && $_POST['estado'] === '0' ? 0 : 1;
    $idUsuario = (int)($usuarioActual['id'] ?? 0);

    if ($nombre !== '' && $idProducto > 0 && $idUsuario > 0) {
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, cantidad = ?, estado = ? WHERE id = ? AND id_usuario = ?");
        $stmt->bind_param('ssdiiii', $nombre, $descripcion, $precio, $cantidad, $estado, $idProducto, $idUsuario);
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();
    header('Location: productor_prod.php?success=Producto+actualizado+correctamente');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $imagen = trim($_POST['imagen'] ?? '');
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $estado = isset($_POST['estado']) && $_POST['estado'] === '0' ? 0 : 1;
    $estadoAprobacion = 'pendiente';
    $idCategoria = !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;
    $nomProductor = trim(($usuarioActual['nombre'] ?? '') . ' ' . ($usuarioActual['apellido'] ?? ''));
    $idUsuario = (int)($usuarioActual['id'] ?? 0);

    if ($nombre === '') {
        $errorMessage = 'El nombre del producto es obligatorio.';
    } elseif ($descripcion === '') {
        $errorMessage = 'La descripción es obligatoria.';
    } elseif ($precio <= 0) {
        $errorMessage = 'El precio debe ser mayor a 0.';
    } elseif ($cantidad < 0) {
        $errorMessage = 'La cantidad no puede ser negativa.';
    } elseif ($idUsuario <= 0) {
        $errorMessage = 'No se ha podido identificar al productor.';
    }

    if ($errorMessage === '') {
        if ($idCategoria !== null) {
            $stmt = $conn->prepare(
                "INSERT INTO productos (nombre, descripcion, precio, imagen, nom_productor, cantidad, estado, estado_aprobacion, id_categoria, id_usuario, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param('ssdssiiisi', $nombre, $descripcion, $precio, $imagen, $nomProductor, $cantidad, $estado, $estadoAprobacion, $idCategoria, $idUsuario);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO productos (nombre, descripcion, precio, imagen, nom_productor, cantidad, estado, estado_aprobacion, id_categoria, id_usuario, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, NOW())"
            );
            $stmt->bind_param('ssdssiiii', $nombre, $descripcion, $precio, $imagen, $nomProductor, $cantidad, $estado, $estadoAprobacion, $idUsuario);
        }

        if ($stmt) {
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header('Location: productor_prod.php?success=Producto+creado+correctamente');
                exit;
            }
            $errorMessage = 'Error al guardar el producto en la base de datos.';
            $stmt->close();
        } else {
            $errorMessage = 'Error interno al preparar la consulta.';
        }
    }
}

$contexto = obtenerContextoProductor($conn, $usuarioActual);
$conn->close();
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DACHI | Mis Productos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="../../css/dachi-brand.css" rel="stylesheet" />
    <link href="../../css/dachi-botanical.css" rel="stylesheet" />
</head>
<body class="dachi-app bg-background text-on-background font-body-md min-h-screen glass-container flex flex-col">
<?php require_once __DIR__ . '/producer_layout_start.php'; ?>
            <header class="mb-8 rounded-2xl border border-[#e1e3e4] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#404942]">Mis productos</p>
                        <h2 class="text-3xl font-bold text-[#004528]">Productos de <?= htmlspecialchars(($contexto['usuario']['nombre'] ?? 'Productor') . ' ' . ($contexto['usuario']['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                </div>
            </header>

            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <button type="button" onclick="toggleProductForm()" class="inline-flex items-center justify-center rounded-2xl bg-[#004528] px-5 py-3 text-white shadow-sm transition hover:bg-[#05391f]">
                    <span class="material-symbols-outlined mr-2">add</span>
                    Agregar producto
                </button>
            </div>

            <?php if ($successMessage): ?>
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div id="newProductForm" class="mb-8 hidden rounded-3xl border border-[#d9dadb] bg-white p-6 shadow-sm">
                <form method="POST" class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Categoría</label>
                        <select name="id_categoria" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>"><?= htmlspecialchars($category['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Nombre del producto</label>
                        <input type="text" name="nombre" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required />
                    </div>
                    <div class="lg:col-span-2 space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Descripción</label>
                        <textarea name="descripcion" rows="4" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Precio</label>
                        <input type="number" name="precio" min="0" step="0.01" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Imagen (URL)</label>
                        <input type="text" name="imagen" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" placeholder="img/products/ejemplo.jpg" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Productor</label>
                        <input type="text" value="<?= htmlspecialchars(trim(($usuarioActual['nombre'] ?? '') . ' ' . ($usuarioActual['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" disabled class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f3f4f5] p-3 text-sm text-[#404942]" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Cantidad</label>
                        <input type="number" name="cantidad" min="0" step="1" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Estado</label>
                        <select name="estado" class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-[#404942]">Estado de aprobación</label>
                        <input type="text" value="Pendiente" disabled class="w-full rounded-2xl border border-[#e1e3e4] bg-[#f3f4f5] p-3 text-sm text-[#404942]" />
                    </div>
                    <div class="lg:col-span-2 flex items-center justify-end">
                        <button type="submit" name="crear_producto" class="rounded-2xl bg-[#004528] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#05391f]">Guardar producto</button>
                    </div>
                </form>
            </div>

            <section class="mb-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Productos activos</p>
                    <p class="mt-2 text-3xl font-bold text-[#004528]"><?= (int) $contexto['totalProductos'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Stock bajo</p>
                    <p class="mt-2 text-3xl font-bold text-[#b91c1c]"><?= (int) $contexto['stockBajo'] ?></p>
                </div>
                <div class="rounded-2xl border border-[#e1e3e4] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#404942]">Agotados</p>

<p class="mt-2 text-3xl font-bold text-[#7c2d12]"><?= (int) ($contexto['agotados'] ?? 0) ?></p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-[#e1e3e4] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-[#e1e3e4] bg-[#f3f4f5]">
                            <tr>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Producto</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Precio</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Stock</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942]">Estado</th>
                                <th class="px-4 py-3 text-sm font-semibold uppercase text-[#404942] text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contexto['productos'])): ?>
                                <tr><td colspan="5" class="px-4 py-6 text-[#404942]">No hay productos registrados para esta cuenta.</td></tr>
                            <?php else: ?>
                                <?php foreach ($contexto['productos'] as $producto): ?>
                                    <?php $cantidad = (int) ($producto['cantidad'] ?? 0); ?>
                                    <?php $estado = $cantidad <= 0 ? 'Agotado' : ($cantidad <= 10 ? 'Bajo stock' : 'Disponible'); ?>
                                    <tr class="border-b border-[#f3f4f5]">
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-[#191c1d]"><?= htmlspecialchars($producto['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-sm text-[#404942]"><?= htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td class="px-4 py-4">$<?= number_format((float) ($producto['precio'] ?? 0), 2, ',', '.') ?></td>
                                        <td class="px-4 py-4"><?= $cantidad ?></td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-3 py-1 text-sm font-semibold <?= $estado === 'Agotado' ? 'bg-[#ffdad6] text-[#93000a]' : ($estado === 'Bajo stock' ? 'bg-[#ffdcc4] text-[#6f3800]' : 'bg-[#cce6d0] text-[#506856]') ?>">
                                                <?= $estado ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" onclick='openEditModal(<?= json_encode($producto) ?>)' class="inline-flex items-center gap-1 rounded-lg bg-[#e8f2ea] px-3 py-1.5 text-xs font-semibold text-[#004528] transition hover:bg-[#d0e8d5]" title="Editar">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                                    Editar
                                                </button>
                                                <form method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');" class="inline">
                                                    <input type="hidden" name="id_producto" value="<?= (int)$producto['id'] ?>">
                                                    <button type="submit" name="eliminar_producto" class="inline-flex items-center gap-1 rounded-lg bg-[#ffdad6] px-3 py-1.5 text-xs font-semibold text-[#93000a] transition hover:bg-[#ffc9c2]" title="Eliminar">
                                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

<!-- MODAL EDITAR PRODUCTO -->
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300] hidden items-center justify-center p-4 modal-overlay" id="editProductModal">
    <div class="bg-white rounded-[24px] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-sm text-headline-sm text-primary">Editar Producto</h3>
            <button class="p-2 hover:bg-surface-container-low rounded-full" onclick="closeEditModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="editar_producto" value="1">
            <input type="hidden" name="id_producto" id="editIdProducto">
            <div>
                <label class="block text-sm font-semibold text-[#404942] mb-1">Nombre</label>
                <input type="text" name="nombre" id="editNombre" class="w-full rounded-xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required />
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#404942] mb-1">Descripción</label>
                <textarea name="descripcion" id="editDescripcion" rows="3" class="w-full rounded-xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-[#404942] mb-1">Precio</label>
                    <input type="number" name="precio" id="editPrecio" min="0" step="0.01" class="w-full rounded-xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#404942] mb-1">Cantidad</label>
                    <input type="number" name="cantidad" id="editCantidad" min="0" step="1" class="w-full rounded-xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]" required />
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-[#404942] mb-1">Estado</label>
                <select name="estado" id="editEstado" class="w-full rounded-xl border border-[#e1e3e4] bg-[#f8f9fa] p-3 text-sm text-[#191c1d]">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="w-full rounded-xl bg-[#004528] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#05391f]">GUARDAR CAMBIOS</button>
        </form>
    </div>
</div>

<script>
function openEditModal(producto) {
    document.getElementById('editIdProducto').value = producto.id;
    document.getElementById('editNombre').value = producto.nombre || '';
    document.getElementById('editDescripcion').value = producto.descripcion || '';
    document.getElementById('editPrecio').value = producto.precio || 0;
    document.getElementById('editCantidad').value = producto.cantidad || 0;
    document.getElementById('editEstado').value = producto.estado ?? 1;
    document.getElementById('editProductModal').classList.remove('hidden');
    document.getElementById('editProductModal').classList.add('flex');
}
function closeEditModal() {
    document.getElementById('editProductModal').classList.add('hidden');
    document.getElementById('editProductModal').classList.remove('flex');
}
document.addEventListener('click', function(e) {
    const overlay = e.target.closest('.modal-overlay');
    if (overlay && e.target === overlay) {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }
});
</script>

<?php require_once __DIR__ . '/producer_layout_end.php'; ?>
