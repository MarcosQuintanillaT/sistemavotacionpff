<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$codigo = trim($_POST['codigo'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($codigo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT * FROM usuarios WHERE identidad = ? AND activo = 1");
$stmt->execute([$codigo]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Código estudiantil no encontrado']);
    exit;
}

if ($user['rol'] === 'votante') {
    if ($password !== '1612') {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        exit;
    }
} else {
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        exit;
    }
}

if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['foto_url'] = $user['foto_url'];

    logAuditoria('LOGIN', "Inicio de sesión: {$user['nombre']}");

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
    echo json_encode(['success' => false, 'message' => 'Código estudiantil no encontrado']);
}
