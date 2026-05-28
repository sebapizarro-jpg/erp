<?php
// Ruta: public/dashboard.php
require_once '../app/Config/Database.php';
require_once '../app/Services/AuthGuard.php';

AuthGuard::protegerRuta();

$database = new Database();
$pdo = $database->getConnection();

// Consultas rápidas para alimentar los indicadores numéricos del ERP
$totalPiezas = $pdo->query("SELECT COUNT(*) FROM catalogo_piezas WHERE activo = 1")->fetchColumn() ?: 0;
$totalProveedores = $pdo->query("SELECT COUNT(*) FROM proveedores WHERE activo = 1")->fetchColumn() ?: 0;
// Estos son ejemplos por si tenés estas tablas, si no, se inicializan en 0 automáticamente:
$alertasCalidad = 0; 
try { $alertasCalidad = $pdo->query("SELECT COUNT(*) FROM alertas_calidad WHERE estado = 'Pendiente'")->fetchColumn() ?: 0; } catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - ERP Industrial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased text-slate-800 flex">

    <aside class="w-64 bg-slate-900 text-slate-400 flex flex-col justify-between p-5 border-r border-slate-800">
        <div>
            <div class="flex items-center gap-3 px-2 py-3 text-white border-b border-slate-800 pb-5">
                <i class="bi bi-gear-fill text-indigo-500 text-lg"></i>
                <span class="text-base font-semibold tracking-wide">ERP Planta</span>
            </div>
            <nav class="mt-6 space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm bg-slate-800 text-white transition">
                    <i class="bi bi-speedometer2 text-indigo-400"></i> Panel Principal
                </a>
                <a href="piezas.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-slate-200 transition">
                    <i class="bi bi-box-seam text-slate-500"></i> Catálogo Piezas
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

    <main class="flex-1 overflow-y-auto p-8 bg-slate-50">
        <div class="flex items-center justify-between border-b border-slate-200 pb-5 mb-6">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-slate-900">Panel Principal</h1>
                <p class="text-xs text-slate-400 mt-0.5">Indicadores de planta en tiempo real y accesos directos.</p>
            </div>
            <div class="text-xs text-slate-400 font-mono bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm font-normal">
                Sesión: <span class="text-slate-700 font-medium"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Operador') ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Catálogo Maestro</p>
                    <h3 class="text-2xl font-normal text-slate-900 mt-1"><?= $totalPiezas ?> <span class="text-xs text-slate-400 font-normal">ítems activos</span></h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Cadena de Suministro</p>
                    <h3 class="text-2xl font-normal text-slate-900 mt-1"><?= $totalProveedores ?> <span class="text-xs text-slate-400 font-normal">proveedores</span></h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="bi bi-truck"></i>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Alertas de Calidad</p>
                    <h3 class="text-2xl font-normal text-slate-900 mt-1"><?= $alertasCalidad ?> <span class="text-xs text-slate-400 font-normal">pendientes</span></h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-sm font-medium text-slate-900 border-b border-slate-100 pb-3 mb-4">Módulos del Sistema</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <a href="piezas.php" class="p-4 border border-slate-150 rounded-xl hover:border-indigo-500 hover:bg-slate-50/50 transition block">
                    <div class="text-sm font-medium text-slate-900 flex items-center gap-2">
                        <i class="bi bi-box-seam text-indigo-500"></i> Catálogo de Piezas
                    </div>
                    <p class="text-xs text-slate-400 mt-1 font-normal font-light">Alta de SKUs, asignación de planos y visualización de etiquetas QR únicas.</p>
                </a>

                <a href="proyectos.php" class="p-4 border border-slate-150 rounded-xl hover:border-indigo-500 hover:bg-slate-50/50 transition block">
                    <div class="text-sm font-medium text-slate-900 flex items-center gap-2">
                        <i class="bi bi-file-earmark-text text-indigo-500"></i> Notas de Fabricación
                    </div>
                    <p class="text-xs text-slate-400 mt-1 font-normal font-light">Emisión de órdenes de mecanizado, seguimiento de proyectos y rutas técnicas.</p>
                </a>

                <a href="calidad.php" class="p-4 border border-slate-150 rounded-xl hover:border-indigo-500 hover:bg-slate-50/50 transition block">
                    <div class="text-sm font-medium text-slate-900 flex items-center gap-2">
                        <i class="bi bi-shield-check text-indigo-500"></i> Control de Calidad
                    </div>
                    <p class="text-xs text-slate-400 mt-1 font-normal font-light">Registro de tolerancias, auditorías de piezas terminadas y desvíos.</p>
                </a>

            </div>
        </div>
    </main>

</body>
</html>