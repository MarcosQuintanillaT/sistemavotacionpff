<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="votantes_' . date('Y-m-d_His') . '.csv"');

if (!isAdmin()) {
    exit('No autorizado');
}

$db = getDB();
$stmt = $db->query("
    SELECT u.nombre, u.identidad, u.email, u.grado, u.seccion, u.activo,
           CASE WHEN v.id IS NOT NULL THEN 'Sí' ELSE 'No' END AS voto
    FROM usuarios u
    LEFT JOIN votos v ON u.id = v.votante_id
    WHERE u.rol = 'votante'
    ORDER BY u.nombre
");

echo "\xEF\xBB\xBF"; // BOM UTF-8
echo "Nombre,Identidad,Email,Grado,Sección,Estado,Votó\n";

while ($row = $stmt->fetch()) {
    echo '"' . str_replace('"', '""', $row['nombre']) . '",';
    echo '"' . str_replace('"', '""', $row['identidad'] ?? '') . '",';
    echo '"' . str_replace('"', '""', $row['email'] ?? '') . '",';
    echo '"' . str_replace('"', '""', $row['grado'] ?? '') . '",';
    echo '"' . str_replace('"', '""', $row['seccion'] ?? '') . '",';
    echo ($row['activo'] ? 'Activo' : 'Inactivo') . ',';
    echo $row['voto'] . "\n";
}
