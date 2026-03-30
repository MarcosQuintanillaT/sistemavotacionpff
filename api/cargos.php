<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = getDB();

$stmt = $db->query("
    SELECT ca.*, 
        (SELECT COUNT(*) FROM candidatos WHERE cargo_id = ca.id AND activo = 1) AS total_candidatos
    FROM cargos ca ORDER BY ca.orden
");

echo json_encode(['success' => true, 'cargos' => $stmt->fetchAll()]);
