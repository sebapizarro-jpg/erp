<?php
// app/Models/Usuario.php

class Usuario {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Autentica a un usuario y devuelve sus datos si el login es exitoso.
     */
    public function autenticar($legajo, $password_plana) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.legajo, u.nombre_completo, u.password_hash, u.activo, r.nombre as rol_nombre 
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.legajo = :legajo AND u.activo = 1
        ");
        
        $stmt->execute([':legajo' => $legajo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos si existe el usuario y si la contraseña coincide con el hash
        if ($usuario && password_verify($password_plana, $usuario['password_hash'])) {
            // Actualizamos la fecha de último acceso
            $this->actualizarUltimoAcceso($usuario['id']);
            
            // Eliminamos el hash del array antes de devolverlo por seguridad
            unset($usuario['password_hash']); 
            return $usuario;
        }

        return false; // Credenciales inválidas o usuario inactivo
    }

    private function actualizarUltimoAcceso($id) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
?>