<?php

class AdminCalificacionService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function bindParams(mysqli_stmt $stmt, string $types, array $params): void
    {
        if (count($params) === 0) return;
        $bindParams = [$types];
        foreach ($params as $i => &$v) {
            $bindParams[] = &$v;
        }
        unset($v);
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }

    public function obtenerKPIs(): array
    {
        $kpis = [
            'promedio_global' => 0,
            'mejor_agricultor' => null,
            'mejor_logistico' => null,
            'consumidor_activo' => null,
            'total_reportes' => 0,
            'reportes_pendientes' => 0
        ];

        $r = $this->conn->query("SELECT ROUND(AVG(calificacion),2) AS prom FROM calificacion");
        if ($r) $kpis['promedio_global'] = (float)($r->fetch_assoc()['prom'] ?? 0);

        $r = $this->conn->query(
            "SELECT u.id, u.nombre, u.apellido, u.foto_perfil,
                    ROUND(AVG(c.calificacion),2) AS promedio, COUNT(c.id) AS total_resenas
             FROM calificacion c
             JOIN productos p ON c.id_producto = p.id
             JOIN usuarios u ON p.id_usuario = u.id
             WHERE c.tipo IN ('producto','productor')
             GROUP BY u.id
             ORDER BY promedio DESC, total_resenas DESC
             LIMIT 1"
        );
        if ($r) $kpis['mejor_agricultor'] = $r->fetch_assoc();

        $r = $this->conn->query(
            "SELECT u.id, u.nombre, u.apellido, u.foto_perfil,
                    ROUND(AVG(c.calificacion),2) AS promedio, COUNT(c.id) AS total_resenas
             FROM calificacion c
             JOIN entregas e ON c.id_pedido = e.id_pedidos
             JOIN usuarios u ON e.id_repartidor = u.id
             WHERE c.tipo = 'logistica'
             GROUP BY u.id
             ORDER BY promedio DESC, total_resenas DESC
             LIMIT 1"
        );
        if ($r) $kpis['mejor_logistico'] = $r->fetch_assoc();

        $r = $this->conn->query(
            "SELECT u.id, u.nombre, u.apellido, u.correo, u.foto_perfil, COUNT(c.id) AS total_calificaciones
             FROM calificacion c
             JOIN usuarios u ON c.id_consumer = u.id
             GROUP BY u.id
             ORDER BY total_calificaciones DESC
             LIMIT 1"
        );
        if ($r) $kpis['consumidor_activo'] = $r->fetch_assoc();

        $r = $this->conn->query("SELECT COUNT(*) AS total FROM calificacion_reportes");
        if ($r) $kpis['total_reportes'] = (int)($r->fetch_assoc()['total'] ?? 0);

        $r = $this->conn->query("SELECT COUNT(*) AS total FROM calificacion_reportes WHERE estado = 'pendiente'");
        if ($r) $kpis['reportes_pendientes'] = (int)($r->fetch_assoc()['total'] ?? 0);

        return $kpis;
    }

    public function obtenerTendenciaMensual(int $meses = 12): array
    {
        $stmt = $this->conn->prepare(
            "SELECT DATE_FORMAT(COALESCE(c.created_at, c.updated_at, NOW()), '%Y-%m') AS mes,
                    ROUND(AVG(c.calificacion),2) AS promedio,
                    COUNT(*) AS total
             FROM calificacion c
             WHERE COALESCE(c.created_at, c.updated_at, NOW()) >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmt->bind_param('i', $meses);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    public function obtenerDistribucion(): array
    {
        $result = $this->conn->query(
            "SELECT calificacion AS estrellas, COUNT(*) AS total,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM calificacion), 1) AS porcentaje
             FROM calificacion
             GROUP BY calificacion
             ORDER BY calificacion DESC"
        );
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    private function buildFilterParams(
        string $busqueda = '',
        string $rol = '',
        string $estado = '',
        string $estrellas = '',
        string $fecha_desde = '',
        string $fecha_hasta = ''
    ): array
    {
        $conds = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $b = '%' . $busqueda . '%';
            $conds[] = "(c.id LIKE ? OR con.nombre LIKE ? OR con.apellido LIKE ? OR p.nombre LIKE ? OR prod_user.nombre LIKE ? OR prod_user.apellido LIKE ? OR rep.nombre LIKE ? OR rep.apellido LIKE ?)";
            $params = array_merge($params, [$b, $b, $b, $b, $b, $b, $b, $b]);
            $types .= 'ssssssss';
        }

        if ($rol !== '') {
            $conds[] = "c.tipo = ?";
            $params[] = $rol;
            $types .= 's';
        }

        if ($estado !== '') {
            $conds[] = "c.estado = ?";
            $params[] = $estado;
            $types .= 's';
        }

        if ($estrellas !== '') {
            $conds[] = "c.calificacion = ?";
            $params[] = (int)$estrellas;
            $types .= 'i';
        }

        if ($fecha_desde !== '') {
            $conds[] = "COALESCE(c.created_at, c.updated_at, NOW()) >= ?";
            $params[] = $fecha_desde . ' 00:00:00';
            $types .= 's';
        }

        if ($fecha_hasta !== '') {
            $conds[] = "COALESCE(c.created_at, c.updated_at, NOW()) <= ?";
            $params[] = $fecha_hasta . ' 23:59:59';
            $types .= 's';
        }

        return [$conds, $params, $types];
    }

    public function obtenerCalificaciones(
        string $busqueda = '',
        string $rol = '',
        string $estado = '',
        string $estrellas = '',
        string $fecha_desde = '',
        string $fecha_hasta = '',
        string $orden = 'c.id',
        string $direccion = 'DESC',
        int $pagina = 1,
        int $limite = 15
    ): array
    {
        [$conds, $params, $types] = $this->buildFilterParams($busqueda, $rol, $estado, $estrellas, $fecha_desde, $fecha_hasta);

        $sql = "SELECT c.id, c.id_pedido, c.id_producto, c.id_consumer, c.tipo, c.calificacion,
                       c.comentario, c.respuesta_admin, c.estado, c.created_at, c.updated_at,
                       con.nombre AS consumer_nombre, con.apellido AS consumer_apellido, con.correo AS consumer_correo, con.foto_perfil AS consumer_foto,
                       p.nombre AS producto_nombre,
                       CASE
                         WHEN c.tipo = 'logistica' THEN rep.nombre
                         ELSE prod_user.nombre
                       END AS evaluado_nombre,
                       CASE
                         WHEN c.tipo = 'logistica' THEN rep.apellido
                         ELSE prod_user.apellido
                       END AS evaluado_apellido,
                       CASE
                         WHEN c.tipo = 'logistica' THEN rep.correo
                         ELSE prod_user.correo
                       END AS evaluado_correo,
                       CASE
                         WHEN c.tipo = 'logistica' THEN rep.foto_perfil
                         ELSE prod_user.foto_perfil
                       END AS evaluado_foto,
                       CASE
                         WHEN c.tipo = 'logistica' THEN 'logistica'
                         ELSE 'productor'
                       END AS rol_evaluado,
                       (SELECT COUNT(*) FROM calificacion_reportes cr WHERE cr.id_calificacion = c.id) AS total_reportes
                FROM calificacion c
                LEFT JOIN usuarios con ON c.id_consumer = con.id
                LEFT JOIN productos p ON c.id_producto = p.id
                LEFT JOIN usuarios prod_user ON p.id_usuario = prod_user.id
                LEFT JOIN entregas e ON c.id_pedido = e.id_pedidos
                LEFT JOIN usuarios rep ON e.id_repartidor = rep.id";

        if (count($conds) > 0) {
            $sql .= " WHERE " . implode(' AND ', $conds);
        }

        $colMap = [
            'id' => 'c.id',
            'calificacion' => 'c.calificacion',
            'fecha' => 'COALESCE(c.created_at, c.updated_at, c.id)',
            'evaluado' => 'evaluado_nombre',
            'evaluador' => 'con.nombre',
            'rol' => 'c.tipo',
            'estado' => 'c.estado',
            'reportes' => 'total_reportes'
        ];
        $col = $colMap[$orden] ?? 'c.id';
        $dir = strtoupper($direccion) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($pagina - 1) * $limite);
        $sql .= " ORDER BY $col $dir LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    public function contarCalificaciones(
        string $busqueda = '',
        string $rol = '',
        string $estado = '',
        string $estrellas = '',
        string $fecha_desde = '',
        string $fecha_hasta = ''
    ): int
    {
        [$conds, $params, $types] = $this->buildFilterParams($busqueda, $rol, $estado, $estrellas, $fecha_desde, $fecha_hasta);

        $sql = "SELECT COUNT(*) AS total
                FROM calificacion c
                LEFT JOIN productos p ON c.id_producto = p.id
                LEFT JOIN usuarios prod_user ON p.id_usuario = prod_user.id
                LEFT JOIN entregas e ON c.id_pedido = e.id_pedidos
                LEFT JOIN usuarios rep ON e.id_repartidor = rep.id
                LEFT JOIN usuarios con ON c.id_consumer = con.id";

        if (count($conds) > 0) {
            $sql .= " WHERE " . implode(' AND ', $conds);
        }

        $stmt = $this->conn->prepare($sql);
        $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }

    public function obtenerCalificacionDetalle(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*,
                    con.nombre AS consumer_nombre, con.apellido AS consumer_apellido, con.correo AS consumer_correo, con.telefono AS consumer_telefono, con.foto_perfil AS consumer_foto,
                    p.nombre AS producto_nombre,
                    CASE
                      WHEN c.tipo = 'logistica' THEN rep.nombre
                      ELSE prod_user.nombre
                    END AS evaluado_nombre,
                    CASE
                      WHEN c.tipo = 'logistica' THEN rep.apellido
                      ELSE prod_user.apellido
                    END AS evaluado_apellido,
                    CASE
                      WHEN c.tipo = 'logistica' THEN rep.correo
                      ELSE prod_user.correo
                    END AS evaluado_correo,
                    CASE
                      WHEN c.tipo = 'logistica' THEN rep.foto_perfil
                      ELSE prod_user.foto_perfil
                    END AS evaluado_foto,
                    CASE
                      WHEN c.tipo = 'logistica' THEN 'logistica'
                      ELSE 'productor'
                    END AS rol_evaluado
             FROM calificacion c
             LEFT JOIN usuarios con ON c.id_consumer = con.id
             LEFT JOIN productos p ON c.id_producto = p.id
             LEFT JOIN usuarios prod_user ON p.id_usuario = prod_user.id
             LEFT JOIN entregas e ON c.id_pedido = e.id_pedidos
             LEFT JOIN usuarios rep ON e.id_repartidor = rep.id
             WHERE c.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        return $item ?: null;
    }

    public function obtenerReportesDeCalificacion(int $idCalificacion): array
    {
        $stmt = $this->conn->prepare(
            "SELECT cr.*, u.nombre AS reporta_nombre, u.apellido AS reporta_apellido,
                    ur.nombre AS reportado_nombre, ur.apellido AS reportado_apellido
             FROM calificacion_reportes cr
             LEFT JOIN usuarios u ON cr.id_usuarioreporta = u.id
             LEFT JOIN usuarios ur ON cr.id_usuarioreportado = ur.id
             WHERE cr.id_calificacion = ?
             ORDER BY cr.created_at DESC"
        );
        $stmt->bind_param('i', $idCalificacion);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    public function obtenerCasosInvestigacion(): array
    {
        $result = $this->conn->query(
            "SELECT cr.id, cr.motivo, cr.prioridad, cr.estado, cr.created_at,
                    TIMESTAMPDIFF(HOUR, cr.created_at, NOW()) AS horas_transcurridas,
                    u.nombre AS reportado_nombre, u.apellido AS reportado_apellido, u.correo AS reportado_correo,
                    c.calificacion, c.id AS id_calificacion
             FROM calificacion_reportes cr
             JOIN calificacion c ON cr.id_calificacion = c.id
             JOIN usuarios u ON cr.id_usuarioreportado = u.id
             WHERE cr.estado IN ('pendiente', 'investigando')
             ORDER BY cr.prioridad DESC, cr.created_at ASC
             LIMIT 10"
        );
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function obtenerPeorReputacion(): array
    {
        $result = $this->conn->query(
            "SELECT u.id, u.nombre, u.apellido, u.correo, u.foto_perfil, u.estado AS estado_cuenta,
                    ROUND(AVG(c.calificacion),2) AS promedio,
                    COUNT(c.id) AS total_calificaciones,
                    (SELECT COUNT(*) FROM calificacion_reportes cr WHERE cr.id_usuarioreportado = u.id) AS total_reportes
             FROM usuarios u
             JOIN productos p ON u.id = p.id_usuario
             JOIN calificacion c ON c.id_producto = p.id
             WHERE c.tipo IN ('producto','productor')
             GROUP BY u.id
             HAVING total_calificaciones >= 2
             ORDER BY promedio ASC
             LIMIT 5"
        );
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function obtenerMejoresAgricultores(): array
    {
        $result = $this->conn->query(
            "SELECT u.id, u.nombre, u.apellido, u.foto_perfil, u.ubicacion_finca,
                    ROUND(AVG(c.calificacion),2) AS promedio,
                    COUNT(c.id) AS total_resenas
             FROM usuarios u
             JOIN productos p ON u.id = p.id_usuario
             JOIN calificacion c ON c.id_producto = p.id
             WHERE c.tipo IN ('producto','productor')
             GROUP BY u.id
             HAVING total_resenas >= 1
             ORDER BY promedio DESC, total_resenas DESC
             LIMIT 5"
        );
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function cambiarEstadoCalificacion(int $id, string $estado): array
    {
        $validos = ['visible', 'oculto', 'investigacion'];
        if (!in_array($estado, $validos, true)) {
            return ['status' => 'error', 'message' => 'Estado no válido'];
        }
        $stmt = $this->conn->prepare("UPDATE calificacion SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();
        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Calificación no encontrada'];
        }
        return ['status' => 'success', 'message' => 'Estado actualizado correctamente'];
    }

    public function crearReporte(int $idCalificacion, int $idReportado, int $idReporta, string $motivo, string $prioridad): array
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO calificacion_reportes (id_calificacion, id_usuarioreportado, id_usuarioreporta, motivo, prioridad) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iiiss', $idCalificacion, $idReportado, $idReporta, $motivo, $prioridad);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        if ($id > 0) {
            $this->cambiarEstadoCalificacion($idCalificacion, 'investigacion');
            return ['status' => 'success', 'message' => 'Reporte creado correctamente', 'id' => $id];
        }
        return ['status' => 'error', 'message' => 'Error al crear reporte'];
    }

    public function responderComentario(int $id, string $respuesta): array
    {
        $stmt = $this->conn->prepare("UPDATE calificacion SET respuesta_admin = ? WHERE id = ?");
        $stmt->bind_param('si', $respuesta, $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();
        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Calificación no encontrada'];
        }
        return ['status' => 'success', 'message' => 'Respuesta guardada correctamente'];
    }

    public function suspenderUsuario(int $idUsuario): array
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE id = ?");
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();
        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Usuario no encontrado'];
        }
        return ['status' => 'success', 'message' => 'Usuario suspendido correctamente'];
    }

    public function resolverReporte(int $id, string $nuevoEstado): array
    {
        $validos = ['investigando', 'resuelto', 'cerrado'];
        if (!in_array($nuevoEstado, $validos, true)) {
            return ['status' => 'error', 'message' => 'Estado no válido'];
        }
        $stmt = $this->conn->prepare("UPDATE calificacion_reportes SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $nuevoEstado, $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();
        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Reporte no encontrado'];
        }
        return ['status' => 'success', 'message' => 'Reporte actualizado correctamente'];
    }
}
