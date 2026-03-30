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
        <a href="candidatos.php" class="nav-link">👥 <span>Candidatos</span></a>
        <a href="votantes.php" class="nav-link active">🎓 <span>Votantes</span></a>
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
            <h1>🎓 Gestión de Votantes</h1>
            <p>Administra los estudiantes habilitados para votar</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-add-user')">
            ➕ Nuevo Votante
        </button>
    </div>

    <!-- Tabla de votantes -->
    <div class="glass" style="padding:24px;">
        <div id="votantes-table">
            <div class="spinner"></div>
        </div>
    </div>
</div>

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
                <label>Código Estudiantil *</label>
                <input type="text" name="codigo_estudiantil" required placeholder="Ej: EST-2026-001">
            </div>
            <div class="input-group">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="estudiante@email.edu">
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
                    <input type="text" name="seccion" placeholder="A, B, C...">
                </div>
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" value="estudiante123" placeholder="Se usará por defecto">
                <small class="text-muted" style="font-size:11px;">Contraseña por defecto: estudiante123</small>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Registrar Votante</button>
        </form>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
<script>window.API_BASE_URL = '../api';</script>
<script src="../assets/js/app.js"></script>
</body>
</html>
