<?php
// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS
// Compatible con: XAMPP, Railway, Heroku, InfinityFree
// ==========================================

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

// ==========================================
// DETECCIÓN AUTOMÁTICA DE ENTORNO
// Railway proporciona variables de entorno automáticamente
// ==========================================

if (getenv('MYSQLHOST') || getenv('MYSQL_URL')) {
    // === RAILWAY / CLOUD ===
    if (getenv('MYSQL_URL')) {
        $db_url = parse_url(getenv('MYSQL_URL'));
        define('DB_HOST', $db_url['host']);
        define('DB_USER', $db_url['user']);
        define('DB_PASS', $db_url['pass']);
        define('DB_NAME', ltrim($db_url['path'], '/'));
    } else {
        define('DB_HOST', getenv('MYSQLHOST'));
        define('DB_USER', getenv('MYSQLUSER'));
        define('DB_PASS', getenv('MYSQLPASSWORD'));
        define('DB_NAME', getenv('MYSQLDATABASE'));
    }
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

} else {
    // === LOCAL (XAMPP) ===
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'elecciones_estudiantiles');
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
        echo '<style>body{font-family:sans-serif;background:#0a192f;color:#e6f1ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}';
        echo '.box{background:#112240;padding:40px;border-radius:16px;text-align:center;max-width:500px}';
        echo '.icon{font-size:64px}h1{color:#ff6b6b}p{color:#8892b0;line-height:1.6}</style></head><body>';
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
