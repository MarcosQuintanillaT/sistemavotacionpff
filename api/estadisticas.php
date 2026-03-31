<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

try {
    $db = getDB();

    $total_votantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'votante' AND activo = 1")->fetchColumn();
    $total_votaron = $db->query("SELECT COUNT(DISTINCT votante_id) FROM votos")->fetchColumn();
    $total_candidatos = $db->query("SELECT COUNT(*) FROM candidatos WHERE activo = 1")->fetchColumn();
    $total_partidos = $db->query("SELECT COUNT(*) FROM partidos WHERE activo = 1")->fetchColumn();
    $total_votos = $db->query("SELECT COUNT(*) FROM votos")->fetchColumn();

    // Votos por hora (últimas 24h)
    $stmt = $db->query("
        SELECT DATE_FORMAT(fecha_voto, '%H:00') AS hora, COUNT(*) AS cantidad
        FROM votos WHERE fecha_voto >= NOW() - INTERVAL 24 HOUR
        GROUP BY hora ORDER BY hora
    ");
    $votos_por_hora = $stmt->fetchAll();

    // Participación por grado
    $stmt = $db->query("
        SELECT u.grado,
            COUNT(DISTINCT u.id) AS total_estudiantes,
            COUNT(DISTINCT v.votante_id) AS votaron
        FROM usuarios u
        LEFT JOIN votos v ON u.id = v.votante_id
        WHERE u.rol = 'votante' AND u.activo = 1 AND u.grado IS NOT NULL AND u.grado != ''
        GROUP BY u.grado
        ORDER BY u.grado
    ");
    $participacion_grado = $stmt->fetchAll();

    // Últimos votos (con protección si no hay votos)
    $ultimos_votos = [];
    try {
        $stmt = $db->query("
            SELECT DATE_FORMAT(v.fecha_voto, '%H:%i:%s') AS hora, u2.nombre AS votante, ca.nombre AS cargo,
                   u3.nombre AS candidato_votado
            FROM votos v
            JOIN usuarios u2 ON v.votante_id = u2.id
            JOIN cargos ca ON v.cargo_id = ca.id
            JOIN candidatos c2 ON v.candidato_id = c2.id
            JOIN usuarios u3 ON c2.usuario_id = u3.id
            ORDER BY v.fecha_voto DESC LIMIT 15
        ");
        $ultimos_votos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $ultimos_votos = [];
    }

    // Líderes por cargo
    $stmt = $db->query("
        SELECT ca.nombre AS cargo, u.nombre AS lider, COUNT(v.id) AS votos, IFNULL(p.color, '#22c55e') AS color
        FROM candidatos c
        JOIN usuarios u ON c.usuario_id = u.id
        JOIN cargos ca ON c.cargo_id = ca.id
        LEFT JOIN partidos p ON c.partido_id = p.id
        LEFT JOIN votos v ON c.id = v.candidato_id
        WHERE c.activo = 1
        GROUP BY c.id, ca.nombre, u.nombre, p.color
        ORDER BY ca.orden, votos DESC
    ");
    $lideres = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'estadisticas' => [
            'total_votantes' => (int)$total_votantes,
            'total_votaron' => (int)$total_votaron,
            'total_candidatos' => (int)$total_candidatos,
            'total_partidos' => (int)$total_partidos,
            'total_votos' => (int)$total_votos,
            'participacion' => $total_votantes > 0 ? round(($total_votaron / $total_votantes) * 100, 1) : 0
        ],
        'votos_por_hora' => $votos_por_hora,
        'participacion_grado' => $participacion_grado,
        'ultimos_votos' => $ultimos_votos,
        'lideres' => $lideres
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la consulta: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
