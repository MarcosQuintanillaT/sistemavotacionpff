<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT p.*, 
        (SELECT COUNT(*) FROM candidatos WHERE partido_id = p.id AND activo = 1) AS total_candidatos
        FROM partidos p WHERE p.activo = 1 ORDER BY p.nombre");
    echo json_encode(['success' => true, 'partidos' => $stmt->fetchAll()]);

} elseif ($method === 'POST' && isAdmin()) {
    $nombre = trim($_POST['nombre'] ?? '');
    $slogan = trim($_POST['slogan'] ?? '');
    $color = $_POST['color'] ?? '#6366f1';
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO partidos (nombre, slogan, color, descripcion) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $slogan, $color, $descripcion]);
    logAuditoria('PARTIDO_CREADO', "Partido: $nombre");
    echo json_encode(['success' => true, 'message' => 'Partido registrado']);

} else {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
}
