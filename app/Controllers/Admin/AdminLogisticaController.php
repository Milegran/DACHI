<?php

class AdminLogisticaController implements AdminControllerInterface
{
    private AdminLogisticaService $service;
    private array $session;

    public function __construct(AdminLogisticaService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_logisticos');

        if ($accion === 'ver_logistico') {
            $this->verLogistico((int)($get['id'] ?? 0));
            return;
        }

        $this->listarLogisticos($get['busqueda'] ?? '');
    }

    private function listarLogisticos(string $busqueda): void
    {
        $logisticos = $this->service->obtenerLogisticos($busqueda);
        $this->renderView('admin/usuarios/logistica_lista', [
            'logisticos' => $logisticos,
            'busqueda' => $busqueda,
            'submodulo' => 'logistica'
        ]);
    }

    private function verLogistico(int $id): void
    {
        $logistico = $this->service->obtenerLogistico($id);
        if ($logistico === null) {
            $this->renderView('admin/usuarios/logistica_lista', [
                'logisticos' => $this->service->obtenerLogisticos(''),
                'busqueda' => '',
                'submodulo' => 'logistica',
                'error' => 'Proveedor logístico no encontrado'
            ]);
            return;
        }
        $entregas = $this->service->obtenerEntregasPorLogistico($id);
        $historial = $this->service->obtenerHistorialActividad($id);
        $this->renderView('admin/usuarios/logistica_perfil', [
            'logistico' => $logistico,
            'entregas' => $entregas,
            'historial' => $historial,
            'submodulo' => 'logistica'
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
