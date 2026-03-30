<?php
// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS
// ==========================================
// COPIA este archivo como config/db.php y pon tus datos reales.

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

// ==========================================
// Cambia IS_HOSTING según tu entorno:
//   true  = Hosting (InfinityFree, etc)
//   false = Local (XAMPP)
// ==========================================

$IS_HOSTING = false;

if ($IS_HOSTING) {
    // === HOSTING (InfinityFree) ===
    // PON TUS DATOS REALES AQUÍ
    define('DB_HOST', 'sqlXXX.infinityfree.com');  // Lo encuentras en phpMyAdmin URL
    define('DB_NAME', 'if0_XXXXXXXXXX_nombrebd');  // Tu nombre de BD
    define('DB_USER', 'if0_XXXXXXXXXX');           // Tu usuario
    define('DB_PASS', 'tu_contraseña_real');       // Tu contraseña
} else {
    // === LOCAL (XAMPP) ===
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'elecciones_estudiantiles');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
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
        $is_api = strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false;

        if ($is_api) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
        }

        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error de Conexión</title>';
        echo '<style>body{font-family:sans-serif;background:#0a192f;color:#e6f1ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}';
        echo '.box{background:#112240;padding:40px;border-radius:16px;text-align:center;max-width:500px}';
        echo '.icon{font-size:64px}h1{color:#ff6b6b}p{color:#8892b0;line-height:1.6}';
        echo '.btn{display:inline-block;padding:12px 24px;background:#d4a520;color:#0a192f;border-radius:8px;text-decoration:none;margin-top:20px;font-weight:bold}';
        echo '.steps{text-align:left;background:#0a192f;padding:16px;border-radius:8px;margin-top:16px;font-size:13px}';
        echo '.steps li{margin:6px 0;color:#8892b0}</style></head><body>';
        echo '<div class="box"><div class="icon">🔌</div><h1>Error de Conexión</h1>';
        echo '<p>No se pudo conectar a la base de datos MySQL.</p>';
        echo '<div class="steps"><strong>Solución:</strong><ol>';
        echo '<li>Ve al <strong>File Manager</strong> de tu hosting</li>';
        echo '<li>Abre <code>config/db.php</code></li>';
        echo '<li>Verifica que DB_NAME, DB_USER y DB_PASS sean correctos</li>';
        echo '<li>Asegúrate de haber importado <strong>eleccion_estudiantil.sql</strong> en phpMyAdmin</li>';
        echo '</ol></div></div></body></html>';
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
