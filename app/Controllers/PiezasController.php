<?php
// Ruta: app/Controllers/PiezasController.php
// Controlador para gestión de piezas

namespace App\Controllers;

use App\Models\Pieza;
use App\Services\AuthGuard;

class PiezasController {
    private $piezaModel;

    public function __construct(Pieza $piezaModel) {
        $this->piezaModel = $piezaModel;
    }

    /**
     * Renderiza la vista del catálogo de piezas
     */
    public function index() {
        AuthGuard::protegerRuta();

        $error = '';
        $exito = '';
        $piezas = $this->piezaModel->obtenerTodas();
        $proveedores = $this->piezaModel->obtenerProveedoresActivos();

        require_once __DIR__ . '/../Views/Piezas/index.php';
    }

    /**
     * Procesa la creación de una nueva pieza
     * 
     * @return array Respuesta con estado y mensaje
     */
    public function crear() {
        $sku = trim($_POST['codigo_sku'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = $_POST['tipo_pieza'] ?? '';
        $proveedor_id = $_POST['proveedor_id'] ?? null;

        if (empty($sku) || empty($nombre) || empty($tipo)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ];
        }

        $resultado = $this->piezaModel->guardar($sku, $nombre, $tipo, $proveedor_id);

        return [
            'success' => $resultado === true,
            'message' => $resultado === true ? 'Pieza incorporada con éxito.' : $resultado
        ];
    }

    /**
     * Procesa la edición de una pieza existente
     * 
     * @return array Respuesta con estado y mensaje
     */
    public function editar() {
        $id = $_POST['id'] ?? '';
        $sku = trim($_POST['codigo_sku'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = $_POST['tipo_pieza'] ?? '';
        $proveedor_id = $_POST['proveedor_id'] ?? null;

        if (empty($id) || empty($sku) || empty($nombre) || empty($tipo)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ];
        }

        $resultado = $this->piezaModel->actualizar($id, $sku, $nombre, $tipo, $proveedor_id);

        return [
            'success' => $resultado === true,
            'message' => $resultado === true ? 'Pieza modificada correctamente.' : $resultado
        ];
    }

    /**
     * Procesa la eliminación (baja lógica) de una pieza
     * 
     * @return array Respuesta con estado y mensaje
     */
    public function eliminar() {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            return [
                'success' => false,
                'message' => 'ID de pieza requerido.'
            ];
        }

        $resultado = $this->piezaModel->eliminar($id);

        return [
            'success' => $resultado === true,
            'message' => $resultado === true ? 'Pieza removida correctamente.' : $resultado
        ];
    }

    /**
     * Obtiene el stock de una pieza en formato JSON (para AJAX)
     * 
     * @return void
     */
    public function obtenerStock() {
        if (!isset($_GET['pieza_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'pieza_id requerido']);
            exit;
        }

        $pieza_id = intval($_GET['pieza_id']);
        $stock = $this->piezaModel->obtenerStock($pieza_id);

        if (!$stock) {
            http_response_code(404);
            echo json_encode(['error' => 'Pieza no encontrada']);
            exit;
        }

        $disponible = $this->piezaModel->obtenerDisponible($pieza_id);

        echo json_encode([
            'cantidad_fisica' => $stock['cantidad_fisica'],
            'cantidad_reservada' => $stock['cantidad_reservada'],
            'disponible' => $disponible,
            'ubicacion' => $stock['ubicacion']
        ]);
    }
}
