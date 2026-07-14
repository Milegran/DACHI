<?php

require_once __DIR__ . '/../Facades/SistemaDachiFacade.php';

/**
 * Controlador MVC - Autenticacion.
 *
 * Recibe la peticion de la vista index.php, decide que operacion de
 * autenticacion corresponde y delega las reglas de negocio al Facade.
 * La vista no conoce repositorios ni consultas SQL.
 */
class AuthController
{
    public function __construct(private SistemaDachiFacade $system)
    {
    }

    public function handle(array $request): array
    {
        $action = $request['accion'] ?? '';

        return match ($action) {
            'login' => $this->system->iniciarSesion(
                trim($request['correo'] ?? ''),
                $request['contrasena'] ?? ''
            ),
            'registro' => $this->system->registrarUsuario(
                trim($request['nombre'] ?? ''),
                trim($request['apellido'] ?? ''),
                trim($request['correo'] ?? ''),
                $request['contrasena'] ?? '',
                trim($request['rol'] ?? '')
            ),
            'recuperar_solicitud' => $this->system->solicitarRecuperacion(
                trim($request['correo'] ?? '')
            ),
            'recuperar_confirmacion' => $this->system->confirmarRecuperacion(
                trim($request['correo'] ?? ''),
                trim($request['codigo'] ?? ''),
                $request['nueva_contrasena'] ?? ''
            ),
            default => ['status' => 'error', 'message' => 'Accion invalida']
        };
    }

    public function startSession(array $response, array &$session): array
    {
        if ($response['status'] === 'success' && isset($response['usuario'])) {
            $session['usuario'] = $response['usuario'];
            unset($response['usuario']);
            $response['redirect'] = 'index.php';
        }

        return $response;
    }
}
