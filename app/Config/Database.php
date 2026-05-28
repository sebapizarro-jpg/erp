<?php
// Ruta: app/Config/Database.php
// Singleton pattern para conexión PDO

require_once __DIR__ . '/Env.php';

class Database {
    private static $instance = null;
    private $conn;

    /**
     * Constructor privado para evitar instanciación directa (Singleton)
     */
    private function __construct() {
        // Cargar variables de entorno si no están ya cargadas
        if (!Env::has('DB_HOST')) {
            Env::load();
        }

        try {
            $host = Env::get('DB_HOST', 'db');
            $dbName = Env::get('DB_NAME', 'erp_industrial');
            $user = Env::get('DB_USER', 'root');
            $password = Env::get('DB_PASSWORD', '');
            $charset = Env::get('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";

            $this->conn = new PDO($dsn, $user, $password);

            // Configurar PDO para lanzar excepciones en caso de errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Configurar modo de fetch por defecto
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $exception) {
            error_log("Error de conexión a BD: " . $exception->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }
    }

    /**
     * Obtiene la instancia singleton de Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Prevenir clonación de singleton
     */
    private function __clone() {}

    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton.");
    }
}
