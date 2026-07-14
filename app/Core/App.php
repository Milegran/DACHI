<?php

require_once __DIR__ . '/AdminControllerInterface.php';
require_once __DIR__ . '/AdminControllerDecorator.php';
require_once __DIR__ . '/AdminAuthDecorator.php';
require_once __DIR__ . '/AdminAuditDecorator.php';
require_once __DIR__ . '/../Services/Admin/AdminUsuarioService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminUsuarioController.php';
require_once __DIR__ . '/../Services/Admin/AdminLogisticaService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminLogisticaController.php';
require_once __DIR__ . '/../Services/Admin/AdminConsumidorService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminConsumidorController.php';
require_once __DIR__ . '/../Services/Admin/AdminProductoService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminProductoController.php';
require_once __DIR__ . '/../Services/Admin/AdminCategoriaService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminCategoriaController.php';
require_once __DIR__ . '/../Services/Admin/AdminPedidoService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminPedidoController.php';
require_once __DIR__ . '/../Services/Admin/AdminCalificacionService.php';
require_once __DIR__ . '/../Controllers/Admin/AdminCalificacionController.php';

class App
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function crearAdminUsuarioController(): AdminControllerInterface
    {
        $service = new AdminUsuarioService($this->conn);
        $core = new AdminUsuarioController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }

    public function crearAdminLogisticaController(): AdminControllerInterface
    {
        $service = new AdminLogisticaService($this->conn);
        $core = new AdminLogisticaController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }

    public function crearAdminConsumidorController(): AdminControllerInterface
    {
        $service = new AdminConsumidorService($this->conn);
        $core = new AdminConsumidorController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }

    public function crearAdminProductoController(): AdminControllerInterface
    {
        $service = new AdminProductoService($this->conn);
        $core = new AdminProductoController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }

    public function crearAdminCategoriaController(): AdminControllerInterface
    {
        $service = new AdminCategoriaService($this->conn);
        $core = new AdminCategoriaController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }

    public function crearAdminPedidoController(): AdminControllerInterface
    {
        $service = new AdminPedidoService($this->conn);
        $core = new AdminPedidoController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }

    public function crearAdminCalificacionController(): AdminControllerInterface
    {
        $service = new AdminCalificacionService($this->conn);
        $core = new AdminCalificacionController($service);

        return new AdminAuditDecorator(
            new AdminAuthDecorator($core),
            $this->conn
        );
    }
}
