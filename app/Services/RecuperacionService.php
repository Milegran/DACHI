<?php

class RecuperacionService
{
    private const MINUTOS_EXPIRACION = 15;
    private const MENSAJE_GENERICO_SOLICITUD = "Si el correo esta registrado, se envio un codigo de verificacion valido por 15 minutos";
    private const MENSAJE_CODIGO_INVALIDO = "Codigo invalido o expirado";

    private UsuarioRepository $usuarios;
    private RecuperacionRepository $tokens;
    private RecuperacionValidator $validator;

    public function __construct(UsuarioRepository $usuarios, RecuperacionRepository $tokens, RecuperacionValidator $validator)
    {
        $this->usuarios = $usuarios;
        $this->tokens = $tokens;
        $this->validator = $validator;
    }

    public function solicitarCodigo(string $correo): array
    {
        $error = $this->validator->validarSolicitud($correo);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        // No revelamos si el correo existe o no en la base (mismo criterio usado en login),
        // para evitar que un atacante enumere correos registrados.
        $usuario = $this->usuarios->buscarPorCorreo($correo);

        if ($usuario !== null) {
            $codigo = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $tokenHash = hash('sha256', $codigo);
            $expiraEn = (new DateTime())->modify('+' . self::MINUTOS_EXPIRACION . ' minutes')->format('Y-m-d H:i:s');

            // Un solo token activo por usuario: cualquier codigo anterior sin usar queda invalidado.
            $this->tokens->invalidarActivosPorUsuario((int) $usuario['id']);
            $this->tokens->crearToken((int) $usuario['id'], $tokenHash, $expiraEn);

            $this->enviarCorreo($usuario['correo'], $usuario['nombre'], $codigo);
        }

        return ["status" => "success", "message" => self::MENSAJE_GENERICO_SOLICITUD];
    }

    public function confirmarNuevaContrasena(string $correo, string $codigo, string $nuevaContrasena): array
    {
        $error = $this->validator->validarConfirmacion($correo, $codigo, $nuevaContrasena);
        if ($error !== null) {
            return ["status" => "error", "message" => $error];
        }

        $usuario = $this->usuarios->buscarPorCorreo($correo);
        if ($usuario === null) {
            return ["status" => "error", "message" => self::MENSAJE_CODIGO_INVALIDO];
        }

        $tokenHash = hash('sha256', $codigo);
        $token = $this->tokens->buscarTokenValido((int) $usuario['id'], $tokenHash);
        if ($token === null) {
            return ["status" => "error", "message" => self::MENSAJE_CODIGO_INVALIDO];
        }

        $nuevoHash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $this->usuarios->actualizarContrasena((int) $usuario['id'], $nuevoHash);
        // Uso unico: el token queda marcado como usado inmediatamente despues del cambio exitoso.
        $this->tokens->marcarUsado((int) $token['id']);

        return ["status" => "success", "message" => "Contrasena actualizada correctamente"];
    }

    private function enviarCorreo(string $correo, string $nombre, string $codigo): void
    {
        $asunto = "Codigo de recuperacion DACHI";
        $cuerpo = "Hola " . $nombre . ",\n\n"
            . "Tu codigo de recuperacion es: " . $codigo . "\n"
            . "Este codigo vence en " . self::MINUTOS_EXPIRACION . " minutos y solo se puede usar una vez.\n\n"
            . "Si no solicitaste este cambio, ignora este mensaje.";
        $cabeceras = "From: no-reply@dachi.local";

        // Envio best-effort. En XAMPP local, sin un servidor SMTP configurado en php.ini,
        // mail() normalmente no entrega el correo. Para pruebas en ambiente local, el codigo
        // tambien puede verificarse directamente en la tabla `recuperacion_contrasena`
        // (columna token_hash = SHA-256 del codigo). No se expone el codigo en la respuesta
        // JSON al navegador por seguridad.
        @mail($correo, $asunto, $cuerpo, $cabeceras);
    }
}
