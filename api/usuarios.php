<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("
        SELECT u.id, u.nombre, u.email, u.rol, u.grado, u.seccion, u.codigo_estudiantil, u.activo,
               u.created_at,
               CASE WHEN v.votante_id IS NOT NULL THEN 1 ELSE 0 END AS ya_voto
        FROM usuarios u
        LEFT JOIN (SELECT DISTINCT votante_id FROM votos) v ON u.id = v.votante_id
        WHERE u.rol = 'votante'
        ORDER BY u.nombre
    ");
    echo json_encode(['success' => true, 'usuarios' => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? 'estudiante123';
    $codigo = trim($_POST['codigo_estudiantil'] ?? '');
    $grado = trim($_POST['grado'] ?? '');
    $seccion = trim($_POST['seccion'] ?? '');
    $accion = $_POST['accion'] ?? 'crear';

    if ($accion === 'toggle' && !empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
        exit;
    }

    if ($accion === 'eliminar' && !empty($_POST['id'])) {
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
        exit;
    }

    if (empty($nombre) || empty($email) || empty($codigo)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, codigo_estudiantil, grado, seccion) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $hash, $codigo, $grado, $seccion]);

    logAuditoria('USUARIO_CREADO', "Votante: $email");
    echo json_encode(['success' => true, 'message' => 'Votante registrado']);

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
