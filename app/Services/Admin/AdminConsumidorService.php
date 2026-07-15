<?php

class AdminConsumidorService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function obtenerConsumidores(string $busqueda = '', bool $soloInactivos = false): array
    {
        $sql = "SELECT u.id, u.id_rol, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                       u.fecha_registro, u.ultimo_acceso,
                       (SELECT COUNT(*) FROM pedidos p WHERE p.id_consumer = u.id) AS total_compras,
                       (SELECT COALESCE(SUM(p.total_compra), 0) FROM pedidos p WHERE p.id_consumer = u.id) AS monto_total
                FROM usuarios u
                WHERE u.id_rol = 1";

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
        $consumidores = [];
        while ($row = $result->fetch_assoc()) {
            $row['total_direcciones'] = $this->contarDirecciones((int)$row['id']);
            $consumidores[] = $row;
        }
        $stmt->close();
        return $consumidores;
    }

    public function obtenerConsumidor(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                    u.fecha_registro, u.ultimo_acceso, u.foto_perfil
             FROM usuarios u
             WHERE u.id = ? AND u.id_rol = 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $consumidor = $result->fetch_assoc();
        $stmt->close();

        if ($consumidor === null) {
            return null;
        }

        $consumidor['total_compras'] = $this->contarCompras($id);
        $consumidor['monto_total'] = $this->sumarMontoCompras($id);
        $consumidor['total_direcciones'] = $this->contarDirecciones($id);
        $consumidor['total_calificaciones'] = $this->contarCalificaciones($id);
        return $consumidor;
    }

    public function obtenerDireccionesConsumidor(int $idConsumidor): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre_direccion, provincia, distrito, corregimiento, detalle
             FROM direccion
             WHERE id_usuario = ?
             ORDER BY id DESC"
        );
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $result = $stmt->get_result();
        $direcciones = [];
        while ($row = $result->fetch_assoc()) {
            $direcciones[] = $row;
        }
        $stmt->close();
        return $direcciones;
    }

    public function obtenerPedidosConsumidor(int $idConsumidor): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.id, p.fecha, p.total_compra, p.estado_detallado, p.metodo_pago,
                    m.nombre AS metodo_pago_nombre,
                    GROUP_CONCAT(DISTINCT pr.nombre SEPARATOR ', ') AS productos_nombres,
                    GROUP_CONCAT(DISTINCT CONCAT(u.nombre, ' ', u.apellido) SEPARATOR ', ') AS productores_nombres
             FROM pedidos p
             LEFT JOIN metodos m ON p.metodo_pago = m.id
             LEFT JOIN info_pedidos ip ON ip.id_pedidos = p.id
             LEFT JOIN productos pr ON ip.id_productos = pr.id
             LEFT JOIN usuarios u ON pr.id_usuario = u.id
             WHERE p.id_consumer = ?
             GROUP BY p.id
             ORDER BY p.fecha DESC
             LIMIT 50"
        );
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $stmt->close();
        return $pedidos;
    }

    public function obtenerCalificacionesConsumidor(int $idConsumidor): array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.calificacion, c.comentario, c.tipo, c.created_at,
                    pr.nombre AS producto_nombre
             FROM calificacion c
             LEFT JOIN productos pr ON c.id_producto = pr.id
             WHERE c.id_consumer = ?
             ORDER BY c.id DESC
             LIMIT 20"
        );
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $result = $stmt->get_result();
        $calificaciones = [];
        while ($row = $result->fetch_assoc()) {
            $calificaciones[] = $row;
        }
        $stmt->close();
        return $calificaciones;
    }

    public function obtenerHistorialActividad(int $idUsuario): array
    {
        $stmt = $this->conn->prepare(
            "SELECT 'ultimo_acceso' AS tipo, u.ultimo_acceso AS fecha, 'Inicio de sesión' AS descripcion
             FROM usuarios u WHERE u.id = ?
             UNION ALL
             SELECT 'pedido' AS tipo, p.fecha AS fecha, CONCAT('Pedido #', p.id, ' - $', p.total_compra) AS descripcion
             FROM pedidos p WHERE p.id_consumer = ?
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

    private function contarCompras(int $idConsumidor): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE id_consumer = ?");
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    private function sumarMontoCompras(int $idConsumidor): float
    {
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(total_compra), 0) AS total FROM pedidos WHERE id_consumer = ?");
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (float)$row['total'];
    }

    private function contarDirecciones(int $idConsumidor): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM direccion WHERE id_usuario = ?");
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    private function contarCalificaciones(int $idConsumidor): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM calificacion WHERE id_consumer = ?");
        $stmt->bind_param('i', $idConsumidor);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }
}
