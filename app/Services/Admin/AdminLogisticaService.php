<?php

class AdminLogisticaService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function obtenerLogisticos(string $busqueda = ''): array
    {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                       u.fecha_registro, u.ultimo_acceso,
                       (SELECT COUNT(*) FROM entregas e WHERE e.id_repartidor = u.id) AS total_entregas
                FROM usuarios u
                WHERE u.id_rol = 3";

        if ($busqueda !== '') {
            $busqueda = '%' . $busqueda . '%';
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ?)";
            $stmt = $this->conn->prepare($sql . " ORDER BY u.id DESC");
            $stmt->bind_param('sss', $busqueda, $busqueda, $busqueda);
        } else {
            $stmt = $this->conn->prepare($sql . " ORDER BY u.id DESC");
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
