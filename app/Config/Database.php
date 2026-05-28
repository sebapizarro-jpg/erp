<?php
// Ruta: app/Config/Database.php

class Database {
    private $host = "db";
    private $db_name = "erp_industrial";
    private $username = "root";
    private $password = "12345678&&&";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // El charset=utf8mb4 es crucial para no tener problemas con acentos y eñes
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            
            // Configuramos PDO para que lance excepciones si hay errores SQL
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
            // En producción, esto debería ir a un archivo de log, no a la pantalla
            exit;
        }
        return $this->conn;
    }
}
?>