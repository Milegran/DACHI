<?php

class AdminPedidoService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function buildFilterParams(
        string $busqueda = '',
        string $estado = '',
        string $metodoPago = '',
        string $agricultor = '',
        string $logistica = '',
        string $provincia = '',
        string $fechaInicio = '',
        string $fechaFin = ''
    ): array
    {
        $conds = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $b = '%' . $busqueda . '%';
            $conds[] = "(p.id LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ?)";
            $params = array_merge($params, [$b, $b, $b, $b]);
            $types .= 'ssss';
        }

        if ($estado !== '') {
            $conds[] = "p.estado_detallado = ?";
            $params[] = $estado;
            $types .= 's';
        }

        if ($metodoPago !== '') {
            $conds[] = "p.metodo_pago = ?";
            $params[] = (int)$metodoPago;
            $types .= 'i';
        }

        if ($agricultor !== '') {
            $conds[] = "ag.id = ?";
            $params[] = (int)$agricultor;
            $types .= 'i';
        }

        if ($logistica !== '') {
            $conds[] = "lg.id = ?";
            $params[] = (int)$logistica;
            $types .= 'i';
        }

        if ($provincia !== '') {
            $conds[] = "d.provincia = ?";
            $params[] = $provincia;
            $types .= 's';
        }

        if ($fechaInicio !== '') {
            $conds[] = "p.fecha >= ?";
            $params[] = $fechaInicio;
            $types .= 's';
        }

        if ($fechaFin !== '') {
            $conds[] = "p.fecha <= ?";
            $params[] = $fechaFin;
            $types .= 's';
        }

        return [$conds, $params, $types];
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

    private function fromSqlResult(\mysqli_result $result): array
    {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function obtenerPedidos(
        string $busqueda = '',
        string $estado = '',
        string $metodoPago = '',
        string $agricultor = '',
        string $logistica = '',
        string $provincia = '',
        string $fechaInicio = '',
        string $fechaFin = '',
        string $orden = 'id',
        string $direccion = 'DESC',
        int $pagina = 1,
        int $limite = 15
    ): array
    {
        [$conds, $params, $types] = $this->buildFilterParams(
            $busqueda, $estado, $metodoPago,
            $agricultor, $logistica, $provincia,
            $fechaInicio, $fechaFin
        );

        $sql = "SELECT p.id, p.fecha, p.total_compra, p.estado_detallado, p.metodo_pago,
                       p.fecha_entrega, p.notas,
                       u.nombre AS consumer_nombre, u.apellido AS consumer_apellido,
                       u.correo AS consumer_correo, u.telefono AS consumer_telefono,
                       m.nombre AS metodo_pago_nombre,
                       GROUP_CONCAT(DISTINCT CONCAT(COALESCE(ag.nombre,''), ' ', COALESCE(ag.apellido,'')) SEPARATOR ', ') AS agricultores,
                       GROUP_CONCAT(DISTINCT CONCAT(COALESCE(lg.nombre,''), ' ', COALESCE(lg.apellido,'')) SEPARATOR ', ') AS logisticos
                FROM pedidos p
                LEFT JOIN usuarios u ON p.id_consumer = u.id
                LEFT JOIN metodos m ON p.metodo_pago = m.id
                LEFT JOIN info_pedidos ip ON p.id = ip.id_pedidos
                LEFT JOIN productos pr ON ip.id_productos = pr.id
                LEFT JOIN usuarios ag ON pr.id_usuario = ag.id
                LEFT JOIN entregas e ON p.id = e.id_pedidos
                LEFT JOIN usuarios lg ON e.id_repartidor = lg.id
                LEFT JOIN direccion d ON e.id_direccion = d.id";

        $where = count($conds) > 0 ? " WHERE " . implode(' AND ', $conds) : "";
        $sql .= $where;
        $sql .= " GROUP BY p.id";

        $colMap = [
            'id' => 'p.id',
            'fecha' => 'p.fecha',
            'total' => 'p.total_compra',
            'estado' => 'p.estado_detallado',
            'cliente' => 'u.nombre',
            'metodo_pago' => 'm.nombre'
        ];
        $col = $colMap[$orden] ?? 'p.id';
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
        $pedidos = $this->fromSqlResult($result);
        $stmt->close();
        return $pedidos;
    }

    public function contarPedidos(
        string $busqueda = '',
        string $estado = '',
        string $metodoPago = '',
        string $agricultor = '',
        string $logistica = '',
        string $provincia = '',
        string $fechaInicio = '',
        string $fechaFin = ''
    ): int
    {
        [$conds, $params, $types] = $this->buildFilterParams(
            $busqueda, $estado, $metodoPago,
            $agricultor, $logistica, $provincia,
            $fechaInicio, $fechaFin
        );

        $sql = "SELECT COUNT(DISTINCT p.id) AS total
                FROM pedidos p
                LEFT JOIN usuarios u ON p.id_consumer = u.id
                LEFT JOIN info_pedidos ip ON p.id = ip.id_pedidos
                LEFT JOIN productos pr ON ip.id_productos = pr.id
                LEFT JOIN usuarios ag ON pr.id_usuario = ag.id
                LEFT JOIN entregas e ON p.id = e.id_pedidos
                LEFT JOIN usuarios lg ON e.id_repartidor = lg.id
                LEFT JOIN direccion d ON e.id_direccion = d.id";

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

    public function obtenerEstadisticasExpandidas(): array
    {
        $stats = [
            'total' => 0, 'pendiente' => 0, 'en_preparacion' => 0,
            'en_transito' => 0, 'entregado' => 0, 'cancelado' => 0,
            'entregados_hoy' => 0, 'con_incidencias' => 0
        ];

        $result = $this->conn->query(
            "SELECT
               COALESCE(estado_detallado, 'pendiente') AS estado,
               COUNT(*) AS total,
               SUM(CASE WHEN estado_detallado = 'entregado' AND fecha = CURDATE() THEN 1 ELSE 0 END) AS entregados_hoy,
               SUM(CASE WHEN (estado_detallado = 'cancelado' AND notas IS NOT NULL AND notas != '') THEN 1 ELSE 0 END) AS con_incidencias
             FROM pedidos
             GROUP BY estado_detallado
             WITH ROLLUP"
        );
        while ($row = $result->fetch_assoc()) {
            $estado = $row['estado'];
            if ($estado === null) {
                $stats['total'] = (int)$row['total'];
                $stats['entregados_hoy'] = (int)$row['entregados_hoy'];
                $stats['con_incidencias'] = (int)$row['con_incidencias'];
            } elseif (isset($stats[$estado])) {
                $stats[$estado] = (int)$row['total'];
            }
        }
        return $stats;
    }

    public function obtenerPedidosPorEstado(): array
    {
        $result = $this->conn->query(
            "SELECT COALESCE(estado_detallado, 'pendiente') AS estado, COUNT(*) AS total
             FROM pedidos
             GROUP BY estado_detallado
             ORDER BY FIELD(estado_detallado, 'pendiente','en_preparacion','en_transito','entregado','cancelado')"
        );
        return $this->fromSqlResult($result);
    }

    public function obtenerPedidosPorDia(): array
    {
        $result = $this->conn->query(
            "SELECT DAYNAME(fecha) AS dia, DAYOFWEEK(fecha) AS num_dia, COUNT(*) AS total
             FROM pedidos
             WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DAYOFWEEK(fecha), DAYNAME(fecha)
             ORDER BY DAYOFWEEK(fecha)"
        );
        $rows = $this->fromSqlResult($result);
        $diasOrden = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $mapa = [];
        foreach ($rows as $r) {
            $mapa[$r['dia']] = (int)$r['total'];
        }
        $resultado = [];
        foreach ($diasOrden as $d) {
            $resultado[] = [
                'dia' => substr($d, 0, 3),
                'total' => $mapa[$d] ?? 0
            ];
        }
        return $resultado;
    }

    public function obtenerAgricultores(): array
    {
        $result = $this->conn->query(
            "SELECT DISTINCT u.id, u.nombre, u.apellido
             FROM usuarios u
             INNER JOIN productos p ON u.id = p.id_usuario
             WHERE u.id_rol IN (2,4) AND u.estado = 'activo'
             ORDER BY u.nombre ASC"
        );
        return $this->fromSqlResult($result);
    }

    public function obtenerLogisticos(): array
    {
        $result = $this->conn->query(
            "SELECT DISTINCT u.id, u.nombre, u.apellido
             FROM usuarios u
             INNER JOIN entregas e ON u.id = e.id_repartidor
             WHERE u.id_rol = 3 AND u.estado = 'activo'
             ORDER BY u.nombre ASC"
        );
        return $this->fromSqlResult($result);
    }

    public function obtenerProvincias(): array
    {
        $result = $this->conn->query(
            "SELECT DISTINCT d.provincia
             FROM direccion d
             INNER JOIN entregas e ON d.id = e.id_direccion
             WHERE d.provincia IS NOT NULL AND d.provincia != ''
             ORDER BY d.provincia ASC"
        );
        $provincias = [];
        while ($row = $result->fetch_assoc()) {
            $provincias[] = $row['provincia'];
        }
        return $provincias;
    }

    public function obtenerEstadisticas(): array
    {
        $stats = [
            'total' => 0, 'pendiente' => 0, 'en_preparacion' => 0,
            'en_transito' => 0, 'entregado' => 0, 'cancelado' => 0
        ];

        $result = $this->conn->query(
            "SELECT COALESCE(estado_detallado, 'pendiente') AS estado, COUNT(*) AS total
             FROM pedidos GROUP BY estado_detallado"
        );
        while ($row = $result->fetch_assoc()) {
            $estado = $row['estado'];
            if (isset($stats[$estado])) {
                $stats[$estado] = (int)$row['total'];
                $stats['total'] += (int)$row['total'];
            }
        }
        return $stats;
    }

    public function obtenerMetodosPago(): array
    {
        $result = $this->conn->query("SELECT id, nombre FROM metodos ORDER BY nombre ASC");
        return $this->fromSqlResult($result);
    }

    public function obtenerPedido(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.*, u.nombre AS consumer_nombre, u.apellido AS consumer_apellido,
                    u.correo AS consumer_correo, u.telefono AS consumer_telefono,
                    m.nombre AS metodo_pago_nombre
             FROM pedidos p
             LEFT JOIN usuarios u ON p.id_consumer = u.id
             LEFT JOIN metodos m ON p.metodo_pago = m.id
             WHERE p.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $stmt->close();
        return $pedido ?: null;
    }

    public function obtenerProductosDePedido(int $pedidoId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT ip.id, ip.cantidad, ip.precio_unitario, ip.subtotal,
                    pr.nombre AS producto_nombre, pr.imagen AS producto_imagen,
                    COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido,'')) AS agricultor_nombre
             FROM info_pedidos ip
             JOIN productos pr ON ip.id_productos = pr.id
             LEFT JOIN categorias c ON pr.id_categoria = c.id
             LEFT JOIN usuarios u ON pr.id_usuario = u.id
             WHERE ip.id_pedidos = ?
             ORDER BY ip.id ASC"
        );
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $this->fromSqlResult($result);
        $stmt->close();
        return $items;
    }

    public function obtenerEntregaDePedido(int $pedidoId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT e.*, u.nombre AS repartidor_nombre, u.apellido AS repartidor_apellido,
                    u.correo AS repartidor_correo, u.telefono AS repartidor_telefono,
                    d.provincia, d.distrito, d.corregimiento, d.detalle AS direccion_detalle,
                    d.nombre_direccion
             FROM entregas e
             LEFT JOIN usuarios u ON e.id_repartidor = u.id
             LEFT JOIN direccion d ON e.id_direccion = d.id
             WHERE e.id_pedidos = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $entrega = $result->fetch_assoc();
        $stmt->close();
        return $entrega ?: null;
    }

    public function actualizarEstadoPedido(int $id, string $nuevoEstado): array
    {
        $validos = ['pendiente', 'en_preparacion', 'en_transito', 'entregado', 'cancelado'];
        if (!in_array($nuevoEstado, $validos, true)) {
            return ['status' => 'error', 'message' => 'Estado no válido'];
        }

        $stmt = $this->conn->prepare("UPDATE pedidos SET estado_detallado = ? WHERE id = ?");
        $stmt->bind_param('si', $nuevoEstado, $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();

        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Pedido no encontrado'];
        }

        return ['status' => 'success', 'message' => 'Estado actualizado correctamente'];
    }

    public function obtenerRepartidores(): array
    {
        $result = $this->conn->query(
            "SELECT id, nombre, apellido, correo FROM usuarios WHERE id_rol = 3 AND estado = 'activo' ORDER BY nombre ASC"
        );
        return $this->fromSqlResult($result);
    }
}
