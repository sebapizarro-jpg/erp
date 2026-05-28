<?php
// Archivo: public/logout.php
require_once '../app/Services/AuthGuard.php';

// Ejecutamos la lógica de limpieza de sesión
AuthGuard::cerrarSesion();

// Redirección explícita absoluta desde la raíz del servidor
header("Location: /erp/public/login.php");
exit();
?>