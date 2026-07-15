<?php

class AdminUsuarioController implements AdminControllerInterface
{
    private AdminUsuarioService $service;
    private array $session;

    public function __construct(AdminUsuarioService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'dashboard');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($accion === 'cambiar_estado_producto') {
                $this->ajaxCambiarEstadoProducto($post);
                return;
            }
            if ($accion === 'crear_usuario') {
                $this->ajaxCrearUsuario($post);
                return;
            }
            if ($accion === 'editar_usuario') {
                $this->ajaxEditarUsuario($post);
                return;
            }
            if ($accion === 'eliminar_usuario') {
                $this->ajaxEliminarUsuario($post);
                return;
            }
        }

        if ($accion === 'ver_productor') {
            $this->verProductor((int)($get['id'] ?? 0));
            return;
        }

        if ($accion === 'dashboard') {
            $this->verDashboard();
            return;
        }

        $this->listarProductores($get['busqueda'] ?? '');
    }

    public function renderListaProductores(string $busqueda = ''): void
    {
        $productores = $this->service->obtenerProductores($busqueda);
        $this->renderView('admin/usuarios/productores_lista', [
            'productores' => $productores,
            'busqueda' => $busqueda,
            'submodulo' => 'productores'
        ]);
    }

    public function renderPerfilProductor(int $id, array $productos): void
    {
        $productor = $this->service->obtenerProductor($id);
        if ($productor === null) {
            $this->renderView('admin/usuarios/productores_lista', [
                'productores' => $this->service->obtenerProductores(''),
                'busqueda' => '',
                'submodulo' => 'productores',
                'error' => 'Productor no encontrado'
            ]);
            return;
        }
        $historial = $this->service->obtenerHistorialActividad($id);
        $this->renderView('admin/usuarios/productor_perfil', [
            'productor' => $productor,
            'productos' => $productos,
            'historial' => $historial,
            'submodulo' => 'productores'
        ]);
    }

    private function listarProductores(string $busqueda): void
    {
        $productores = $this->service->obtenerProductores($busqueda);
        $this->renderView('admin/usuarios/productores_lista', [
            'productores' => $productores,
            'busqueda' => $busqueda,
            'submodulo' => 'productores'
        ]);
    }

    private function verDashboard(): void
    {
        $stats = $this->service->obtenerDashboardStats();
        $this->renderView('admin/dashboard', [
            'stats' => $stats,
        ]);
    }

    private function verProductor(int $id): void
    {
        $productos = $this->service->obtenerProductosPorProductor($id);
        $this->renderPerfilProductor($id, $productos);
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

    private function ajaxCrearUsuario(array $post): void
    {
        header('Content-Type: application/json');
        $resultado = $this->service->crearUsuario($post);
        echo json_encode($resultado);
    }

    private function ajaxEditarUsuario(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de usuario inválido']);
            return;
        }
        $resultado = $this->service->actualizarUsuario($id, $post);
        echo json_encode($resultado);
    }

    private function ajaxEliminarUsuario(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de usuario inválido']);
            return;
        }
        $resultado = $this->service->eliminarUsuario($id);
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
