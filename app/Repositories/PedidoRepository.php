<?php

class PedidoRepository
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function listarMetodosPago(): array
    {
        $resultado = $this->conn->query(
            "SELECT id, nombre FROM metodos
             WHERE nombre IN ('Efectivo contra entrega', 'Transferencia')
             ORDER BY FIELD(nombre, 'Efectivo contra entrega', 'Transferencia')"
        );
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerMetodoPago(int $idMetodoPago): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre FROM metodos
             WHERE id = ? AND nombre IN ('Efectivo contra entrega', 'Transferencia')"
        );
        $stmt->bind_param("i", $idMetodoPago);
        $stmt->execute();
        $metodo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $metodo ?: null;
    }

    public function registrarCompra(int $idUsuario, int $idMetodoPago, array $carrito): array
    {
        $this->conn->begin_transaction();

        try {
            $productos = [];
            $total = 0.0;

            ksort($carrito);
            $stmtProducto = $this->conn->prepare(
                "SELECT id, nombre, precio, cantidad, estado FROM productos WHERE id = ? FOR UPDATE"
            );

            foreach ($carrito as $idProducto => $cantidad) {
                $idProducto = (int) $idProducto;
                $cantidad = (int) $cantidad;
                $stmtProducto->bind_param("i", $idProducto);
                $stmtProducto->execute();
                $producto = $stmtProducto->get_result()->fetch_assoc();

                if ($producto === null || (int) $producto['estado'] !== 1) {
                    throw new RuntimeException("Uno de los productos ya no esta disponible");
                }

                if ($cantidad < 1 || $cantidad > (int) $producto['cantidad']) {
                    throw new RuntimeException("El stock de " . $producto['nombre'] . " cambio. Actualiza el carrito");
                }

                $precio = (float) $producto['precio'];
                $subtotal = round($precio * $cantidad, 2);
                $total += $subtotal;
                $productos[] = [
                    "id" => $idProducto,
                    "cantidad" => $cantidad,
                    "precio" => $precio,
                    "subtotal" => $subtotal
                ];
            }
            $stmtProducto->close();

            $total = round($total, 2);
            $estado = 0;
            $stmtPedido = $this->conn->prepare(
                "INSERT INTO pedidos (id_consumer, fecha, total_compra, metodo_pago, estado) VALUES (?, CURDATE(), ?, ?, ?)"
            );
            $stmtPedido->bind_param("idii", $idUsuario, $total, $idMetodoPago, $estado);
            $stmtPedido->execute();
            $idPedido = (int) $this->conn->insert_id;
            $stmtPedido->close();

            $stmtDetalle = $this->conn->prepare(
                "INSERT INTO info_pedidos (id_pedidos, id_productos, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)"
            );
            $stmtStock = $this->conn->prepare(
                "UPDATE productos SET cantidad = cantidad - ? WHERE id = ?"
            );

            foreach ($productos as $producto) {
                $idProducto = $producto['id'];
                $cantidad = $producto['cantidad'];
                $precio = $producto['precio'];
                $subtotal = $producto['subtotal'];
                $stmtDetalle->bind_param(
                    "iiidd",
                    $idPedido,
                    $idProducto,
                    $cantidad,
                    $precio,
                    $subtotal
                );
                $stmtDetalle->execute();

                $stmtStock->bind_param("ii", $cantidad, $idProducto);
                $stmtStock->execute();
            }
            $stmtDetalle->close();
            $stmtStock->close();

            $this->conn->commit();

            return ["id_pedido" => $idPedido, "total" => $total];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
