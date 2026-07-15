<?php

class AdminProductoController implements AdminControllerInterface
{
    private AdminProductoService $service;
    private array $session;

    public function __construct(AdminProductoService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_productos');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'cambiar_estado_producto') {
            $this->ajaxCambiarEstadoProducto($post);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear_producto') {
            $this->ajaxCrearProducto($post, $_FILES);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'editar_producto') {
            $this->ajaxEditarProducto($post, $_FILES);
            return;
        }

        $this->listarProductos($get);
    }

    private function listarProductos(array $get): void
    {
        $busqueda = $get['busqueda'] ?? '';
        $estado = $get['estado'] ?? '';
        $categoria = $get['categoria'] ?? '';
        $orden = $get['orden'] ?? 'id';
        $direccion = $get['direccion'] ?? 'DESC';
        $pagina = max(1, (int)($get['pagina'] ?? 1));
        $limite = 15;

        $productos = $this->service->obtenerProductos($busqueda, $estado, $categoria, $orden, $direccion, $pagina, $limite);
        $total = $this->service->contarProductos($busqueda, $estado, $categoria);
        $totalPaginas = max(1, (int)ceil($total / $limite));
        $stats = $this->service->obtenerEstadisticas();
        $categorias = $this->service->obtenerCategorias();
        $this->renderView('admin/productos_lista', [
            'productos' => $productos,
            'stats' => $stats,
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'filtro_estado' => $estado,
            'filtro_categoria' => $categoria,
            'orden' => $orden,
            'direccion' => $direccion,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'total' => $total,
            'limite' => $limite,
            'submodulo' => 'productos'
        ]);
    }

    private function ajaxCambiarEstadoProducto(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        $estado = $post['estado'] ?? '';
        $motivo = $post['motivo'] ?? '';

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de producto inválido']);
            return;
        }

        $resultado = $this->service->cambiarEstadoProducto($id, $estado, $motivo);
        echo json_encode($resultado);
    }

    private function ajaxCrearProducto(array $post, array $files): void
    {
        header('Content-Type: application/json');

        $rutaImagen = '';

        if (!empty($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
            $archivo = $files['imagen'];
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowed, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no válido. Permitidos: JPG, PNG, WebP, GIF']);
                return;
            }
            if ($archivo['size'] > 2 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'La imagen no puede superar los 2 MB']);
                return;
            }
            $nombreUnico = uniqid('prod_', true) . '.' . $ext;
            $destino = __DIR__ . '/../../../img/products/' . $nombreUnico;
            if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
                return;
            }
            $rutaImagen = 'img/products/' . $nombreUnico;
        }

        $post['imagen'] = $rutaImagen;
        $resultado = $this->service->crearProducto($post);
        echo json_encode($resultado);
    }

    private function ajaxEditarProducto(array $post, array $files): void
    {
        header('Content-Type: application/json');

        $rutaImagen = $post['imagen_actual'] ?? '';

        if (!empty($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
            $archivo = $files['imagen'];
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowed, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no válido. Permitidos: JPG, PNG, WebP, GIF']);
                return;
            }
            if ($archivo['size'] > 2 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'La imagen no puede superar los 2 MB']);
                return;
            }
            $nombreUnico = uniqid('prod_', true) . '.' . $ext;
            $destino = __DIR__ . '/../../../img/products/' . $nombreUnico;
            if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
                return;
            }
            $rutaImagen = 'img/products/' . $nombreUnico;
        }

        $post['imagen'] = $rutaImagen;
        $resultado = $this->service->actualizarProducto($post);
        echo json_encode($resultado);
    }

    private function renderView(string $view, array $data): void
    {
        $data['usuarioAdmin'] = $this->session['usuario'] ?? [];
        extract($data);
        $contenido = __DIR__ . "/../../../views/{$view}.php";
        require __DIR__ . '/../../../views/admin/layout.php';
    }
}
