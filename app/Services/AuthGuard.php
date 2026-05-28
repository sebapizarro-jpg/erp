<?php
// app/Services/AuthGuard.php

class AuthGuard {
    
    public static function iniciarSesion($usuario_datos) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerar el ID de sesión previene ataques de fijación de sesión (Session Fixation)
        session_regenerate_id(true); 
        
        $_SESSION['usuario_id'] = $usuario_datos['id'];
        $_SESSION['legajo'] = $usuario_datos['legajo'];
        $_SESSION['nombre_completo'] = $usuario_datos['nombre_completo'];
        $_SESSION['rol'] = $usuario_datos['rol_nombre'];
    }

    public static function protegerRuta($roles_permitidos = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. ¿Está logueado?
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /login.php");
            exit;
        }

        // 2. ¿Tiene el rol adecuado? (Si se especificaron roles)
        if (!empty($roles_permitidos)) {
            if (!in_array($_SESSION['rol'], $roles_permitidos)) {
                // Si no tiene permiso, lo mandamos a un panel general o página de error 403
                header("Location: /dashboard.php?error=acceso_denegado");
                exit;
            }
        }
    }

    public static function cerrarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /login.php");
        exit;
    }
}
?>