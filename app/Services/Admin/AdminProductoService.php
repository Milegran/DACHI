<?php

class AdminProductoService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    private function buildFilterParams(
        string $busqueda = '',
        string $estado = '',
        string $categoria = ''
    ): array
    {
        $conds = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $b = '%' . $busqueda . '%';
            $conds[] = "(p.nombre LIKE ? OR c.nombre LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR p.nom_productor LIKE ? OR p.estado_aprobacion LIKE ?)";
            $params = array_merge($params, [$b, $b, $b, $b, $b, $b]);
            $types .= 'ssssss';
        }

        if ($estado !== '') {
            $conds[] = "p.estado_aprobacion = ?";
            $params[] = $estado;
            $types .= 's';
        }

        if ($categoria !== '') {
            $conds[] = "c.id = ?";
            $params[] = (int)$categoria;
            $types .= 'i';
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

    public function obtenerProductos(
        string $busqueda = '',
        string $estado = '',
        string $categoria = '',
        string $orden = 'id',
        string $direccion = 'DESC',
        int $pagina = 1,
        int $limite = 15
    ): array
    {
        [$conds, $params, $types] = $this->buildFilterParams($busqueda, $estado, $categoria);

        $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.cantidad AS stock,
                       p.imagen, p.estado_aprobacion, p.motivo_rechazo,
                       p.created_at, p.nom_productor,
                       COALESCE(c.nombre, 'Sin categoría') AS categoria,
                       COALESCE(u.nombre, '') AS productor_nombre,
                       COALESCE(u.apellido, '') AS productor_apellido
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN usuarios u ON p.id_usuario = u.id";

        if (count($conds) > 0) {
            $sql .= " WHERE " . implode(' AND ', $conds);
        }

        $colMap = [
            'id' => 'p.id', 'nombre' => 'p.nombre', 'categoria' => 'c.nombre',
            'productor' => 'u.nombre', 'precio' => 'p.precio', 'stock' => 'p.cantidad',
            'estado' => 'p.estado_aprobacion', 'created_at' => 'p.created_at'
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
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        $stmt->close();
        return $productos;
    }

    public function contarProductos(
        string $busqueda = '',
        string $estado = '',
        string $categoria = ''
    ): int
    {
        [$conds, $params, $types] = $this->buildFilterParams($busqueda, $estado, $categoria);

        $sql = "SELECT COUNT(*) AS total
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN usuarios u ON p.id_usuario = u.id";

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

    public function obtenerEstadisticas(): array
    {
        $stats = ['total' => 0, 'pendiente' => 0, 'aprobado' => 0, 'rechazado' => 0, 'inactivo' => 0];

        $result = $this->conn->query(
            "SELECT COALESCE(estado_aprobacion, 'pendiente') AS estado, COUNT(*) AS total
             FROM productos GROUP BY estado_aprobacion"
        );
        while ($row = $result->fetch_assoc()) {
            $estado = $row['estado'];
            $stats[$estado] = (int)$row['total'];
            $stats['total'] += (int)$row['total'];
        }
        return $stats;
    }

    public function obtenerCategorias(): array
    {
        $result = $this->conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
        $cats = [];
        while ($row = $result->fetch_assoc()) {
            $cats[] = $row;
        }
        return $cats;
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

    public function obtenerProducto(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.*, COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    COALESCE(u.nombre, '') AS productor_nombre,
                    COALESCE(u.apellido, '') AS productor_apellido
             FROM productos p
             LEFT JOIN categorias c ON p.id_categoria = c.id
             LEFT JOIN usuarios u ON p.id_usuario = u.id
             WHERE p.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function actualizarProducto(array $data): array
    {
        $id = (int)($data['id'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $precio = (float)($data['precio'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $idCategoria = !empty($data['id_categoria']) ? (int)$data['id_categoria'] : null;
        $estado = in_array($data['estado'] ?? '', ['pendiente', 'aprobado', 'rechazado', 'inactivo']) ? $data['estado'] : 'pendiente';
        $imagen = trim($data['imagen'] ?? '');

        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID de producto inválido'];
        }
        if ($nombre === '') {
            return ['status' => 'error', 'message' => 'El nombre del producto es obligatorio'];
        }
        if (mb_strlen($nombre) > 100) {
            return ['status' => 'error', 'message' => 'El nombre no puede exceder 100 caracteres'];
        }
        if ($precio <= 0) {
            return ['status' => 'error', 'message' => 'El precio debe ser mayor a 0'];
        }
        if ($precio > 999999.99) {
            return ['status' => 'error', 'message' => 'El precio no puede exceder 999,999.99'];
        }
        if ($stock < 0) {
            return ['status' => 'error', 'message' => 'El stock no puede ser negativo'];
        }
        if ($stock > 999999) {
            return ['status' => 'error', 'message' => 'El stock no puede exceder 999,999'];
        }

        if ($idCategoria === null) {
            $stmt = $this->conn->prepare(
                "UPDATE productos SET nombre=?, descripcion=?, precio=?, cantidad=?, id_categoria=NULL, estado_aprobacion=?"
                . ($imagen !== '' ? ", imagen=?" : "") . " WHERE id=?"
            );
            if ($imagen !== '') {
                $stmt->bind_param('ssdissi', $nombre, $descripcion, $precio, $stock, $estado, $imagen, $id);
            } else {
                $stmt->bind_param('ssdisi', $nombre, $descripcion, $precio, $stock, $estado, $id);
            }
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE productos SET nombre=?, descripcion=?, precio=?, cantidad=?, id_categoria=?, estado_aprobacion=?"
                . ($imagen !== '' ? ", imagen=?" : "") . " WHERE id=?"
            );
            if ($imagen !== '') {
                $stmt->bind_param('ssdiissi', $nombre, $descripcion, $precio, $stock, $idCategoria, $estado, $imagen, $id);
            } else {
                $stmt->bind_param('ssdiisi', $nombre, $descripcion, $precio, $stock, $idCategoria, $estado, $id);
            }
        }
        $stmt->execute();
        $stmt->close();

        return ['status' => 'success', 'message' => 'Producto actualizado correctamente'];
    }

    public function crearProducto(array $data): array
    {
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $precio = (float)($data['precio'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $idCategoria = !empty($data['id_categoria']) ? (int)$data['id_categoria'] : null;
        $estado = in_array($data['estado'] ?? '', ['pendiente', 'aprobado', 'rechazado', 'inactivo']) ? $data['estado'] : 'pendiente';
        $imagen = trim($data['imagen'] ?? '');

        if ($nombre === '') {
            return ['status' => 'error', 'message' => 'El nombre del producto es obligatorio'];
        }
        if (mb_strlen($nombre) > 100) {
            return ['status' => 'error', 'message' => 'El nombre no puede exceder 100 caracteres'];
        }
        if ($precio <= 0) {
            return ['status' => 'error', 'message' => 'El precio debe ser mayor a 0'];
        }
        if ($precio > 999999.99) {
            return ['status' => 'error', 'message' => 'El precio no puede exceder 999,999.99'];
        }
        if ($stock < 0) {
            return ['status' => 'error', 'message' => 'El stock no puede ser negativo'];
        }
        if ($stock > 999999) {
            return ['status' => 'error', 'message' => 'El stock no puede exceder 999,999'];
        }

        if ($idCategoria === null) {
            $stmt = $this->conn->prepare(
                "INSERT INTO productos (nombre, descripcion, precio, cantidad, id_categoria, estado_aprobacion, imagen, created_at)
                 VALUES (?, ?, ?, ?, NULL, ?, ?, NOW())"
            );
            $stmt->bind_param('ssdsss', $nombre, $descripcion, $precio, $stock, $estado, $imagen);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO productos (nombre, descripcion, precio, cantidad, id_categoria, estado_aprobacion, imagen, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param('ssdiiss', $nombre, $descripcion, $precio, $stock, $idCategoria, $estado, $imagen);
        }
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'Error al crear el producto'];
        }

        return ['status' => 'success', 'message' => 'Producto creado correctamente', 'id' => $id];
    }
}
