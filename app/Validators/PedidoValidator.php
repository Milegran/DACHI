<?php

class PedidoValidator
{
    public function validarProductoCantidad(int $idProducto, int $cantidad): ?string
    {
        if ($idProducto <= 0) {
            return "Producto no valido";
        }

        if ($cantidad < 1 || $cantidad > 99) {
            return "La cantidad debe estar entre 1 y 99";
        }

        return null;
    }

    public function validarCompra(int $idUsuario, int $idMetodoPago, array $carrito): ?string
    {
        if ($idUsuario <= 0) {
            return "La sesion del usuario no es valida";
        }

        if ($idMetodoPago <= 0) {
            return "Selecciona un metodo de pago";
        }

        if ($carrito === []) {
            return "El carrito esta vacio";
        }

        foreach ($carrito as $idProducto => $cantidad) {
            if ((int) $idProducto <= 0 || (int) $cantidad < 1 || (int) $cantidad > 99) {
                return "El carrito contiene datos no validos";
            }
        }

        return null;
    }

    public function validarDatosTarjeta(array $datos): ?string
    {
        $titular = trim((string) ($datos['titular'] ?? ''));
        $numero = preg_replace('/\D+/', '', (string) ($datos['numero'] ?? ''));
        $vencimiento = trim((string) ($datos['vencimiento'] ?? ''));
        $cvv = preg_replace('/\D+/', '', (string) ($datos['cvv'] ?? ''));

        if (!preg_match("/^[\p{L}\s.'-]{3,80}$/u", $titular)) {
            return "Ingresa el nombre del titular de la tarjeta";
        }

        if (!preg_match('/^\d{13,19}$/', $numero) || !$this->numeroTarjetaValido($numero)) {
            return "El numero de tarjeta no es valido";
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $vencimiento, $partes)) {
            return "El vencimiento debe tener formato MM/AA";
        }

        $mes = (int) $partes[1];
        $anio = 2000 + (int) $partes[2];
        $mesActual = (int) date('n');
        $anioActual = (int) date('Y');
        if ($anio < $anioActual || ($anio === $anioActual && $mes < $mesActual) || $anio > $anioActual + 20) {
            return "La fecha de vencimiento no es valida";
        }

        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            return "El codigo de seguridad no es valido";
        }

        return null;
    }

    private function numeroTarjetaValido(string $numero): bool
    {
        $suma = 0;
        $duplicar = false;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];
            if ($duplicar) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
            $duplicar = !$duplicar;
        }

        return $suma % 10 === 0;
    }
}
