<?php

class AdminCategoriaController implements AdminControllerInterface
{
    private AdminCategoriaService $service;
    private array $session;

    public function __construct(AdminCategoriaService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_categorias');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($accion === 'crear_categoria') {
                $this->ajaxCrearCategoria($post);
                return;
            }
            if ($accion === 'editar_categoria') {
                $this->ajaxEditarCategoria($post);
                return;
            }
            if ($accion === 'cambiar_estado_categoria') {
                $this->ajaxCambiarEstadoCategoria($post);
                return;
            }
            if ($accion === 'eliminar_categoria') {
                $this->ajaxEliminarCategoria($post);
                return;
            }
        }

        $this->listarCategorias($get);
    }

    private function listarCategorias(array $get): void
    {
        $busqueda = $get['busqueda'] ?? '';
        $orden = $get['orden'] ?? 'id';
        $direccion = $get['direccion'] ?? 'DESC';
        $pagina = max(1, (int)($get['pagina'] ?? 1));
        $limite = 15;

        $categorias = $this->service->obtenerCategorias($busqueda, $orden, $direccion, $pagina, $limite);
        $total = $this->service->contarCategorias($busqueda);
        $totalPaginas = max(1, (int)ceil($total / $limite));
        $stats = $this->service->obtenerEstadisticas();

        $this->renderView('admin/categorias_lista', [
            'categorias' => $categorias,
            'stats' => $stats,
            'busqueda' => $busqueda,
            'orden' => $orden,
            'direccion' => $direccion,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'total' => $total,
            'limite' => $limite,
            'submodulo' => 'categorias'
        ]);
    }

    private function ajaxCrearCategoria(array $post): void
    {
        header('Content-Type: application/json');
        $resultado = $this->service->crearCategoria($post);
        echo json_encode($resultado);
    }

    private function ajaxEditarCategoria(array $post): void
    {
        header('Content-Type: application/json');
        $resultado = $this->service->actualizarCategoria($post);
        echo json_encode($resultado);
    }

    private function ajaxCambiarEstadoCategoria(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        $estado = $post['estado'] ?? '';
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de categoría inválido']);
            return;
        }
        $resultado = $this->service->cambiarEstadoCategoria($id, $estado);
        echo json_encode($resultado);
    }

    private function ajaxEliminarCategoria(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de categoría inválido']);
            return;
        }
        $resultado = $this->service->eliminarCategoria($id);
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
