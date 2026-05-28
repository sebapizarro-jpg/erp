<?php
// Ruta: public/piezas.php
require_once '../app/Config/Database.php';
require_once '../app/Models/Pieza.php';
require_once '../app/Services/AuthGuard.php';

AuthGuard::protegerRuta();

$database = new Database();
$pdo = $database->getConnection();
$piezaModel = new Pieza($pdo);

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $sku = $_POST['codigo_sku'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $tipo = $_POST['tipo_pieza'] ?? '';
        $proveedor_id = $_POST['proveedor_id'] ?? null;

        if (empty($sku) || empty($nombre) || empty($tipo)) {
            $error = 'Todos los campos son obligatorios.';
        } else {
            $resultado = $piezaModel->guardar($sku, $nombre, $tipo, $proveedor_id);
            if ($resultado === true) {
                $exito = 'Pieza incorporada al catálogo con éxito.';
            } else {
                $error = $resultado;
            }
        }
    } 
    
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $sku = $_POST['codigo_sku'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $tipo = $_POST['tipo_pieza'] ?? '';
        $proveedor_id = $_POST['proveedor_id'] ?? null;

        if (empty($id) || empty($sku) || empty($nombre) || empty($tipo)) {
            $error = 'Todos los campos son obligatorios para editar.';
        } else {
            $resultado = $piezaModel->actualizar($id, $sku, $nombre, $tipo, $proveedor_id);
            if ($resultado === true) {
                $exito = 'Pieza modificada correctamente.';
            } else {
                $error = $resultado;
            }
        }
    } 
    
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $resultado = $piezaModel->eliminar($id);
            if ($resultado === true) {
                $exito = 'Pieza removida del catálogo correctamente.';
            } else {
                $error = $resultado;
            }
        }
    }
}

