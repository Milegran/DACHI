<?php

function obtenerContextoProductor(mysqli $conn, array $usuarioActual): array
{
    $idProductor = (int) ($usuarioActual['id'] ?? 0);
    $rolActual = strtolower(trim($usuarioActual['nom_rol'] ?? 'productor'));

    $usuario = [
        'id' => $idProductor,
        'nombre' => $usuarioActual['nombre'] ?? '',
        'apellido' => $usuarioActual['apellido'] ?? '',
        'correo' => $usuarioActual['correo'] ?? '',
    ];

    $productos = [];
    $stmt = $conn->prepare(
        "SELECT id, nombre, descripcion, precio, cantidad, estado, nom_productor
         FROM productos
         WHERE id_usuario = ?
         ORDER BY id DESC"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $fila['precio'] = (float) $fila['precio'];
        $fila['cantidad'] = (int) $fila['cantidad'];
        $productos[] = $fila;
    }
    $stmt->close();

    $totalProductos = count($productos);
    $stockBajo = 0;
    $agotados = 0;
    $valorInventario = 0.0;
    foreach ($productos as $producto) {
        if ((int) $producto['estado'] === 1 && $producto['cantidad'] <= 10) {
            $stockBajo++;
        }
        if ((int) $producto['estado'] === 1 && $producto['cantidad'] <= 0) {
            $agotados++;
        }
        if ((int) $producto['estado'] === 1) {
            $valorInventario += $producto['precio'] * $producto['cantidad'];
        }
    }

    $pedidosPendientes = 0;
    $ventasTotales = 0.0;
    $pedidosRecientes = [];
    $stmt = $conn->prepare(
        "SELECT COUNT(DISTINCT ip.id_pedidos) AS pedidos_pendientes,
                COALESCE(SUM(ip.subtotal), 0) AS ventas_totales
         FROM info_pedidos ip
         JOIN productos p ON ip.id_productos = p.id
         JOIN pedidos ped ON ip.id_pedidos = ped.id
         WHERE p.id_usuario = ? AND ped.estado < 2"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $result = $stmt->get_result();
    $fila = $result->fetch_assoc();
    if ($fila) {
        $pedidosPendientes = (int) ($fila['pedidos_pendientes'] ?? 0);
        $ventasTotales = (float) ($fila['ventas_totales'] ?? 0);
    }
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT DISTINCT ip.id_pedidos AS id,
                p.nombre AS producto_nombre,
                u.nombre AS comprador_nombre,
                u.apellido AS comprador_apellido
         FROM info_pedidos ip
         JOIN productos p ON ip.id_productos = p.id
         JOIN pedidos ped ON ip.id_pedidos = ped.id
         JOIN usuarios u ON ped.id_consumer = u.id
         WHERE p.id_usuario = ?
         ORDER BY ped.fecha DESC, ip.id_pedidos DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($pedido = $resultado->fetch_assoc()) {
        $pedidosRecientes[] = $pedido;
    }
    $stmt->close();

    $promedioResenas = 0.0;
    $totalResenas = 0;
    $stmt = $conn->prepare(
        "SELECT ROUND(AVG(c.calificacion), 1) AS promedio, COUNT(*) AS total
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         WHERE p.id_usuario = ? AND c.tipo = 'producto'"
    );
    if ($stmt) {
        $stmt->bind_param('i', $idProductor);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        if ($fila) {
            $promedioResenas = (float) ($fila['promedio'] ?? 0);
            $totalResenas = (int) ($fila['total'] ?? 0);
        }
        $stmt->close();
    }

    return [
        'usuario' => $usuario,
        'rolActual' => $rolActual,
        'totalProductos' => $totalProductos,
        'stockBajo' => $stockBajo,
        'agotados' => $agotados,
        'pedidoPers' => $pedidosPendientes,
        'pedidosPendientes' => $pedidosPendientes,
        'valorInventario' => $valorInventario,
        'productos' => $productos,
        'ventasTotales' => $ventasTotales,
        'promedioResenas' => $promedioResenas,
        'totalResenas' => $totalResenas,
        'pedidosRecientes' => $pedidosRecientes,
    ];
}

function obtenerPedidosProductor(mysqli $conn, array $usuarioActual): array
{
    $idProductor = (int) ($usuarioActual['id'] ?? 0);

    $pedidosHoy = 0;
    $stmt = $conn->prepare(
        "SELECT COUNT(DISTINCT ped.id) AS total
         FROM pedidos ped
         JOIN info_pedidos ip ON ip.id_pedidos = ped.id
         JOIN productos p ON ip.id_productos = p.id
         WHERE p.id_usuario = ? AND ped.fecha = CURDATE()"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $pedidosHoy = (int) ($fila['total'] ?? 0);
    $stmt->close();

    $pendientes = 0;
    $stmt = $conn->prepare(
        "SELECT COUNT(DISTINCT ped.id) AS total
         FROM pedidos ped
         JOIN info_pedidos ip ON ip.id_pedidos = ped.id
         JOIN productos p ON ip.id_productos = p.id
         WHERE p.id_usuario = ? AND ped.estado IN (0, 1)"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $pendientes = (int) ($fila['total'] ?? 0);
    $stmt->close();

    $ingresosMes = 0.0;
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(ip.subtotal), 0) AS total
         FROM info_pedidos ip
         JOIN productos p ON ip.id_productos = p.id
         JOIN pedidos ped ON ip.id_pedidos = ped.id
         WHERE p.id_usuario = ? AND MONTH(ped.fecha) = MONTH(CURDATE()) AND YEAR(ped.fecha) = YEAR(CURDATE()) AND ped.estado NOT IN (4)"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $ingresosMes = (float) ($fila['total'] ?? 0);
    $stmt->close();

    $incidencias = 0;
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         WHERE p.id_usuario = ? AND c.calificacion <= 2"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $incidencias = (int) ($fila['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT DISTINCT
                ped.id,
                ped.fecha,
                ped.total_compra,
                ped.estado,
                u.nombre AS comprador_nombre,
                u.apellido AS comprador_apellido
         FROM pedidos ped
         JOIN info_pedidos ip ON ip.id_pedidos = ped.id
         JOIN productos p ON ip.id_productos = p.id
         JOIN usuarios u ON ped.id_consumer = u.id
         WHERE p.id_usuario = ?
         ORDER BY ped.fecha DESC, ped.id DESC"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $pedidosRaw = [];
    while ($fila = $resultado->fetch_assoc()) {
        $idPedido = (int) $fila['id'];
        if (!isset($pedidosRaw[$idPedido])) {
            $pedidosRaw[$idPedido] = [
                'id' => $idPedido,
                'fecha' => $fila['fecha'],
                'total_compra' => (float) $fila['total_compra'],
                'estado' => (int) $fila['estado'],
                'comprador_nombre' => $fila['comprador_nombre'],
                'comprador_apellido' => $fila['comprador_apellido'],
                'productos' => [],
            ];
        }
    }
    $stmt->close();

    if (!empty($pedidosRaw)) {
        $idsPedidos = array_keys($pedidosRaw);
        $placeholders = implode(',', array_fill(0, count($idsPedidos), '?'));
        $tipos = str_repeat('i', count($idsPedidos));

        $stmt = $conn->prepare(
            "SELECT ip.id_pedidos,
                    p.nombre AS producto_nombre,
                    ip.cantidad,
                    ip.precio_unitario,
                    ip.subtotal
             FROM info_pedidos ip
             JOIN productos p ON ip.id_productos = p.id
             WHERE ip.id_pedidos IN ($placeholders)"
        );
        $stmt->bind_param($tipos, ...$idsPedidos);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $idPedido = (int) $fila['id_pedidos'];
            if (isset($pedidosRaw[$idPedido])) {
                $pedidosRaw[$idPedido]['productos'][] = [
                    'producto_nombre' => $fila['producto_nombre'],
                    'cantidad' => (int) $fila['cantidad'],
                    'precio_unitario' => (float) $fila['precio_unitario'],
                    'subtotal' => (float) $fila['subtotal'],
                ];
            }
        }
        $stmt->close();
    }

    $pedidos = array_values($pedidosRaw);

    return [
        'pedidosHoy' => $pedidosHoy,
        'pendientes' => $pendientes,
        'ingresosMes' => $ingresosMes,
        'incidencias' => $incidencias,
        'pedidos' => $pedidos,
    ];
}

function obtenerReclamosProductor(mysqli $conn, array $usuarioActual): array
{
    $idProductor = (int) ($usuarioActual['id'] ?? 0);

    $promedioGeneral = 0.0;
    $stmt = $conn->prepare(
        "SELECT ROUND(AVG(c.calificacion), 1) AS promedio
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         WHERE p.id_usuario = ?"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $promedioGeneral = (float) ($fila['promedio'] ?? 0);
    $stmt->close();

    $totalCalificaciones = 0;
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         WHERE p.id_usuario = ?"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $totalCalificaciones = (int) ($fila['total'] ?? 0);
    $stmt->close();

    $totalReclamos = 0;
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         WHERE p.id_usuario = ? AND c.calificacion <= 2"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $totalReclamos = (int) ($fila['total'] ?? 0);
    $stmt->close();

    $reclamos = [];
    $stmt = $conn->prepare(
        "SELECT c.id, c.calificacion, c.comentario, c.tipo, c.created_at,
                p.nombre AS producto_nombre,
                u.nombre AS consumidor_nombre,
                u.apellido AS consumidor_apellido
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         JOIN usuarios u ON c.id_consumer = u.id
         WHERE p.id_usuario = ? AND c.calificacion <= 2
         ORDER BY c.created_at DESC"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $reclamos[] = $fila;
    }
    $stmt->close();

    $buenasCalificaciones = [];
    $stmt = $conn->prepare(
        "SELECT c.id, c.calificacion, c.comentario, c.tipo, c.created_at,
                p.nombre AS producto_nombre,
                u.nombre AS consumidor_nombre,
                u.apellido AS consumidor_apellido
         FROM calificacion c
         JOIN productos p ON c.id_producto = p.id
         JOIN usuarios u ON c.id_consumer = u.id
         WHERE p.id_usuario = ? AND c.calificacion >= 4
         ORDER BY c.created_at DESC
         LIMIT 5"
    );
    $stmt->bind_param('i', $idProductor);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $buenasCalificaciones[] = $fila;
    }
    $stmt->close();

    return [
        'promedioGeneral' => $promedioGeneral,
        'totalCalificaciones' => $totalCalificaciones,
        'totalReclamos' => $totalReclamos,
        'reclamos' => $reclamos,
        'buenasCalificaciones' => $buenasCalificaciones,
    ];
}
