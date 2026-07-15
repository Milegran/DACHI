<?php

class SeguimientoValidator
{
    public function validarDireccion(string $provincia, string $distrito, string $corregimiento, string $detalle): ?string
    {
        $campos = [
            'provincia' => [$provincia, 2, 50],
            'distrito' => [$distrito, 2, 20],
            'corregimiento' => [$corregimiento, 2, 50]
        ];

        foreach ($campos as $nombre => [$valor, $minimo, $maximo]) {
            $largo = mb_strlen(trim($valor));
            if ($largo < $minimo || $largo > $maximo) {
                return "El campo $nombre no tiene una longitud valida";
            }
        }

        $largoDetalle = mb_strlen(trim($detalle));
        if ($largoDetalle < 5 || $largoDetalle > 250) {
            return "El detalle de la direccion debe tener entre 5 y 250 caracteres";
        }

        return null;
    }
}
