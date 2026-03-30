<?php
// ==========================================
// DIAGNÓSTICO PARA HOSTING
// Sube este archivo a la raíz y accede:
// https://tudominio.com/test_hosting.php
// ==========================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico Hosting</title>
    <style>
        body { font-family: monospace; background: #0a192f; color: #e6f1ff; padding: 30px; }
        h1 { color: #d4a520; }
        .step { padding: 14px 18px; margin: 10px 0; border-radius: 8px; background: #112240; border-left: 4px solid #d4a520; }
        .ok { border-left-color: #64ffda; }
        .err { border-left-color: #ff6b6b; }
        code { background: #1d3461; padding: 2px 8px; border-radius: 4px; }
        a { color: #d4a520; }
    </style>
</head>
<body>
<h1>🔍 Diagnóstico del Hosting</h1>

<?php

// 1. PHP Version
echo '<div class="step ' . (version_compare(PHP_VERSION, '8.0') >= 0 ? 'ok' : 'err') . '">';
echo '<strong>1. PHP Version:</strong> ' . PHP_VERSION;
if (version_compare(PHP_VERSION, '8.0') < 0) echo ' ⚠️ Se recomienda PHP 8+';
echo '</div>';

// 2. Extensiones
$exts = ['pdo', 'pdo_mysql', 'json', 'session'];
foreach ($exts as $ext) {
    $loaded = extension_loaded($ext);
    echo '<div class="step ' . ($loaded ? 'ok' : 'err') . '">';
    echo '<strong>Extensión ' . $ext . ':</strong> ' . ($loaded ? '✅ Cargada' : '❌ NO encontrada');
    echo '</div>';
}

// 3. Directorio actual
echo '<div class="step"><strong>Directorio:</strong> ' . __DIR__ . '</div>';
echo '<div class="step"><strong>Archivos encontrados:</strong><br><pre>';
$files = scandir(__DIR__);
foreach ($files as $f) {
    if ($f !== '.' && $f !== '..') echo "  $f\n";
}
echo '</pre></div>';

// 4. Verificar config/db.php existe
$db_file = __DIR__ . '/config/db.php';
echo '<div class="step ' . (file_exists($db_file) ? 'ok' : 'err') . '">';
echo '<strong>4. config/db.php:</strong> ' . (file_exists($db_file) ? '✅ Existe' : '❌ NO encontrado');
echo '</div>';

// 5. Verificar database.sql existe
$sql_file = __DIR__ . '/database.sql';
echo '<div class="step ' . (file_exists($sql_file) ? 'ok' : 'err') . '">';
echo '<strong>5. database.sql:</strong> ' . (file_exists($sql_file) ? '✅ Existe' : '❌ NO encontrado');
echo '</div>';

// 6. Intentar conexión
echo '<div class="step">';
echo '<strong>6. Probando conexión a BD...</strong><br><br>';

// Leer valores de config
if (file_exists($db_file)) {
    $content = file_get_contents($db_file);
    preg_match("/define\('DB_HOST',\s*'([^']+)'\)/", $content, $m_host);
    preg_match("/define\('DB_NAME',\s*'([^']+)'\)/", $content, $m_name);
    preg_match("/define\('DB_USER',\s*'([^']+)'\)/", $content, $m_user);
    preg_match("/define\('DB_PASS',\s*'([^']+)'\)/", $content, $m_pass);

    $db_host = $m_host[1] ?? 'localhost';
    $db_name = $m_name[1] ?? '';
    $db_user = $m_user[1] ?? '';
    $db_pass = $m_pass[1] ?? '';

    echo "Host: <code>$db_host</code><br>";
    echo "DB: <code>$db_name</code><br>";
    echo "User: <code>$db_user</code><br>";
    echo "Pass: <code>" . (empty($db_pass) ? '(vacía)' : '***') . "</code><br><br>";

    try {
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<span style="color:#64ffda">✅ Conexión exitosa!</span><br>';

        // Verificar tablas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo '<br>Tablas (' . count($tables) . '): <code>' . implode(', ', $tables) . '</code>';

        if (count($tables) === 0) {
            echo '<br><br><span style="color:#ff6b6b">⚠️ No hay tablas. Debes importar database.sql desde phpMyAdmin.</span>';
        }

    } catch (PDOException $e) {
        echo '<span style="color:#ff6b6b">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
        echo '<br><br>👉 Verifica que:';
        echo '<br>- El nombre de la BD sea correcto';
        echo '<br>- El usuario y contraseña sean correctos';
        echo '<br>- La BD exista en el panel de hosting';
    }
} else {
    echo '<span style="color:#ff6b6b">❌ No se puede probar: config/db.php no existe</span>';
}
echo '</div>';

// 7. Probar login.php
$login_file = __DIR__ . '/login.php';
echo '<div class="step ' . (file_exists($login_file) ? 'ok' : 'err') . '">';
echo '<strong>7. login.php:</strong> ' . (file_exists($login_file) ? '✅ Existe' : '❌ NO encontrado');
if (file_exists($login_file)) {
    echo '<br><a href="login.php">👉 Ir a Login</a>';
}
echo '</div>';

// 8. Probar diagnostico.php
$diag_file = __DIR__ . '/diagnostico.php';
if (file_exists($diag_file)) {
    echo '<div class="step ok"><strong>8. diagnostico.php:</strong> ✅ Existe';
    echo '<br><a href="diagnostico.php">👉 Ir a Diagnóstico</a></div>';
}

?>

<br>
<div class="step" style="border-left-color: #d4a520;">
    <strong>📋 Resumen:</strong> Si la conexión falla, edita <code>config/db.php</code> con los datos
    correctos de tu hosting (los encuentras en el panel de InfinityFree en la sección "MySQL Databases").
</div>

</body>
</html>
