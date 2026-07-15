<?php

class SeguimientoService
{
    private const ESTADOS = [0 => 'pendiente', 1 => 'en camino', 2 => 'entregado'];
    private const ORIGENES_PRODUCTOR = [
        'Finca El Roble' => 'Chiriquí',
        'Huerto Santa Maria' => 'Panamá Oeste',
        'Apiario Valle Verde' => 'Coclé',
        'Finca Los Robles' => 'Coclé',
        'Café Boquete Dorado' => 'Chiriquí',
        'Cafe Boquete Dorado' => 'Chiriquí',
        'Cacao Darién Orgánico' => 'Darién',
        'Cacao Darien Organico' => 'Darién',
        'Hacienda El Valle' => 'Panamá Oeste',
        'Finca Cerro Azul' => 'Panamá'
    ];
    private const ZONAS_PROVINCIA = [
        'bocas del toro' => 'occidente',
        'chiriqui' => 'occidente',
        'veraguas' => 'occidente',
        'cocle' => 'centro',
        'herrera' => 'centro',
        'los santos' => 'centro',
        'colon' => 'centro',
        'panama oeste' => 'centro',
        'panama' => 'centro',
        'darien' => 'oriente'
    ];

    private SeguimientoRepository $seguimiento;
    private SeguimientoValidator $validator;

    public function __construct(SeguimientoRepository $seguimiento, SeguimientoValidator $validator)
    {
        $this->seguimiento = $seguimiento;
        $this->validator = $validator;
    }

    public function listarPedidosUsuario(int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            return ["status" => "error", "message" => "Usuario no valido"];
        }

        $direccion = $this->seguimiento->obtenerDireccionUsuario($idUsuario);
        $provinciaDestino = trim($direccion['provincia'] ?? '');

        $pedidos = array_map(function (array $pedido) use ($provinciaDestino): array {
            $estado = (int) $pedido['estado'];
            $items = array_map(static function (array $item): array {
                return [
                    "id_producto" => (int) $item['id_producto'],
                    "nombre" => $item['nombre'],
                    "productor" => $item['productor'],
                    "cantidad" => (int) $item['cantidad'],
                    "precio" => (float) $item['precio_unitario'],
                    "subtotal" => (float) $item['subtotal']
                ];
            }, $pedido['items']);
            $entrega = $this->calcularEntrega($items, $provinciaDestino, $estado);

            return [
                "id" => (int) $pedido['id'],
                "fecha" => $pedido['fecha'],
                "total" => (float) $pedido['total_compra'],
                "estado" => $estado,
                "estado_label" => self::ESTADOS[$estado] ?? 'pendiente',
                "metodo_pago" => $pedido['metodo_pago'],
                "fecha_entrega" => $pedido['fecha_entrega'],
                "tarifa_envio" => (float) ($pedido['tarifa_envio'] ?? 0),
                "repartidor" => trim($pedido['repartidor'] ?? ''),
                "origen" => $entrega['origen'],
                "tiempo_estimado" => $entrega['tiempo_estimado'],
                "items" => $items
            ];
        }, $this->seguimiento->listarPedidosUsuario($idUsuario));

