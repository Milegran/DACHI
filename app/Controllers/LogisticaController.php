<?php
//BLOQUE CONTROLADOR DE LOGISTICA
class LogisticaController implements LogisticaControllerInterface
{
    private $conn;
    private $sistema;

    public function __construct($conn, $sistema)
    {
        $this->conn = $conn;
        $this->sistema = $sistema;
    }

    //BLOQUE ACCIONES POST
    public function manejarAccion(array $datos, array $usuarioActual)
    {
        $accion = $datos['accion'] ?? '';

        switch ($accion) {
            case 'guardar_perfil':
                $this->guardarPerfil($datos, $usuarioActual);
                break;
            case 'registrar_entrega':
                $this->registrarEntrega($datos, $usuarioActual);
                break;
            default:
                http_response_code(400);
                echo 'Accion no reconocida';
        }
    }

    private function guardarPerfil(array $datos, array $usuarioActual)
    {
        $nombre = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');
        $telefono = trim($datos['telefono'] ?? '');
        $stmt = $this->conn->prepare("UPDATE usuarios SET nombre=?, apellido=?, telefono=? WHERE id=?");
        $stmt->bind_param('sssi', $nombre, $apellido, $telefono, $usuarioActual['id']);
        $stmt->execute();
        $stmt->close();
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['telefono'] = $telefono;
        echo 'ok';
    }

    //BLOQUE REGISTRAR ENTREGA (pedidos.estado_detallado + tabla entregas)
    private function registrarEntrega(array $datos, array $usuarioActual)
    {
        $idPedido = (int) ($datos['id_pedido'] ?? 0);
        $nuevoEstado = $datos['estado'] ?? '';
        $permitidos = ['pendiente', 'en_preparacion', 'en_transito', 'entregado', 'cancelado'];

        if ($idPedido <= 0 || !in_array($nuevoEstado, $permitidos, true)) {
            http_response_code(400);
            echo 'Datos invalidos';
            return;
        }

        // BLOQUE ACTUALIZAR PEDIDO
        if ($nuevoEstado === 'entregado' || $nuevoEstado === 'cancelado') {
            $stmt = $this->conn->prepare("UPDATE pedidos SET estado_detallado=?, fecha_entrega=NOW() WHERE id=?");
        } else {
            $stmt = $this->conn->prepare("UPDATE pedidos SET estado_detallado=? WHERE id=?");
        }
        $stmt->bind_param('si', $nuevoEstado, $idPedido);
        $stmt->execute();
        $stmt->close();

        // BLOQUE TABLA ENTREGAS - se registra en cuanto hay movimiento logistico real
        // (en_transito, entregado o cancelado). pendiente/en_preparacion aun no tienen
        // repartidor asignado, por eso no generan fila en entregas.
        if (in_array($nuevoEstado, ['en_transito', 'entregado', 'cancelado'], true)) {
            // BLOQUE BUSCAR DIRECCION DEL CONSUMIDOR DEL PEDIDO
            $stmtDir = $this->conn->prepare(
                "SELECT d.id AS id_direccion
                 FROM pedidos p
                 JOIN direccion d ON d.id_usuario = p.id_consumer
                 WHERE p.id = ? LIMIT 1"
            );
            $stmtDir->bind_param('i', $idPedido);
            $stmtDir->execute();
            $direccion = $stmtDir->get_result()->fetch_assoc();
            $stmtDir->close();
            $idDireccion = $direccion['id_direccion'] ?? null;

            // BLOQUE VER SI YA EXISTE REGISTRO EN ENTREGAS
            $stmtBuscar = $this->conn->prepare("SELECT id FROM entregas WHERE id_pedidos = ? LIMIT 1");
            $stmtBuscar->bind_param('i', $idPedido);
            $stmtBuscar->execute();
            $entregaExistente = $stmtBuscar->get_result()->fetch_assoc();
            $stmtBuscar->close();

            // BLOQUE CODIGO DE ESTADO EN ENTREGAS: 0 = en curso, 1 = entregado, 2 = cancelado
            $estadoEntrega = match ($nuevoEstado) {
                'entregado' => 1,
                'cancelado' => 2,
                default => 0,
            };

            if ($entregaExistente) {
                $stmtUp = $this->conn->prepare("UPDATE entregas SET estado=?, id_repartidor=? WHERE id_pedidos=?");
                $stmtUp->bind_param('iii', $estadoEntrega, $usuarioActual['id'], $idPedido);
                $stmtUp->execute();
                $stmtUp->close();
            } elseif ($idDireccion !== null) {
                $stmtIns = $this->conn->prepare(
                    "INSERT INTO entregas (id_pedidos, id_repartidor, id_direccion, tarifa_envio, fecha, estado)
                     VALUES (?, ?, ?, 0.00, CURDATE(), ?)"
                );
                $stmtIns->bind_param('iiii', $idPedido, $usuarioActual['id'], $idDireccion, $estadoEntrega);
                $stmtIns->execute();
                $stmtIns->close();
            }
        }

        echo 'ok';
    }

    //BLOQUE DATOS PEDIDOS 
    public function obtenerPedidos()
    {
        $pedidos = [];
        $resPed = $this->conn->query(
            "SELECT p.id, p.fecha, p.fecha_entrega, p.total_compra, p.estado_detallado, p.id_consumer,
                    u.nombre AS comprador_nombre, u.apellido AS comprador_apellido,
                    u.correo AS comprador_correo, u.telefono AS comprador_telefono,
                    d.provincia AS zona, d.distrito, d.corregimiento, d.detalle AS direccion_detalle,
                    m.nombre AS metodo_pago_nombre,
                    e.id_repartidor, ur.nombre AS repartidor_nombre
             FROM pedidos p
             JOIN usuarios u ON p.id_consumer = u.id
             LEFT JOIN direccion d ON d.id_usuario = u.id
             LEFT JOIN metodos m ON m.id = p.metodo_pago
             LEFT JOIN entregas e ON e.id_pedidos = p.id
             LEFT JOIN usuarios ur ON ur.id = e.id_repartidor
             ORDER BY p.id DESC"
        );
        while ($ped = $resPed->fetch_assoc()) {
            $stmtItems = $this->conn->prepare("SELECT ip.cantidad, ip.precio_unitario, ip.subtotal, pr.nombre AS producto_nombre, pr.id AS producto_id
                 FROM info_pedidos ip JOIN productos pr ON ip.id_productos = pr.id WHERE ip.id_pedidos = ?");
            $stmtItems->bind_param('i', $ped['id']);
            $stmtItems->execute();
            $itemsRes = $stmtItems->get_result();
            $items = [];
            while ($it = $itemsRes->fetch_assoc())
                $items[] = $it;
            $stmtItems->close();

            $ped['items'] = $items;
            $ped['estado_label'] = $ped['estado_detallado'] ?? 'pendiente';
            $pedidos[] = $ped;
        }
        return $pedidos;
    }
}