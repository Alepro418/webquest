<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$docenteId = (int)$user['id'];
$proyectos = ProyectoAula::byDocenteWithStats($docenteId);

$proyectoId = (int)($_GET['proyecto'] ?? 0);
if ($proyectoId === 0 && count($proyectos) > 0) {
    $activo = ProyectoAula::activo($docenteId);
    $proyectoId = $activo !== null ? (int)$activo['id_proyecto'] : (int)$proyectos[0]['id_proyecto'];
}

$proyecto = $proyectoId > 0 ? ProyectoAula::find($proyectoId) : null;
if ($proyecto !== null && (int)$proyecto['id_docente'] !== $docenteId) {
    flash('error', 'Proyecto de Aula no encontrado o sin permiso.');
    redirect(site_url('/public/teaching/reports.php'));
}

$estudianteId = (int)($_GET['estudiante'] ?? 0);
$resumen   = $proyecto !== null ? Reporte::proyectoResumen((int)$proyecto['id_proyecto'], $estudianteId > 0 ? $estudianteId : null) : null;
$totales   = $resumen !== null ? Reporte::totalesGrupales($resumen) : null;
$individual = ($proyecto !== null && $estudianteId > 0)
    ? Reporte::reporteIndividual((int)$proyecto['id_proyecto'], $estudianteId)
    : null;

$export = $_GET['export'] ?? '';

