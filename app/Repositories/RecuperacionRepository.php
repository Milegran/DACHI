<?php

class RecuperacionRepository
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function invalidarActivosPorUsuario(int $idUsuario): void
    {
        $stmt = $this->conn->prepare("UPDATE recuperacion_contrasena SET usado = 1 WHERE id_usuario = ? AND usado = 0");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->close();
    }

    public function crearToken(int $idUsuario, string $tokenHash, string $expiraEn): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO recuperacion_contrasena (id_usuario, token_hash, expira_en, usado, creado_en)
             VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->bind_param("iss", $idUsuario, $tokenHash, $expiraEn);
        $stmt->execute();
        $stmt->close();
    }

    public function buscarTokenValido(int $idUsuario, string $tokenHash): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM recuperacion_contrasena
             WHERE id_usuario = ? AND token_hash = ? AND usado = 0 AND expira_en > NOW()
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->bind_param("is", $idUsuario, $tokenHash);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }

    public function marcarUsado(int $idToken): void
    {
        $stmt = $this->conn->prepare("UPDATE recuperacion_contrasena SET usado = 1 WHERE id = ?");
        $stmt->bind_param("i", $idToken);
        $stmt->execute();
        $stmt->close();
    }
}
