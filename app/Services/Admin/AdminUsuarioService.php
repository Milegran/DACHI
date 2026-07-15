<?php

class AdminUsuarioService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function obtenerProductores(string $busqueda = '', bool $soloInactivos = false): array
    {
        $sql = "SELECT u.id, u.id_rol, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                       u.fecha_registro, u.ubicacion_finca, u.ultimo_acceso,
                       (SELECT COUNT(*) FROM productos p WHERE p.id_usuario = u.id) AS total_productos
                FROM usuarios u
                WHERE u.id_rol = 2";

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
        $productores = [];
        while ($row = $result->fetch_assoc()) {
            $row['calificacion_promedio'] = $this->obtenerCalificacionProductor((int)$row['id']);
            $productores[] = $row;
        }
        $stmt->close();
        return $productores;
    }

    public function obtenerProductor(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, u.estado,
                    u.fecha_registro, u.ultimo_acceso, u.foto_perfil,
                    u.ubicacion_finca, u.datos_bancarios, u.practicas_produccion
             FROM usuarios u
             WHERE u.id = ? AND u.id_rol = 2"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $productor = $result->fetch_assoc();
        $stmt->close();

        if ($productor === null) {
            return null;
        }

        $productor['calificacion_promedio'] = $this->obtenerCalificacionProductor($id);
        $productor['total_productos'] = $this->contarProductosProductor($id);
        $productor['total_ventas'] = $this->contarVentasProductor($id);
        return $productor;
    }

    public function obtenerCalificacionProductor(int $idProductor): float
    {
        $stmt = $this->conn->prepare(
            "SELECT ROUND(AVG(c.calificacion), 1) AS promedio
             FROM calificacion c
             JOIN productos p ON c.id_producto = p.id
             WHERE p.id_usuario = ? AND c.tipo = 'producto'"
        );
        $stmt->bind_param('i', $idProductor);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['promedio'] ?? 0);
    }

    private function contarProductosProductor(int $idProductor): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM productos WHERE id_usuario = ?");
        $stmt->bind_param('i', $idProductor);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    private function contarVentasProductor(int $idProductor): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT ip.id_pedidos) AS total
             FROM info_pedidos ip
             JOIN productos p ON ip.id_productos = p.id
             WHERE p.id_usuario = ?"
        );
        $stmt->bind_param('i', $idProductor);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    public function obtenerProductosPorProductor(int $idProductor): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.id, p.nombre, p.descripcion, p.precio, p.cantidad AS stock,
                    p.imagen, p.estado_aprobacion, p.motivo_rechazo,
                    p.created_at, p.updated_at,
                    COALESCE(c.nombre, 'Sin categoría') AS categoria
             FROM productos p
             LEFT JOIN categorias c ON p.id_categoria = c.id
             WHERE p.id_usuario = ?
             ORDER BY p.id DESC"
        );
        $stmt->bind_param('i', $idProductor);
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $row['precio'] = number_format((float)$row['precio'], 2);
            $productos[] = $row;
        }
        $stmt->close();
        return $productos;
    }

    public function cambiarEstadoProducto(int $idProducto, string $nuevoEstado, string $motivo = ''): array
    {
        $validos = ['pendiente', 'aprobado', 'rechazado', 'inactivo'];
        if (!in_array($nuevoEstado, $validos, true)) {
            return ['status' => 'error', 'message' => 'Estado no válido'];
        }

        if ($nuevoEstado === 'rechazado' && $motivo === '') {
            return ['status' => 'error', 'message' => 'Debe indicar un motivo de rechazo'];
        }

        $motivo = $nuevoEstado === 'rechazado' ? $motivo : null;
        $stmt = $this->conn->prepare(
            "UPDATE productos SET estado_aprobacion = ?, motivo_rechazo = ? WHERE id = ?"
        );
        $stmt->bind_param('ssi', $nuevoEstado, $motivo, $idProducto);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();

        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Producto no encontrado'];
        }

        return ['status' => 'success', 'message' => 'Estado actualizado correctamente'];
    }

    public function obtenerDashboardStats(): array
    {
        $stats = [];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM productos");
        $stats['total_productos'] = (int)$result->fetch_assoc()['total'];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM usuarios");
        $stats['total_usuarios'] = (int)$result->fetch_assoc()['total'];

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM pedidos");
        $stats['total_pedidos'] = (int)$result->fetch_assoc()['total'];

        $result = $this->conn->query("SELECT COALESCE(SUM(total_compra), 0) AS total FROM pedidos");
        $stats['total_ingresos'] = (float)$result->fetch_assoc()['total'];

        $result = $this->conn->query("SELECT ROUND(AVG(calificacion), 1) AS promedio FROM calificacion");
        $row = $result->fetch_assoc();
        $stats['calificacion_promedio'] = $row['promedio'] ? (float)$row['promedio'] : 0;

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM calificacion");
        $stats['total_comentarios'] = (int)$result->fetch_assoc()['total'];

        $pedidos = $this->conn->query(
            "SELECT p.id, p.fecha, p.total_compra, p.estado,
                    u.nombre, u.apellido, u.correo
             FROM pedidos p
             JOIN usuarios u ON p.id_consumer = u.id
             ORDER BY p.id DESC
             LIMIT 5"
        );
        $stats['pedidos_recientes'] = [];
        while ($row = $pedidos->fetch_assoc()) {
            $stats['pedidos_recientes'][] = $row;
        }

        return $stats;
    }

    public function crearUsuario(array $data): array
    {
        $idRol = (int)($data['id_rol'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $password = $data['password'] ?? '';

        if ($idRol < 1 || $idRol > 4) {
            return ['status' => 'error', 'message' => 'Rol inválido'];
        }
        if ($nombre === '' || $apellido === '' || $correo === '' || $password === '') {
            return ['status' => 'error', 'message' => 'Campos obligatorios: nombre, apellido, correo, contraseña'];
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Correo electrónico inválido'];
        }

        $check = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $check->bind_param('s', $correo);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $check->close();
            return ['status' => 'error', 'message' => 'El correo ya está registrado'];
        }
        $check->close();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            "INSERT INTO usuarios (id_rol, nombre, apellido, correo, contraseña, telefono, estado)
             VALUES (?, ?, ?, ?, ?, ?, 'activo')"
        );
        $stmt->bind_param('isssss', $idRol, $nombre, $apellido, $correo, $hash, $telefono);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'Error al crear el usuario'];
        }

        return ['status' => 'success', 'message' => 'Usuario creado correctamente', 'id' => $id];
    }

    public function actualizarUsuario(int $id, array $data): array
    {
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $password = $data['password'] ?? '';

        if ($nombre === '' || $apellido === '' || $correo === '') {
            return ['status' => 'error', 'message' => 'Campos obligatorios: nombre, apellido, correo'];
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Correo electrónico inválido'];
        }

        $check = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $check->bind_param('si', $correo, $id);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $check->close();
            return ['status' => 'error', 'message' => 'El correo ya está en uso por otro usuario'];
        }
        $check->close();

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET nombre=?, apellido=?, correo=?, telefono=?, contraseña=? WHERE id=?"
            );
            $stmt->bind_param('sssssi', $nombre, $apellido, $correo, $telefono, $hash, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE usuarios SET nombre=?, apellido=?, correo=?, telefono=? WHERE id=?"
            );
            $stmt->bind_param('ssssi', $nombre, $apellido, $correo, $telefono, $id);
        }
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();

        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Usuario no encontrado o sin cambios'];
        }

        return ['status' => 'success', 'message' => 'Usuario actualizado correctamente'];
    }

    public function eliminarUsuario(int $id): array
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET estado='inactivo' WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();

        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Usuario no encontrado'];
        }

        return ['status' => 'success', 'message' => 'Usuario desactivado correctamente.'];
    }

    public function activarUsuario(int $id): array
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET estado='activo' WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();

        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Usuario no encontrado'];
        }

        return ['status' => 'success', 'message' => 'Usuario activado correctamente. Ya puede acceder a la plataforma.'];
    }

    public function obtenerHistorialActividad(int $idUsuario): array
    {
        $stmt = $this->conn->prepare(
            "SELECT 'ultimo_acceso' AS tipo, u.ultimo_acceso AS fecha, 'Inicio de sesión' AS descripcion
             FROM usuarios u WHERE u.id = ?
             UNION ALL
             SELECT 'pedido' AS tipo, p.fecha AS fecha, CONCAT('Pedido #', p.id, ' - $', p.total_compra) AS descripcion
             FROM info_pedidos ip
             JOIN productos pr ON ip.id_productos = pr.id
             JOIN pedidos p ON ip.id_pedidos = p.id
             WHERE pr.id_usuario = ?
             UNION ALL
             SELECT 'producto' AS tipo, p.created_at AS fecha, CONCAT('Producto: ', p.nombre) AS descripcion
             FROM productos p WHERE p.id_usuario = ?
             ORDER BY fecha DESC
             LIMIT 20"
        );
        $stmt->bind_param('iii', $idUsuario, $idUsuario, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $actividad = [];
        while ($row = $result->fetch_assoc()) {
            $actividad[] = $row;
        }
        $stmt->close();
        return $actividad;
    }
}
