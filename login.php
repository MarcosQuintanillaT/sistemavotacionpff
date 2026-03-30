<?php require_once 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elecciones Estudiantiles 2026</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🗳️</text></svg>">
</head>
<body>

<!-- Fondo animado -->
<div class="bg-particles"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<div class="auth-container">
    <div class="auth-card glass glass-strong">

        <!-- Header -->
        <div class="auth-header">
            <div class="auth-logo">🗳️</div>
            <h1>Elecciones Estudiantiles</h1>
            <p>Gobierno Estudiantil 2026 — Tu voz cuenta</p>
        </div>

        <!-- Tabs -->
        <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login" onclick="switchAuthTab('login')">Iniciar Sesión</button>
            <button class="auth-tab" data-tab="register" onclick="switchAuthTab('register')">Registrarse</button>
        </div>

        <!-- Login Form -->
        <form id="form-login" class="auth-form" onsubmit="handleLogin(event)">
            <div class="input-group">
                <label>Correo Electrónico</label>
                <span class="input-icon">📧</span>
                <input type="email" name="email" placeholder="tu@email.edu" required>
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <span class="input-icon">🔒</span>
                <input type="password" name="password" placeholder="Tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:8px;">
                Entrar al Sistema →
            </button>
            <div class="auth-footer" style="margin-top:16px;">
                <small class="text-muted">Admin: admin@elecciones.edu / admin123</small>
            </div>
        </form>

        <!-- Register Form -->
        <form id="form-register" class="auth-form hidden" onsubmit="handleRegister(event)">
            <div class="input-group">
                <label>Nombre Completo *</label>
                <input type="text" name="nombre" placeholder="Tu nombre completo" required>
            </div>
            <div class="input-group">
                <label>Código Estudiantil *</label>
                <input type="text" name="codigo_estudiantil" placeholder="Ej: EST-2026-001" required>
            </div>
            <div class="input-group">
                <label>Correo Electrónico *</label>
                <input type="email" name="email" placeholder="tu@email.edu" required>
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
                <label>Contraseña *</label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
            </div>
            <button type="submit" class="btn btn-secondary btn-lg" style="width:100%;margin-top:8px;">
                Crear Cuenta ✨
            </button>
        </form>
    </div>
</div>

<script>window.API_BASE_URL = 'api';</script>
<script src="assets/js/app.js"></script>
</body>
</html>
