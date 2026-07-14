<?php

require_once __DIR__ . '/../Facades/SistemaDachiFacade.php';

/**
 * Controlador MVC - Seguimiento.
 *
 * Protege las operaciones del consumidor y coordina pedidos/direccion con
 * el Facade. La vista seguimiento.php queda enfocada en HTML y JavaScript.
 */
class SeguimientoController
{
    public function __construct(private SistemaDachiFacade $system)
    {
    }

    public function handle(array $request, int $userId): array
    {
        return match ($request['accion'] ?? '') {
            'listar' => $this->system->listarPedidosUsuario($userId),
            'guardar_direccion' => $this->system->guardarDireccionEntrega(
                $userId,
                trim($request['provincia'] ?? ''),
                trim($request['distrito'] ?? ''),
                trim($request['corregimiento'] ?? ''),
                trim($request['detalle'] ?? '')
            ),
            default => ['status' => 'error', 'message' => 'Accion invalida']
        };
    }

    public function initialState(int $userId): array
    {
        return [
            'orders' => $this->system->listarPedidosUsuario($userId),
            'address' => $this->system->obtenerDireccionEntrega($userId)['direccion'] ?? null
        ];
    }
}