$piezas = $piezaModel->obtenerTodas();
$proveedores = $piezaModel->obtenerProveedoresActivos(); 
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Piezas - ERP Industrial</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Iconos limpios -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        // Controlador nativo y limpio para los modales
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
                modal.classList.toggle('flex');
            }
        }

        function verQr(sku, descripcion) {
            document.getElementById('qrTitle').innerText = sku;
            document.getElementById('qrDesc').innerText = descripcion;
            document.getElementById('qrTextFooter').innerText = sku;
            const urlQr = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(sku);
            document.getElementById('qrImage').src = urlQr;
            toggleModal('modalQR');
        }

        function prepararEditar(id, sku, nombre, tipo, proveedorId) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_codigo_sku').value = sku;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_selectTipoPieza').value = tipo;
            document.getElementById('edit_proveedor_id').value = proveedorId ? proveedorId : "";
            toggleModal('modalEditarPieza');
        }

        function prepararEliminar(id, sku) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_sku_text').innerText = sku;
            toggleModal('modalEliminarPieza');
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex">

    <!-- Menú Lateral Fino -->
    <aside class="w-64 bg-slate-900 text-slate-400 flex flex-col justify-between p-5 border-r border-slate-800 print:hidden">
        <div>
            <div class="flex items-center gap-3 px-2 py-3 text-white border-b border-slate-800 pb-5">
                <i class="bi bi-gear-fill text-indigo-500 text-lg"></i>
                <span class="text-base font-semibold tracking-wide">ERP Planta</span>
            </div>
            <nav class="mt-6 space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-slate-200 transition">
                    <i class="bi bi-speedometer2 text-slate-500"></i> Panel Principal
                </a>
                <a href="piezas.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm bg-slate-800 text-white transition">
                    <i class="bi bi-box-seam text-indigo-400"></i> Catálogo Piezas
                </a>
                <a href="proyectos.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-slate-200 transition">
                    <i class="bi bi-file-earmark-text text-slate-500"></i> Notas Fabricación
                </a>
                <a href="calidad.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-slate-200 transition">
                    <i class="bi bi-shield-check text-slate-500"></i> Control Calidad
                </a>
            </nav>
        </div>
        <a href="logout.php" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-slate-800 hover:bg-rose-950 hover:text-rose-400 text-slate-300 text-sm font-medium rounded-lg transition border border-slate-700/50">
            <i class="bi bi-box-arrow-left"></i> Salir
        </a>
    </aside>

    <!-- Contenedor Principal -->
    <main class="flex-1 overflow-y-auto p-8 bg-slate-50">
        <!-- Encabezado Fino -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-5 mb-6 print:hidden">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-slate-900">Catálogo Maestro de Piezas</h1>
                <p class="text-xs text-slate-400 mt-0.5">Gestión interna de planos, SKUs e inventario base.</p>
            </div>
            <button onclick="toggleModal('modalAltaPieza')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <i class="bi bi-plus-circle text-indigo-200"></i> Nueva Pieza
            </button>
        </div>

        <!-- Mensajes de Estado Flotantes -->
        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-lg flex items-center gap-2">
                <i class="bi bi-exclamation-triangle text-rose-500"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg flex items-center gap-2">
                <i class="bi bi-check-circle text-emerald-500"></i> <?= htmlspecialchars($exito) ?>
            </div>
        <?php endif; ?>

        <!-- Tabla Estilo Minimalista (Bordes delgados, fuentes medianas/regulares) -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-medium uppercase tracking-wider text-slate-400">
                        <th class="py-3.5 px-4 w-14 text-center">ID</th>
                        <th class="py-3.5 px-4">Código SKU</th>
                        <th class="py-3.5 px-4">Nombre / Descripción Técnica</th>
                        <th class="py-3.5 px-4">Configuración</th>
                        <th class="py-3.5 px-4">Origen / Proveedor</th>
                        <th class="py-3.5 px-4 text-center">Trazabilidad</th>
                        <th class="py-3.5 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 text-sm text-slate-600">
                    <?php if (empty($piezas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400 font-light">No hay piezas registradas en el catálogo operativo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($piezas as $p): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4 text-center font-normal text-slate-400 text-xs"><?= $p['id'] ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono bg-slate-50 border border-slate-200/80 text-slate-600 px-2 py-0.5 rounded text-xs"><?= htmlspecialchars($p['codigo_sku']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 font-normal text-slate-900"><?= htmlspecialchars($p['nombre']) ?></td>
                                <td class="py-3.5 px-4">
                                    <?php
                                    $badgeStyle = 'bg-sky-50 text-sky-700 border-sky-150';
                                    if ($p['tipo_pieza'] === 'Standard') $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-150';
                                    if ($p['tipo_pieza'] === 'Variable') $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-150';
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 border rounded text-xs font-medium <?= $badgeStyle ?>"><?= $p['tipo_pieza'] ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-normal">
                                    <?= !empty($p['proveedor']) ? htmlspecialchars($p['proveedor']) : '<span class="text-slate-400 font-mono text-xs">Interno (Planta)</span>' ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button onclick="verQr('<?= addslashes($p['codigo_sku']) ?>', '<?= addslashes(htmlspecialchars($p['nombre'])) ?>')" class="px-2 py-1 text-xs bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 rounded transition font-medium">
                                        <i class="bi bi-qr-code text-slate-400 mr-1"></i> QR
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex rounded-md shadow-sm space-x-1" role="group">
                                        <button onclick="prepararEditar('<?= $p['id'] ?>', '<?= addslashes($p['codigo_sku']) ?>', '<?= addslashes(htmlspecialchars($p['nombre'])) ?>', '<?= $p['tipo_pieza'] ?>', '<?= $p['proveedor_id'] ?? '' ?>')" class="px-2 py-1 text-xs bg-slate-50 hover:bg-amber-50 text-slate-600 hover:text-amber-700 border border-slate-200 rounded transition">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="prepararEliminar('<?= $p['id'] ?>', '<?= addslashes($p['codigo_sku']) ?>')" class="px-2 py-1 text-xs bg-slate-50 hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 rounded transition">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- ── MODAL: ALTA DE PIEZA ──────────────────────────────── -->
    <div id="modalAltaPieza" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-semibold text-slate-900"><i class="bi bi-box-seam text-indigo-500 mr-2"></i>Incorporar Nueva Pieza</h3>
                <button onclick="toggleModal('modalAltaPieza')" class="text-slate-400 hover:text-slate-600 text-sm"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="piezas.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Código SKU / Plano</label>
                    <input type="text" name="codigo_sku" required class="w-full font-mono text-sm uppercase px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500" placeholder="Ej: SK-EJE-32MM" autocomplete="off">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Descripción Técnica</label>
                    <input type="text" name="nombre" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500" placeholder="Ej: Eje central de transmisión">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Tipo de Configuración</label>
                    <select name="tipo_pieza" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg bg-white focus:outline-none focus:border-indigo-500">
                        <option value="" disabled selected>Selecciona una opción...</option>
                        <option value="Standard">Standard (Stock propio habitual)</option>
                        <option value="Variable">Variable (A medida / Planos)</option>
                        <option value="Comercial">Comercial (Proveedor Externo)</option>
                    </select>
                </div>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <label class="block text-xs font-medium text-indigo-600 uppercase tracking-wider mb-1">Vincular Proveedor</label>
                    <select name="proveedor_id" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg bg-white focus:outline-none focus:border-indigo-500">
                        <option value="" selected>Opcional / Interno</option>
                        <?php foreach ($proveedores as $prov): ?>
                            <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-100 justify-end">
                    <button type="button" onclick="toggleModal('modalAltaPieza')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">Guardar Pieza</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── MODAL: EDICIÓN DE PIEZA ───────────────────────────── -->
    <div id="modalEditarPieza" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-semibold text-slate-900"><i class="bi bi-pencil-square text-amber-500 mr-2"></i>Modificar Datos</h3>
                <button onclick="toggleModal('modalEditarPieza')" class="text-slate-400 hover:text-slate-600 text-sm"><i class="bi bi-x-lg"></i></button>
            </div>
            <form action="piezas.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Código SKU / Plano</label>
                    <input type="text" id="edit_codigo_sku" name="codigo_sku" required class="w-full font-mono text-sm uppercase px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Descripción Técnica</label>
                    <input type="text" id="edit_nombre" name="nombre" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Tipo de Configuración</label>
                    <select id="edit_selectTipoPieza" name="tipo_pieza" required class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg bg-white focus:outline-none focus:border-indigo-500">
                        <option value="Standard">Standard (Stock propio habitual)</option>
                        <option value="Variable">Variable (A medida / Planos)</option>
                        <option value="Comercial">Comercial (Proveedor Externo)</option>
                    </select>
                </div>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                    <label class="block text-xs font-medium text-indigo-600 uppercase tracking-wider mb-1">Proveedor Asignado</label>
                    <select id="edit_proveedor_id" name="proveedor_id" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg bg-white focus:outline-none focus:border-indigo-500">
                        <option value="">Ninguno / Interno (Planta)</option>
                        <?php foreach ($proveedores as $prov): ?>
                            <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-100 justify-end">
                    <button type="button" onclick="toggleModal('modalEditarPieza')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition">Aplicar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── MODAL: CONFIRMACIÓN DE ELIMINACIÓN ─────────────────────── -->
    <div id="modalEliminarPieza" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden p-6 text-center">
            <div class="inline-flex p-3 bg-rose-50 text-rose-500 rounded-full mb-3">
                <i class="bi bi-exclamation-triangle text-lg"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900 mb-1">Confirmar Baja del Catálogo</h3>
            <div class="my-2">
                <span id="delete_sku_text" class="font-mono bg-rose-50 text-rose-700 border border-rose-150 px-2 py-0.5 rounded text-xs font-medium">SKU</span>
            </div>
            <p class="text-xs text-slate-400 mt-3 leading-relaxed">Esta acción removerá el ítem de las vistas del catálogo maestro activo, preservando el stock físico y las relaciones operativas previas.</p>
            <form action="piezas.php" method="POST" class="mt-5 flex gap-2">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_id" name="id">
                <button type="button" onclick="toggleModal('modalEliminarPieza')" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition">Cancelar</button>
                <button type="submit" class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition">Dar de Baja</button>
            </form>
        </div>
    </div>

    <!-- ── MODAL: VISUALIZADOR DE CÓDIGO QR ────────────────────────── -->
    <div id="modalQR" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden p-6 text-center">
            <h3 id="qrTitle" class="text-base font-mono font-medium text-slate-900">SKU</h3>
            <p id="qrDesc" class="text-xs text-slate-400 font-normal mt-0.5"></p>
            <div class="my-4 inline-block p-4 border border-slate-150 rounded-xl bg-white shadow-sm">
                <img id="qrImage" src="" alt="QR" class="w-44 h-44 mx-auto">
            </div>
            <div id="qrTextFooter" class="text-xs font-mono text-slate-400 mb-4 uppercase tracking-wider"></div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="flex-1 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium rounded-lg transition">Imprimir Etiqueta</button>
                <button onclick="toggleModal('modalQR')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition">Cerrar</button>
            </div>
        </div>
    </div>

</body>
</html>