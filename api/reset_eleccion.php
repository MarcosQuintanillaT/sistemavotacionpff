<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$db = getDB();

// Contar votos antes de borrar
$totalVotos = $db->query("SELECT COUNT(*) FROM votos")->fetchColumn();

// Borrar todos los votos
$db->exec("DELETE FROM votos");

// Actualizar configuración para cerrar votación
$stmt = $db->prepare("
    INSERT INTO config (clave, valor) VALUES ('votacion_abierta', '0')
    ON DUPLICATE KEY UPDATE valor = '0'
");
$stmt->execute();

logAuditoria('RESET_ELECCION', "Elección reiniciada. $totalVotos votos eliminados.");

echo json_encode([
    'success' => true, 
    'message' => "Elección reiniciada. $totalVotos votos eliminados."
]);
