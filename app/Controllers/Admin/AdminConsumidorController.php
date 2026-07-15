<?php

class AdminConsumidorController implements AdminControllerInterface
{
    private AdminConsumidorService $service;
    private array $session;

    public function __construct(AdminConsumidorService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_consumidores');

        if ($accion === 'ver_consumidor') {
            $this->verConsumidor((int)($get['id'] ?? 0));
            return;
        }

        $this->listarConsumidores($get['busqueda'] ?? '', ($get['inactivos'] ?? '') === '1');
    }

    private function listarConsumidores(string $busqueda, bool $soloInactivos = false): void
    {
        $consumidores = $this->service->obtenerConsumidores($busqueda, $soloInactivos);
        $this->renderView('admin/usuarios/consumidores_lista', [
            'consumidores' => $consumidores,
            'busqueda' => $busqueda,
            'submodulo' => 'consumidores',
            'mostrandoInactivos' => $soloInactivos
        ]);
    }

    private function verConsumidor(int $id): void
    {
        $consumidor = $this->service->obtenerConsumidor($id);
        if ($consumidor === null) {
            $this->renderView('admin/usuarios/consumidores_lista', [
                'consumidores' => $this->service->obtenerConsumidores(''),
                'busqueda' => '',
                'submodulo' => 'consumidores',
                'error' => 'Consumidor no encontrado'
            ]);
            return;
        }
        $direcciones = $this->service->obtenerDireccionesConsumidor($id);
        $pedidos = $this->service->obtenerPedidosConsumidor($id);
        $calificaciones = $this->service->obtenerCalificacionesConsumidor($id);
        $historial = $this->service->obtenerHistorialActividad($id);
        $this->renderView('admin/usuarios/consumidor_perfil', [
            'consumidor' => $consumidor,
            'direcciones' => $direcciones,
            'pedidos' => $pedidos,
            'calificaciones' => $calificaciones,
            'historial' => $historial,
            'submodulo' => 'consumidores'
        ]);
    }

    private function renderView(string $view, array $data): void
    {
        $data['usuarioAdmin'] = $this->session['usuario'] ?? [];
        extract($data);
        $contenido = __DIR__ . "/../../../views/{$view}.php";
        require __DIR__ . '/../../../views/admin/layout.php';
    }
}
