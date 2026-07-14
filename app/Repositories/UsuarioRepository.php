<?php

class UsuarioRepository
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.correo, u.telefono, u.`contraseña` AS contrasena_hash, r.nom_rol
                FROM usuarios u
                INNER JOIN rol r ON u.id_rol = r.id
                WHERE u.correo = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        return $usuario ?: null;
    }

    public function existeCorreo(string $correo): bool
    {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    public function obtenerIdRol(string $rol): ?int
    {
        $stmt = $this->conn->prepare("SELECT id FROM rol WHERE nom_rol = ?");
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        return $fila ? (int) $fila['id'] : null;
    }

    public function crearUsuario(int $idRol, string $nombre, string $apellido, string $correo, string $hash, string $telefono): int
    {
        $stmt = $this->conn->prepare("INSERT INTO usuarios (id_rol, nombre, apellido, correo, `contraseña`, telefono) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $idRol, $nombre, $apellido, $correo, $hash, $telefono);
        $stmt->execute();
        $idUsuario = (int) $this->conn->insert_id;
        $stmt->close();

        return $idUsuario;
    }

    public function actualizarContrasena(int $idUsuario, string $hash): void
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET `contraseña` = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $idUsuario);
        $stmt->execute();
        $stmt->close();
    }
}
