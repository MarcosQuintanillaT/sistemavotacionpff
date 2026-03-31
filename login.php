<?php require_once 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elecciones Estudiantiles 2026</title>
    <link rel="stylesheet" href="assets/css/style.css?v=13">
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
            <img src="imagen/logoPFF.png" alt="Logo" style="width:70px;height:auto;margin-bottom:0;">
            <h1 style="margin-top:5px;">Elecciones Estudiantiles CEMG Pascual Fajardo</h1>
            <p>Gobierno Estudiantil 2026</p>
        </div>

        <!-- Tabs -->
        <div class="auth-tabs">
            <button class="auth-tab active" data-tab="login" onclick="switchAuthTab('login')">Iniciar Sesión</button>
            <button class="auth-tab" data-tab="register" onclick="switchAuthTab('register')">Registrarse</button>
        </div>

        <!-- Login Form -->
        <form id="form-login" class="auth-form" onsubmit="handleLogin(event)">
            <div class="input-group">
                <label>Identidad del Estudiante</label>
                <span class="input-icon">🎓</span>
                <input type="text" name="codigo" placeholder="Ej: 1612200800130" required>
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <span class="input-icon">🔒</span>
                <input type="password" name="password" placeholder="Ingrese su contraseña" maxlength="4" pattern="[0-9]{4}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:8px;">
                Entrar al Sistema →
            </button>
        </form>

        <!-- Register Form -->
        <form id="form-register" class="auth-form hidden" onsubmit="handleRegister(event)">
            <div class="input-group">
                <label>Nombre Completo *</label>
                <input type="text" name="nombre" placeholder="Tu nombre completo" required>
            </div>
            <div class="input-group">
                <label>Identidad del Estudiante *</label>
                <input type="text" name="identidad" placeholder="Ej: 1612200800130" required>
            </div>
            <div class="input-group">
                <label>Correo Electrónico *</label>
                <input type="email" name="email" placeholder="tu@email.edu" required>
            </div>
            <div class="input-group">
                <label>Rol</label>
                <select name="rol" id="register-rol" onchange="toggleAdminFields()">
                    <option value="votante">Estudiante</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="input-group" id="admin-code-field" style="display:none;">
                <label>Código de Administrador *</label>
                <input type="password" name="admin_code" placeholder="Código secreto">
            </div>
            <div class="input-group" id="password-field" style="display:none;">
                <label>Contraseña Personal *</label>
                <input type="password" name="password" placeholder="Mínimo 4 dígitos" minlength="4">
            </div>
            <div id="grado-seccion-fields" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
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
            <button type="submit" class="btn btn-secondary btn-lg" style="width:100%;margin-top:8px;">
                Crear Cuenta ✨
            </button>
        </form>
    </div>
</div>

<script>window.API_BASE_URL = 'api';</script>
<script>
function toggleAdminFields() {
    const rol = document.getElementById('register-rol').value;
    const adminCodeField = document.getElementById('admin-code-field');
    const passwordField = document.getElementById('password-field');
    const gradoSeccionFields = document.getElementById('grado-seccion-fields');
    const adminCodeInput = adminCodeField.querySelector('input');
    const passwordInput = passwordField.querySelector('input');
    
    if (rol === 'admin') {
        adminCodeField.style.display = 'block';
        passwordField.style.display = 'block';
        gradoSeccionFields.style.display = 'none';
        adminCodeInput.setAttribute('required', 'required');
        passwordInput.setAttribute('required', 'required');
        passwordInput.setAttribute('maxlength', '20');
        passwordInput.setAttribute('pattern', '.{4,}');
    } else {
        adminCodeField.style.display = 'none';
        passwordField.style.display = 'none';
        gradoSeccionFields.style.display = 'grid';
        adminCodeInput.removeAttribute('required');
        passwordInput.removeAttribute('required');
        passwordInput.removeAttribute('maxlength');
        passwordInput.removeAttribute('pattern');
        adminCodeInput.value = '';
        passwordInput.value = '';
    }
}
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
