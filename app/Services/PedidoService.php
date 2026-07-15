<?php

class PedidoService
{
    private PedidoRepository $pedidos;
    private CarritoService $carritoService;
    private PedidoValidator $validator;

    public function __construct(PedidoRepository $pedidos, CarritoService $carritoService, PedidoValidator $validator)
    {
        $this->pedidos = $pedidos;
        $this->carritoService = $carritoService;
        $this->validator = $validator;
    }

    public function listarMetodosPago(): array
    {
        $metodos = array_map(static function (array $metodo): array {
            return [
                "id" => (int) $metodo['id'],
                "nombre" => $metodo['nombre'],
                "requiere_tarjeta" => $metodo['nombre'] === 'Transferencia'
            ];
        }, $this->pedidos->listarMetodosPago());

        return ["status" => "success", "metodos" => $metodos];
    }

    public function confirmarCompra(int $idUsuario, int $idMetodoPago, array $carrito, array $datosPago = []): array
    {
        $error = $this->validator->validarCompra($idUsuario, $idMetodoPago, $carrito);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        $metodoPago = $this->pedidos->obtenerMetodoPago($idMetodoPago);
        if ($metodoPago === null) {
            return ["status" => "error", "message" => "El metodo de pago seleccionado no existe"];
        }

        if ($metodoPago['nombre'] === 'Transferencia') {
            $errorTarjeta = $this->validator->validarDatosTarjeta($datosPago);
            if ($errorTarjeta !== null) {
                return ["status" => "error", "message" => $errorTarjeta];
            }
        }

        $resumen = $this->carritoService->obtenerResumen($carrito);
        if (!$resumen['puede_comprar'] || count($resumen['items']) !== count($carrito)) {
            return ["status" => "error", "message" => "Revisa el stock y los productos del carrito antes de pagar"];
        }

        try {
            $pedido = $this->pedidos->registrarCompra($idUsuario, $idMetodoPago, $carrito);
        } catch (RuntimeException $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        } catch (mysqli_sql_exception $e) {
            return ["status" => "error", "message" => "No se pudo registrar el pedido"];
        }

        return [
            "status" => "success",
            "message" => "Pedido registrado correctamente",
            "pedido" => $pedido
        ];
    }
}
