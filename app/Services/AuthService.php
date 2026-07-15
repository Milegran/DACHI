<?php

class AuthService
{
    private UsuarioRepository $usuarios;
    private AuthValidator $validator;

    public function __construct(UsuarioRepository $usuarios, AuthValidator $validator)
    {
        $this->usuarios = $usuarios;
        $this->validator = $validator;
    }

    public function iniciarSesion(string $correo, string $contrasena): array
    {
        $error = $this->validator->validarLogin($correo, $contrasena);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        $usuario = $this->usuarios->buscarPorCorreo($correo);
        if ($usuario === null || !password_verify($contrasena, $usuario['contrasena_hash'])) {
            return ["status" => "error", "message" => "Credenciales incorrectas"];
        }

        return [
            "status" => "success",
            "usuario" => $this->formatearUsuarioSesion($usuario)
        ];
    }

    public function registrarUsuario(string $nombre, string $apellido, string $correo, string $contrasena, string $rol): array
    {
        $error = $this->validator->validarRegistro($nombre, $apellido, $correo, $contrasena, $rol);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        if ($this->usuarios->existeCorreo($correo)) {
            return ["status" => "error", "message" => "Este correo ya esta registrado"];
        }

        $idRol = $this->usuarios->obtenerIdRol($rol);
        if ($idRol === null) {
            return ["status" => "error", "message" => "El rol seleccionado no existe en el sistema"];
        }

        try {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $telefono = "";
            $nuevoId = $this->usuarios->crearUsuario($idRol, $nombre, $apellido, $correo, $hash, $telefono);
        } catch (mysqli_sql_exception $e) {
            return ["status" => "error", "message" => "No se pudo registrar el usuario"];
        }

        return [
            "status" => "success",
            "usuario" => [
                "id" => $nuevoId,
                "nombre" => $nombre,
                "apellido" => $apellido,
                "correo" => $correo,
                "telefono" => $telefono,
                "nom_rol" => $rol
            ]
        ];
    }

    private function formatearUsuarioSesion(array $usuario): array
    {
        return [
            "id" => $usuario['id'],
            "nombre" => $usuario['nombre'],
            "apellido" => $usuario['apellido'],
            "correo" => $usuario['correo'],
            "telefono" => $usuario['telefono'] ?? '',
            "nom_rol" => $usuario['nom_rol']
        ];
    }
}
