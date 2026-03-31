<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votantes Admin — Elecciones Estudiantiles</title>
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
        <a href="auditoria.php" class="nav-link">📋 <span>Auditoría</span></a>
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
            <h1>🎓 Gestión de Votantes</h1>
            <p>Administra los estudiantes habilitados para votar</p>
        </div>
        <div style="display:flex;gap:12px;">
            <button class="btn btn-outline" onclick="exportarVotantes()">📥 Exportar CSV</button>
            <button class="btn btn-secondary" onclick="openModal('modal-import-excel')">
                📥 Importar Excel
            </button>
            <button class="btn btn-primary" onclick="openModal('modal-add-user')">
                ➕ Nuevo Votante
            </button>
        </div>
    </div>

    <!-- Búsqueda y Filtros -->
    <div class="glass" style="padding:16px;margin-bottom:20px;">
        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
            <div class="input-group" style="margin-bottom:0;flex:1;min-width:250px;">
                <input type="text" id="votantes-search" placeholder="🔍 Buscar por nombre, identidad o email..." oninput="renderVotantes()" style="padding:10px 16px;">
            </div>
            <div class="input-group" style="margin-bottom:0;min-width:180px;">
                <select id="filter-status" onchange="renderVotantes()">
                    <option value="all">Todos</option>
                    <option value="voted">Ya votaron</option>
                    <option value="not-voted">No han votado</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </select>
            </div>
            <span id="votantes-count" style="color:var(--text-muted);font-size:13px;">0 votantees</span>
        </div>
    </div>

    <!-- Tabla de votantes -->
    <div class="glass" style="padding:24px;">
        <div id="votantes-table">
            <div class="spinner"></div>
        </div>
    </div>
</div>

<script>
function exportarVotantes() {
    window.open('../api/exportar_votantes.php', '_blank');
}
</script>

<!-- Modal: Agregar Votante -->
<div id="modal-add-user" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-add-user')">
    <div class="modal glass glass-strong">
        <div class="modal-header">
            <h2>➕ Nuevo Votante</h2>
            <button class="modal-close" onclick="closeModal('modal-add-user')">✕</button>
        </div>
        <form onsubmit="addUser(event)">
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
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" value="1612" placeholder="Por defecto: 1612">
                <small class="text-muted" style="font-size:11px;">Contraseña por defecto: 1612</small>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Registrar Votante</button>
        </form>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>

<!-- Modal: Importar Excel -->
<div id="modal-import-excel" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-import-excel')">
    <div class="modal glass glass-strong" style="max-width:700px;">
        <div class="modal-header">
            <h2>📥 Importar Votantes desde Excel</h2>
            <button class="modal-close" onclick="closeModal('modal-import-excel')">✕</button>
        </div>
        <div style="padding:20px;">
            <div class="input-group">
                <label>Seleccionar archivo Excel</label>
                <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" onchange="previewExcel()" style="padding:12px;background:rgba(255,255,255,0.05);border:1px solid var(--glass-border);border-radius:8px;color:white;">
                <small class="text-muted" style="font-size:11px;">Formatos: .xlsx, .xls, .csv</small>
            </div>
            
            <div style="background:rgba(255,255,255,0.05);padding:12px;border-radius:8px;margin:16px 0;font-size:13px;">
                <strong>Formato de columnas:</strong>
                <div style="margin-top:8px;color:var(--text-muted);">
                    Columna A: Nombre | Columna B: Identidad | Columna C: Grado (7mo-12vo) | Columna D: Sección (1-2)
                </div>
            </div>

            <div id="excel-preview" style="display:none;margin-top:16px;">
                <div id="excel-count" style="font-weight:600;margin-bottom:8px;"></div>
                <div class="table-container" style="max-height:300px;overflow:auto;">
                    <table class="data-table" id="excel-preview-table">
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

            <div id="excel-result" style="display:none;margin-top:16px;"></div>

            <button type="button" id="btn-import-excel" class="btn btn-primary btn-lg" style="width:100%;margin-top:16px;" onclick="importExcel()" disabled>
                📥 Importar Votantes
            </button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>window.API_BASE_URL = '../api';</script>
<script src="../assets/js/app.js"></script>
</body>
</html>
