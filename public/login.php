<?php
// Ruta: public/login.php
require_once '../app/Config/Database.php';
require_once '../app/Services/AuthGuard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_legajo = $_POST['usuario'] ?? ''; 
    $password = $_POST['password'] ?? '';

    if (empty($input_legajo) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $stmt = $pdo->prepare("SELECT id, legajo, password_hash, rol_id FROM usuarios WHERE legajo = :legajo AND activo = 1");
        $stmt->execute([':legajo' => $input_legajo]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userRow && password_verify($password, $userRow['password_hash'])) {
            $_SESSION['usuario_id'] = $userRow['id'];
            $_SESSION['usuario_nombre'] = $userRow['legajo'];
            $_SESSION['usuario_rol'] = $userRow['rol_id'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Credenciales incorrectas o usuario no autorizado.';
        }
    }
}
// AQUÍ CIERRA EL BLOQUE PHP PARA QUE EL NAVEGADOR PUEDA LEER EL HTML
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso al Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center">
    <div class="max-w-sm w-full bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-center font-semibold mb-6">ERP Industrial</h2>
        <?php if ($error): ?>
            <div class="bg-rose-50 text-rose-800 p-3 mb-4 rounded text-sm"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" class="space-y-4">
            <input type="text" name="usuario" placeholder="Legajo" required class="w-full border p-2 rounded">
            <input type="password" name="password" placeholder="Contraseña" required class="w-full border p-2 rounded">
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Ingresar</button>
        </form>
    </div>
</body>
</html>