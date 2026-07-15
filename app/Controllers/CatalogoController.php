<?php

require_once __DIR__ . '/../Facades/SistemaDachiFacade.php';

/**
 * Controlador MVC - Catalogo.
 *
 * Coordina las solicitudes de busqueda/detalle y la carga inicial. La vista
 * catalogo.php solo se encarga de renderizar HTML y consumir estas respuestas.
 */
class CatalogoController
{
    public function __construct(private SistemaDachiFacade $system)
    {
    }

    public function handle(array $request): array
    {
        return match ($request['accion'] ?? '') {
            'buscar' => $this->system->listarProductos(trim($request['busqueda'] ?? '')),
            'detalle' => $this->system->obtenerProducto((int) ($request['id'] ?? 0)),
            default => ['status' => 'error', 'message' => 'Accion invalida']
        };
    }

    public function initialCatalog(): array
    {
        return $this->system->listarProductos('');
    }
}
