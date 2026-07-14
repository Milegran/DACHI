<?php

class AdminCategoriaService
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

    public function obtenerCategorias(
        string $busqueda = '',
        string $orden = 'id',
        string $direccion = 'DESC',
        int $pagina = 1,
        int $limite = 15
    ): array {
        $conds = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $b = '%' . $busqueda . '%';
            $conds[] = "(c.nombre LIKE ? OR c.descripcion LIKE ?)";
            $params = array_merge($params, [$b, $b]);
            $types .= 'ss';
        }

        $sql = "SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id) AS total_productos
                FROM categorias c";

        if (count($conds) > 0) {
            $sql .= " WHERE " . implode(' AND ', $conds);
        }

        $colMap = ['id' => 'c.id', 'nombre' => 'c.nombre', 'total_productos' => 'total_productos'];
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
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public function contarCategorias(string $busqueda = ''): int
    {
        $conds = [];
        $params = [];
        $types = '';

        if ($busqueda !== '') {
            $b = '%' . $busqueda . '%';
            $conds[] = "(nombre LIKE ? OR descripcion LIKE ?)";
            $params = array_merge($params, [$b, $b]);
            $types .= 'ss';
        }

        $sql = "SELECT COUNT(*) AS total FROM categorias c";
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

    public function obtenerCategoria(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id) AS total_productos
             FROM categorias c WHERE c.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function obtenerEstadisticas(): array
    {
        $stats = ['total' => 0, 'activo' => 0, 'inactivo' => 0];
        $result = $this->conn->query(
            "SELECT COALESCE(estado, 'activo') AS estado, COUNT(*) AS total FROM categorias GROUP BY estado"
        );
        while ($row = $result->fetch_assoc()) {
            $estado = $row['estado'];
            $stats[$estado] = (int)$row['total'];
            $stats['total'] += (int)$row['total'];
        }
        return $stats;
    }

    public function crearCategoria(array $data): array
    {
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if ($nombre === '') {
            return ['status' => 'error', 'message' => 'El nombre de la categoría es obligatorio'];
        }
        if (mb_strlen($nombre) > 20) {
            return ['status' => 'error', 'message' => 'El nombre no puede exceder 20 caracteres'];
        }
        if (mb_strlen($descripcion) > 50) {
            return ['status' => 'error', 'message' => 'La descripción no puede exceder 50 caracteres'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)"
        );
        $stmt->bind_param('ss', $nombre, $descripcion);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'Error al crear la categoría'];
        }

        return ['status' => 'success', 'message' => 'Categoría creada correctamente', 'id' => $id];
    }

    public function actualizarCategoria(array $data): array
    {
        $id = (int)($data['id'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID de categoría inválido'];
        }
        if ($nombre === '') {
            return ['status' => 'error', 'message' => 'El nombre de la categoría es obligatorio'];
        }
        if (mb_strlen($nombre) > 20) {
            return ['status' => 'error', 'message' => 'El nombre no puede exceder 20 caracteres'];
        }
        if (mb_strlen($descripcion) > 50) {
            return ['status' => 'error', 'message' => 'La descripción no puede exceder 50 caracteres'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE categorias SET nombre=?, descripcion=? WHERE id=?"
        );
        $stmt->bind_param('ssi', $nombre, $descripcion, $id);
        $stmt->execute();
        $stmt->close();

        return ['status' => 'success', 'message' => 'Categoría actualizada correctamente'];
    }

    public function cambiarEstadoCategoria(int $id, string $nuevoEstado): array
    {
        $validos = ['activo', 'inactivo'];
        if (!in_array($nuevoEstado, $validos, true)) {
            return ['status' => 'error', 'message' => 'Estado no válido'];
        }

        $stmt = $this->conn->prepare("UPDATE categorias SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $nuevoEstado, $id);
        $stmt->execute();
        $afectado = $stmt->affected_rows;
        $stmt->close();

        if ($afectado === 0) {
            return ['status' => 'error', 'message' => 'Categoría no encontrada'];
        }

        $msg = $nuevoEstado === 'inactivo' ? 'Categoría deshabilitada correctamente' : 'Categoría activada correctamente';
        return ['status' => 'success', 'message' => $msg];
    }

    public function categoriaTieneProductos(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM productos WHERE id_categoria = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)($row['total'] ?? 0) > 0;
    }

    public function eliminarCategoria(int $id): array
    {
        if ($this->categoriaTieneProductos($id)) {
            return ['status' => 'error', 'message' => 'No se puede eliminar la categoría porque tiene productos asociados'];
        }

        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        return ['status' => 'success', 'message' => 'Categoría eliminada correctamente'];
    }
}
