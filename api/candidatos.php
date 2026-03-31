<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $cargo_id = $_GET['cargo'] ?? null;

    if ($id) {
        $stmt = $db->prepare("
            SELECT c.*, u.nombre AS nombre_candidato, u.foto_url AS usuario_foto, u.grado, u.seccion,
                   p.nombre AS partido, p.color AS partido_color, p.logo_url AS partido_logo,
                   ca.nombre AS cargo, ca.id AS cargo_id
            FROM candidatos c
            JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN partidos p ON c.partido_id = p.id
            JOIN cargos ca ON c.cargo_id = ca.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $candidato = $stmt->fetch();
        echo json_encode(['success' => true, 'candidato' => $candidato]);
        exit;
    }

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
    $accion = $_POST['accion'] ?? 'crear';

    if ($accion === 'editar' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $partido_id = $_POST['partido_id'] ?? null;
        $cargo_id = $_POST['cargo_id'] ?? '';
        $propuesta = $_POST['propuesta'] ?? '';
        $numero = $_POST['numero_candidato'] ?? null;

        if (empty($cargo_id)) {
            echo json_encode(['success' => false, 'message' => 'El cargo es obligatorio']);
            exit;
        }

        // Verificar que el candidato existe
        $stmt = $db->prepare("SELECT id, usuario_id FROM candidatos WHERE id = ? AND activo = 1");
        $stmt->execute([$id]);
        $candidato = $stmt->fetch();
        if (!$candidato) {
            echo json_encode(['success' => false, 'message' => 'Candidato no encontrado']);
            exit;
        }

        $stmt = $db->prepare("UPDATE candidatos SET partido_id = ?, cargo_id = ?, propuesta = ?, numero_candidato = ? WHERE id = ?");
        $stmt->execute([$partido_id ?: null, $cargo_id, $propuesta, $numero, $id]);

        // Subir nueva foto si se envió
        if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../imagen/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
                $filename = 'candidato_' . $candidato['usuario_id'] . '_' . time() . '.' . $ext;
                $rutaDestino = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    $fotoUrl = 'imagen/' . $filename;
                    $db->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?")->execute([$fotoUrl, $candidato['usuario_id']]);
                }
            }
        }

        logAuditoria('CANDIDATO_EDITADO', "Candidato id=$id");
        echo json_encode(['success' => true, 'message' => 'Candidato actualizado exitosamente']);
        exit;
    }

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

    // Subir foto si se envió
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../imagen/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $filename = 'candidato_' . $usuario_id . '_' . time() . '.' . $ext;
            $rutaDestino = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                $fotoUrl = 'imagen/' . $filename;
                $db->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?")->execute([$fotoUrl, $usuario_id]);
            }
        }
    }

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
