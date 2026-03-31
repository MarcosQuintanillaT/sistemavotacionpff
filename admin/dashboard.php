<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();
$user = currentUser();

$db = getDB();

// Obtener configuración actual
$stmt = $db->prepare("SELECT clave, valor FROM config WHERE clave IN ('votacion_abierta', 'nombre_eleccion', 'fecha_inicio', 'fecha_fin', 'sistema_bloqueado')");
$stmt->execute();
$configs = [];
while ($row = $stmt->fetch()) {
    $configs[$row['clave']] = $row['valor'];
}

$votacion_abierta = $configs['votacion_abierta'] ?? '0';
$nombre_eleccion = $configs['nombre_eleccion'] ?? 'Elecciones Estudiantiles 2026';
$sistema_bloqueado = $configs['sistema_bloqueado'] ?? '1';

// Estadísticas rápidas
$stats = [
    'votantes' => $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'votante' AND activo = 1")->fetchColumn(),
    'votaron' => $db->query("SELECT COUNT(DISTINCT votante_id) FROM votos")->fetchColumn(),
    'candidatos' => $db->query("SELECT COUNT(*) FROM candidatos WHERE activo = 1")->fetchColumn(),
    'partidos' => $db->query("SELECT COUNT(*) FROM partidos WHERE activo = 1")->fetchColumn(),
];

