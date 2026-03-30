<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para votar']);
    exit;
}

$votante_id = $_SESSION['user_id'];

if ($_SESSION['rol'] === 'admin') {
    echo json_encode(['success' => false, 'message' => 'Los administradores no pueden votar']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$candidato_id = $payload['candidato_id'] ?? '';

if (empty($candidato_id)) {
    echo json_encode(['success' => false, 'message' => 'No se seleccionó candidato']);
    exit;
}

$db = getDB();

// Verificar que el candidato existe y está activo
$stmt = $db->prepare("
    SELECT c.*, ca.nombre AS cargo FROM candidatos c
    JOIN cargos ca ON c.cargo_id = ca.id
    WHERE c.id = ? AND c.activo = 1
");
$stmt->execute([$candidato_id]);
$candidato = $stmt->fetch();

if (!$candidato) {
    echo json_encode(['success' => false, 'message' => 'Candidato no válido']);
    exit;
}

// Verificar si ya votó por este cargo
$stmt = $db->prepare("SELECT id FROM votos WHERE votante_id = ? AND cargo_id = ?");
$stmt->execute([$votante_id, $candidato['cargo_id']]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => "Ya votaste por el cargo de {$candidato['cargo']}"]);
    exit;
}

// Registrar el voto
try {
    $stmt = $db->prepare("INSERT INTO votos (votante_id, candidato_id, cargo_id, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$votante_id, $candidato_id, $candidato['cargo_id'], $_SERVER['REMOTE_ADDR']]);

    logAuditoria('VOTO_EMITIDO', "Voto por candidato_id=$candidato_id cargo={$candidato['cargo']}");

    // Obtener resultados actualizados
    $stmt = $db->prepare("
        SELECT c.id, u.nombre, COUNT(v.id) AS votos
        FROM candidatos c
        JOIN usuarios u ON c.usuario_id = u.id
        LEFT JOIN votos v ON c.id = v.candidato_id
        WHERE c.cargo_id = ?
        GROUP BY c.id ORDER BY votos DESC
    ");
    $stmt->execute([$candidato['cargo_id']]);

    echo json_encode([
        'success' => true,
        'message' => "¡Tu voto para {$candidato['cargo']} ha sido registrado!",
        'cargo' => $candidato['cargo']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar el voto']);
}