        return ["status" => "success", "pedidos" => $pedidos];
    }

    private function calcularEntrega(array $items, string $provinciaDestino, int $estado): array
    {
        $origenes = [];
        foreach ($items as $item) {
            $productor = trim($item['productor'] ?? 'Productor local');
            $provincia = self::ORIGENES_PRODUCTOR[$productor] ?? 'Panama';
            $origenes[$productor] = $provincia;
        }

        $origenResumen = implode(' / ', array_map(
            static fn(string $productor, string $provincia): string => $productor . ' (' . $provincia . ')',
            array_keys($origenes),
            array_values($origenes)
        ));

        if ($estado === 2) {
            return ['origen' => $origenResumen, 'tiempo_estimado' => 'Entrega completada'];
        }
        if ($provinciaDestino === '') {
            return ['origen' => $origenResumen, 'tiempo_estimado' => 'Registra tu direccion para calcularlo'];
        }

        $destinoNormalizado = $this->normalizarProvincia($provinciaDestino);
        $zonaDestino = self::ZONAS_PROVINCIA[$destinoNormalizado] ?? 'centro';
        $distanciaMaxima = 0;

        foreach ($origenes as $provinciaOrigen) {
            $origenNormalizado = $this->normalizarProvincia($provinciaOrigen);
            if ($origenNormalizado === $destinoNormalizado) {
                $distancia = 0;
            } else {
                $zonaOrigen = self::ZONAS_PROVINCIA[$origenNormalizado] ?? 'centro';
                $distancia = $zonaOrigen === $zonaDestino ? 1 : 2;
                if (in_array($zonaOrigen . '-' . $zonaDestino, ['occidente-oriente', 'oriente-occidente'], true)) {
                    $distancia = 3;
                }
            }
            $distanciaMaxima = max($distanciaMaxima, $distancia);
        }

        $rangos = $estado === 1
            ? [0 => '12 a 24 horas', 1 => '18 a 36 horas', 2 => '24 a 48 horas', 3 => '48 a 72 horas']
            : [0 => '24 a 36 horas', 1 => '36 a 48 horas', 2 => '48 a 72 horas', 3 => '72 a 96 horas'];

        return [
            'origen' => $origenResumen,
            'tiempo_estimado' => $rangos[$distanciaMaxima]
        ];
    }

    private function normalizarProvincia(string $provincia): string
    {
        return strtr(mb_strtolower(trim($provincia), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u'
        ]);
    }

    public function obtenerDireccion(int $idUsuario): array
    {
        return ["status" => "success", "direccion" => $this->seguimiento->obtenerDireccionUsuario($idUsuario)];
    }

    public function guardarDireccion(int $idUsuario, string $provincia, string $distrito, string $corregimiento, string $detalle): array
    {
        if ($idUsuario <= 0) {
            return ["status" => "error", "message" => "Usuario no valido"];
        }

        $provincia = trim($provincia);
        $distrito = trim($distrito);
        $corregimiento = trim($corregimiento);
        $detalle = trim($detalle);
        $error = $this->validator->validarDireccion($provincia, $distrito, $corregimiento, $detalle);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        try {
            $this->seguimiento->guardarDireccionUsuario($idUsuario, $provincia, $distrito, $corregimiento, $detalle);
        } catch (mysqli_sql_exception $e) {
            return ["status" => "error", "message" => "No se pudo guardar la direccion"];
        }

        return ["status" => "success", "message" => "Direccion de entrega guardada"];
    }

    public function asignarEntrega(int $idPedido, int $idRepartidor): array
    {
        if ($idPedido <= 0 || $idRepartidor <= 0) {
            return ["status" => "error", "message" => "Pedido o repartidor no valido"];
        }

        try {
            $this->seguimiento->asignarEntrega($idPedido, $idRepartidor);
            return ["status" => "success", "message" => "Entrega asignada correctamente"];
        } catch (RuntimeException $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } catch (mysqli_sql_exception $e) {
            return ["status" => "error", "message" => "No se pudo asignar la entrega"];
        }
    }

    public function confirmarEntrega(int $idPedido, int $idRepartidor): array
    {
        if ($idPedido <= 0 || $idRepartidor <= 0) {
            return ["status" => "error", "message" => "Pedido o repartidor no valido"];
        }

        try {
            $this->seguimiento->confirmarEntrega($idPedido, $idRepartidor);
            return ["status" => "success", "message" => "Entrega completada correctamente"];
        } catch (RuntimeException $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } catch (mysqli_sql_exception $e) {
            return ["status" => "error", "message" => "No se pudo completar la entrega"];
        }
    }
}