// Votantes por grado
$porGrado = $db->query("
    SELECT grado, COUNT(*) as total,
           (SELECT COUNT(DISTINCT v.votante_id) FROM votos v JOIN usuarios u ON v.votante_id = u.id WHERE u.grado = usuarios.grado) as voted
    FROM usuarios WHERE rol = 'votante' AND activo = 1 AND grado != ''
    GROUP BY grado
")->fetchAll();

// Actividad reciente
$actividadReciente = $db->query("
    SELECT a.*, u.nombre AS usuario_nombre 
    FROM auditoria a 
    LEFT JOIN usuarios u ON a.usuario_id = u.id 
    ORDER BY a.fecha DESC LIMIT 10
")->fetchAll();

// Datos para modales
$usuarios = $db->query("SELECT id, nombre FROM usuarios WHERE rol = 'votante' AND activo = 1 ORDER BY nombre")->fetchAll();
$partidos = $db->query("SELECT id, nombre, color FROM partidos WHERE activo = 1 ORDER BY nombre")->fetchAll();
$cargos = $db->query("SELECT id, nombre FROM cargos ORDER BY orden")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=13">
</head>
<body>

<style>
.nav-link.disabled {
    opacity: 0.4;
    pointer-events: none;
    cursor: not-allowed;
}
</style>

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
        <a href="dashboard.php" class="nav-link active">📊 <span>Dashboard</span></a>
        <a href="candidatos.php" class="nav-link<?= $sistema_bloqueado === '1' ? ' disabled' : '' ?>">👥 <span>Candidatos</span></a>
        <a href="votantes.php" class="nav-link<?= $sistema_bloqueado === '1' ? ' disabled' : '' ?>">🎓 <span>Votantes</span></a>
        <a href="integracion.php" class="nav-link<?= $sistema_bloqueado === '1' ? ' disabled' : '' ?>">🔗 <span>Integración</span></a>
        <a href="../resultados.php" class="nav-link<?= $sistema_bloqueado === '1' ? ' disabled' : '' ?>">📈 <span>Resultados</span></a>
        <a href="auditoria.php" class="nav-link<?= $sistema_bloqueado === '1' ? ' disabled' : '' ?>">📋 <span>Auditoría</span></a>
    </div>
    <div class="navbar-user">
        <div class="user-avatar"><?= substr($user['nombre'], 0, 1) ?></div>
        <a href="../api/logout.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Salir</a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <?php if ($sistema_bloqueado === '1'): ?>
    <!-- Pantalla de Bloqueo -->
    <div class="glass" style="padding:40px;text-align:center;max-width:600px;margin:40px auto;">
        <div style="font-size:60px;margin-bottom:20px;">🔒</div>
        <h2 style="margin-bottom:16px;color:var(--danger);">Sistema Bloqueado</h2>
        <p style="color:var(--text-muted);margin-bottom:24px;">
            El sistema está bloqueado. Configure los parámetros de la elección para desbloquearlo.
        </p>
        <button class="btn btn-primary btn-lg" onclick="openModal('modal-config')">
            ⚙️ Configurar y Desbloquear
        </button>
    </div>
    <?php else: ?>
    <!-- Header con estado de votación -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
        <div>
            <h1 style="margin-bottom:4px;">📊 Panel de Administración</h1>
            <p style="margin:0;"><?= htmlspecialchars($nombre_eleccion) ?></p>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <span id="votacion-status" style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:20px;font-weight:600;font-size:13px;background:<?= $votacion_abierta === '1' ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)' ?>;color:<?= $votacion_abierta === '1' ? '#22c55e' : '#ef4444' ?>;">
                <?= $votacion_abierta === '1' ? '🟢 VOTACIÓN ABIERTA' : '🔴 VOTACIÓN CERRADA' ?>
            </span>
            <button class="btn <?= $votacion_abierta === '1' ? 'btn-outline' : 'btn-primary' ?>" onclick="openModal('modal-config')">
                ⚙️ Configuración
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid mb-30">
        <div class="stat-card glass">
            <div class="stat-icon primary">👥</div>
            <div class="stat-value"><?= number_format($stats['votantes']) ?></div>
            <div class="stat-label">Votantes Registrados</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon secondary">✅</div>
            <div class="stat-value"><?= number_format($stats['votaron']) ?></div>
            <div class="stat-label">Ya Votaron</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon success">🏃</div>
            <div class="stat-value"><?= $stats['votantes'] > 0 ? round(($stats['votaron'] / $stats['votantes']) * 100, 1) : 0 ?>%</div>
            <div class="stat-label">Participación</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon accent">🏛️</div>
            <div class="stat-value"><?= number_format($stats['candidatos']) ?></div>
            <div class="stat-label">Candidatos</div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="glass mb-30" style="padding:20px;">
        <h3 style="margin-bottom:16px;">⚡ Acciones Rápidas</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button class="btn btn-primary" onclick="openModal('modal-add-votante')">➕ Agregar Votante</button>
            <button class="btn btn-secondary" onclick="openModal('modal-add-candidato')">👥 Agregar Candidato</button>
            <button class="btn btn-outline" onclick="openModal('modal-import-votantes')">📥 Importar Votantes</button>
            <button class="btn btn-outline" onclick="exportarVotantes()">📤 Exportar Votantes CSV</button>
            <button class="btn btn-outline" onclick="window.open('../resultados.php','_blank')">📈 Ver Resultados</button>
            <button class="btn btn-outline" onclick="toggleVotacion()" id="btn-toggle">
                <?= $votacion_abierta === '1' ? '🔒 Cerrar Votación' : '🔓 Abrir Votación' ?>
            </button>
        </div>
    </div>

    <div class="grid-2 mb-30">
        <!-- Participación por Grado -->
        <div class="glass" style="padding:24px;">
            <h3 style="margin-bottom:20px;">📊 Participación por Grado</h3>
            <?php if (empty($porGrado)): ?>
                <p class="text-muted">No hay datos de grados</p>
            <?php else: ?>
                <?php foreach ($porGrado as $g): ?>
                    <?php $pct = $g['total'] > 0 ? round(($g['voted'] / $g['total']) * 100, 1) : 0; ?>
                    <div style="margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <strong><?= htmlspecialchars($g['grado']) ?></strong>
                            <span style="color:var(--text-muted);"><?= $g['voted'] ?>/<?= $g['total'] ?> (<?= $pct ?>%)</span>
                        </div>
                        <div style="height:8px;background:rgba(59,130,246,0.1);border-radius:4px;overflow:hidden;">
                            <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:4px;transition:width 1s;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Actividad Reciente -->
        <div class="glass" style="padding:24px;">
            <h3 style="margin-bottom:20px;">🕐 Actividad Reciente</h3>
            <?php if (empty($actividadReciente)): ?>
                <p class="text-muted">Sin actividad reciente</p>
            <?php else: ?>
                <?php foreach ($actividadReciente as $a): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--glass-border);">
                        <div style="width:8px;height:8px;border-radius:50%;background:<?= $a['accion'] === 'LOGIN' ? 'var(--primary)' : ($a['accion'] === 'VOTO_EMITIDO' ? 'var(--accent)' : 'var(--text-muted)') ?>;"></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <strong><?= htmlspecialchars($a['usuario_nombre'] ?? 'Sistema') ?></strong>
                                <span class="text-muted"> - <?= htmlspecialchars($a['accion']) ?></span>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                <?= date('d/m H:i', strtotime($a['fecha'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla de Votantes Recientes -->
    <div class="glass" style="padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3>👥 Últimos Votantes</h3>
            <a href="votantes.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Ver Todos →</a>
        </div>
        <div id="recent-votantes">
            <div class="spinner"></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Configuración -->
<div id="modal-config" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-config')">
    <div class="modal glass glass-strong">
        <div class="modal-header">
            <h2>⚙️ Configuración de Elecciones</h2>
            <button class="modal-close" onclick="closeModal('modal-config')">✕</button>
        </div>
        <form onsubmit="guardarConfig(event)">
            <div class="input-group">
                <label>Nombre de la Elección</label>
                <input type="text" name="nombre_eleccion" value="<?= htmlspecialchars($nombre_eleccion) ?>" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="input-group">
                    <label>Fecha de Inicio</label>
                    <input type="datetime-local" name="fecha_inicio" value="<?= $configs['fecha_inicio'] ?? '' ?>">
                </div>
                <div class="input-group">
                    <label>Fecha de Fin</label>
                    <input type="datetime-local" name="fecha_fin" value="<?= $configs['fecha_fin'] ?? '' ?>">
                </div>
            </div>
            <div class="input-group">
                <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
                    <input type="checkbox" name="votacion_abierta" value="1" <?= $votacion_abierta === '1' ? 'checked' : '' ?> style="width:20px;height:20px;">
                    <span>Votación Abierta (permitir emitir votos)</span>
                </label>
            </div>
            <div class="input-group">
                <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
                    <input type="checkbox" name="sistema_bloqueado" value="0" <?= $sistema_bloqueado === '0' ? 'checked' : '' ?> style="width:20px;height:20px;" onchange="this.value = this.checked ? '0' : '1'">
                    <span style="color:var(--success);">Sistema Desbloqueado (permitir acceso)</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Guardar Configuración</button>
        </form>
        
        <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--glass-border);">
            <h4 style="margin-bottom:12px;color:var(--danger);">⚠️ Zona de Peligro</h4>
            <button type="button" class="btn btn-outline" style="border-color:var(--danger);color:var(--danger);width:100%;margin-bottom:8px;" onclick="resetEleccion()">
                🗑️ Reiniciar Elección (Borrar todos los votos)
            </button>
        </div>
    </div>
</div>

<!-- Modal: Agregar Votante -->
<div id="modal-add-votante" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-add-votante')">
    <div class="modal glass glass-strong">
        <div class="modal-header">
            <h2>➕ Nuevo Votante</h2>
            <button class="modal-close" onclick="closeModal('modal-add-votante')">✕</button>
        </div>
        <form onsubmit="addUserFromDashboard(event)">
            <div class="input-group">
                <label>Nombre Completo *</label>
                <input type="text" name="nombre" required placeholder="Nombre del estudiante">
            </div>
            <div class="input-group">
                <label>Identidad *</label>
                <input type="text" name="identidad" required placeholder="Ej: 1612200800130">
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="estudiante@email.edu">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="input-group">
                    <label>Grado</label>
                    <select name="grado">
                        <option value="">Seleccionar</option>
                        <option>7mo</option>
                        <option>8vo</option>
                        <option>9no</option>
                        <option>10mo</option>
                        <option>11vo</option>
                        <option>12vo</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Sección</label>
                    <select name="seccion">
                        <option value="">Seleccionar</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Registrar Votante</button>
        </form>
    </div>
</div>

<!-- Modal: Agregar Candidato -->
<div id="modal-add-candidato" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-add-candidato')">
    <div class="modal glass glass-strong">
        <div class="modal-header">
            <h2>👥 Nuevo Candidato</h2>
            <button class="modal-close" onclick="closeModal('modal-add-candidato')">✕</button>
        </div>
        <form onsubmit="addCandidatoFromDashboard(event)" enctype="multipart/form-data">
            <div class="input-group">
                <label>Estudiante *</label>
                <select name="usuario_id" required>
                    <option value="">Seleccionar estudiante</option>
                    <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Cargo *</label>
                <select name="cargo_id" required>
                    <option value="">Seleccionar cargo</option>
                    <?php foreach ($cargos as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Partido</label>
                <select name="partido_id">
                    <option value="">Sin partido</option>
                    <?php foreach ($partidos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Número de Candidato</label>
                <input type="number" name="numero_candidato" placeholder="Ej: 1, 2, 3...">
            </div>
            <div class="input-group">
                <label>Propuesta</label>
                <textarea name="propuesta" rows="3" placeholder="Describe las propuestas del candidato..."></textarea>
            </div>
            <div class="input-group">
                <label>Foto del Candidato</label>
                <input type="file" name="foto" accept="image/*" style="padding:10px;background:rgba(15,23,42,0.8);border:1px solid var(--glass-border);border-radius:10px;color:var(--text);">
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">💾 Registrar Candidato</button>
        </form>
    </div>
</div>

<!-- Modal: Importar Votantes -->
<div id="modal-import-votantes" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-import-votantes')">
    <div class="modal glass glass-strong" style="max-width:700px;">
        <div class="modal-header">
            <h2>📥 Importar Votantes desde Excel</h2>
            <button class="modal-close" onclick="closeModal('modal-import-votantes')">✕</button>
        </div>
        <div style="padding:20px;">
            <div class="input-group">
                <label>Seleccionar archivo Excel</label>
                <input type="file" id="excel-file-dashboard" accept=".xlsx,.xls,.csv" onchange="previewExcelDashboard()" style="padding:12px;background:rgba(15,23,42,0.8);border:1px solid var(--glass-border);border-radius:10px;color:var(--text);width:100%;">
                <small class="text-muted" style="font-size:11px;">Formatos: .xlsx, .xls, .csv</small>
            </div>
            
            <div style="background:rgba(59,130,246,0.1);padding:12px;border-radius:8px;margin:16px 0;font-size:13px;">
                <strong>Formato de columnas:</strong>
                <div style="margin-top:8px;color:var(--text-muted);">
                    Columna A: Nombre | Columna B: Identidad | Columna C: Grado (7mo-12vo) | Columna D: Sección (1-2)
                </div>
            </div>

            <div id="excel-preview-dashboard" style="display:none;margin-top:16px;">
                <div id="excel-count-dashboard" style="font-weight:600;margin-bottom:8px;"></div>
                <div class="table-container" style="max-height:300px;overflow:auto;">
                    <table class="data-table" id="excel-preview-table-dashboard">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Identidad</th>
                                <th>Grado</th>
                                <th>Sección</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div id="excel-result-dashboard" style="display:none;margin-top:16px;"></div>

            <button type="button" id="btn-import-excel-dashboard" class="btn btn-primary btn-lg" style="width:100%;margin-top:16px;" onclick="importExcelDashboard()" disabled>
                📥 Importar Votantes
            </button>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>window.API_BASE_URL = '../api';</script>
<script src="../assets/js/app.js"></script>
<script>
async function guardarConfig(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const response = await fetch('../api/config.php', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        setTimeout(() => location.reload(), 1000);
    }
}

async function toggleVotacion() {
    const status = document.getElementById('votacion-status');
    const btn = document.getElementById('btn-toggle');
    const isOpen = status.textContent.includes('ABIERTA');
    
    const formData = new FormData();
    formData.append('toggle_votacion', '1');
    formData.append('votacion_abierta', isOpen ? '0' : '1');
    formData.append('nombre_eleccion', document.querySelector('[name="nombre_eleccion"]')?.value || '<?= $nombre_eleccion ?>');
    
    const response = await fetch('../api/config.php', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    
    if (result.success) {
        if (isOpen) {
            status.innerHTML = '🔴 VOTACIÓN CERRADA';
            status.style.background = 'rgba(239,68,68,0.2)';
            status.style.color = '#ef4444';
            btn.innerHTML = '🔓 Abrir Votación';
            btn.className = 'btn btn-primary';
        } else {
            status.innerHTML = '🟢 VOTACIÓN ABIERTA';
            status.style.background = 'rgba(34,197,94,0.2)';
            status.style.color = '#22c55e';
            btn.innerHTML = '🔒 Cerrar Votación';
            btn.className = 'btn btn-outline';
        }
    }
    showToast(result.message, result.success ? 'success' : 'error');
}

function exportarVotantes() {
    window.open('../api/exportar_votantes.php', '_blank');
}

function resetEleccion() {
    if (!confirm('⚠️ ¿Estás seguro?\n\nEsto borrará TODOS los votos registrados.\n\nEsta acción no se puede deshacer.')) return;
    if (!confirm('🔴 CONFIRMA: ¿Realmente deseas reiniciar la elección?\n\nTodos los votos serán eliminados.')) return;
    
    fetch('../api/reset_eleccion.php', { method: 'POST' })
        .then(r => r.json())
        .then(result => {
            showToast(result.message, result.success ? 'success' : 'error');
        });
}

async function addUserFromDashboard(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const result = await apiCall('../api/usuarios.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        e.target.reset();
        closeModal('modal-add-votante');
        loadRecentVotantes();
    }
}

async function addCandidatoFromDashboard(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const result = await apiCall('../api/candidatos.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        e.target.reset();
        closeModal('modal-add-candidato');
    }
}

// Importar Excel desde Dashboard
let excelDataDashboard = [];

function previewExcelDashboard() {
    const fileInput = document.getElementById('excel-file-dashboard');
    const file = fileInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });

            excelDataDashboard = [];

            let startRow = 0;
            if (rows.length > 0) {
                const firstRow = rows[0].map(c => String(c).toLowerCase().trim());
                if (firstRow.some(c => c.includes('nombre') || c.includes('email') || c.includes('correo') || c.includes('identidad') || c.includes('codigo'))) {
                    startRow = 1;
                }
            }

            for (let i = startRow; i < rows.length; i++) {
                const row = rows[i];
                const nombre = String(row[0] || '').trim();
                const identidad = String(row[1] || '').trim();
                const grado = String(row[2] || '').trim();
                const seccion = String(row[3] || '').trim();

                if (nombre || identidad) {
                    excelDataDashboard.push({ nombre, email: '', identidad, grado, seccion });
                }
            }

            const tbody = document.querySelector('#excel-preview-table-dashboard tbody');
            tbody.innerHTML = '';
            const preview = excelDataDashboard.slice(0, 50);
            preview.forEach((v, i) => {
                tbody.innerHTML += `<tr><td>${i+1}</td><td>${v.nombre}</td><td>${v.identidad}</td><td>${v.grado}</td><td>${v.seccion}</td></tr>`;
            });
            if (excelDataDashboard.length > 50) {
                tbody.innerHTML += `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">... y ${excelDataDashboard.length - 50} filas más</td></tr>`;
            }

            document.getElementById('excel-count-dashboard').textContent = excelDataDashboard.length + ' votantes encontrados';
            document.getElementById('excel-preview-dashboard').style.display = 'block';
            document.getElementById('excel-result-dashboard').style.display = 'none';
            document.getElementById('btn-import-excel-dashboard').disabled = false;
        } catch (err) {
            showToast('Error al leer el archivo Excel', 'error');
            document.getElementById('excel-preview-dashboard').style.display = 'none';
            document.getElementById('btn-import-excel-dashboard').disabled = true;
        }
    };
    reader.readAsArrayBuffer(file);
}

async function importExcelDashboard() {
    if (excelDataDashboard.length === 0) {
        showToast('No hay datos para importar', 'error');
        return;
    }

    const btn = document.getElementById('btn-import-excel-dashboard');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;margin:0;border-width:2px;"></div> Importando...';

    const result = await apiCall('../api/importar_votantes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ votantes: excelDataDashboard })
    });

    btn.disabled = false;
    btn.innerHTML = '📥 Importar Votantes';

    const resultDiv = document.getElementById('excel-result-dashboard');
    resultDiv.style.display = 'block';

    if (result.success) {
        resultDiv.innerHTML = `
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.2);border-radius:10px;padding:14px;">
                <p style="font-weight:700;color:var(--primary-light);margin-bottom:6px;">✅ ${result.message}</p>
                ${result.errores && result.errores.length > 0 ? `<p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Detalle de omitidos:</p><ul style="font-size:11px;color:var(--text-dim);margin:4px 0 0 16px;">${result.errores.map(e => '<li>'+e+'</li>').join('')}</ul>` : ''}
            </div>`;
        showToast(result.message, 'success');
        excelDataDashboard = [];
        closeModal('modal-import-votantes');
        loadRecentVotantes();
    } else {
        resultDiv.innerHTML = `
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:14px;">
                <p style="font-weight:700;color:var(--danger);">❌ ${result.message}</p>
            </div>`;
        showToast(result.message, 'error');
    }
}

