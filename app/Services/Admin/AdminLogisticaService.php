<?php

class AdminLogisticaService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function obtenerLogisticos(string $busqueda = '', bool $soloInactivos = false): array
    {
        $sql = "SELECT u.id, u.id_rol, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                       u.fecha_registro, u.ultimo_acceso,
                       (SELECT COUNT(*) FROM entregas e WHERE e.id_repartidor = u.id) AS total_entregas
                FROM usuarios u
                WHERE u.id_rol = 3";

        if ($soloInactivos) {
            $sql .= " AND u.estado = 'inactivo'";
        }

        if ($busqueda !== '') {
            $busqueda = '%' . $busqueda . '%';
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ?)";
            $stmt = $this->conn->prepare($sql . " ORDER BY u.estado = 'inactivo' ASC, u.id DESC");
            $stmt->bind_param('sss', $busqueda, $busqueda, $busqueda);
        } else {
            $stmt = $this->conn->prepare($sql . " ORDER BY u.estado = 'inactivo' ASC, u.id DESC");
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $logisticos = [];
        while ($row = $result->fetch_assoc()) {
            $row['calificacion_promedio'] = $this->obtenerCalificacionLogistica((int)$row['id']);
            $row['entregas_exitosas'] = $this->contarEntregasExitosas((int)$row['id']);
            $logisticos[] = $row;
        }
        $stmt->close();
        return $logisticos;
    }

    public function obtenerLogistico(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                    u.fecha_registro, u.ultimo_acceso, u.foto_perfil
             FROM usuarios u
             WHERE u.id = ? AND u.id_rol = 3"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $logistico = $result->fetch_assoc();
        $stmt->close();

        if ($logistico === null) {
            return null;
        }

        $logistico['calificacion_promedio'] = $this->obtenerCalificacionLogistica($id);
        $logistico['total_entregas'] = $this->contarEntregasTotales($id);
        $logistico['entregas_exitosas'] = $this->contarEntregasExitosas($id);
        $logistico['porcentaje_exito'] = $this->calcularPorcentajeExito($id);
        return $logistico;
    }

    public function obtenerEntregasPorLogistico(int $idLogistico): array
    {
        $stmt = $this->conn->prepare(
            "SELECT e.id, e.id_pedidos, e.fecha, e.estado, e.evidencia, e.notas, e.tarifa_envio,
                    p.id AS pedido_id, p.fecha AS pedido_fecha, p.total_compra, p.estado_detallado,
                    CONCAT(u.nombre, ' ', u.apellido) AS productor_nombre,
                    CONCAT(c.nombre, ' ', c.apellido) AS consumidor_nombre
             FROM entregas e
             JOIN pedidos p ON e.id_pedidos = p.id
             JOIN info_pedidos ip ON ip.id_pedidos = p.id
             JOIN productos pr ON ip.id_productos = pr.id
             JOIN usuarios u ON pr.id_usuario = u.id
             JOIN usuarios c ON p.id_consumer = c.id
             WHERE e.id_repartidor = ?
             GROUP BY e.id
             ORDER BY e.fecha DESC
             LIMIT 50"
        );
        $stmt->bind_param('i', $idLogistico);
        $stmt->execute();
        $result = $stmt->get_result();
        $entregas = [];
        while ($row = $result->fetch_assoc()) {
            $entregas[] = $row;
        }
        $stmt->close();
        return $entregas;
    }

    public function obtenerCalificacionLogistica(int $idLogistico): float
    {
        $stmt = $this->conn->prepare(
            "SELECT ROUND(AVG(c.calificacion), 1) AS promedio
             FROM calificacion c
             WHERE c.id_producto = ? AND c.tipo = 'logistica'"
        );
        $stmt->bind_param('i', $idLogistico);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['promedio'] ?? 0);
    }

    public function obtenerHistorialActividad(int $idUsuario): array
    {
        $stmt = $this->conn->prepare(
            "SELECT 'ultimo_acceso' AS tipo, u.ultimo_acceso AS fecha, 'Inicio de sesión' AS descripcion
             FROM usuarios u WHERE u.id = ?
             UNION ALL
             SELECT 'entrega' AS tipo, e.fecha AS fecha, CONCAT('Entrega #', e.id, ' - Pedido #', e.id_pedidos) AS descripcion
             FROM entregas e WHERE e.id_repartidor = ?
             ORDER BY fecha DESC
             LIMIT 20"
        );
        $stmt->bind_param('ii', $idUsuario, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $actividad = [];
        while ($row = $result->fetch_assoc()) {
            $actividad[] = $row;
        }
        $stmt->close();
        return $actividad;
    }

    public function obtenerKPIsLogisticos(): array
    {
        $result = $this->conn->query(
            "SELECT
                COALESCE(estado_detallado, 'pendiente') AS estado,
                COUNT(*) AS total
             FROM pedidos
             GROUP BY estado_detallado"
        );
        $kpis = [
            'en_transito' => 0, 'pendiente' => 0, 'entregado' => 0,
            'cancelado' => 0, 'en_preparacion' => 0, 'tiempo_promedio' => 0
        ];
        while ($row = $result->fetch_assoc()) {
            $estado = $row['estado'];
            if (isset($kpis[$estado])) {
                $kpis[$estado] = (int)$row['total'];
            }
        }
        $resTiempo = $this->conn->query(
            "SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, p.fecha, p.fecha_entrega))) AS minutos
             FROM pedidos p
             WHERE p.estado_detallado = 'entregado' AND p.fecha_entrega IS NOT NULL"
        );
        $rowTiempo = $resTiempo->fetch_assoc();
        $kpis['tiempo_promedio'] = (int)($rowTiempo['minutos'] ?? 0);
        return $kpis;
    }

    public function obtenerRendimientoLogistas(): array
    {
        $result = $this->conn->query(
            "SELECT
                u.id, u.nombre, u.apellido,
                COUNT(e.id) AS total_entregas,
                SUM(CASE WHEN p.estado_detallado = 'entregado' THEN 1 ELSE 0 END) AS entregas_exitosas,
                SUM(CASE WHEN p.estado_detallado = 'cancelado' THEN 1 ELSE 0 END) AS entregas_canceladas,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, p.fecha, p.fecha_entrega))) AS tiempo_promedio
             FROM usuarios u
             LEFT JOIN entregas e ON u.id = e.id_repartidor
             LEFT JOIN pedidos p ON e.id_pedidos = p.id
             WHERE u.id_rol = 3
             GROUP BY u.id
             ORDER BY total_entregas DESC"
        );
        $logistas = [];
        while ($row = $result->fetch_assoc()) {
            $total = (int)$row['total_entregas'];
            $exitosas = (int)$row['entregas_exitosas'];
            $row['porcentaje_exito'] = $total > 0 ? round(($exitosas / $total) * 100, 1) : 0;
            $row['calificacion'] = $this->obtenerCalificacionLogistica((int)$row['id']);
            $row['incidencias'] = $this->contarIncidenciasLogista((int)$row['id']);
            $logistas[] = $row;
        }
        return $logistas;
    }

    public function obtenerEntregasPorProvincia(): array
    {
        $result = $this->conn->query(
            "SELECT
                d.provincia,
                COUNT(e.id) AS total_entregas,
                SUM(CASE WHEN p.estado_detallado = 'entregado' THEN 1 ELSE 0 END) AS entregadas,
                SUM(CASE WHEN p.estado_detallado = 'cancelado' THEN 1 ELSE 0 END) AS canceladas
             FROM entregas e
             JOIN pedidos p ON e.id_pedidos = p.id
             JOIN direccion d ON e.id_direccion = d.id
             WHERE d.provincia IS NOT NULL AND d.provincia != ''
             GROUP BY d.provincia
             ORDER BY total_entregas DESC"
        );
        $provincias = [];
        while ($row = $result->fetch_assoc()) {
            $provincias[] = $row;
        }
        return $provincias;
    }

    public function obtenerIncidencias(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                p.id AS pedido_id, p.fecha, p.notas AS motivo,
                COALESCE(p.estado_detallado, 'pendiente') AS estado,
                CONCAT(u.nombre, ' ', u.apellido) AS consumidor,
                CONCAT(lg.nombre, ' ', lg.apellido) AS logista
             FROM pedidos p
             JOIN usuarios u ON p.id_consumer = u.id
             LEFT JOIN entregas e ON p.id = e.id_pedidos
             LEFT JOIN usuarios lg ON e.id_repartidor = lg.id
             WHERE p.notas IS NOT NULL AND p.notas != ''
             ORDER BY p.fecha DESC
             LIMIT 50"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $incidencias = [];
        while ($row = $result->fetch_assoc()) {
            $incidencias[] = $row;
        }
        $stmt->close();
        return $incidencias;
    }

    public function obtenerEntregasActivas(): array
    {
        $result = $this->conn->query(
            "SELECT
                p.id AS pedido_id, p.fecha,
                CONCAT(u.nombre, ' ', u.apellido) AS consumidor,
                d.provincia AS zona,
                CONCAT(lg.nombre, ' ', lg.apellido) AS logista,
                CASE WHEN e.id_repartidor IS NOT NULL
                     THEN TIMESTAMPDIFF(MINUTE, p.fecha, NOW())
                     ELSE NULL
                END AS minutos_transcurridos
             FROM pedidos p
             JOIN usuarios u ON p.id_consumer = u.id
             LEFT JOIN entregas e ON p.id = e.id_pedidos
             LEFT JOIN usuarios lg ON e.id_repartidor = lg.id
             LEFT JOIN direccion d ON e.id_direccion = d.id
             WHERE p.estado_detallado = 'en_transito'
             ORDER BY p.fecha DESC
             LIMIT 20"
        );
        $activas = [];
        while ($row = $result->fetch_assoc()) {
            $activas[] = $row;
        }
        return $activas;
    }

    private function contarIncidenciasLogista(int $idLogistico): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM pedidos p
             JOIN entregas e ON p.id = e.id_pedidos
             WHERE e.id_repartidor = ? AND p.notas IS NOT NULL AND p.notas != ''"
        );
        $stmt->bind_param('i', $idLogistico);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }

    private function contarEntregasTotales(int $idLogistico): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM entregas WHERE id_repartidor = ?");
        $stmt->bind_param('i', $idLogistico);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    private function contarEntregasExitosas(int $idLogistico): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM entregas e
             JOIN pedidos p ON e.id_pedidos = p.id
             WHERE e.id_repartidor = ? AND (p.estado_detallado = 'entregado' OR e.estado = 2)"
        );
        $stmt->bind_param('i', $idLogistico);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    private function calcularPorcentajeExito(int $idLogistico): float
    {
        $total = $this->contarEntregasTotales($idLogistico);
        if ($total === 0) {
            return 0;
        }
        $exitosas = $this->contarEntregasExitosas($idLogistico);
        return round(($exitosas / $total) * 100, 1);
    }
}
