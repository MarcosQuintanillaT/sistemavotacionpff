<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("
        SELECT u.id, u.nombre, u.email, u.rol, u.grado, u.seccion, u.identidad, u.activo, u.foto_url,
               u.created_at,
               CASE WHEN v.votante_id IS NOT NULL THEN 1 ELSE 0 END AS ya_voto
        FROM usuarios u
        LEFT JOIN (SELECT DISTINCT votante_id FROM votos) v ON u.id = v.votante_id
        WHERE u.rol = 'votante'
        ORDER BY u.nombre
    ");
    echo json_encode(['success' => true, 'usuarios' => $stmt->fetchAll()]);

} elseif ($method === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '1612';
    $codigo = trim($_POST['identidad'] ?? '');
    $grado = trim($_POST['grado'] ?? '');
    $seccion = trim($_POST['seccion'] ?? '');
    $accion = $_POST['accion'] ?? 'crear';

    if ($accion === 'toggle' && !empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
        exit;
    }

    if ($accion === 'eliminar' && !empty($_POST['id'])) {
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
        exit;
    }

    if ($accion === 'editar' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $codigo = trim($_POST['identidad'] ?? '');
        $grado = trim($_POST['grado'] ?? '');
        $seccion = trim($_POST['seccion'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nombre) || empty($codigo)) {
            echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
            exit;
        }

        // Verificar que el email no esté duplicado en otro usuario (solo si se proporcionó)
        if (!empty($email)) {
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El email ya está en uso por otro usuario']);
                exit;
            }
        }

        // Verificar que el código no esté duplicado en otro usuario
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE identidad = ? AND id != ?");
        $stmt->execute([$codigo, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El código estudiantil ya está en uso por otro usuario']);
            exit;
        }

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, email = ?, identidad = ?, grado = ?, seccion = ?, password = ? WHERE id = ?");
            $stmt->execute([$nombre, $email ?: null, $codigo, $grado, $seccion, $hash, $id]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, email = ?, identidad = ?, grado = ?, seccion = ? WHERE id = ?");
            $stmt->execute([$nombre, $email ?: null, $codigo, $grado, $seccion, $id]);
        }

        // Subir nueva foto si se envió
        if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../imagen/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
                $filename = 'usuario_' . $id . '_' . time() . '.' . $ext;
                $rutaDestino = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    $fotoUrl = 'imagen/' . $filename;
                    $db->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?")->execute([$fotoUrl, $id]);
                }
            }
        }

        logAuditoria('USUARIO_EDITADO', "Votante editado: $email (ID: $id)");
        echo json_encode(['success' => true, 'message' => 'Votante actualizado exitosamente']);
        exit;
    }

    if (empty($nombre) || empty($codigo)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
        exit;
    }

    // Verificar que el email no esté duplicado (solo si se proporcionó)
    if (!empty($email)) {
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El email ya está en uso']);
            exit;
        }
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, identidad, grado, seccion) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email ?: null, $hash, $codigo, $grado, $seccion]);
    $newUserId = $db->lastInsertId();

    // Subir foto si se envió
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../imagen/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $filename = 'usuario_' . $newUserId . '_' . time() . '.' . $ext;
            $rutaDestino = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                $fotoUrl = 'imagen/' . $filename;
                $db->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?")->execute([$fotoUrl, $newUserId]);
            }
        }
    }

    logAuditoria('USUARIO_CREADO', "Votante: $email");
    echo json_encode(['success' => true, 'message' => 'Votante registrado']);

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
