<?php
require_once 'config/db.php';

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
</head>
<body>

<div class="bg-particles"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- Navbar -->
<nav class="navbar">
    <a href="<?= isLoggedIn() ? (isAdmin() ? 'admin/dashboard.php' : 'votacion.php') : 'login.php' ?>" class="navbar-brand">
        <div class="logo">🗳️</div>
        <span>Elecciones 2026</span>
    </a>
    <div class="navbar-nav">
        <?php if (isLoggedIn()): ?>
        <?php if (!isAdmin()): ?>
        <a href="votacion.php" class="nav-link">🗳️ <span>Votar</span></a>
        <?php endif; ?>
        <a href="resultados.php" class="nav-link active">📊 <span>Resultados</span></a>
        <?php if (isAdmin()): ?>
        <a href="admin/dashboard.php" class="nav-link">⚙️ <span>Admin</span></a>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="navbar-user">
        <?php if (isLoggedIn()): ?>
        <div class="user-avatar"><?= substr($user['nombre'], 0, 1) ?></div>
        <a href="api/logout.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Salir</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-primary" style="padding:8px 20px;font-size:13px;">Iniciar Sesión</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1>📊 Resultados en Vivo</h1>
        <p>Resultados actualizados en tiempo real de las elecciones de gobierno estudiantil</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card glass">
            <div class="stat-icon primary">👥</div>
            <div class="stat-value" id="stat-votantes">0</div>
            <div class="stat-label">Votantes Habilitados</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon success">✅</div>
            <div class="stat-value" id="stat-votaron">0</div>
            <div class="stat-label">Ya Votaron</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon accent">📈</div>
            <div class="stat-value" id="stat-participacion">0%</div>
            <div class="stat-label">Participación</div>
        </div>
        <div class="stat-card glass">
            <div style="text-align:center;">
                <div id="participation-ring" class="progress-ring">
                    <svg width="120" height="120">
                        <defs>
                            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#6366f1" />
                                <stop offset="100%" style="stop-color:#ec4899" />
                            </linearGradient>
                        </defs>
                        <circle class="progress-bg" cx="60" cy="60" r="52" />
                        <circle class="progress-fill" cx="60" cy="60" r="52" />
                    </svg>
                    <div class="progress-value">0%</div>
                </div>
                <div class="stat-label">Participación</div>
            </div>
        </div>
    </div>

    <!-- Resultados Container -->
    <div id="resultados-container">
        <div class="glass" style="padding:60px;text-align:center;">
            <div class="spinner"></div>
            <p class="text-muted" style="margin-top:16px;">Cargando resultados...</p>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<script>window.API_BASE_URL = 'api';</script>
<script src="assets/js/app.js"></script>
</body>
</html>
