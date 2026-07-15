<?php

class AuthValidator
{
    public function validarLogin(string $correo, string $contrasena): ?string
    {
        if ($correo === '' || $contrasena === '') {
            return "Debe completar todos los campos";
        }

        if (!$this->correoValido($correo)) {
            return "Ingrese un correo electronico valido";
        }

        return null;
    }

    public function validarRegistro(string $nombre, string $apellido, string $correo, string $contrasena, string $rol): ?string
    {
        if ($nombre === '' || $apellido === '' || $correo === '' || $contrasena === '' || $rol === '') {
            return "Debe completar todos los campos";
        }

        if (!$this->nombreValido($nombre)) {
            return "El nombre solo debe contener letras y espacios";
        }

        if (!$this->nombreValido($apellido)) {
            return "El apellido solo debe contener letras y espacios";
        }

        if (!$this->correoValido($correo)) {
            return "Ingrese un correo electronico valido";
        }

        if (!$this->contrasenaValida($contrasena)) {
            return "La contrasena debe tener minimo 8 caracteres, una mayuscula, una minuscula y un numero";
        }

        if (!in_array($rol, ["consumidor", "productor"], true)) {
            return "Rol invalido";
        }

        return null;
    }

    public function correoValido(string $correo): bool
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false && strlen($correo) <= 150;
    }

    private function nombreValido(string $valor): bool
    {
        $valor = trim($valor);
        return strlen($valor) >= 2
            && strlen($valor) <= 60
            && preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/u', $valor) === 1;
    }

    public function contrasenaValida(string $contrasena): bool
    {
        return strlen($contrasena) >= 8
            && preg_match('/[A-Z]/', $contrasena) === 1
            && preg_match('/[a-z]/', $contrasena) === 1
            && preg_match('/[0-9]/', $contrasena) === 1;
    }
}
