<?php
// ==========================================
// Diagnóstico y auto-instalación
// ==========================================
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico - Elecciones Estudiantiles</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #f1f5f9; padding: 40px; }
        h1 { color: #818cf8; }
        .step { padding: 16px 20px; margin: 12px 0; border-radius: 12px; background: #1e293b; border-left: 4px solid #6366f1; }
        .ok { border-left-color: #10b981; }
        .error { border-left-color: #ef4444; }
        .warn { border-left-color: #f59e0b; }
        .btn { display: inline-block; padding: 12px 28px; background: #6366f1; color: white; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; text-decoration: none; margin-top: 10px; }
        .btn:hover { background: #4f46e5; }
        code { background: #334155; padding: 2px 8px; border-radius: 4px; }
        pre { background: #1e293b; padding: 16px; border-radius: 8px; overflow-x: auto; }
    </style>
</head>
<body>

<h1>🗳️ Diagnóstico del Sistema</h1>

<?php

// PASO 1: Verificar extensión PDO
echo '<div class="step ' . (extension_loaded('pdo_mysql') ? 'ok' : 'error') . '">';
echo '<strong>1. Extensión PDO MySQL:</strong> ';
if (extension_loaded('pdo_mysql')) {
    echo '✅ Instalada';
} else {
    echo '❌ NO encontrada — Activa <code>extension=pdo_mysql</code> en php.ini';
}
echo '</div>';

// PASO 2: Verificar conexión a MySQL
$connected = false;
$db_error = '';
try {
    $conn = new PDO('mysql:host=localhost', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    $connected = true;
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}

echo '<div class="step ' . ($connected ? 'ok' : 'error') . '">';
echo '<strong>2. Conexión a MySQL (localhost):</strong> ';
if ($connected) {
    echo '✅ Conectado';
} else {
    echo '❌ Error: ' . htmlspecialchars($db_error);
    echo '<br><br>👉 Asegúrate de que <strong>Apache</strong> y <strong>MySQL</strong> estén corriendo en XAMPP.';
}
echo '</div>';

if (!$connected) {
    echo '<div class="step error"><strong>No se puede continuar.</strong> Inicia MySQL en el panel de XAMPP.</div>';
    echo '</body></html>';
    exit;
}

// PASO 3: Verificar si la base de datos existe
$db_exists = false;
try {
    $stmt = $conn->query("SHOW DATABASES LIKE 'elecciones_estudiantiles'");
    $db_exists = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    // ignore
}

echo '<div class="step ' . ($db_exists ? 'ok' : 'warn') . '">';
echo '<strong>3. Base de datos "elecciones_estudiantiles":</strong> ';
if ($db_exists) {
    echo '✅ Existe';
} else {
    echo '⚠️ No existe — Se creará automáticamente';
}
echo '</div>';

// PASO 4: Crear la base de datos si no existe
if (!$db_exists) {
    echo '<div class="step">';
    echo '<strong>4. Creando base de datos...</strong><br>';
    try {
        $sql = file_get_contents(__DIR__ . '/database.sql');
        $conn->exec($sql);
        echo '✅ Base de datos y tablas creadas exitosamente!';
        $db_exists = true;
    } catch (PDOException $e) {
        echo '❌ Error al crear: ' . htmlspecialchars($e->getMessage());
    }
    echo '</div>';
}

// PASO 5: Verificar tablas
if ($db_exists) {
    try {
        $conn->exec('USE elecciones_estudiantiles');
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo '<div class="step ' . (count($tables) >= 7 ? 'ok' : 'warn') . '">';
        echo '<strong>5. Tablas encontradas (' . count($tables) . '):</strong><br>';
        echo '<pre>' . implode("\n", $tables) . '</pre>';
        echo '</div>';

        // Verificar admin
        $stmt = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
        $admin_count = $stmt->fetchColumn();

        echo '<div class="step ' . ($admin_count > 0 ? 'ok' : 'warn') . '">';
        echo '<strong>6. Usuario administrador:</strong> ';
        if ($admin_count > 0) {
            echo '✅ Existe (admin@elecciones.edu / admin123)';
        } else {
            echo '⚠️ No encontrado';
        }
        echo '</div>';

        // Verificar cargos
        $stmt = $conn->query("SELECT COUNT(*) FROM cargos");
        $cargos_count = $stmt->fetchColumn();

        echo '<div class="step ' . ($cargos_count > 0 ? 'ok' : 'warn') . '">';
        echo '<strong>7. Cargos configurados:</strong> ';
        echo $cargos_count > 0 ? '✅ ' . $cargos_count . ' cargos' : '⚠️ Ninguno';
        echo '</div>';

    } catch (PDOException $e) {
        echo '<div class="step error"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Resultado final
echo '<br><div style="text-align:center;">';
if ($connected && $db_exists) {
    echo '<div class="step ok" style="text-align:center;font-size:18px;"><strong>✅ ¡Sistema listo!</strong></div><br>';
    echo '<a href="login.php" class="btn">Ir al Login →</a> ';
    echo '<a href="admin/dashboard.php" class="btn" style="background:#10b981;">Ir al Admin →</a>';
} else {
    echo '<div class="step error" style="text-align:center;font-size:18px;"><strong>❌ Hay problemas que resolver</strong></div>';
}
echo '</div>';

?>
</body>
</html>
