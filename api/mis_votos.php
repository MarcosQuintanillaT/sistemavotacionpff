<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$db = getDB();
$votante_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT v.cargo_id, v.candidato_id, ca.nombre AS cargo, u.nombre AS candidato
    FROM votos v
    JOIN candidatos c ON v.candidato_id = c.id
    JOIN usuarios u ON c.usuario_id = u.id
    JOIN cargos ca ON v.cargo_id = ca.id
    WHERE v.votante_id = ?
");
$stmt->execute([$votante_id]);

echo json_encode(['success' => true, 'votos' => $stmt->fetchAll()]);