async function loadRecentVotantes() {
    const container = document.getElementById('recent-votantes');
    if (!container) return;
    
    try {
        const response = await fetch('../api/usuarios.php');
        const result = await response.json();
        
        if (!result.success || !result.usuarios) {
            container.innerHTML = '<p class="text-muted">Error cargando datos</p>';
            return;
        }
        
        const votantees = result.usuarios.slice(0, 5);
        
        let html = '<table class="data-table"><thead><tr><th>Nombre</th><th>Identidad</th><th>Grado/Sección</th><th>Estado</th><th>Votó</th></tr></thead><tbody>';
        votantees.forEach(v => {
            html += `<tr>
                <td><strong>${v.nombre}</strong></td>
                <td><code>${v.identidad || '-'}</code></td>
                <td>${v.grado || '-'} / ${v.seccion || '-'}</td>
                <td><span class="status-badge ${v.activo ? 'active' : 'inactive'}">${v.activo ? 'Activo' : 'Inactivo'}</span></td>
                <td><span class="status-badge ${v.ya_voto ? 'voted' : 'not-voted'}">${v.ya_voto ? 'Sí' : 'No'}</span></td>
            </tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    } catch (err) {
        container.innerHTML = '<p class="text-muted">Error cargando datos</p>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadRecentVotantes();
});
</script>
</body>
</html>
