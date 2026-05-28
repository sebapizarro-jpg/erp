<?php
// Ruta: app/Models/Pieza.php
// Namespace PSR-4

namespace App\Models;

use PDO;
use PDOException;

class Pieza {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las piezas activas incluyendo la razón social del proveedor
     * 
     * @return array
     */
    public function obtenerTodas() {
        try {
            $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.tipo_pieza, p.proveedor_id, p.created_at, prov.razon_social as proveedor 
                    FROM catalogo_piezas p 
                    LEFT JOIN proveedores prov ON p.proveedor_id = prov.id 
                    WHERE p.activo = 1 
                    ORDER BY p.id DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener piezas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una pieza por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT p.*, prov.razon_social as proveedor 
                    FROM catalogo_piezas p 
                    LEFT JOIN proveedores prov ON p.proveedor_id = prov.id 
                    WHERE p.id = :id AND p.activo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener pieza por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene proveedores activos para selectores
     * 
     * @return array
     */
    public function obtenerProveedoresActivos() {
        try {
            $stmt = $this->pdo->query("SELECT id, razon_social FROM proveedores WHERE activo = 1 ORDER BY razon_social ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener proveedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda una nueva pieza en el catálogo e inicializa stock
     * 
     * @param string $codigo_sku
     * @param string $nombre
     * @param string $tipo_pieza
     * @param int|null $proveedor_id
     * @return bool|string
     */
    public function guardar($codigo_sku, $nombre, $tipo_pieza, $proveedor_id = null) {
        try {
            // Iniciar transacción para integridad de datos
            $this->pdo->beginTransaction();

            $sqlPieza = "INSERT INTO catalogo_piezas (codigo_sku, nombre, tipo_pieza, proveedor_id) 
                         VALUES (:sku, :nombre, :tipo, :proveedor_id)";
            $stmt = $this->pdo->prepare($sqlPieza);
            
            $stmt->execute([
                ':sku'          => trim($codigo_sku),
                ':nombre'       => trim($nombre),
                ':tipo'         => $tipo_pieza,
                ':proveedor_id' => (!empty($proveedor_id) && $tipo_pieza === 'Comercial') ? $proveedor_id : null
            ]);
            
            $pieza_id = $this->pdo->lastInsertId();

            // Inicializar stock en inventario
            $sqlStock = "INSERT INTO stock_inventario (pieza_catalogo_id, cantidad_fisica, cantidad_reservada, ubicacion) 
                         VALUES (:pieza_id, 0.00, 0.00, 'No Asignada')";
            $stmtStock = $this->pdo->prepare($sqlStock);
            $stmtStock->execute([':pieza_id' => $pieza_id]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al guardar pieza: " . $e->getMessage());
            
            if ($e->getCode() == 23000) {
                return 'El código SKU ya existe en el catálogo.';
            }
            return 'Error al guardar la pieza: ' . $e->getMessage();
        }
    }

    /**
     * Actualiza una pieza existente
     * 
     * @param int $id
     * @param string $codigo_sku
     * @param string $nombre
     * @param string $tipo_pieza
     * @param int|null $proveedor_id
     * @return bool|string
     */
    public function actualizar($id, $codigo_sku, $nombre, $tipo_pieza, $proveedor_id = null) {
        try {
            $sql = "UPDATE catalogo_piezas 
                    SET codigo_sku = :sku, nombre = :nombre, tipo_pieza = :tipo, proveedor_id = :proveedor_id 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            
            $resultado = $stmt->execute([
                ':id'           => $id,
                ':sku'          => trim($codigo_sku),
                ':nombre'       => trim($nombre),
                ':tipo'         => $tipo_pieza,
                ':proveedor_id' => (!empty($proveedor_id) && $tipo_pieza === 'Comercial') ? $proveedor_id : null
            ]);

            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al actualizar pieza: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                return 'El código SKU ya existe en otra pieza.';
            }
            return 'Error al actualizar la pieza: ' . $e->getMessage();
        }
    }

    /**
     * Baja lógica de una pieza (activo = 0)
     * 
     * @param int $id
     * @return bool|string
     */
    public function eliminar($id) {
        try {
            $sql = "UPDATE catalogo_piezas SET activo = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar pieza: " . $e->getMessage());
            return 'Error al eliminar la pieza: ' . $e->getMessage();
        }
    }

    /**
     * Obtiene el stock disponible de una pieza
     * 
     * @param int $pieza_id
     * @return array|null
     */
    public function obtenerStock($pieza_id) {
        try {
            $sql = "SELECT cantidad_fisica, cantidad_reservada, ubicacion 
                    FROM stock_inventario 
                    WHERE pieza_catalogo_id = :pieza_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pieza_id' => $pieza_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener stock: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcula cantidad disponible (física - reservada)
     * 
     * @param int $pieza_id
     * @return float
     */
    public function obtenerDisponible($pieza_id) {
        $stock = $this->obtenerStock($pieza_id);
        if (!$stock) return 0;
        return $stock['cantidad_fisica'] - $stock['cantidad_reservada'];
    }
}
