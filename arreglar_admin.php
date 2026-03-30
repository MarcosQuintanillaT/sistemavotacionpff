<?php
// ==========================================
// Corregir contraseña del admin
// Ejecutar: http://localhost/elecciones_estudiantiles/arreglar_admin.php
// ==========================================
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Arreglar Admin</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #1e293b; padding: 40px; border-radius: 16px; text-align: center; max-width: 500px; }
        .ok { border-left: 4px solid #10b981; padding: 12px 16px; border-radius: 8px; background: rgba(16,185,129,0.1); margin: 10px 0; text-align: left; }
        .err { border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 8px; background: rgba(239,68,68,0.1); margin: 10px 0; text-align: left; }
        a { display: inline-block; padding: 12px 24px; background: #6366f1; color: white; border-radius: 8px; text-decoration: none; margin-top: 16px; font-weight: bold; }
    </style>
</head>
<body>
<div class="box">
    <h2>🔧 Arreglar Credenciales Admin</h2>
    <?php

    try {
        $conn = new PDO('mysql:host=localhost;dbname=elecciones_estudiantiles;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = 'admin@elecciones.edu'");
        $stmt->execute([$new_hash]);

        if ($stmt->rowCount() > 0) {
            echo '<div class="ok">✅ Contraseña del admin actualizada correctamente.<br>';
            echo '<strong>Email:</strong> admin@elecciones.edu<br>';
            echo '<strong>Contraseña:</strong> admin123</div>';
        } else {
            // El admin no existe, crearlo
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, codigo_estudiantil) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['Administrador del Sistema', 'admin@elecciones.edu', $new_hash, 'admin', 'ADM001']);
            echo '<div class="ok">✅ Usuario admin creado exitosamente.<br>';
            echo '<strong>Email:</strong> admin@elecciones.edu<br>';
            echo '<strong>Contraseña:</strong> admin123</div>';
        }

    } catch (PDOException $e) {
        echo '<div class="err">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<p>Asegúrate de haber ejecutado <a href="diagnostico.php">diagnostico.php</a> primero.</p>';
    }

    ?>
    <a href="login.php">Ir al Login →</a>
</div>
</body>
</html>
