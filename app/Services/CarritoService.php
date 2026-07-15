<?php

class CarritoService
{
    private const MAXIMO_PRODUCTOS_DISTINTOS = 50;

    private ProductoRepository $productos;
    private PedidoValidator $validator;

    public function __construct(ProductoRepository $productos, PedidoValidator $validator)
    {
        $this->productos = $productos;
        $this->validator = $validator;
    }

    public function agregar(array $carrito, int $idProducto, int $cantidad): array
    {
        $error = $this->validator->validarProductoCantidad($idProducto, $cantidad);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        $carrito = $this->normalizar($carrito);
        if (!isset($carrito[$idProducto]) && count($carrito) >= self::MAXIMO_PRODUCTOS_DISTINTOS) {
            return ["status" => "error", "message" => "El carrito alcanzo su limite de productos"];
        }

        $producto = $this->productos->buscarActivoPorId($idProducto);
        if ($producto === null) {
            return ["status" => "error", "message" => "Producto no encontrado o no disponible"];
        }

        $nuevaCantidad = ($carrito[$idProducto] ?? 0) + $cantidad;
        if ($nuevaCantidad > (int) $producto['cantidad']) {
            return ["status" => "error", "message" => "No hay suficiente stock para esa cantidad"];
        }

        $carrito[$idProducto] = $nuevaCantidad;

        return $this->respuestaCarrito($carrito, "Producto agregado al carrito");
    }

    public function actualizar(array $carrito, int $idProducto, int $cantidad): array
    {
        $carrito = $this->normalizar($carrito);
        if (!isset($carrito[$idProducto])) {
            return ["status" => "error", "message" => "El producto no esta en el carrito"];
        }

        if ($cantidad === 0) {
            unset($carrito[$idProducto]);
            return $this->respuestaCarrito($carrito, "Producto eliminado del carrito");
        }

        $error = $this->validator->validarProductoCantidad($idProducto, $cantidad);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        $producto = $this->productos->buscarActivoPorId($idProducto);
        if ($producto === null) {
            return ["status" => "error", "message" => "Producto no encontrado o no disponible"];
        }

        if ($cantidad > (int) $producto['cantidad']) {
            return ["status" => "error", "message" => "No hay suficiente stock para esa cantidad"];
        }

        $carrito[$idProducto] = $cantidad;

        return $this->respuestaCarrito($carrito, "Cantidad actualizada");
    }

    public function eliminar(array $carrito, int $idProducto): array
    {
        $carrito = $this->normalizar($carrito);
        unset($carrito[$idProducto]);

        return $this->respuestaCarrito($carrito, "Producto eliminado del carrito");
    }

    public function obtenerResumen(array $carrito): array
    {
        $carrito = $this->normalizar($carrito);
        $items = [];
        $carritoVigente = [];
        $total = 0.0;
        $totalUnidades = 0;
        $puedeComprar = true;

        foreach ($carrito as $idProducto => $cantidad) {
            $producto = $this->productos->buscarActivoPorId($idProducto);
            if ($producto === null) {
                continue;
            }

            $stock = (int) $producto['cantidad'];
            $precio = (float) $producto['precio'];
            $stockSuficiente = $cantidad <= $stock;
            $subtotal = round($precio * $cantidad, 2);

            $carritoVigente[$idProducto] = $cantidad;
            $items[] = [
                "id" => (int) $producto['id'],
                "nombre" => $producto['nombre'],
                "productor" => $producto['nom_productor'],
                "precio" => $precio,
                "cantidad" => $cantidad,
                "stock" => $stock,
                "stock_suficiente" => $stockSuficiente,
                "subtotal" => $subtotal
            ];

            $total += $subtotal;
            $totalUnidades += $cantidad;
            $puedeComprar = $puedeComprar && $stockSuficiente;
        }

        return [
            "status" => "success",
            "carrito" => $carritoVigente,
            "items" => $items,
            "total" => round($total, 2),
            "total_unidades" => $totalUnidades,
            "puede_comprar" => $items !== [] && $puedeComprar
        ];
    }

    private function normalizar(array $carrito): array
    {
        $normalizado = [];
        foreach ($carrito as $idProducto => $cantidad) {
            $idProducto = (int) $idProducto;
            $cantidad = (int) $cantidad;
            if ($idProducto > 0 && $cantidad > 0 && $cantidad <= 99) {
                $normalizado[$idProducto] = $cantidad;
            }
        }

        return $normalizado;
    }

    private function respuestaCarrito(array $carrito, string $mensaje): array
    {
        return [
            "status" => "success",
            "message" => $mensaje,
            "carrito" => $carrito,
            "total_unidades" => array_sum($carrito)
        ];
    }
}
