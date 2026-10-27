<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$cohortes = Reporte::cohortesDeDocente((int)$user['id']);

$grado = (int)($_GET['grado'] ?? 0);
$seccion = '';
if ($grado === 0 && count($cohortes) > 0) {
    $grado = (int)$cohortes[0]['grado'];
    $seccion = $cohortes[0]['seccion'];
} else {
    if ($grado === 0) {
        $grado = null;
    } else {
        $seccion = (string)($_GET['seccion'] ?? 'A');
    }
}

$estudianteId = (int)($_GET['estudiante'] ?? 0);
$resumen = null;

if ($grado !== null && $seccion !== '') {
    $resumen = Reporte::resumenSeccion((int)$user['id'], $grado, $seccion, $estudianteId > 0 ? $estudianteId : null);
    $promedioSeccion = Reporte::promedioSeccion($resumen);
}

$export = $_GET['export'] ?? '';

if ($export === 'csv' && $resumen !== null) {
    $nombre = "reporte_general_{$grado}_{$seccion}";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre . '.csv"');
    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Reporte General - Grado', $grado, 'Seccion', $seccion, 'Docente', $user['nombre']]);
    fputcsv($out, []);
    fputcsv($out, ['Estudiante', 'Asignaturas', 'Talleres Totales', 'Entregados', 'Evaluados', 'Promedio', 'Estatus']);
    foreach ($resumen['rows'] as $r) {
        $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null;
        fputcsv($out, [
            $r['nombre'],
            $r['asignaturas'],
            $r['talleres'],
            $r['entregados'],
            $r['evaluados'],
            $r['promedio'] !== null ? number_format($r['promedio'], 1) : 'Sin evaluaciones',
            $est['nombre'] ?? '-',
        ]);
    }
    fputcsv($out, []);
    fputcsv($out, ['PROMEDIO DE LA SECCION', $promedioSeccion !== null ? number_format($promedioSeccion, 1) . '/20' : 'Sin evaluaciones']);
    fclose($out);
    exit;
}

