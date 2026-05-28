<?php
// app/Controllers/ControlCalidadController.php

require_once '../app/Services/AuthGuard.php';

// Protegemos la ruta inmediatamente. Si es un Operario, el script se detiene y lo expulsa.
AuthGuard::protegerRuta(['Admin', 'Calidad']);

// A partir de aquí, es seguro ejecutar la lógica de QC
// ...
// Y cuando necesites registrar un cambio de estado en la trazabilidad:
$usuario_responsable = $_SESSION['usuario_id']; // <-- ¡Aquí obtenemos quién hizo la acción!

// $nfModel->registrarTrazabilidad(..., $usuario_responsable, ...);
?>