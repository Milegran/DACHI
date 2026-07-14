<?php

class ProductoRepository
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Devuelve solo productos activos (estado = 1), opcionalmente filtrados
     * por nombre de producto o nombre de productor.
     */
    public function listarActivos(string $busqueda = ''): array
    {
        if ($busqueda === '') {
            $sql = "SELECT id, nombre, descripcion, precio, imagen, nom_productor, cantidad, estado
                    FROM productos
                    WHERE estado = 1
                    ORDER BY nombre ASC";
            $resultado = $this->conn->query($sql);
            return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        }

        $sql = "SELECT id, nombre, descripcion, precio, imagen, nom_productor, cantidad, estado
                FROM productos
                WHERE estado = 1 AND (nombre LIKE ? OR nom_productor LIKE ?)
                ORDER BY nombre ASC";
        $comodin = '%' . $busqueda . '%';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $comodin, $comodin);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $filas;
    }

    /**
     * Devuelve el detalle de un producto solo si esta activo.
     */
    public function buscarActivoPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre, descripcion, precio, imagen, nom_productor, cantidad, estado
             FROM productos
             WHERE id = ? AND estado = 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }
}
