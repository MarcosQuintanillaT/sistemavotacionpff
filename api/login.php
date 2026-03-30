<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['foto_url'] = $user['foto_url'];

    logAuditoria('LOGIN', "Inicio de sesión: {$user['email']}");

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'rol' => $user['rol']
        ],
        'redirect' => $user['rol'] === 'admin' ? 'admin/dashboard.php' : 'votacion.php'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas o cuenta desactivada']);
}
