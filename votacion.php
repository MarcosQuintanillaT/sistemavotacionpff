<?php
require_once 'config/db.php';
requireLogin();

// Si es admin, redirigir
if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votación — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="assets/css/style.css?v=13">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🗳️</text></svg>">
</head>
<body>

<div class="bg-particles"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- Navbar -->
<nav class="navbar">
    <a href="votacion.php" class="navbar-brand">
        <div class="logo">🗳️</div>
        <span>Elecciones 2026</span>
    </a>
    <div class="navbar-nav">
        <a href="votacion.php" class="nav-link active">🗳️ <span>Votar</span></a>
        <a href="resultados.php" class="nav-link">📊 <span>Resultados</span></a>
    </div>
    <div class="navbar-user">
        <div class="user-avatar"><?= substr($user['nombre'], 0, 1) ?></div>
        <a href="api/logout.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Salir</a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="page-header">
        <h1>🗳️ Emite tu Voto</h1>
        <p>Hola <strong><?= htmlspecialchars($user['nombre']) ?></strong>, selecciona tus candidatos para cada cargo. Tu voto es secreto y seguro.</p>
    </div>

    <!-- Votación Container -->
    <div id="votacion-container">
        <div class="glass" style="padding:60px;text-align:center;">
            <div class="spinner"></div>
            <p class="text-muted" style="margin-top:16px;">Cargando candidatos...</p>
        </div>
    </div>

    <!-- Botón Votar -->
    <div style="text-align:center;margin-top:30px;" id="votar-btn-container">
        <button class="btn btn-success btn-lg" onclick="emitirVotos()" style="font-size:18px;padding:18px 60px;">
            ✅ Confirmar Mi Voto
        </button>
        <p class="text-muted" style="margin-top:12px;font-size:13px;">Al confirmar, tus selecciones serán registradas de forma definitiva</p>
    </div>
</div>

<!-- Vote Success Overlay -->
<div id="vote-success-overlay" class="vote-success-overlay">
    <div class="vote-success-content">
        <div class="vote-success-icon">✓</div>
        <h2>¡Voto Registrado!</h2>
        <p>Tu voto ha sido registrado exitosamente. Gracias por participar en las elecciones estudiantiles.</p>
        <button class="btn btn-primary btn-lg" onclick="document.getElementById('vote-success-overlay').classList.remove('active');">
            Continuar →
        </button>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Confetti Container -->
<div class="confetti-container" style="display:none;"></div>

<script>window.API_BASE_URL = 'api';</script>
<script src="assets/js/app.js"></script>
</body>
</html>
