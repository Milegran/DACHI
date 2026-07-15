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
        if ($accion === 'logistica_dashboard') {
            $this->logisticaDashboard();
            return;
        }

        $this->listarLogisticos($get['busqueda'] ?? '', ($get['inactivos'] ?? '') === '1');
    }

    private function listarLogisticos(string $busqueda, bool $soloInactivos = false): void
    {
        $logisticos = $this->service->obtenerLogisticos($busqueda, $soloInactivos);
        $this->renderView('admin/usuarios/logistica_lista', [
            'logisticos' => $logisticos,
            'busqueda' => $busqueda,
            'submodulo' => 'logistica',
            'mostrandoInactivos' => $soloInactivos
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

    private function logisticaDashboard(): void
    {
        $kpis = $this->service->obtenerKPIsLogisticos();
        $rendimiento = $this->service->obtenerRendimientoLogistas();
        $provincias = $this->service->obtenerEntregasPorProvincia();
        $incidencias = $this->service->obtenerIncidencias();
        $activas = $this->service->obtenerEntregasActivas();
        $this->renderView('admin/logistica/dashboard', [
            'kpis' => $kpis,
            'rendimiento' => $rendimiento,
            'provincias' => $provincias,
            'incidencias' => $incidencias,
            'activas' => $activas,
            'submodulo' => 'logistica_dashboard'
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
