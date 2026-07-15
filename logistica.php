<?php
// BLOQUE CONEXION - PATRON DE DISEÑO: SINGLETON
class BD
{
    private static $instancia = null;
    private $conn;

    private function __construct()
    {
        require 'conexion.php';
        $this->conn = $conn;
    }

    public static function obtener()
    {
        if (self::$instancia === null) {
            self::$instancia = new BD();
        }
        return self::$instancia;
    }

    public function link()
    {
        return $this->conn;
    }
}

$conn = BD::obtener()->link();
require_once __DIR__ . '/app/Facades/SistemaDachiFacade.php';
require_once __DIR__ . '/app/Controllers/LogisticaController.php';

// BLOQUE SESION
session_start();

// BLOQUE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// BLOQUE VALIDAR SESION ACTIVA
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$rolSesion = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
$rolActual = match ($rolSesion) {
    'administrador' => 'admin',
    default => $rolSesion
};

// BLOQUE VALIDAR ROL (esta vista es solo para logistico)
if ($rolActual !== 'logistico') {
    header('Location: index.php');
    exit;
}

// BLOQUE DECORATOR - PATRON DE DISEÑO: DECORATOR
// Envuelve a LogisticaController para agregar, sin modificar su codigo:
// 1) una segunda barrera de rol a nivel de controlador (defensa en profundidad,
//    ademas de la validacion de sesion/rol que ya se hizo arriba a nivel de vista)
// 2) trazabilidad de cada accion que cambia el estado de un pedido
interface LogisticaControllerInterface
{
    public function obtenerPedidos();
    public function manejarAccion(array $datos, array $usuarioActual);
}

abstract class LogisticaControllerDecorator implements LogisticaControllerInterface
{
    protected LogisticaControllerInterface $controladorLogistica;

    public function __construct(LogisticaControllerInterface $controladorLogistica)
    {
        $this->controladorLogistica = $controladorLogistica;
    }

    public function obtenerPedidos()
    {
        return $this->controladorLogistica->obtenerPedidos();
    }

    public function manejarAccion(array $datos, array $usuarioActual)
    {
        return $this->controladorLogistica->manejarAccion($datos, $usuarioActual);
    }
}

class LogisticaAuthDecorator extends LogisticaControllerDecorator
{
    private array $rolesPermitidos = ['logistico', 'admin'];

    private function validarAcceso(array $usuarioActual): void
    {
        $rol = strtolower(trim($usuarioActual['nom_rol'] ?? ''));
        $rol = $rol === 'administrador' ? 'admin' : $rol;

        if (!in_array($rol, $this->rolesPermitidos, true)) {
            http_response_code(403);
            throw new Exception('Acceso denegado: rol no autorizado para el modulo de Logistica.');
        }
    }

    public function obtenerPedidos()
    {
        return parent::obtenerPedidos();
    }

    public function manejarAccion(array $datos, array $usuarioActual)
    {
        $this->validarAcceso($usuarioActual);
        return parent::manejarAccion($datos, $usuarioActual);
    }
}

class LogisticaAuditDecorator extends LogisticaControllerDecorator
{
    private mysqli $conexion;

    public function __construct(LogisticaControllerInterface $controladorLogistica, mysqli $conexion)
    {
        parent::__construct($controladorLogistica);
        $this->conexion = $conexion;
    }

    public function manejarAccion(array $datos, array $usuarioActual)
    {
        $idPedido = isset($datos['id_pedido']) ? (int) $datos['id_pedido'] : null;
        $estadoAnterior = $idPedido !== null ? $this->obtenerEstadoActual($idPedido) : null;

        $resultado = parent::manejarAccion($datos, $usuarioActual);

        if ($idPedido !== null && isset($datos['accion'])) {
            $this->registrarCambioEnAuditoria($idPedido, $estadoAnterior, $datos['accion'], $usuarioActual);
        }

        return $resultado;
    }

    private function obtenerEstadoActual(int $idPedido): ?string
    {
        $consulta = "SELECT estado_detallado FROM pedidos WHERE id = ? LIMIT 1";
        $sentencia = $this->conexion->prepare($consulta);
        $sentencia->bind_param("i", $idPedido);
        $sentencia->execute();
        $resultado = $sentencia->get_result();
        $fila = $resultado->fetch_assoc();
        $sentencia->close();

        return $fila['estado_detallado'] ?? null;
    }

    private function registrarCambioEnAuditoria(int $idPedido, ?string $estadoAnterior, string $accion, array $usuarioActual): void
    {
        $idUsuario = $usuarioActual['id_usuario'] ?? null;
        $estadoNuevo = $this->obtenerEstadoActual($idPedido);

        $consulta = "INSERT INTO logistica_auditoria
                        (id_pedido, estado_anterior, estado_nuevo, accion, id_usuario, fecha_cambio)
                     VALUES (?, ?, ?, ?, ?, NOW())";

        $sentencia = $this->conexion->prepare($consulta);
        $sentencia->bind_param("isssi", $idPedido, $estadoAnterior, $estadoNuevo, $accion, $idUsuario);
        $sentencia->execute();
        $sentencia->close();
    }
}

$sistema = new SistemaDachiFacade($conn);
$ctrlBase = new LogisticaController($conn, $sistema);
$ctrl = new LogisticaAuditDecorator(new LogisticaAuthDecorator($ctrlBase), $conn);

// BLOQUE ACCIONES 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    header('Content-Type: text/plain');
    $ctrl->manejarAccion($_POST, $usuarioActual);
    exit;
}

// BLOQUE AJUSTES 
$ajustes = isset($_COOKIE['dachi_ajustes']) ? json_decode($_COOKIE['dachi_ajustes'], true) : [];
$fontSize = $ajustes['fontSize'] ?? 'mediano';
$darkMode = !empty($ajustes['darkMode']);
$fontSizesPx = ['pequeno' => '14px', 'mediano' => '16px', 'grande' => '18px'];

// BLOQUE DATOS
$pedidos = $ctrl->obtenerPedidos();

// BLOQUE NOTIFICACIONES 
$pedidosPendientes = count(array_filter($pedidos, static function (array $ped): bool {
    $estado = $ped['estado_detallado'] ?? 'pendiente';
    return in_array($estado, ['pendiente', 'en_preparacion'], true);
}));
$notificationCount = $pedidosPendientes > 0 ? 1 : 0;

require __DIR__ . '/views/logistica/dashboard.php';