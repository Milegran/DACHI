<?php

class AdminCalificacionController implements AdminControllerInterface
{
    private AdminCalificacionService $service;
    private array $session;

    public function __construct(AdminCalificacionService $service)
    {
        $this->service = $service;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $this->session = $session;
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_calificaciones');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($accion === 'cambiar_estado_calificacion') {
                $this->ajaxCambiarEstado($post);
                return;
            }
            if ($accion === 'crear_reporte') {
                $this->ajaxCrearReporte($post);
                return;
            }
            if ($accion === 'responder_comentario') {
                $this->ajaxResponder($post);
                return;
            }
            if ($accion === 'suspender_usuario') {
                $this->ajaxSuspenderUsuario($post);
                return;
            }
            if ($accion === 'resolver_reporte') {
                $this->ajaxResolverReporte($post);
                return;
            }
        }

        if (isset($get['ajax_calificacion_detalle'])) {
            $this->ajaxDetalle((int)$get['ajax_calificacion_detalle']);
            return;
        }

        $this->listarCalificaciones($get);
    }

    private function listarCalificaciones(array $get): void
    {
        $busqueda = $get['busqueda'] ?? '';
        $rol = $get['rol'] ?? '';
        $estado = $get['estado'] ?? '';
        $estrellas = $get['estrellas'] ?? '';
        $fecha_desde = $get['fecha_desde'] ?? '';
        $fecha_hasta = $get['fecha_hasta'] ?? '';
        $orden = $get['orden'] ?? 'c.id';
        $direccion = $get['direccion'] ?? 'DESC';
        $pagina = max(1, (int)($get['pagina'] ?? 1));
        $limite = 15;

        $calificaciones = $this->service->obtenerCalificaciones($busqueda, $rol, $estado, $estrellas, $fecha_desde, $fecha_hasta, $orden, $direccion, $pagina, $limite);
        $total = $this->service->contarCalificaciones($busqueda, $rol, $estado, $estrellas, $fecha_desde, $fecha_hasta);
        $totalPaginas = max(1, (int)ceil($total / $limite));
        $kpis = $this->service->obtenerKPIs();
        $tendencia = $this->service->obtenerTendenciaMensual();
        $distribucion = $this->service->obtenerDistribucion();
        $casos = $this->service->obtenerCasosInvestigacion();
        $peorReputacion = $this->service->obtenerPeorReputacion();
        $mejoresAgricultores = $this->service->obtenerMejoresAgricultores();

        $this->renderView('admin/calificaciones_lista', [
            'calificaciones' => $calificaciones,
            'kpis' => $kpis,
            'tendencia' => $tendencia,
            'distribucion' => $distribucion,
            'casos' => $casos,
            'peorReputacion' => $peorReputacion,
            'mejoresAgricultores' => $mejoresAgricultores,
            'busqueda' => $busqueda,
            'filtro_rol' => $rol,
            'filtro_estado' => $estado,
            'filtro_estrellas' => $estrellas,
            'filtro_fecha_desde' => $fecha_desde,
            'filtro_fecha_hasta' => $fecha_hasta,
            'orden' => $orden,
            'direccion' => $direccion,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'total' => $total,
            'limite' => $limite,
            'submodulo' => 'calificaciones'
        ]);
    }

    private function ajaxCambiarEstado(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        $estado = $post['estado'] ?? '';
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            return;
        }
        echo json_encode($this->service->cambiarEstadoCalificacion($id, $estado));
    }

    private function ajaxDetalle(int $id): void
    {
        header('Content-Type: application/json');
        $calificacion = $this->service->obtenerCalificacionDetalle($id);
        if (!$calificacion) {
            echo json_encode(['error' => 'Calificación no encontrada']);
            return;
        }
        $reportes = $this->service->obtenerReportesDeCalificacion($id);
        echo json_encode(['calificacion' => $calificacion, 'reportes' => $reportes]);
    }

    private function ajaxCrearReporte(array $post): void
    {
        header('Content-Type: application/json');
        $idCalificacion = (int)($post['id_calificacion'] ?? 0);
        $idReportado = (int)($post['id_reportado'] ?? 0);
        $idReporta = (int)($this->session['usuario']['id'] ?? 0);
        $motivo = trim($post['motivo'] ?? '');
        $prioridad = $post['prioridad'] ?? 'media';

        if ($idCalificacion <= 0 || $idReportado <= 0 || $motivo === '') {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }
        echo json_encode($this->service->crearReporte($idCalificacion, $idReportado, $idReporta, $motivo, $prioridad));
    }

    private function ajaxResponder(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id'] ?? 0);
        $respuesta = trim($post['respuesta'] ?? '');
        if ($id <= 0 || $respuesta === '') {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }
        echo json_encode($this->service->responderComentario($id, $respuesta));
    }

    private function ajaxSuspenderUsuario(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id_usuario'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de usuario inválido']);
            return;
        }
        echo json_encode($this->service->suspenderUsuario($id));
    }

    private function ajaxResolverReporte(array $post): void
    {
        header('Content-Type: application/json');
        $id = (int)($post['id_reporte'] ?? 0);
        $estado = $post['estado'] ?? '';
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de reporte inválido']);
            return;
        }
        echo json_encode($this->service->resolverReporte($id, $estado));
    }

    private function renderView(string $view, array $data): void
    {
        $data['usuarioAdmin'] = $this->session['usuario'] ?? [];
        extract($data);
        $contenido = __DIR__ . "/../../../views/{$view}.php";
        require __DIR__ . '/../../../views/admin/layout.php';
    }
}
