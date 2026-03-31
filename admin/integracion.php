<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();
$user = currentUser();
$db = getDB();

// Obtener datos para la planilla
$cargos = $db->query("SELECT * FROM cargos ORDER BY orden")->fetchAll();
$partidos = $db->query("SELECT * FROM partidos WHERE activo = 1 ORDER BY nombre")->fetchAll();

// Obtener candidatos con votos
$candidatos = $db->query("
    SELECT c.*, u.nombre AS nombre_candidato, 
           p.nombre AS partido, p.color AS partido_color, p.id AS partido_id,
           ca.nombre AS cargo, ca.orden AS cargo_orden,
           (SELECT COUNT(*) FROM votos v WHERE v.candidato_id = c.id) AS total_votos
    FROM candidatos c
    JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN partidos p ON c.partido_id = p.id
    JOIN cargos ca ON c.cargo_id = ca.id
    WHERE c.activo = 1
    ORDER BY p.nombre, ca.orden
")->fetchAll();

// Total votantes
$total_votantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'votante' AND activo = 1")->fetchColumn();
$total_votaron = $db->query("SELECT COUNT(DISTINCT votante_id) FROM votos")->fetchColumn();

// Calcular total de votos por partido
$votos_por_partido = [];
foreach ($candidatos as $c) {
    $pid = $c['partido_id'] ?? 'sin_partido';
    if (!isset($votos_por_partido[$pid])) {
        $votos_por_partido[$pid] = ['nombre' => $c['partido'] ?? 'Sin Partido', 'color' => $c['partido_color'] ?? '#888', 'total' => 0];
    }
    $votos_por_partido[$pid]['total'] += $c['total_votos'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integración — Elecciones Estudiantiles</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=13">
</head>
<body>

<div class="bg-particles"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <div class="logo">⚙️</div>
        <span>Admin Panel</span>
    </a>
    <div class="navbar-nav">
        <a href="dashboard.php" class="nav-link">📊 <span>Dashboard</span></a>
        <a href="candidatos.php" class="nav-link">👥 <span>Candidatos</span></a>
        <a href="votantes.php" class="nav-link">🎓 <span>Votantes</span></a>
        <a href="integracion.php" class="nav-link active">🔗 <span>Integración</span></a>
        <a href="../resultados.php" class="nav-link">📈 <span>Resultados</span></a>
        <a href="auditoria.php" class="nav-link">📋 <span>Auditoría</span></a>
    </div>
    <div class="navbar-user">
        <div class="user-avatar"><?= substr($user['nombre'], 0, 1) ?></div>
        <a href="../api/logout.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">Salir</a>
    </div>
</nav>

<div class="main-content">
    <div class="page-header">
        <h1>📋 Planilla Electoral por Partido</h1>
        <p>Integración de candidatos, cargos y votos por cada partido</p>
    </div>

    <!-- Resumen de votos por partido -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-bottom:30px;">
        <?php foreach ($votos_por_partido as $pid => $vp): ?>
        <div class="glass" style="padding:20px;border-left:4px solid <?= $vp['color'] ?>;">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px;"><?= htmlspecialchars($vp['nombre']) ?></div>
            <div style="font-size:28px;font-weight:800;font-family:'Poppins',sans-serif;color:var(--primary-light);"><?= $vp['total'] ?></div>
            <div style="font-size:12px;color:var(--text-muted);">votos totales</div>
            <?php $pct = $total_votantes > 0 ? round(($vp['total'] / ($total_votantes * count($cargos))) * 100, 1) : 0; ?>
            <div style="margin-top:8px;height:6px;background:rgba(255,255,255,0.1);border-radius:3px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:<?= $vp['color'] ?>;border-radius:3px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="glass" style="padding:20px;border-left:4px solid var(--secondary);">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px;">Participación</div>
            <div style="font-size:28px;font-weight:800;font-family:'Poppins',sans-serif;color:var(--secondary);"><?= $total_votantes > 0 ? round(($total_votaron / $total_votantes) * 100, 1) : 0 ?>%</div>
            <div style="font-size:12px;color:var(--text-muted);"><?= $total_votaron ?> de <?= $total_votantes ?> votantes</div>
        </div>
    </div>

    <!-- Planilla por partido -->
    <?php foreach ($partidos as $partido): ?>
    <div class="glass" style="padding:24px;margin-bottom:24px;border-top:4px solid <?= $partido['color'] ?>;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:40px;height:40px;border-radius:10px;background:<?= $partido['color'] ?>;"></div>
            <div>
                <h2 style="font-family:'Poppins',sans-serif;font-size:20px;font-weight:700;margin:0;"><?= htmlspecialchars($partido['nombre']) ?></h2>
                <?php if ($partido['slogan']): ?>
                <div style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($partido['slogan']) ?></div>
                <?php endif; ?>
            </div>
            <?php $total_partido = $votos_por_partido[$partido['id']]['total'] ?? 0; ?>
            <div style="margin-left:auto;text-align:right;">
                <div style="font-size:24px;font-weight:800;font-family:'Poppins',sans-serif;color:var(--primary-light);"><?= $total_partido ?></div>
                <div style="font-size:11px;color:var(--text-muted);">votos totales</div>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Candidato</th>
                        <th>#</th>
                        <th>Votos</th>
                        <th>% del Partido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $candidatos_partido = array_filter($candidatos, fn($c) => $c['partido_id'] == $partido['id']);
                    $total_votos_partido = array_sum(array_column($candidatos_partido, 'total_votos'));
                    foreach ($cargos as $cargo):
                        $cand = null;
                        foreach ($candidatos_partido as $c) {
                            if ($c['cargo_id'] == $cargo['id']) { $cand = $c; break; }
                        }
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cargo['nombre']) ?></strong></td>
                        <?php if ($cand): ?>
                        <td><?= htmlspecialchars($cand['nombre_candidato']) ?></td>
                        <td><span class="badge" style="background:rgba(99,102,241,0.15);color:#818cf8;padding:3px 10px;border-radius:20px;font-weight:700;"><?= $cand['numero_candidato'] ?: '-' ?></span></td>
                        <td><strong style="color:var(--primary-light);"><?= $cand['total_votos'] ?></strong></td>
                        <td>
                            <?php $pct_cargo = $total_votos_partido > 0 ? round(($cand['total_votos'] / max($total_votos_partido, 1)) * 100, 1) : 0; ?>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;max-width:100px;height:8px;background:rgba(255,255,255,0.1);border-radius:4px;overflow:hidden;">
                                    <div style="width:<?= $pct_cargo ?>%;height:100%;background:<?= $partido['color'] ?>;border-radius:4px;"></div>
                                </div>
                                <span style="font-size:12px;font-weight:600;"><?= $pct_cargo ?>%</span>
                            </div>
                        </td>
                        <?php else: ?>
                        <td colspan="4" style="text-align:center;color:var(--danger);font-style:italic;">Sin candidato registrado</td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid rgba(34,197,94,0.1);">
                        <td colspan="3" style="text-align:right;font-weight:700;">Total Planilla:</td>
                        <td><strong style="font-size:16px;color:var(--primary-light);"><?= $total_votos_partido ?></strong></td>
                        <td>
                            <?php $pct_general = $total_votantes > 0 ? round(($total_votos_partido / max($total_votantes * count($cargos), 1)) * 100, 1) : 0; ?>
                            <span style="font-weight:700;"><?= $pct_general ?>% del total</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Resultados por Cargo (Ganadores) -->
    <div class="page-header" style="margin-top:40px;">
        <h1>🏆 Ganadores por Cargo</h1>
        <p>Candidato con más votos en cada cargo</p>
    </div>

    <div class="glass" style="padding:24px;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Ganador</th>
                        <th>Partido</th>
                        <th>Votos</th>
                        <th>Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cargos as $cargo):
                        $cands_cargo = array_filter($candidatos, fn($c) => $c['cargo_id'] == $cargo['id']);
                        usort($cands_cargo, fn($a, $b) => $b['total_votos'] - $a['total_votos']);
                        $ganador = reset($cands_cargo);
                        $total_cargo = array_sum(array_column($cands_cargo, 'total_votos'));
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cargo['nombre']) ?></strong></td>
                        <?php if ($ganador): ?>
                        <td><strong style="color:var(--accent);"><?= htmlspecialchars($ganador['nombre_candidato']) ?></strong></td>
                        <td>
                            <?php if ($ganador['partido']): ?>
                            <span style="display:inline-flex;align-items:center;gap:6px;">
                                <span style="width:12px;height:12px;border-radius:4px;background:<?= $ganador['partido_color'] ?>;display:inline-block;"></span>
                                <?= htmlspecialchars($ganador['partido']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">Sin partido</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= $ganador['total_votos'] ?></strong></td>
                        <td>
                            <?php $pct_ganador = $total_cargo > 0 ? round(($ganador['total_votos'] / $total_cargo) * 100, 1) : 0; ?>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;max-width:120px;height:10px;background:rgba(255,255,255,0.1);border-radius:5px;overflow:hidden;">
                                    <div style="width:<?= $pct_ganador ?>%;height:100%;background:linear-gradient(90deg,var(--primary),var(--accent));border-radius:5px;"></div>
                                </div>
                                <span style="font-weight:700;color:var(--primary-light);"><?= $pct_ganador ?>%</span>
                            </div>
                        </td>
                        <?php else: ?>
                        <td colspan="4" style="text-align:center;color:var(--text-muted);font-style:italic;">Sin candidatos</td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
