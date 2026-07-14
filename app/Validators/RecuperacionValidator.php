<?php

class RecuperacionValidator
{
    private AuthValidator $authValidator;

    public function __construct(AuthValidator $authValidator)
    {
        $this->authValidator = $authValidator;
    }

    public function validarSolicitud(string $correo): ?string
    {
        if ($correo === '') {
            return "Debe ingresar su correo electronico";
        }

        if (!$this->authValidator->correoValido($correo)) {
            return "Ingrese un correo electronico valido";
        }

        return null;
    }

    public function validarConfirmacion(string $correo, string $codigo, string $nuevaContrasena): ?string
    {
        if ($correo === '' || $codigo === '' || $nuevaContrasena === '') {
            return "Debe completar todos los campos";
        }

        if (!$this->authValidator->correoValido($correo)) {
            return "Ingrese un correo electronico valido";
        }

        if (preg_match('/^\d{6}$/', $codigo) !== 1) {
            return "El codigo debe tener 6 digitos";
        }

        if (!$this->authValidator->contrasenaValida($nuevaContrasena)) {
            return "La contrasena debe tener minimo 8 caracteres, una mayuscula, una minuscula y un numero";
        }

        return null;
    }
}
