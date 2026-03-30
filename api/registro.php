<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$codigo = trim($_POST['codigo_estudiantil'] ?? '');
$grado = trim($_POST['grado'] ?? '');
$seccion = trim($_POST['seccion'] ?? '');

if (empty($nombre) || empty($email) || empty($password) || empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

$db = getDB();

// Verificar duplicados
$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? OR codigo_estudiantil = ?");
$stmt->execute([$email, $codigo]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'El email o código estudiantil ya está registrado']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, codigo_estudiantil, grado, seccion) VALUES (?, ?, ?, ?, ?, ?)");

try {
    $stmt->execute([$nombre, $email, $hash, $codigo, $grado, $seccion]);
    logAuditoria('REGISTRO', "Nuevo votante registrado: $email");
    echo json_encode(['success' => true, 'message' => 'Registro exitoso. Ya puedes iniciar sesión']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . $e->getMessage()]);
}
