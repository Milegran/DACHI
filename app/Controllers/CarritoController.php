<?php

require_once __DIR__ . '/../Facades/SistemaDachiFacade.php';

/**
 * Controlador MVC - Carrito y pago.
 *
 * El controlador traduce la peticion HTTP a llamadas del Facade y devuelve
 * el nuevo estado de sesion. La vista no contiene reglas de stock, pagos ni
 * persistencia de pedidos: solo muestra el resultado recibido.
 */
class CarritoController
{
    public function __construct(private SistemaDachiFacade $system)
    {
    }

    public function handle(array $request, array $user, array $cart, bool $isConsumer): array
    {
        $action = $request['accion'] ?? '';
        $response = ['status' => 'error', 'message' => 'Accion invalida'];
        $nextCart = $cart;

        if ($action === 'resumen') {
            $response = $this->system->obtenerCarrito($cart);
            $nextCart = $response['carrito'] ?? $cart;
        } elseif (!$isConsumer && in_array($action, ['agregar', 'actualizar', 'eliminar', 'vaciar', 'confirmar'], true)) {
            $response = ['status' => 'error', 'message' => 'Solo una cuenta consumidora puede realizar compras'];
        } elseif ($action === 'agregar') {
            $response = $this->system->agregarAlCarrito($cart, (int) ($request['id_producto'] ?? 0), (int) ($request['cantidad'] ?? 1));
            $nextCart = $response['carrito'] ?? $cart;
        } elseif ($action === 'actualizar') {
            $response = $this->system->actualizarCarrito($cart, (int) ($request['id_producto'] ?? 0), (int) ($request['cantidad'] ?? 0));
            if ($response['status'] === 'success') {
                $nextCart = $response['carrito'];
                $response = $this->system->obtenerCarrito($nextCart);
                $nextCart = $response['carrito'];
            }
        } elseif ($action === 'eliminar') {
            $response = $this->system->eliminarDelCarrito($cart, (int) ($request['id_producto'] ?? 0));
            if ($response['status'] === 'success') {
                $nextCart = $response['carrito'];
                $response = $this->system->obtenerCarrito($nextCart);
            }
        } elseif ($action === 'vaciar') {
            $nextCart = [];
            $response = $this->system->obtenerCarrito([]);
        } elseif ($action === 'confirmar') {
            $response = $this->system->confirmarCompra(
                (int) ($user['id'] ?? 0),
                (int) ($request['id_metodo_pago'] ?? 0),
                $cart,
                [
                    'titular' => trim($request['titular_tarjeta'] ?? ''),
                    'numero' => $request['numero_tarjeta'] ?? '',
                    'vencimiento' => trim($request['vencimiento_tarjeta'] ?? ''),
                    'cvv' => $request['cvv_tarjeta'] ?? ''
                ]
            );
            if ($response['status'] === 'success') {
                $nextCart = [];
            }
        }

        return ['response' => $response, 'cart' => $nextCart];
    }

    public function initialState(array $cart): array
    {
        return [
            'summary' => $this->system->obtenerCarrito($cart),
            'paymentMethods' => $this->system->listarMetodosPago()
        ];
    }
}
