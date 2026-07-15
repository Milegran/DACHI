<?php

class SeguimientoRepository
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function listarPedidosUsuario(int $idUsuario): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.id, p.fecha, p.total_compra, p.estado, m.nombre AS metodo_pago,
                    e.fecha AS fecha_entrega, e.tarifa_envio, e.estado AS estado_entrega,
                    CONCAT(COALESCE(r.nombre, ''), ' ', COALESCE(r.apellido, '')) AS repartidor
             FROM pedidos p
             INNER JOIN metodos m ON m.id = p.metodo_pago
             LEFT JOIN entregas e ON e.id_pedidos = p.id
             LEFT JOIN usuarios r ON r.id = e.id_repartidor
             WHERE p.id_consumer = ?
             ORDER BY p.id DESC"
        );
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmtItems = $this->conn->prepare(
            "SELECT ip.id_productos AS id_producto, pr.nombre, pr.nom_productor AS productor,
                    ip.cantidad, ip.precio_unitario, ip.subtotal
             FROM info_pedidos ip
             INNER JOIN productos pr ON pr.id = ip.id_productos
             WHERE ip.id_pedidos = ?
             ORDER BY ip.id ASC"
        );

        foreach ($pedidos as &$pedido) {
            $idPedido = (int) $pedido['id'];
            $stmtItems->bind_param("i", $idPedido);
            $stmtItems->execute();
            $pedido['items'] = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        unset($pedido);
        $stmtItems->close();

        return $pedidos;
    }

    public function obtenerDireccionUsuario(int $idUsuario): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, provincia, distrito, corregimiento, detalle FROM direccion WHERE id_usuario = ?"
        );
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $direccion = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $direccion ?: null;
    }

    public function guardarDireccionUsuario(int $idUsuario, string $provincia, string $distrito, string $corregimiento, string $detalle): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO direccion (id_usuario, provincia, distrito, corregimiento, detalle)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE provincia = VALUES(provincia), distrito = VALUES(distrito),
                                     corregimiento = VALUES(corregimiento), detalle = VALUES(detalle)"
        );
        $stmt->bind_param("issss", $idUsuario, $provincia, $distrito, $corregimiento, $detalle);
        $stmt->execute();
        $stmt->close();
    }

    public function asignarEntrega(int $idPedido, int $idRepartidor): void
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                "SELECT p.estado, d.id AS id_direccion, e.id AS id_entrega, e.id_repartidor
                 FROM pedidos p
                 LEFT JOIN direccion d ON d.id_usuario = p.id_consumer
                 LEFT JOIN entregas e ON e.id_pedidos = p.id
                 WHERE p.id = ? FOR UPDATE"
            );
            $stmt->bind_param("i", $idPedido);
            $stmt->execute();
            $pedido = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($pedido === null) {
                throw new RuntimeException("Pedido no encontrado");
            }
            if ((int) $pedido['estado'] !== 0) {
                throw new RuntimeException("El pedido ya no esta disponible para asignacion");
            }
            if (empty($pedido['id_direccion'])) {
                throw new RuntimeException("El consumidor debe registrar una direccion de entrega");
            }
            if (!empty($pedido['id_repartidor']) && (int) $pedido['id_repartidor'] !== $idRepartidor) {
                throw new RuntimeException("El pedido ya fue asignado a otro repartidor");
            }

            $idDireccion = (int) $pedido['id_direccion'];
            $estadoEnCamino = 1;
            if (empty($pedido['id_entrega'])) {
                $tarifa = 0.0;
                $stmtEntrega = $this->conn->prepare(
                    "INSERT INTO entregas (id_pedidos, id_repartidor, id_direccion, tarifa_envio, fecha, estado)
                     VALUES (?, ?, ?, ?, CURDATE(), ?)"
                );
                $stmtEntrega->bind_param("iiidi", $idPedido, $idRepartidor, $idDireccion, $tarifa, $estadoEnCamino);
            } else {
                $stmtEntrega = $this->conn->prepare(
                    "UPDATE entregas SET id_repartidor = ?, id_direccion = ?, fecha = CURDATE(), estado = ? WHERE id_pedidos = ?"
                );
                $stmtEntrega->bind_param("iiii", $idRepartidor, $idDireccion, $estadoEnCamino, $idPedido);
            }
            $stmtEntrega->execute();
            $stmtEntrega->close();

            $stmtPedido = $this->conn->prepare("UPDATE pedidos SET estado = 1 WHERE id = ?");
            $stmtPedido->bind_param("i", $idPedido);
            $stmtPedido->execute();
            $stmtPedido->close();

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function confirmarEntrega(int $idPedido, int $idRepartidor): void
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                "SELECT e.id, e.estado, p.estado AS estado_pedido
                 FROM entregas e INNER JOIN pedidos p ON p.id = e.id_pedidos
                 WHERE e.id_pedidos = ? AND e.id_repartidor = ? FOR UPDATE"
            );
            $stmt->bind_param("ii", $idPedido, $idRepartidor);
            $stmt->execute();
            $entrega = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($entrega === null || (int) $entrega['estado'] !== 1 || (int) $entrega['estado_pedido'] !== 1) {
                throw new RuntimeException("La entrega no esta asignada a esta cuenta o ya fue completada");
            }

            $stmtEntrega = $this->conn->prepare("UPDATE entregas SET estado = 2 WHERE id_pedidos = ? AND id_repartidor = ?");
            $stmtEntrega->bind_param("ii", $idPedido, $idRepartidor);
            $stmtEntrega->execute();
            $stmtEntrega->close();

            $stmtPedido = $this->conn->prepare("UPDATE pedidos SET estado = 2 WHERE id = ?");
            $stmtPedido->bind_param("i", $idPedido);
            $stmtPedido->execute();
            $stmtPedido->close();

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
