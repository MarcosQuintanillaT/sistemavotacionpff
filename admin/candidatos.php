<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();
$user = currentUser();

// Cargar datos para selects
$db = getDB();
$usuarios = $db->query("SELECT id, nombre FROM usuarios WHERE rol = 'votante' AND activo = 1 ORDER BY nombre")->fetchAll();
$partidos = $db->query("SELECT id, nombre, color FROM partidos WHERE activo = 1 ORDER BY nombre")->fetchAll();
$cargos = $db->query("SELECT id, nombre FROM cargos ORDER BY orden")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos Admin — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <a href="candidatos.php" class="nav-link active">👥 <span>Candidatos</span></a>
        <a href="votantes.php" class="nav-link">🎓 <span>Votantes</span></a>
        <a href="../resultados.php" class="nav-link">📈 <span>Resultados</span></a>
    </div>
    <div class="navbar-user">
        <div class="user-avatar"><?= substr($user['nombre'], 0, 1) ?></div>
        <a href="../api/logout.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Salir</a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header page-header-actions">
        <div>
            <h1>👥 Gestión de Candidatos</h1>
            <p>Administra los candidatos para cada cargo disputado</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-add-candidato')">
            ➕ Nuevo Candidato
        </button>
    </div>

    <!-- Tabla de candidatos -->
    <div class="glass" style="padding:24px;">
        <div id="candidatos-admin-table">
            <div class="spinner"></div>
        </div>
    </div>

    <!-- Partidos Section -->
    <div class="page-header page-header-actions" style="margin-top:40px;">
        <div>
            <h1>🏛️ Partidos / Movimientos</h1>
            <p>Administra los partidos estudiantiles</p>
        </div>
        <button class="btn btn-secondary" onclick="openModal('modal-add-partido')">
            ➕ Nuevo Partido
        </button>
    </div>

    <div class="glass" style="padding:24px;">
        <div id="partidos-table">
            <div class="spinner"></div>
        </div>
    </div>
</div>

<!-- Modal: Agregar Candidato -->
<div id="modal-add-candidato" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-add-candidato')">
    <div class="modal glass glass-strong">
        <div class="modal-header">
            <h2>➕ Nuevo Candidato</h2>
            <button class="modal-close" onclick="closeModal('modal-add-candidato')">✕</button>
        </div>
        <form onsubmit="addCandidato(event)">
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
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Registrar Candidato</button>
        </form>
    </div>
</div>

<!-- Modal: Agregar Partido -->
<div id="modal-add-partido" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-add-partido')">
    <div class="modal glass glass-strong">
        <div class="modal-header">
            <h2>🏛️ Nuevo Partido</h2>
            <button class="modal-close" onclick="closeModal('modal-add-partido')">✕</button>
        </div>
        <form onsubmit="addPartido(event)">
            <div class="input-group">
                <label>Nombre del Partido *</label>
                <input type="text" name="nombre" required placeholder="Ej: Movimiento Estudiantil Progreso">
            </div>
            <div class="input-group">
                <label>Slogan</label>
                <input type="text" name="slogan" placeholder="Ej: Juntos por un mejor mañana">
            </div>
            <div class="input-group">
                <label>Color</label>
                <input type="color" name="color" value="#6366f1" style="height:50px;cursor:pointer;">
            </div>
            <div class="input-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" placeholder="Descripción del movimiento..."></textarea>
            </div>
            <button type="submit" class="btn btn-secondary btn-lg" style="width:100%;">Registrar Partido</button>
        </form>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
<script>window.API_BASE_URL = '../api';</script>
<script src="../assets/js/app.js"></script>
<script>
async function loadPartidosAdmin() {
    const container = document.getElementById('partidos-table');
    if (!container) return;
    const result = await apiCall('partidos.php');
    if (!result.success) return;
    
    let html = '<div class="table-container"><table class="data-table"><thead><tr><th>Color</th><th>Nombre</th><th>Slogan</th><th>Candidatos</th></tr></thead><tbody>';
    result.partidos.forEach(p => {
        html += `<tr>
            <td><div style="width:30px;height:30px;border-radius:8px;background:${p.color};"></div></td>
            <td><strong>${p.nombre}</strong></td>
            <td class="text-muted">${p.slogan || '-'}</td>
            <td><span class="badge" style="background:rgba(99,102,241,0.15);color:var(--primary-light);padding:4px 12px;border-radius:20px;font-size:12px;">${p.total_candidatos}</span></td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

async function addCandidato(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const result = await apiCall('candidatos.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        e.target.reset();
        closeModal('modal-add-candidato');
        loadCandidatosAdmin();
    }
}

async function addPartido(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const result = await apiCall('partidos.php', { method: 'POST', body: formData });
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        e.target.reset();
        closeModal('modal-add-partido');
        loadPartidosAdmin();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadPartidosAdmin();
});
</script>
</body>
</html>
