<?php

/**
 * Modelo MVC - Producto.
 *
 * El modelo representa los datos que vienen de productos y evita que la
 * capa de servicio tenga que devolver filas de mysqli sin estructura. La
 * vista sigue recibiendo un arreglo compatible con el frontend existente
 * mediante toArray().
 */
class Producto
{
    public function __construct(
        private int $id,
        private string $nombre,
        private string $descripcion,
        private float $precio,
        private ?string $imagen,
        private string $productor,
        private int $cantidad,
        private bool $activo
    ) {
    }

    public static function fromDatabase(array $fila): self
    {
        return new self(
            (int) $fila['id'],
            (string) $fila['nombre'],
            (string) $fila['descripcion'],
            (float) $fila['precio'],
            $fila['imagen'] ?? null,
            (string) $fila['nom_productor'],
            (int) $fila['cantidad'],
            (bool) $fila['estado']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'imagen' => $this->imagen,
            'productor' => $this->productor,
            'cantidad' => $this->cantidad,
            'disponible' => $this->cantidad > 0,
            'activo' => $this->activo
        ];
    }
}
