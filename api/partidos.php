<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("SELECT p.*,
            (SELECT COUNT(*) FROM candidatos WHERE partido_id = p.id AND activo = 1) AS total_candidatos
            FROM partidos p WHERE p.id = ?");
        $stmt->execute([$id]);
        $partido = $stmt->fetch();
        echo json_encode(['success' => true, 'partido' => $partido]);
        exit;
    }

    $stmt = $db->query("SELECT p.*,
        (SELECT COUNT(*) FROM candidatos WHERE partido_id = p.id AND activo = 1) AS total_candidatos
        FROM partidos p WHERE p.activo = 1 ORDER BY p.nombre");
    echo json_encode(['success' => true, 'partidos' => $stmt->fetchAll()]);

} elseif ($method === 'POST' && isAdmin()) {
    $accion = $_POST['accion'] ?? 'crear';

    if ($accion === 'editar' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $slogan = trim($_POST['slogan'] ?? '');
        $color = $_POST['color'] ?? '#22c55e';
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            exit;
        }

        $stmt = $db->prepare("SELECT id FROM partidos WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Partido no encontrado']);
            exit;
        }

        $stmt = $db->prepare("UPDATE partidos SET nombre = ?, slogan = ?, color = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$nombre, $slogan, $color, $descripcion, $id]);
        logAuditoria('PARTIDO_EDITADO', "Partido id=$id nombre=$nombre");
        echo json_encode(['success' => true, 'message' => 'Partido actualizado exitosamente']);
        exit;
    }

    if ($accion === 'eliminar' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("UPDATE partidos SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        logAuditoria('PARTIDO_ELIMINADO', "Partido id=$id");
        echo json_encode(['success' => true, 'message' => 'Partido eliminado']);
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $slogan = trim($_POST['slogan'] ?? '');
    $color = $_POST['color'] ?? '#22c55e';
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
