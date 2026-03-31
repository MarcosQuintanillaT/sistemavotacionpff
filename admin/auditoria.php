<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();
$user = currentUser();

$db = getDB();

// Configuración de paginación
$porPagina = 50;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $porPagina;

// Filtros
$filtroAccion = $_GET['accion'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';

// Construir consulta
$where = "1=1";
$params = [];

if ($filtroAccion) {
    $where .= " AND a.accion = ?";
    $params[] = $filtroAccion;
}

if ($filtroFecha) {
    $where .= " AND DATE(a.fecha) = ?";
    $params[] = $filtroFecha;
}

// Total de registros
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM auditoria a WHERE $where");
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $porPagina);

// Obtener registros con información del usuario
$query = "SELECT a.*, u.nombre AS usuario_nombre, u.email AS usuario_email 
          FROM auditoria a 
          LEFT JOIN usuarios u ON a.usuario_id = u.id 
          WHERE $where 
          ORDER BY a.fecha DESC 
          LIMIT $porPagina OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$registros = $stmt->fetchAll();

// Obtener tipos de acciones únicas
$acciones = $db->query("SELECT DISTINCT accion FROM auditoria ORDER BY accion")->fetchAll(PDO::FETCH_COLUMN);

// Estadísticas rápidas
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM auditoria")->fetchColumn(),
    'hoy' => $db->query("SELECT COUNT(*) FROM auditoria WHERE DATE(fecha) = CURDATE()")->fetchColumn(),
    'logins' => $db->query("SELECT COUNT(*) FROM auditoria WHERE accion IN ('LOGIN', 'LOGOUT')")->fetchColumn(),
    'votos' => $db->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'VOTO_EMITIDO'")->fetchColumn(),
];

function getAccionColor($accion) {
    $colores = [
        'LOGIN' => '#3b82f6',
        'LOGOUT' => '#6b7280',
        'REGISTRO' => '#10b981',
        'VOTO_EMITIDO' => '#f59e0b',
        'USUARIO_CREADO' => '#22c55e',
        'USUARIO_EDITADO' => '#06b6d4',
        'CANDIDATO_CREADO' => '#8b5cf6',
        'CANDIDATO_ELIMINADO' => '#ef4444',
        'PARTIDO_CREADO' => '#ec4899',
        'IMPORTAR_VOTANTES' => '#14b8a6',
    ];
    return $colores[$accion] ?? '#64748b';
}

function getAccionIcono($accion) {
    $iconos = [
        'LOGIN' => '🔓',
        'LOGOUT' => '🔒',
        'REGISTRO' => '📝',
        'VOTO_EMITIDO' => '🗳️',
        'USUARIO_CREADO' => '👤',
        'USUARIO_EDITADO' => '✏️',
        'CANDIDATO_CREADO' => '👥',
        'CANDIDATO_ELIMINADO' => '🗑️',
        'PARTIDO_CREADO' => '🏛️',
        'IMPORTAR_VOTANTES' => '📥',
    ];
    return $iconos[$accion] ?? '📋';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=13">
</head>
<body>

<div class="bg-particles"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- Navbar -->
<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <div class="logo">⚙️</div>
        <span>Admin Panel</span>
    </a>
    <div class="navbar-nav">
        <a href="dashboard.php" class="nav-link">📊 <span>Dashboard</span></a>
        <a href="candidatos.php" class="nav-link">👥 <span>Candidatos</span></a>
        <a href="votantes.php" class="nav-link">🎓 <span>Votantes</span></a>
        <a href="integracion.php" class="nav-link">🔗 <span>Integración</span></a>
        <a href="../resultados.php" class="nav-link">📈 <span>Resultados</span></a>
        <a href="auditoria.php" class="nav-link active">📋 <span>Auditoría</span></a>
    </div>
    <div class="navbar-user">
        <div class="user-avatar"><?= substr($user['nombre'], 0, 1) ?></div>
        <a href="../api/logout.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Salir</a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1>📋 Registro de Auditoría</h1>
        <p>Historial completo de todas las acciones en el sistema</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom:30px;">
        <div class="stat-card glass">
            <div class="stat-icon primary">📊</div>
            <div class="stat-value"><?= number_format($stats['total']) ?></div>
            <div class="stat-label">Total Acciones</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon secondary">📅</div>
            <div class="stat-value"><?= number_format($stats['hoy']) ?></div>
            <div class="stat-label">Hoy</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon primary">🔓</div>
            <div class="stat-value"><?= number_format($stats['logins']) ?></div>
            <div class="stat-label">Sesiones</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon success">🗳️</div>
            <div class="stat-value"><?= number_format($stats['votos']) ?></div>
            <div class="stat-label">Votos Emitidos</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass" style="padding:20px;margin-bottom:24px;">
        <form method="GET" style="display:flex;gap:16px;align-items:end;flex-wrap:wrap;">
            <div class="input-group" style="margin-bottom:0;min-width:200px;">
                <label>Tipo de Acción</label>
                <select name="accion" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($acciones as $acc): ?>
                    <option value="<?= htmlspecialchars($acc) ?>" <?= $filtroAccion === $acc ? 'selected' : '' ?>><?= htmlspecialchars($acc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group" style="margin-bottom:0;min-width:200px;">
                <label>Fecha</label>
                <input type="date" name="fecha" value="<?= htmlspecialchars($filtroFecha) ?>" onchange="this.form.submit()">
            </div>
            <?php if ($filtroAccion || $filtroFecha): ?>
            <a href="auditoria.php" class="btn btn-outline" style="padding:12px 20px;">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="glass" style="padding:24px;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Acción</th>
                        <th>Usuario</th>
                        <th>Detalles</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">
                            No hay registros de auditoría
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($registros as $r): ?>
                    <tr>
                        <td>
                            <span style="font-size:12px;color:var(--text-muted);">
                                <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                            </span>
                            <br>
                            <span style="font-weight:600;">
                                <?= date('H:i:s', strtotime($r['fecha'])) ?>
                            </span>
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;background:<?= getAccionColor($r['accion']) ?>22;color:<?= getAccionColor($r['accion']) ?>;">
                                <?= getAccionIcono($r['accion']) ?> <?= htmlspecialchars($r['accion']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($r['usuario_nombre']): ?>
                            <strong style="color:var(--text);"><?= htmlspecialchars($r['usuario_nombre']) ?></strong>
                            <br>
                            <span style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($r['usuario_email'] ?? '') ?></span>
                            <?php else: ?>
                            <span style="color:var(--text-muted);">Sistema</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($r['detalles']) ?>">
                            <?= htmlspecialchars($r['detalles']) ?>
                        </td>
                        <td>
                            <code style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($r['ip_address']) ?></code>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:24px;align-items:center;">
            <?php if ($pagina > 1): ?>
            <a href="?pagina=<?= $pagina - 1 ?>&accion=<?= urlencode($filtroAccion) ?>&fecha=<?= urlencode($filtroFecha) ?>" class="btn btn-outline" style="padding:8px 16px;">← Anterior</a>
            <?php endif; ?>
            
            <span style="color:var(--text-muted);padding:8px 16px;">
                Página <?= $pagina ?> de <?= $totalPaginas ?>
                <span style="color:var(--text-dim);">(<?= number_format($totalRegistros) ?> registros)</span>
            </span>
            
            <?php if ($pagina < $totalPaginas): ?>
            <a href="?pagina=<?= $pagina + 1 ?>&accion=<?= urlencode($filtroAccion) ?>&fecha=<?= urlencode($filtroFecha) ?>" class="btn btn-outline" style="padding:8px 16px;">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
</body>
</html>
