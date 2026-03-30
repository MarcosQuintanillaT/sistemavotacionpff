<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $cargo_id = $_GET['cargo'] ?? null;
    if ($cargo_id) {
        $stmt = $db->prepare("
            SELECT c.*, u.nombre AS nombre_candidato, u.foto_url AS usuario_foto, u.grado, u.seccion,
                   p.nombre AS partido, p.color AS partido_color, p.logo_url AS partido_logo,
                   ca.nombre AS cargo, ca.id AS cargo_id
            FROM candidatos c
            JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN partidos p ON c.partido_id = p.id
            JOIN cargos ca ON c.cargo_id = ca.id
            WHERE c.activo = 1 AND c.cargo_id = ?
            ORDER BY c.numero_candidato
        ");
        $stmt->execute([$cargo_id]);
    } else {
        $stmt = $db->query("
            SELECT c.*, u.nombre AS nombre_candidato, u.foto_url AS usuario_foto,
                   p.nombre AS partido, p.color AS partido_color,
                   ca.nombre AS cargo, ca.orden AS cargo_orden
            FROM candidatos c
            JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN partidos p ON c.partido_id = p.id
            JOIN cargos ca ON c.cargo_id = ca.id
            WHERE c.activo = 1
            ORDER BY ca.orden, c.numero_candidato
        ");
    }
    echo json_encode(['success' => true, 'candidatos' => $stmt->fetchAll()]);

} elseif ($method === 'POST' && isAdmin()) {
    $usuario_id = $_POST['usuario_id'] ?? '';
    $partido_id = $_POST['partido_id'] ?? null;
    $cargo_id = $_POST['cargo_id'] ?? '';
    $propuesta = $_POST['propuesta'] ?? '';
    $numero = $_POST['numero_candidato'] ?? null;

    if (empty($usuario_id) || empty($cargo_id)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO candidatos (usuario_id, partido_id, cargo_id, propuesta, numero_candidato) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $partido_id ?: null, $cargo_id, $propuesta, $numero]);

    logAuditoria('CANDIDATO_CREADO', "Candidato usuario_id=$usuario_id cargo=$cargo_id");
    echo json_encode(['success' => true, 'message' => 'Candidato registrado exitosamente']);

} elseif ($method === 'DELETE' && isAdmin()) {
    $id = $_GET['id'] ?? '';
    if ($id) {
        $stmt = $db->prepare("UPDATE candidatos SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        logAuditoria('CANDIDATO_ELIMINADO', "Candidato id=$id");
        echo json_encode(['success' => true, 'message' => 'Candidato eliminado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
}
