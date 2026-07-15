<?php

/**
 * Controlador MVC - Contexto del panel.
 *
 * El dashboard tiene una vista grande y heredada en panel.php. Este
 * controlador separa su responsabilidad de entrada: valida la sesion,
 * normaliza el rol y entrega a la vista el contexto que necesita. Las
 * secciones visuales existentes se conservan para no cambiar la interfaz.
 */
class PanelController
{
    private const ROLES_PERMITIDOS = ['admin', 'logistico', 'productor', 'consumidor'];

    public function context(array $session): ?array
    {
        if (!isset($session['usuario']) || !is_array($session['usuario'])) {
            return null;
        }

        $usuario = $session['usuario'];
        $rolSesion = strtolower(trim($usuario['nom_rol'] ?? ''));
        $rolActual = $rolSesion === 'administrador' ? 'admin' : $rolSesion;

        if (!in_array($rolActual, self::ROLES_PERMITIDOS, true)) {
            return null;
        }

        return [
            'usuario' => $usuario,
            'rol' => $rolActual,
            'totalCarrito' => array_sum(is_array($session['carrito'] ?? null) ? $session['carrito'] : [])
        ];
    }
}
