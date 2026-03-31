<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$db = getDB();

// Guardar configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toggle de votación
    if (isset($_POST['toggle_votacion'])) {
        $stmt = $db->prepare("
            INSERT INTO config (clave, valor) VALUES ('votacion_abierta', ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        $stmt->execute([$_POST['votacion_abierta']]);
        $nuevoEstado = $_POST['votacion_abierta'] === '1' ? 'abierta' : 'cerrada';
        logAuditoria('VOTACION_TOGGLE', "Votación $nuevoEstado");
        echo json_encode([
            'success' => true, 
            'message' => 'Estado de votación actualizado',
            'votacion_abierta' => $_POST['votacion_abierta']
        ]);
        exit;
    }
    
    $configs = [
        'nombre_eleccion' => $_POST['nombre_eleccion'] ?? '',
        'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
        'fecha_fin' => $_POST['fecha_fin'] ?? '',
        'votacion_abierta' => isset($_POST['votacion_abierta']) ? '1' : '0',
        'sistema_bloqueado' => isset($_POST['sistema_bloqueado']) ? $_POST['sistema_bloqueado'] : '1'
    ];
    
    foreach ($configs as $clave => $valor) {
        $stmt = $db->prepare("
            INSERT INTO config (clave, valor) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        $stmt->execute([$clave, $valor]);
    }
    
    $msg = 'Configuración de elecciones actualizada';
    if (isset($_POST['sistema_bloqueado'])) {
        $msg .= ($_POST['sistema_bloqueado'] === '0') ? ' - Sistema desbloqueado' : ' - Sistema bloqueado';
    }
    logAuditoria('CONFIG_UPDATE', $msg);
    echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
    exit;
}

// Obtener configuración
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query("SELECT clave, valor FROM config");
    $configs = [];
    while ($row = $stmt->fetch()) {
        $configs[$row['clave']] = $row['valor'];
    }
    echo json_encode(['success' => true, 'config' => $configs]);
    exit;
}
