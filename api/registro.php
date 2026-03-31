<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$password = $_POST['password'] ?? '1612';
$identidad = trim($_POST['identidad'] ?? '');
$email = trim($_POST['email'] ?? '');
$grado = trim($_POST['grado'] ?? '');
$seccion = trim($_POST['seccion'] ?? '');
$rol = $_POST['rol'] ?? 'votante';
$admin_code = $_POST['admin_code'] ?? '';

if (empty($nombre) || empty($identidad)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse']);
    exit;
}

if ($rol === 'admin') {
    $secret_code = 'ADMIN_PFF2026';
    if ($admin_code !== $secret_code) {
        echo json_encode(['success' => false, 'message' => 'Código de administrador inválido']);
        exit;
    }
} else {
    $password = '1612';
}

$db = getDB();

// Verificar si la identidad ya existe
$stmt = $db->prepare("SELECT id FROM usuarios WHERE identidad = ?");
$stmt->execute([$identidad]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Esta identidad ya está registrada']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// Generar email interno automáticamente si no se proporciona
$email_interno = $email ?: ($identidad . '@estudiante.local');

$stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, identidad, grado, seccion, rol) VALUES (?, ?, ?, ?, ?, ?, ?)");

try {
    $stmt->execute([$nombre, $email_interno, $hash, $identidad, $grado, $seccion, $rol]);
    $newUserId = $db->lastInsertId();

    // Subir foto si se envió
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../imagen/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $filename = 'usuario_' . $newUserId . '_' . time() . '.' . $ext;
            $rutaDestino = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                $fotoUrl = 'imagen/' . $filename;
                $db->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?")->execute([$fotoUrl, $newUserId]);
            }
        }
    }

    logAuditoria('REGISTRO', "Nuevo votante registrado: $nombre (Identidad: $identidad)");
    echo json_encode(['success' => true, 'message' => 'Registro exitoso. Ya puedes iniciar sesión con tu código']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . $e->getMessage()]);
}
