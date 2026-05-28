<?php
// Ruta: autoload.php
// Autoloader PSR-4 para carga automática de clases

spl_autoload_register(function ($class) {
    // Definir el namespace base
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    // Verificar si la clase pertenece a nuestro namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obtener la parte relativa del namespace
    $relativeClass = substr($class, $len);

    // Convertir namespace a ruta de archivo
    // App\Models\Pieza -> Models/Pieza.php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Si el archivo existe, incluirlo
    if (file_exists($file)) {
        require $file;
    }
});

// Cargar variables de entorno
require_once __DIR__ . '/app/Config/Env.php';
Env::load();
