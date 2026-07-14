<?php

require_once __DIR__ . '/../Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Repositories/RecuperacionRepository.php';
require_once __DIR__ . '/../Repositories/ProductoRepository.php';
require_once __DIR__ . '/../Repositories/PedidoRepository.php';
require_once __DIR__ . '/../Repositories/SeguimientoRepository.php';
require_once __DIR__ . '/../Validators/AuthValidator.php';
require_once __DIR__ . '/../Validators/RecuperacionValidator.php';
require_once __DIR__ . '/../Validators/PedidoValidator.php';
require_once __DIR__ . '/../Validators/SeguimientoValidator.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Services/RecuperacionService.php';
require_once __DIR__ . '/../Services/ProductoService.php';
require_once __DIR__ . '/../Services/CarritoService.php';
require_once __DIR__ . '/../Services/PedidoService.php';
require_once __DIR__ . '/../Services/SeguimientoService.php';

class SistemaDachiFacade
{
    private AuthService $authService;
    private RecuperacionService $recuperacionService;
    private ProductoService $productoService;
    private CarritoService $carritoService;
    private PedidoService $pedidoService;
    private SeguimientoService $seguimientoService;

    public function __construct(mysqli $conn)
    {
        $usuarioRepository = new UsuarioRepository($conn);
        $authValidator = new AuthValidator();
        $this->authService = new AuthService($usuarioRepository, $authValidator);

        $recuperacionRepository = new RecuperacionRepository($conn);
        $recuperacionValidator = new RecuperacionValidator($authValidator);
        $this->recuperacionService = new RecuperacionService($usuarioRepository, $recuperacionRepository, $recuperacionValidator);

        $productoRepository = new ProductoRepository($conn);
        $this->productoService = new ProductoService($productoRepository);

        $pedidoValidator = new PedidoValidator();
        $this->carritoService = new CarritoService($productoRepository, $pedidoValidator);
        $pedidoRepository = new PedidoRepository($conn);
        $this->pedidoService = new PedidoService($pedidoRepository, $this->carritoService, $pedidoValidator);

        $seguimientoRepository = new SeguimientoRepository($conn);
        $seguimientoValidator = new SeguimientoValidator();
        $this->seguimientoService = new SeguimientoService($seguimientoRepository, $seguimientoValidator);
    }

    public function iniciarSesion(string $correo, string $contrasena): array
    {
        return $this->authService->iniciarSesion($correo, $contrasena);
    }

    public function registrarUsuario(string $nombre, string $apellido, string $correo, string $contrasena, string $rol): array
    {
        return $this->authService->registrarUsuario($nombre, $apellido, $correo, $contrasena, $rol);
    }

    public function solicitarRecuperacion(string $correo): array
    {
        return $this->recuperacionService->solicitarCodigo($correo);
    }

    public function confirmarRecuperacion(string $correo, string $codigo, string $nuevaContrasena): array
    {
        return $this->recuperacionService->confirmarNuevaContrasena($correo, $codigo, $nuevaContrasena);
    }

    public function listarProductos(string $busqueda = ''): array
    {
        return $this->productoService->listarCatalogo($busqueda);
    }

    public function obtenerProducto(int $id): array
    {
        return $this->productoService->obtenerDetalle($id);
    }

    public function agregarAlCarrito(array $carrito, int $idProducto, int $cantidad = 1): array
    {
        return $this->carritoService->agregar($carrito, $idProducto, $cantidad);
    }

    public function actualizarCarrito(array $carrito, int $idProducto, int $cantidad): array
    {
        return $this->carritoService->actualizar($carrito, $idProducto, $cantidad);
    }

    public function eliminarDelCarrito(array $carrito, int $idProducto): array
    {
        return $this->carritoService->eliminar($carrito, $idProducto);
    }

    public function obtenerCarrito(array $carrito): array
    {
        return $this->carritoService->obtenerResumen($carrito);
    }

    public function listarMetodosPago(): array
    {
        return $this->pedidoService->listarMetodosPago();
    }

    public function confirmarCompra(int $idUsuario, int $idMetodoPago, array $carrito, array $datosPago = []): array
    {
        return $this->pedidoService->confirmarCompra($idUsuario, $idMetodoPago, $carrito, $datosPago);
    }

    public function listarPedidosUsuario(int $idUsuario): array
    {
        return $this->seguimientoService->listarPedidosUsuario($idUsuario);
    }

    public function obtenerDireccionEntrega(int $idUsuario): array
    {
        return $this->seguimientoService->obtenerDireccion($idUsuario);
    }

    public function guardarDireccionEntrega(int $idUsuario, string $provincia, string $distrito, string $corregimiento, string $detalle): array
    {
        return $this->seguimientoService->guardarDireccion($idUsuario, $provincia, $distrito, $corregimiento, $detalle);
    }

    public function asignarEntrega(int $idPedido, int $idRepartidor): array
    {
        return $this->seguimientoService->asignarEntrega($idPedido, $idRepartidor);
    }

    public function confirmarEntrega(int $idPedido, int $idRepartidor): array
    {
        return $this->seguimientoService->confirmarEntrega($idPedido, $idRepartidor);
    }
}
