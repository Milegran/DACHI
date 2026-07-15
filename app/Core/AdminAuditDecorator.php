<?php

class AdminAuditDecorator extends AdminControllerDecorator
{
    private mysqli $conn;

    public function __construct(AdminControllerInterface $wrapped, mysqli $conn)
    {
        parent::__construct($wrapped);
        $this->conn = $conn;
    }

    public function handle(array $get, array $post, array $session): void
    {
        $usuarioId = (int)($session['usuario']['id'] ?? 0);
        $accion = $get['accion'] ?? ($post['accion'] ?? 'listar_productores');
        $metodo = $_SERVER['REQUEST_METHOD'];

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO auditoria (id_usuario, accion, metodo, datos, ip, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            if ($stmt) {
                $datos = json_encode([
                    'get' => $this->sanitizar($get),
                    'post' => $this->sanitizar($post)
                ]);
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $stmt->bind_param('issss', $usuarioId, $accion, $metodo, $datos, $ip);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            error_log("AdminAuditDecorator: " . $e->getMessage());
        }

        $this->wrapped->handle($get, $post, $session);
    }

    private function sanitizar(array $data): array
    {
        $claves = ['accion', 'id', 'busqueda', 'estado'];
        $resultado = [];
        foreach ($claves as $k) {
            if (isset($data[$k])) {
                $resultado[$k] = $data[$k];
            }
        }
        return $resultado;
    }
}