if ($export === 'csv' && $proyecto !== null && $resumen !== null) {
    $momento = ProyectoAula::momentoLabel((string)$proyecto['momento']);
    $nombre  = 'reporte_' . $proyecto['momento'] . 'momento_' . (int)$proyecto['grado'] . $proyecto['seccion'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre . '.csv"');
    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['REPORTE DE NOTAS - ' . mb_strtoupper($momento)]);
    fputcsv($out, ['Proyecto de Aula', $proyecto['titulo']]);
    fputcsv($out, ['Periodo', ProyectoAula::periodoTexto($proyecto)]);
    fputcsv($out, ['Docente', $proyecto['docente_nombre'] ?? '']);
    fputcsv($out, ['Grado y Seccion', (int)$proyecto['grado'] . 'to "' . $proyecto['seccion'] . '"']);
    fputcsv($out, ['Anio Escolar', $proyecto['anio_escolar']]);
    fputcsv($out, ['Estado del PA', ucfirst((string)$proyecto['estado'])]);
    fputcsv($out, []);

    if ($individual !== null && $individual['alumno'] !== null) {
        $alumno = $individual['alumno'];
        fputcsv($out, ['Estudiante', $alumno['nombre']]);
        fputcsv($out, []);
        fputcsv($out, ['Asignatura', 'Talleres', 'Entregados', 'Evaluados', 'Promedio']);
        foreach ($alumno['detalle'] as $d) {
            fputcsv($out, [
                $d['titulo'],
                $d['talleres'],
                $d['entregados'],
                $d['evaluados'],
                $d['promedio'] !== null ? number_format($d['promedio'], 2) : 'Sin evaluaciones',
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Taller', 'Asignatura', 'Estado', 'Nota']);
        foreach ($individual['talleres'] as $t) {
            fputcsv($out, [
                $t['taller'],
                $t['asignatura'],
                $t['estado'],
                $t['puntaje'] !== null ? number_format($t['puntaje'], 1) : '—',
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['PROMEDIO DEL MOMENTO', $individual['promedio'] !== null ? number_format($individual['promedio'], 2) . '/20' : 'Sin evaluaciones']);
        fputcsv($out, ['ESTATUS', $individual['estatus']['nombre'] ?? 'Sin evaluaciones']);
    } else {
        fputcsv($out, ['#', 'Estudiante', 'Asignaturas', 'Talleres', 'Entregados', 'Notas', 'Promedio', 'Estatus']);
        $i = 1;
        foreach ($resumen['rows'] as $r) {
            $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null;
            fputcsv($out, [
                $i++,
                $r['nombre'],
                $r['asignaturas'],
                $r['talleres'],
                $r['entregados'] . ' / ' . $r['talleres'],
                $r['porcentaje'] . '%',
                $r['promedio'] !== null ? number_format($r['promedio'], 2) : 'Sin evaluaciones',
                $est['nombre'] ?? 'Sin evaluaciones',
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, [
            'TOTAL',
            '',
            $totales['asignaturas'],
            $totales['talleres'],
            $totales['entregados'] . ' / ' . $totales['talleres'],
            $totales['porcentaje'] . '%',
            $totales['promedio'] !== null ? number_format($totales['promedio'], 2) : 'Sin evaluaciones',
        ]);
    }
    fclose($out);
    exit;
}

$flashSuccess = flash('success');
$flashError   = flash('error');
$volverQuery  = 'proyecto=' . (int)($proyecto['id_proyecto'] ?? 0);
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
                <h1>📊 Reporte de Notas por Momento</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <?php if (count($proyectos) === 0): ?>
                <section class="data-section">
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">
                        Todavía no has configurado un Proyecto de Aula, por lo que no hay notas que reportar.
                    </p>
                    <p style="text-align:center;margin:0;">
                        <a class="btn-primary" href="pa.php">⚙️ Ir a Configuración</a>
                    </p>
                </section>
            <?php else: ?>

            <!-- Filtros -->
            <div class="filter-bar">
                <form method="get" action="reports.php" style="display:flex;gap:.5rem;align-items:center;width:100%;flex-wrap:wrap;">
                    <label style="font-weight:600;">Momento</label>
                    <select name="proyecto" onchange="this.form.submit()">
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= (int)$p['id_proyecto'] ?>" <?= (int)$p['id_proyecto'] === $proyectoId ? 'selected' : '' ?>>
                                <?= e(ProyectoAula::momentoLabel((string)$p['momento'])) ?> · <?= e($p['titulo']) ?>
                                (<?= (int)$p['grado'] ?>° <?= e($p['seccion']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($resumen !== null): ?>
                        <select name="estudiante" onchange="this.form.submit()">
                            <option value="0">Toda la sección</option>
                            <?php foreach ($resumen['rows'] as $r): ?>
                                <option value="<?= (int)$r['id_usuario'] ?>" <?= $estudianteId === (int)$r['id_usuario'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a class="btn-primary" href="reports.php?<?= $volverQuery ?>&estudiante=<?= $estudianteId ?>&export=csv" style="margin-left:auto;">⬇️ Exportar CSV</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($proyecto !== null && $resumen !== null): ?>
                <!-- Encabezado del momento -->
                <section class="pa-active">
                    <div class="pa-active-head">
                        <div>
                            <span class="pa-eyebrow">REPORTE DE NOTAS · <?= e(mb_strtoupper(ProyectoAula::momentoLabel((string)$proyecto['momento']))) ?></span>
                            <h2><?= e($proyecto['titulo']) ?></h2>
                            <p><?= e((string)($proyecto['descripcion'] ?? '')) ?></p>
                        </div>
                        <span class="badge <?= e($proyecto['estado']) ?>"><?= e(ucfirst((string)$proyecto['estado'])) ?></span>
                    </div>
                    <div class="pa-active-meta">
                        <span>📅 <?= e(ProyectoAula::periodoTexto($proyecto)) ?></span>
                        <span>👨‍🏫 <?= e($proyecto['docente_nombre'] ?? $user['nombre']) ?></span>
                        <span>🎓 <?= (int)$proyecto['grado'] ?>° "<?= e($proyecto['seccion']) ?>"</span>
                        <span>🗓️ Año escolar <?= e($proyecto['anio_escolar']) ?></span>
                    </div>
                </section>

                <!-- KPIs -->
                <div class="kpi-grid" style="margin:1.5rem 0;">
                    <div class="kpi-card"><h3>👥 Estudiantes</h3><p class="number"><?= count($resumen['rows']) ?></p></div>
                    <div class="kpi-card"><h3>📚 Asignaturas</h3><p class="number"><?= (int)$resumen['subjects'] ?></p></div>
                    <div class="kpi-card"><h3>📤 Entregas</h3><p class="number"><?= (int)$totales['entregados'] ?> / <?= (int)$totales['talleres'] ?></p></div>
                    <div class="kpi-card" style="border-left:4px solid var(--primary);"><h3>⭐ Promedio del Momento</h3><p class="number"><?= $totales['promedio'] !== null ? number_format((float)$totales['promedio'], 2) . '/20' : '—' ?></p></div>
                </div>

                <?php if ($individual !== null && $individual['alumno'] !== null): ?>
                    <?php $alumno = $individual['alumno']; $est = $individual['estatus']; ?>
                    <section class="data-section">
                        <div class="table-header">
                            <h2>👤 Reporte Individual: <?= e($alumno['nombre']) ?></h2>
                            <a class="btn-sm" href="reports.php?proyecto=<?= (int)$proyecto['id_proyecto'] ?>">← Ver toda la sección</a>
                        </div>

                        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.2rem;">
                            <div class="kpi-card" style="flex:1;min-width:160px;"><h3>Promedio del Momento</h3><p class="number"><?= $individual['promedio'] !== null ? number_format((float)$individual['promedio'], 2) . '/20' : '—' ?></p></div>
                            <div class="kpi-card" style="flex:1;min-width:160px;"><h3>Talleres</h3><p class="number"><?= (int)$alumno['entregados'] ?> / <?= (int)$alumno['talleres'] ?></p></div>
                            <div class="kpi-card" style="flex:1;min-width:160px;"><h3>Estatus</h3><p class="number" style="font-size:1rem;">
                                <?php if ($est !== null): ?>
                                    <span class="status" style="background:<?= e($est['bg_color']) ?>;color:<?= e($est['color']) ?>;"><?= e($est['icon']) ?> <?= e($est['nombre']) ?></span>
                                <?php else: ?>
                                    <span class="status" style="background:#f1f5f9;color:#475569;">Sin evaluaciones</span>
                                <?php endif; ?>
                            </p></div>
                        </div>

                        <h3 style="color:var(--sidebar-color);margin:1rem 0 .5rem;">Asignaturas cursadas en el momento</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Talleres</th>
                                    <th>Entregados</th>
                                    <th>Evaluados</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($alumno['detalle']) === 0): ?>
                                    <tr><td colspan="5" style="text-align:center;color:#5a7a6a;">Este PA aún no tiene asignaturas.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($alumno['detalle'] as $d): ?>
                                    <tr>
                                        <td><strong><?= e($d['titulo']) ?></strong></td>
                                        <td><?= (int)$d['talleres'] ?></td>
                                        <td><?= (int)$d['entregados'] ?></td>
                                        <td><?= (int)$d['evaluados'] ?></td>
                                        <td><strong><?= $d['promedio'] !== null ? number_format((float)$d['promedio'], 2) . '/20' : '—' ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background:#f6fbf6;border-top:2px solid var(--primary);">
                                    <td colspan="4" style="text-align:right;font-weight:700;">⭐ PROMEDIO DEL MOMENTO</td>
                                    <td><strong style="font-size:1.1rem;color:var(--primary);"><?= $individual['promedio'] !== null ? number_format((float)$individual['promedio'], 2) . '/20' : '—' ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <h3 style="color:var(--sidebar-color);margin:1.6rem 0 .5rem;">Talleres entregados y su estado</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Taller</th>
                                    <th>Unidad</th>
                                    <th>Fecha límite</th>
                                    <th>Estado</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($individual['talleres']) === 0): ?>
                                    <tr><td colspan="6" style="text-align:center;color:#5a7a6a;">Este momento aún no tiene talleres.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($individual['talleres'] as $t): ?>
                                    <tr>
                                        <td><?= e($t['asignatura']) ?></td>
                                        <td><strong><?= e($t['taller']) ?></strong></td>
                                        <td><?= $t['unidad'] !== null && $t['unidad'] !== '' ? e($t['unidad']) : '—' ?></td>
                                        <td><?= $t['fecha_limite'] ? e(date('d/m/Y', strtotime((string)$t['fecha_limite']))) : '—' ?></td>
                                        <td>
                                            <?php if ($t['estado'] === 'Evaluado'): ?>
                                                <span class="status" style="background:#dcfce7;color:#166534;">✅ Evaluado</span>
                                            <?php elseif ($t['estado'] === 'Entregado'): ?>
                                                <span class="status" style="background:#fef3c7;color:#92400e;">⏳ Entregado</span>
                                            <?php else: ?>
                                                <span class="status" style="background:#f1f5f9;color:#475569;">⚪ Sin entrega</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= $t['puntaje'] !== null ? number_format((float)$t['puntaje'], 1) . '/20' : '—' ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                <?php else: ?>
                    <!-- Reporte grupal del momento -->
                    <section class="data-section">
                        <div class="table-header">
                            <h2>Reporte grupal · <?= e(ProyectoAula::momentoLabel((string)$proyecto['momento'])) ?></h2>
                        </div>

                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Estudiante</th>
                                    <th>Asignaturas</th>
                                    <th>Talleres</th>
                                    <th>Notas</th>
                                    <th>Promedio</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($resumen['rows']) === 0): ?>
                                    <tr><td colspan="7" style="text-align:center;color:#5a7a6a;">No hay estudiantes en esta sección.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($resumen['rows'] as $i => $r): ?>
                                    <?php $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null; ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= e($r['nombre']) ?></strong></td>
                                        <td><?= (int)$r['asignaturas'] ?></td>
                                        <td><?= (int)$r['entregados'] ?> / <?= (int)$r['talleres'] ?></td>
                                        <td><?= (int)$r['porcentaje'] ?>%</td>
                                        <td><strong><?= $r['promedio'] !== null ? number_format((float)$r['promedio'], 2) : '—' ?></strong></td>
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
                                    <td colspan="2" style="text-align:right;font-weight:700;">TOTAL</td>
                                    <td><?= (int)$totales['asignaturas'] ?></td>
                                    <td><?= (int)$totales['entregados'] ?> / <?= (int)$totales['talleres'] ?></td>
                                    <td><?= (int)$totales['porcentaje'] ?>%</td>
                                    <td><strong style="font-size:1.1rem;color:var(--primary);"><?= $totales['promedio'] !== null ? number_format((float)$totales['promedio'], 2) . '/20' : '—' ?></strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="reporte-leyenda">
                            <strong>LEYENDA</strong>
                            <p><strong>Asignaturas:</strong> cantidad de asignaturas registradas en el PA.
                               <strong>Talleres:</strong> talleres entregados / talleres asignados.
                               <strong>Notas:</strong> porcentaje de entregas realizadas.
                               <strong>Promedio:</strong> promedio aritmético de las notas registradas.</p>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
