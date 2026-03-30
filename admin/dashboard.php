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
    <title>Dashboard Admin — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
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
        <a href="dashboard.php" class="nav-link active">📊 <span>Dashboard</span></a>
        <a href="candidatos.php" class="nav-link">👥 <span>Candidatos</span></a>
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
    <div class="page-header">
        <h1>📊 Panel de Administración</h1>
        <p>Monitoreo en tiempo real de las elecciones estudiantiles</p>
    </div>

    <!-- Stats -->
    <div id="admin-stats" class="stats-grid">
        <div class="stat-card glass">
            <div class="stat-icon primary">👥</div>
            <div class="stat-value" id="stat-votantes">0</div>
            <div class="stat-label">Votantes Registrados</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon secondary">✅</div>
            <div class="stat-value" id="stat-votaron">0</div>
            <div class="stat-label">Ya Votaron</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon success">🏃</div>
            <div class="stat-value" id="stat-candidatos">0</div>
            <div class="stat-label">Candidatos</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon accent">🏛️</div>
            <div class="stat-value" id="stat-partidos">0</div>
            <div class="stat-label">Partidos</div>
        </div>
    </div>

    <!-- Grid: Participación + Líderes -->
    <div class="grid-2 mb-30">
        <!-- Participación Ring -->
        <div class="glass" style="padding:24px;text-align:center;">
            <h3 style="margin-bottom:20px;font-size:18px;">📈 Participación General</h3>
            <div id="participation-ring" class="progress-ring">
                <svg width="140" height="140">
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#6366f1" />
                            <stop offset="100%" style="stop-color:#10b981" />
                        </linearGradient>
                    </defs>
                    <circle class="progress-bg" cx="70" cy="70" r="60" style="stroke-width:10" />
                    <circle class="progress-fill" cx="70" cy="70" r="60" style="stroke-width:10" />
                </svg>
                <div class="progress-value" style="font-size:28px;">0%</div>
            </div>
        </div>

        <!-- Líderes -->
        <div class="glass" style="padding:24px;">
            <h3 style="margin-bottom:20px;font-size:18px;">🏆 Líderes por Cargo</h3>
            <div id="leaders-container">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <!-- Gráfico por Hora -->
    <div class="chart-container glass mb-30">
        <h3>⏰ Votos por Hora (Últimas 24h)</h3>
        <div id="hourly-chart" style="margin-top:16px;">
            <p class="text-muted">Cargando datos...</p>
        </div>
    </div>

    <!-- Últimos Votos -->
    <div class="chart-container glass">
        <h3>🕐 Últimos Votos Emitidos</h3>
        <div id="recent-votes" style="margin-top:16px;">
            <p class="text-muted">Cargando...</p>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
<script>window.API_BASE_URL = '../api';</script>
<script src="../assets/js/app.js"></script>
</body>
</html>
