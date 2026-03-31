<?php
// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS
// ==========================================

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

// ==========================================
// INFINITYFREE - Configuración
// ==========================================
$inf_host = 'sql306.infinityfree.com';
$inf_user = 'if0_41510758';
$inf_pass = '4WVvpqTNjCj';
$inf_name = 'if0_41510758_eleccion_estudiantil';

if ($inf_user !== 'tu_usuario_mysql') {
    define('DB_HOST', $inf_host);
    define('DB_USER', $inf_user);
    define('DB_PASS', $inf_pass);
    define('DB_NAME', $inf_name);
    define('DB_PORT', '3306');
} else {
    // Local (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'eleccion_estudiantil');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', '3306');
}

function getConnection() {
    try {
        $port = defined('DB_PORT') ? DB_PORT : '3306';
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";port=$port;dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        $is_api = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false;

        if ($is_api) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
        }

        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title>';
        echo '<style>body{font-family:sans-serif;background:#040d08;color:#e8fce8;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}';
        echo '.box{background:#0a1f12;padding:40px;border-radius:16px;text-align:center;max-width:500px}';
        echo '.icon{font-size:64px}h1{color:#ef4444}p{color:#7a9a7a;line-height:1.6}</style></head><body>';
        echo '<div class="box"><div class="icon">🔌</div><h1>Error de Conexión</h1>';
        echo '<p>No se pudo conectar a la base de datos.</p>';
        echo '</div></body></html>';
        exit;
    }
}

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = getConnection();
    }
    return $conn;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'nombre' => $_SESSION['nombre'],
        'email' => $_SESSION['email'],
        'rol' => $_SESSION['rol']
    ];
}

function logAuditoria($accion, $detalles = '') {
    try {
        $db = getDB();
        if (!$db) return;
        $stmt = $db->prepare("INSERT INTO auditoria (usuario_id, accion, detalles, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $accion,
            $detalles,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    } catch (Exception $e) {
        // Silenciar
    }
}
