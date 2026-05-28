<?php
// Ruta: app/Config/Env.php
// Carga variables de entorno desde archivo .env

class Env {
    private static $variables = [];
    private static $loaded = false;

    /**
     * Carga variables de entorno desde el archivo .env
     */
    public static function load($filePath = null) {
        if (self::$loaded) {
            return; // Evitar carga múltiple
        }

        if ($filePath === null) {
            $filePath = dirname(__DIR__, 2) . '/.env';
        }

        if (!file_exists($filePath)) {
            throw new Exception("Archivo .env no encontrado en: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea KEY=VALUE
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remover comillas si existen
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }

                self::$variables[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtiene una variable de entorno
     * 
     * @param string $key Clave de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get($key, $default = null) {
        return self::$variables[$key] ?? $default;
    }

    /**
     * Obtiene todas las variables cargadas
     */
    public static function all() {
        return self::$variables;
    }

    /**
     * Verifica si una variable existe
     */
    public static function has($key) {
        return isset(self::$variables[$key]);
    }
}
