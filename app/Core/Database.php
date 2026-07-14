<?php

/**
 * Infraestructura - Singleton de conexion a la base de datos.
 *
 * Esta clase garantiza una unica instancia de mysqli durante la peticion
 * actual. El constructor privado impide que cada pagina cree conexiones
 * diferentes y getInstance() centraliza el acceso para todo el sistema.
 *
 * Importante: en PHP el proceso termina al finalizar la peticion HTTP, por
 * lo que el Singleton controla la conexion dentro de una peticion, no crea
 * una conexion global permanente entre usuarios.
 */
class Database
{
    private static ?self $instance = null;
    private mysqli $connection;

    private function __construct()
    {
        $this->connection = new mysqli('127.0.0.1', 'root', '', 'dachitos', 3306);

        if ($this->connection->connect_error) {
            throw new RuntimeException('Error de conexion: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function connection(): mysqli
    {
        return $this->connection;
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('La instancia Database no puede restaurarse desde una serializacion.');
    }
}
