<?php

class AdminAuthDecorator extends AdminControllerDecorator
{
    public function handle(array $get, array $post, array $session): void
    {
        $usuario = $session['usuario'] ?? null;

        if ($usuario === null) {
            header('Location: index.php');
            exit;
        }

        $rolSesion = strtolower(trim($usuario['nom_rol'] ?? ''));
        $rolNormalizado = $rolSesion === 'administrador' ? 'admin' : $rolSesion;

        if ($rolNormalizado !== 'admin') {
            header('Location: panel.php');
            exit;
        }

        $this->wrapped->handle($get, $post, $session);
    }
}
