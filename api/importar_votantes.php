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

$input = json_decode(file_get_contents('php://input'), true);
$votantes = $input['votantes'] ?? [];

if (empty($votantes)) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
    exit;
}

$db = getDB();
$defaultPassword = password_hash('1612', PASSWORD_DEFAULT);

$insertados = 0;
$omitidos = 0;
$errores = [];

foreach ($votantes as $i => $v) {
    $fila = $i + 2;
    $nombre = trim($v['nombre'] ?? '');
    $email = trim($v['email'] ?? '');
    $codigo = trim($v['identidad'] ?? '');
    $grado = trim($v['grado'] ?? '');
    $seccion = trim($v['seccion'] ?? '');

    if (empty($nombre) && empty($email) && empty($codigo)) {
        continue;
    }

    if (empty($nombre) || empty($codigo)) {
        $errores[] = "Fila $fila: faltan campos obligatorios (nombre o identidad)";
        $omitidos++;
        continue;
    }

    try {
        // Verificar email duplicado solo si se proporcionó
        if (!empty($email)) {
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errores[] = "Fila $fila: email '$email' ya existe";
                $omitidos++;
                continue;
            }
        }

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE identidad = ?");
        $stmt->execute([$codigo]);
        if ($stmt->fetch()) {
            $errores[] = "Fila $fila: identidad '$codigo' ya existe";
            $omitidos++;
            continue;
        }

        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, identidad, grado, seccion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email ?: null, $defaultPassword, $codigo, $grado, $seccion]);
        $insertados++;
    } catch (PDOException $e) {
        $errores[] = "Fila $fila: " . $e->getMessage();
        $omitidos++;
    }
}

logAuditoria('IMPORTAR_VOTANTES', "Insertados: $insertados, Omitidos: $omitidos");

echo json_encode([
    'success' => true,
    'message' => "Importación completada: $insertados votantes registrados, $omitidos omitidos",
    'insertados' => $insertados,
    'omitidos' => $omitidos,
    'errores' => array_slice($errores, 0, 20)
]);
