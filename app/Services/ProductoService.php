<?php

require_once __DIR__ . '/../Models/Producto.php';

class ProductoService
{
    private const BUSQUEDA_LARGO_MAXIMO = 60;

    private ProductoRepository $productos;

    public function __construct(ProductoRepository $productos)
    {
        $this->productos = $productos;
    }

    public function listarCatalogo(string $busqueda): array
    {
        $busqueda = trim($busqueda);
        if (strlen($busqueda) > self::BUSQUEDA_LARGO_MAXIMO) {
            $busqueda = substr($busqueda, 0, self::BUSQUEDA_LARGO_MAXIMO);
        }

        $filas = $this->productos->listarActivos($busqueda);
        $productos = array_map([$this, 'formatearProducto'], $filas);

        return ["status" => "success", "productos" => $productos];
    }

    public function obtenerDetalle(int $id): array
    {
        if ($id <= 0) {
            return ["status" => "error", "message" => "Producto no valido"];
        }

        $producto = $this->productos->buscarActivoPorId($id);
        if ($producto === null) {
            return ["status" => "error", "message" => "Producto no encontrado o no disponible"];
        }

        return ["status" => "success", "producto" => $this->formatearProducto($producto)];
    }

    private function formatearProducto(array $fila): array
    {
        // MODELO MVC: el servicio transforma la fila SQL en un objeto
        // Producto y luego lo serializa al formato que consume la vista.
        return Producto::fromDatabase($fila)->toArray();
    }
}
