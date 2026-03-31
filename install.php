<?php
// Script para instalar la base de datos
// Ejecutar: http://localhost/eleccion_estudiantil/install.php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'eleccion_estudiantil';

echo "<h1>🗳️ Instalación del Sistema de Elecciones Estudiantiles</h1>";

try {
    $conn = new PDO("mysql:host=$db_host;", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear base de datos si no existe
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $conn->exec("USE `$db_name`");

    // Leer el archivo SQL principal
    $sql = file_get_contents(__DIR__ . '/eleccion_estudiantil.sql');
    
    // Eliminar todo para install limpia
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    $conn->exec("DROP VIEW IF EXISTS `v_resultados`");
    $conn->exec("DROP TABLE IF EXISTS `auditoria`, `votos`, `candidatos`, `cargos`, `partidos`, `usuarios`, `configuracion`");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Separar por sentencias y ejecutar solo CREATE (estructura, no datos)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        $stmtUpper = strtoupper($stmt);
        // Solo ejecutar CREATE TABLE y CREATE VIEW (omitir configuracion que tiene estructura diferente)
        if ((strpos($stmtUpper, 'CREATE TABLE') !== false || strpos($stmtUpper, 'CREATE VIEW') !== false) 
            && strpos($stmt, 'configuracion') === false && strpos($stmt, 'v_resultados') === false) {
            try {
                $conn->exec($stmt);
            } catch (PDOException $e) {
                // Ignorar errores menores
            }
        }
    }

    // Crear tabla config con estructura clave/valor
    $conn->exec("CREATE TABLE IF NOT EXISTS `config` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `clave` VARCHAR(100) NOT NULL UNIQUE,
        `valor` TEXT DEFAULT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insertar configuración inicial
    $conn->exec("INSERT IGNORE INTO `config` (`clave`, `valor`) VALUES 
        ('nombre_eleccion', 'Elecciones Estudiantiles CEMG Pascual Fajardo 2026'),
        ('votacion_abierta', '0'),
        ('fecha_inicio', ''),
        ('fecha_fin', ''),
        ('sistema_bloqueado', '1')");

    echo "<p style='color:green;font-size:18px;'>✅ Base de datos instalada limpiamente!</p>";
    echo "<p>Tablas creadas:</p><ul>";
    echo "<li>config (incluye sistema_bloqueado = 1)</li>";
    echo "<li>usuarios</li>";
    echo "<li>partidos</li>";
    echo "<li>cargos</li>";
    echo "<li>candidatos</li>";
    echo "<li>votos</li>";
    echo "<li>auditoria</li>";
    echo "</ul>";
    echo "<br><a href='login.php' style='background:#22c55e;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>Ir al Sistema → Login</a>";
    echo "<br><br><p style='color:red;font-weight:bold;'>⚠️ IMPORTANTE: Elimina este archivo (install.php) después de la instalación por seguridad.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;font-size:16px;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de que MySQL esté corriendo en XAMPP.</p>";
}
