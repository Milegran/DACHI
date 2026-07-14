<?php

/**
 * Punto unico de infraestructura.
 *
 * Todas las paginas incluyen este archivo y reciben la misma instancia de
 * Database::getInstance() durante la peticion. Esto aplica el Singleton de
 * forma uniforme a login, panel, catalogo, carrito y seguimiento.
 */
require_once __DIR__ . '/app/Core/Database.php';

try {
    $conn = Database::getInstance()->connection();
} catch (RuntimeException $exception) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => $exception->getMessage()]));
}
?>
