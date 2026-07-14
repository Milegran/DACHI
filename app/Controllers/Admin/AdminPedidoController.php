<?php

class AdminPedidoController implements AdminControllerInterface
{
    private AdminPedidoService $service;
    private array $session;

    public function __construct(AdminPedidoService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_pedidos');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'cambiar_estado_pedido') {
            $this->ajaxCambiarEstadoPedido($post);
            return;
        }

        if (isset($get['ajax_pedido_detalle'])) {
            $this->ajaxPedidoDetalle((int)$get['ajax_pedido_detalle']);
            return;
        }

        if ($accion === 'pedido_estadisticas') {
            $this->ajaxEstadisticasExpandidas();
            return;
        }

        if ($accion === 'pedido_por_estado') {
            $this->ajaxPedidosPorEstado();
            return;
        }

        if ($accion === 'pedido_por_dia') {
            $this->ajaxPedidosPorDia();
            return;
        }

        if ($accion === 'exportar_pdf_pedidos') {
            $this->exportarPDF($get);
            return;
        }

        if ($accion === 'exportar_excel_pedidos') {
            $this->exportarExcel($get);
            return;
        }

        $this->listarPedidos($get);
    }

    private function listarPedidos(array $get): void
    {
        $busqueda = $get['busqueda'] ?? '';
        $estado = $get['estado'] ?? '';
        $metodoPago = $get['metodo_pago'] ?? '';
        $agricultor = $get['agricultor'] ?? '';
        $logistica = $get['logistica'] ?? '';
        $provincia = $get['provincia'] ?? '';
        $fechaInicio = $get['fecha_inicio'] ?? '';
        $fechaFin = $get['fecha_fin'] ?? '';
        $orden = $get['orden'] ?? 'id';
        $direccion = $get['direccion'] ?? 'DESC';
        $pagina = max(1, (int)($get['pagina'] ?? 1));
        $limite = 15;

        $pedidos = $this->service->obtenerPedidos(
            $busqueda, $estado, $metodoPago,
            $agricultor, $logistica, $provincia,
            $fechaInicio, $fechaFin,
            $orden, $direccion, $pagina, $limite
        );
        $total = $this->service->contarPedidos(
            $busqueda, $estado, $metodoPago,
            $agricultor, $logistica, $provincia,
            $fechaInicio, $fechaFin
        );
        $totalPaginas = max(1, (int)ceil($total / $limite));
        $statsExpandidas = $this->service->obtenerEstadisticasExpandidas();
        $metodosPago = $this->service->obtenerMetodosPago();
        $agricultores = $this->service->obtenerAgricultores();
        $logisticos = $this->service->obtenerLogisticos();
        $provincias = $this->service->obtenerProvincias();
        $datosDona = $this->service->obtenerPedidosPorEstado();
        $datosLinea = $this->service->obtenerPedidosPorDia();

        $this->renderView('admin/pedidos_lista', [
            'pedidos' => $pedidos,
            'statsExpandidas' => $statsExpandidas,
            'metodosPago' => $metodosPago,
            'agricultores' => $agricultores,
            'logisticos' => $logisticos,
            'provincias' => $provincias,
            'datosDona' => $datosDona,
            'datosLinea' => $datosLinea,
            'busqueda' => $busqueda,
            'filtro_estado' => $estado,
            'filtro_metodo_pago' => $metodoPago,
            'filtro_agricultor' => $agricultor,
            'filtro_logistica' => $logistica,
            'filtro_provincia' => $provincia,
            'filtro_fecha_inicio' => $fechaInicio,
            'filtro_fecha_fin' => $fechaFin,
            'orden' => $orden,
            'direccion' => $direccion,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'total' => $total,
            'limite' => $limite,
            'submodulo' => 'pedidos'
        ]);
    }

    private function ajaxEstadisticasExpandidas(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->service->obtenerEstadisticasExpandidas());
    }

    private function ajaxPedidosPorEstado(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->service->obtenerPedidosPorEstado());
    }

    private function ajaxPedidosPorDia(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->service->obtenerPedidosPorDia());
    }

    private function ajaxCambiarEstadoPedido(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        $estado = $post['estado'] ?? '';
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de pedido inválido']);
            return;
        }
        $resultado = $this->service->actualizarEstadoPedido($id, $estado);
        echo json_encode($resultado);
    }

    private function ajaxPedidoDetalle(int $id): void
    {
        header('Content-Type: application/json');
        $pedido = $this->service->obtenerPedido($id);
        if (!$pedido) {
            echo json_encode(['productos' => [], 'entrega' => null]);
            return;
        }
        $productos = $this->service->obtenerProductosDePedido($id);
        $entrega = $this->service->obtenerEntregaDePedido($id);
        echo json_encode(['productos' => $productos, 'entrega' => $entrega]);
    }

    private function exportarPDF(array $get): void
    {
        $pedidos = $this->service->obtenerPedidos(
            $get['busqueda'] ?? '',
            $get['estado'] ?? '',
            $get['metodo_pago'] ?? '',
            $get['agricultor'] ?? '',
            $get['logistica'] ?? '',
            $get['provincia'] ?? '',
            $get['fecha_inicio'] ?? '',
            $get['fecha_fin'] ?? '',
            $get['orden'] ?? 'id',
            $get['direccion'] ?? 'DESC',
            1, 9999
        );

        $html = '<html><head><meta charset="utf-8"><title>Pedidos DACHI</title>';
        $html .= '<style>
            body { font-family: sans-serif; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
            th { background: #11663C; color: white; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            h1 { color: #11663C; }
        </style></head><body>';
        $html .= '<h1>Gestión de Pedidos - DACHI</h1>';
        $html .= '<p>Fecha: ' . date('d/m/Y H:i') . '</p>';
        $html .= '<table><thead><tr>';
        $html .= '<th>Pedido</th><th>Fecha</th><th>Consumidor</th><th>Agricultor</th><th>Logística</th><th>Estado</th><th>Pago</th><th>Total</th>';
        $html .= '</tr></thead><tbody>';
        $estadosLabel = ['pendiente'=>'Pendiente','en_preparacion'=>'En preparación','en_transito'=>'En tránsito','entregado'=>'Entregado','cancelado'=>'Cancelado'];
        foreach ($pedidos as $p) {
            $est = $estadosLabel[$p['estado_detallado'] ?? 'pendiente'] ?? 'Pendiente';
            $html .= '<tr>';
            $html .= '<td>#' . (int)$p['id'] . '</td>';
            $html .= '<td>' . ($p['fecha'] ? date('d/m/Y', strtotime($p['fecha'])) : '—') . '</td>';
            $html .= '<td>' . htmlspecialchars(trim(($p['consumer_nombre']??'') . ' ' . ($p['consumer_apellido']??'')), ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($p['agricultores'] ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($p['logisticos'] ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . $est . '</td>';
            $html .= '<td>' . htmlspecialchars($p['metodo_pago_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td class="text-right">$' . number_format((float)($p['total_compra'] ?? 0), 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="pedidos_dachi_' . date('Ymd') . '.html"');
        echo $html;
    }

    private function exportarExcel(array $get): void
    {
        $pedidos = $this->service->obtenerPedidos(
            $get['busqueda'] ?? '',
            $get['estado'] ?? '',
            $get['metodo_pago'] ?? '',
            $get['agricultor'] ?? '',
            $get['logistica'] ?? '',
            $get['provincia'] ?? '',
            $get['fecha_inicio'] ?? '',
            $get['fecha_fin'] ?? '',
            $get['orden'] ?? 'id',
            $get['direccion'] ?? 'DESC',
            1, 9999
        );

        $estadosLabel = ['pendiente'=>'Pendiente','en_preparacion'=>'En preparación','en_transito'=>'En tránsito','entregado'=>'Entregado','cancelado'=>'Cancelado'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="pedidos_dachi_' . date('Ymd') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Pedido', 'Fecha', 'Consumidor', 'Agricultor', 'Logística', 'Estado', 'Pago', 'Total']);
        foreach ($pedidos as $p) {
            fputcsv($output, [
                '#' . (int)$p['id'],
                $p['fecha'] ? date('d/m/Y', strtotime($p['fecha'])) : '—',
                trim(($p['consumer_nombre']??'') . ' ' . ($p['consumer_apellido']??'')),
                $p['agricultores'] ?? '—',
                $p['logisticos'] ?? '—',
                $estadosLabel[$p['estado_detallado'] ?? 'pendiente'] ?? 'Pendiente',
                $p['metodo_pago_nombre'] ?? '—',
                number_format((float)($p['total_compra'] ?? 0), 2)
            ]);
        }
        fclose($output);
    }

    private function renderView(string $view, array $data): void
    {
        $data['usuarioAdmin'] = $this->session['usuario'] ?? [];
        extract($data);
        $contenido = __DIR__ . "/../../../views/{$view}.php";
        require __DIR__ . '/../../../views/admin/layout.php';
    }
}
