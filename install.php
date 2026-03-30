<?php
// Script para instalar la base de datos
// Ejecutar: http://localhost/elecciones_estudiantiles/install.php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

echo "<h1>🗳️ Instalación del Sistema de Elecciones Estudiantiles</h1>";

try {
    $conn = new PDO("mysql:host=$db_host;", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/database.sql');

    // Ejecutar cada statement
    $conn->exec($sql);

    echo "<p style='color:green;font-size:18px;'>✅ Base de datos creada exitosamente!</p>";
    echo "<p>Tablas creadas:</p><ul>";
    echo "<li>configuracion</li>";
    echo "<li>usuarios (admin: admin@elecciones.edu / admin123)</li>";
    echo "<li>partidos</li>";
    echo "<li>cargos (6 cargos predefinidos)</li>";
    echo "<li>candidatos</li>";
    echo "<li>votos</li>";
    echo "<li>auditoria</li>";
    echo "</ul>";
    echo "<br><a href='login.php' style='background:#6366f1;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>Ir al Sistema → Login</a>";
    echo "<br><br><p style='color:red;font-weight:bold;'>⚠️ IMPORTANTE: Elimina este archivo (install.php) después de la instalación por seguridad.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;font-size:16px;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de que MySQL esté corriendo en XAMPP.</p>";
}
