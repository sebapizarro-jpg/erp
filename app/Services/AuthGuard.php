<?php
// Ruta: app/Services/AuthGuard.php
// Servicio de autenticación y autorización

namespace App\Services;

class AuthGuard {
    
    /**
     * Inicia una sesión de usuario con protección contra fijación de sesión
     * 
     * @param array $usuario_datos Datos del usuario
     */
    public static function iniciarSesion($usuario_datos) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerar ID de sesión previene ataques de Session Fixation
        session_regenerate_id(true); 
        
        $_SESSION['usuario_id'] = $usuario_datos['id'];
        $_SESSION['legajo'] = $usuario_datos['legajo'];
        $_SESSION['nombre_completo'] = $usuario_datos['nombre_completo'];
        $_SESSION['usuario_rol'] = $usuario_datos['rol_id'];
    }

    /**
     * Protege una ruta verificando autenticación y roles
     * 
     * @param array $roles_permitidos Roles que pueden acceder (vacío = todos autenticados)
     */
    public static function protegerRuta($roles_permitidos = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. ¿Está logueado?
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: /erp/public/login.php");
            exit;
        }

        // 2. ¿Tiene el rol adecuado? (Si se especificaron roles)
        if (!empty($roles_permitidos)) {
            $rol_usuario = $_SESSION['usuario_rol'] ?? null;
            if (!in_array($rol_usuario, $roles_permitidos)) {
                header("Location: /erp/public/dashboard.php?error=acceso_denegado");
                exit;
            }
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public static function cerrarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /erp/public/login.php");
        exit;
    }

    /**
     * Verifica si el usuario está autenticado
     * 
     * @return bool
     */
    public static function estaAutenticado() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Obtiene el rol del usuario actual
     * 
     * @return int|null
     */
    public static function obtenerRolUsuario() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_rol'] ?? null;
    }

    /**
     * Verifica si el usuario tiene un rol específico
     * 
     * @param int $rol_id
     * @return bool
     */
    public static function tieneRol($rol_id) {
        return self::obtenerRolUsuario() === $rol_id;
    }
}
