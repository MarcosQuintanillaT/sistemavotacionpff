<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = getDB();

$stmt = $db->query("
    SELECT c.id AS candidato_id, u.nombre AS nombre_candidato, u.foto_url,
           ca.nombre AS cargo, ca.id AS cargo_id,
           p.nombre AS partido, p.color AS partido_color, p.logo_url AS partido_logo,
           COUNT(v.id) AS total_votos,
           c.propuesta, c.numero_candidato
    FROM candidatos c
    JOIN usuarios u ON c.usuario_id = u.id
    JOIN cargos ca ON c.cargo_id = ca.id
    LEFT JOIN partidos p ON c.partido_id = p.id
    LEFT JOIN votos v ON c.id = v.candidato_id
    WHERE c.activo = 1
    GROUP BY c.id
    ORDER BY cargo_id, total_votos DESC
");

$resultados = $stmt->fetchAll();

// Total de votantes habilitados
$total_votantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'votante' AND activo = 1")->fetchColumn();
// Total que ya votaron (al menos un voto)
$total_votaron = $db->query("SELECT COUNT(DISTINCT votante_id) FROM votos")->fetchColumn();
// Total de votos emitidos (cada voto por cargo cuenta)
$total_votos = $db->query("SELECT COUNT(*) FROM votos")->fetchColumn();

echo json_encode([
    'success' => true,
    'resultados' => $resultados,
    'estadisticas' => [
        'total_votantes' => (int)$total_votantes,
        'total_votaron' => (int)$total_votaron,
        'total_votos' => (int)$total_votos,
        'participacion' => $total_votantes > 0 ? round(($total_votaron / $total_votantes) * 100, 1) : 0
    ]
]);
