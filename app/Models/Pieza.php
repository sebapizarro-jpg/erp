<?php
// Ruta: app/Models/Pieza.php

class Pieza {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las piezas activas incluyendo la razón social del proveedor si corresponde
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
            return [];
        }
    }

    /**
     * Obtiene la lista de proveedores activos para alimentar los selectores de los formularios
     */
    public function obtenerProveedoresActivos() {
        try {
            $stmt = $this->pdo->query("SELECT id, razon_social FROM proveedores WHERE activo = 1 ORDER BY razon_social ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Guarda una nueva pieza en el catálogo e inicializa automáticamente su registro de stock en 0.00
     */
    public function guardar($codigo_sku, $nombre, $tipo_pieza, $proveedor_id = null) {
        try {
            // Iniciamos transacción para asegurar que si falla la inserción de stock, no se cree la pieza huérfana
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

            // Sincronización e inicialización del stock en el inventario general
            $sqlStock = "INSERT INTO stock_inventario (pieza_catalogo_id, cantidad_fisica, cantidad_reservada, ubicacion) 
                         VALUES (:pieza_id, 0.00, 0.00, 'No Asignada')";
            $stmtStock = $this->pdo->prepare($sqlStock);
            $stmtStock->execute([':pieza_id' => $pieza_id]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Código de error 23000 representa violación de restricción única (SKU duplicado)
            if ($e->getCode() == 23000) {
                return 'El código SKU ya existe en el catálogo.';
            }
            return 'Error al guardar la pieza: ' . $e->getMessage();
        }
    }

    /**
     * Actualiza las especificaciones técnicas, SKU, tipo o proveedor de una pieza existente
     */
    public function actualizar($id, $codigo_sku, $nombre, $tipo_pieza, $proveedor_id = null) {
        try {
            $sql = "UPDATE catalogo_piezas 
                    SET codigo_sku = :sku, nombre = :nombre, tipo_pieza = :tipo, proveedor_id = :proveedor_id 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':id'           => $id,
                ':sku'          => trim($codigo_sku),
                ':nombre'       => trim($nombre),
                ':tipo'         => $tipo_pieza,
                ':proveedor_id' => (!empty($proveedor_id) && $tipo_pieza === 'Comercial') ? $proveedor_id : null
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return 'El código SKU ya existe en otra pieza registrada.';
            }
            return 'Error al actualizar la pieza: ' . $e->getMessage();
        }
    }

    /**
     * Aplica una baja lógica (activo = 0) para ocultar del catálogo sin romper la trazabilidad histórica de stock
     */
    public function eliminar($id) {
        try {
            $sql = "UPDATE catalogo_piezas SET activo = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return 'Error al eliminar la pieza: ' . $e->getMessage();
        }
    }
}