<?php
// ==========================================
// Configuración de conexión a Base de Datos
// ==========================================

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', '0');

define('DB_HOST', 'localhost');
define('DB_NAME', 'elecciones_estudiantiles');
define('DB_USER', 'root');
define('DB_PASS', '');

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
        // Detectar si es una llamada API o una página HTML
        $is_api = strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false;

        if ($is_api) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
        }

        // Mostrar error amigable en páginas HTML
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error de Conexión</title>';
        echo '<style>body{font-family:sans-serif;background:#0f172a;color:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}';
        echo '.box{background:#1e293b;padding:40px;border-radius:16px;text-align:center;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,0.4)}';
        echo '.icon{font-size:64px;margin-bottom:16px}h1{color:#ef4444;margin-bottom:8px}p{color:#94a3b8;line-height:1.6}';
        echo '.btn{display:inline-block;padding:12px 24px;background:#6366f1;color:white;border-radius:8px;text-decoration:none;margin-top:20px;font-weight:bold}';
        echo '.btn:hover{background:#4f46e5}.steps{text-align:left;background:#0f172a;padding:16px;border-radius:8px;margin-top:16px;font-size:13px}';
        echo '.steps li{margin:6px 0;color:#94a3b8}</style></head><body>';
        echo '<div class="box">';
        echo '<div class="icon">🔌</div>';
        echo '<h1>Error de Conexión</h1>';
        echo '<p>No se pudo conectar a la base de datos MySQL.</p>';
        echo '<div class="steps"><strong>Pasos para solucionarlo:</strong><ol>';
        echo '<li>Abre el <strong>Panel de Control de XAMPP</strong></li>';
        echo '<li>Inicia <strong>Apache</strong> (botón Start)</li>';
        echo '<li>Inicia <strong>MySQL</strong> (botón Start)</li>';
        echo '<li>Asegúrate que ambos muestren <span style="color:#10b981">verde</span></li>';
        echo '<li>Luego haz clic en el botón de abajo</li>';
        echo '</ol></div>';
        echo '<a href="/elecciones_estudiantiles/diagnostico.php" class="btn">🔧 Ejecutar Diagnóstico</a>';
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
        // Silenciar errores de auditoría
    }
}
