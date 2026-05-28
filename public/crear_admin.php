<?php
// Ruta temporal: public/crear_admin.php

require_once '../app/Config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Datos del primer administrador
$rol_id = 1; // 1 = Admin (Según nuestro script SQL)
$legajo = "ADMIN01"; // Con este legajo iniciarás sesión
$nombre_completo = "Administrador del Sistema";
$password_plana = "admin123"; // La contraseña que escribirás en el login

// Hasheamos la contraseña
$password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO usuarios (rol_id, legajo, nombre_completo, password_hash) VALUES (:rol_id, :legajo, :nombre, :hash)");
    $stmt->execute([
        ':rol_id' => $rol_id,
        ':legajo' => $legajo,
        ':nombre' => $nombre_completo,
        ':hash' => $password_hash
    ]);
    
    echo "<h1>¡Usuario administrador creado con éxito!</h1>";
    echo "<p>Legajo: <strong>" . htmlspecialchars($legajo) . "</strong></p>";
    echo "<p>Contraseña: <strong>" . htmlspecialchars($password_plana) . "</strong></p>";
    echo "<p><a href='login.php'>Ir al Login</a></p>";
    echo "<p style='color:red;'>IMPORTANTE: Por seguridad, elimina este archivo (crear_admin.php) de tu servidor ahora mismo.</p>";

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Código de error de duplicidad en MySQL
        echo "El usuario con ese legajo ya existe.";
    } else {
        echo "Error al crear el usuario: " . $e->getMessage();
    }
}
?>