$flashSuccess = flash('success');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📊 Reporte de la Sección</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>

            <?php if (count($cohortes) === 0): ?>
                <section class="data-section">
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">Aún no tienes asignaturas. Crea una para generar el reporte de tu sección.</p>
                </section>
            <?php else: ?>

            <!-- Filtros -->
            <div class="filter-bar">
                <form method="get" action="reports.php" style="display:flex;gap:.5rem;align-items:center;width:100%;flex-wrap:wrap;">
                    <label style="font-weight:600;">Cohorte</label>
                    <select name="grado" onchange="this.form.submit()">
                        <?php foreach ($cohortes as $c): ?>
                            <?php if ((int)$c['grado'] === $grado && $c['seccion'] === $seccion): ?>
                                <option value="<?= (int)$c['grado'] ?>" selected><?= (int)$c['grado'] ?>° <?= e($c['seccion']) ?></option>
                            <?php else: ?>
                                <option value="<?= (int)$c['grado'] ?>"><?= (int)$c['grado'] ?>° <?= e($c['seccion']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($grado !== null && $seccion !== ''): ?>
                        <select name="estudiante" onchange="this.form.submit()">
                            <option value="0">Toda la sección</option>
                            <?php foreach ($resumen['rows'] as $r): ?>
                                <option value="<?= (int)$r['id_usuario'] ?>" <?= $estudianteId === (int)$r['id_usuario'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a class="btn-primary" href="reports.php?grado=<?= $grado ?>&seccion=<?= e($seccion) ?>&estudiante=<?= $estudianteId ?>&export=csv" style="margin-left:auto;">⬇️ Exportar CSV</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($grado !== null && $seccion !== ''): ?>
                <!-- KPIs de la sección -->
                <div class="kpi-grid" style="margin:1.5rem 0;">
                    <div class="kpi-card"><h3>🎓 Estudiantes</h3><p class="number"><?= count($resumen['rows']) ?></p></div>
                    <div class="kpi-card"><h3>📚 Asignaturas</h3><p class="number"><?= $resumen['subjects'] ?></p></div>
                    <div class="kpi-card"><h3>📤 Entregas Totales</h3><p class="number"><?= array_sum(array_column($resumen['rows'], 'entregados')) ?></p></div>
                    <div class="kpi-card" style="border-left:4px solid var(--primary);"><h3>⭐ Promedio de la Sección</h3><p class="number"><?= $promedioSeccion !== null ? number_format($promedioSeccion, 1) . '/20' : '—' ?></p></div>
                </div>

                <?php if ($estudianteId > 0 && count($resumen['rows']) > 0): ?>
                    <!-- Reporte individual con desglose por asignatura -->
                    <?php $alumno = $resumen['rows'][0]; $est = $alumno['promedio'] !== null ? Reporte::estatusConfig((float)$alumno['promedio']) : null; ?>
                    <section class="data-section">
                        <div class="table-header">
                            <h2>👤 Reporte Individual: <?= e($alumno['nombre']) ?></h2>
                            <a class="btn-sm" href="reports.php?grado=<?= $grado ?>&seccion=<?= e($seccion) ?>">← Ver toda la sección</a>
                        </div>
                        <div class="table-actions" style="margin-bottom:1rem;">
                            <a class="btn-sm" href="reports.php?grado=<?= $grado ?>&seccion=<?= e($seccion) ?>&estudiante=<?= $estudianteId ?>&export=csv">⬇️ Descargar CSV</a>
                        </div>

                        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
                            <div class="kpi-card" style="flex:1;min-width:160px;"><h3>Promedio General</h3><p class="number"><?= $alumno['promedio'] !== null ? number_format($alumno['promedio'], 1) . '/20' : '—' ?></p></div>
                            <div class="kpi-card" style="flex:1;min-width:160px;"><h3>Talleres</h3><p class="number"><?= $alumno['talleres'] ?></p></div>
                            <div class="kpi-card" style="flex:1;min-width:160px;"><h3>Estatus</h3><p class="number" style="font-size:1rem;">
                                <?php if ($est !== null): ?>
                                    <span class="status" style="background:<?= e($est['bg_color']) ?>;color:<?= e($est['color']) ?>;"><?= e($est['icon']) ?> <?= e($est['nombre']) ?></span>
                                <?php else: ?>
                                    <span class="status" style="background:#f1f5f9;color:#475569;">Sin evaluaciones</span>
                                <?php endif; ?>
                            </p></div>
                        </div>

                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Talleres Totales</th>
                                    <th>Entregados</th>
                                    <th>Evaluados</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumno['detalle'] as $d): ?>
                                    <tr>
                                        <td><strong><?= e($d['titulo']) ?></strong></td>
                                        <td><?= $d['talleres'] ?></td>
                                        <td><?= $d['entregados'] ?></td>
                                        <td><?= $d['evaluados'] ?></td>
                                        <td><strong><?= $d['promedio'] !== null ? number_format($d['promedio'], 1) . '/20' : '—' ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background:#f6fbf6;border-top:2px solid var(--primary);">
                                    <td colspan="4" style="text-align:right;font-weight:700;">⭐ PROMEDIO GENERAL</td>
                                    <td><strong style="font-size:1.1rem;color:var(--primary);"><?= $alumno['promedio'] !== null ? number_format($alumno['promedio'], 1) . '/20' : '—' ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </section>
                <?php else: ?>
                    <!-- Tabla general de la sección -->
                    <section class="data-section">
                        <div class="table-header">
                            <h2>Vista General <?= $grado ?>° "<?= e($seccion) ?>"</h2>
                        </div>

                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Asignaturas</th>
                                    <th>Talleres Totales</th>
                                    <th>Entregados</th>
                                    <th>Evaluados</th>
                                    <th>Promedio</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($resumen['rows']) === 0): ?>
                                    <tr><td colspan="7" style="text-align:center;color:#5a7a6a;">No hay estudiantes en esta sección.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($resumen['rows'] as $r): ?>
                                    <?php $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null; ?>
                                    <tr>
                                        <td><strong><?= e($r['nombre']) ?></strong></td>
                                        <td><?= $r['asignaturas'] ?></td>
                                        <td><?= $r['talleres'] ?></td>
                                        <td><?= $r['entregados'] ?></td>
                                        <td><?= $r['evaluados'] ?></td>
                                        <td><strong><?= $r['promedio'] !== null ? number_format($r['promedio'], 1) : '—' ?></strong></td>
                                        <td>
                                            <?php if ($est !== null): ?>
                                                <span class="status" style="background:<?= e($est['bg_color']) ?>;color:<?= e($est['color']) ?>;"><?= e($est['icon']) ?> <?= e($est['nombre']) ?></span>
                                            <?php else: ?>
                                                <span class="status" style="background:#f1f5f9;color:#475569;">Sin evaluaciones</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background:#f6fbf6;border-top:2px solid var(--primary);">
                                    <td colspan="6" style="text-align:right;font-weight:700;">⭐ PROMEDIO DE LA SECCIÓN</td>
                                    <td><strong style="font-size:1.1rem;color:var(--primary);"><?= $promedioSeccion !== null ? number_format($promedioSeccion, 1) . '/20' : '—' ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
