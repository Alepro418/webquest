<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$grado   = (int)($user['grado'] ?? 0);
$seccion = (string)($user['seccion'] ?? '');

$proyectos = ($grado > 0 && $seccion !== '') ? Asignatura::proyectosDeCohorte($grado, $seccion) : [];

// Momento por defecto: el que está en curso; si ninguno, el último configurado.
$proyectoId = (int)($_GET['proyecto'] ?? 0);
if ($proyectoId === 0 && count($proyectos) > 0) {
    $porDefecto = null;
    foreach ($proyectos as $p) {
        if (ProyectoAula::enPeriodo($p)) {
            $porDefecto = (int)$p['id_proyecto'];
            break;
        }
    }
    $proyectoId = $porDefecto ?? (int)$proyectos[count($proyectos) - 1]['id_proyecto'];
}

$proyecto = $proyectoId > 0 ? ProyectoAula::find($proyectoId) : null;
$valido = $proyecto !== null
    && (int)$proyecto['grado'] === $grado
    && (string)$proyecto['seccion'] === $seccion
    && in_array($proyecto['estado'], ['publicada', 'cerrada'], true);
if (!$valido) {
    $proyecto = null;
    $proyectoId = 0;
}

$reporte = $proyecto !== null ? Reporte::reporteIndividual((int)$proyecto['id_proyecto'], (int)$user['id']) : null;
$alumno  = $reporte['alumno'] ?? null;
$est     = $reporte['estatus'] ?? null;

$ultimas = $proyecto !== null
    ? Evaluacion::lastByStudent((int)$user['id'], 5, (int)$proyecto['id_proyecto'])
    : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reportes - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📊 Mi Rendimiento por Momento</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if (count($proyectos) === 0): ?>
                <section class="data-section">
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">
                        Tu docente aún no ha publicado un Proyecto de Aula para tu cohorte
                        (<?= (int)$grado ?>° "<?= e($seccion) ?>"). Vuelve más tarde.
                    </p>
                </section>
            <?php else: ?>

            <div class="filter-bar">
                <form method="get" action="reports.php" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                    <label style="font-weight:600;">Momento</label>
                    <select name="proyecto" onchange="this.form.submit()">
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= (int)$p['id_proyecto'] ?>" <?= $proyectoId === (int)$p['id_proyecto'] ? 'selected' : '' ?>>
                                <?= e(ProyectoAula::momentoLabel((string)$p['momento'])) ?> · <?= e($p['titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($proyecto !== null && $reporte !== null): ?>
                <section class="pa-active">
                    <div class="pa-active-head">
                        <div>
                            <span class="pa-eyebrow"><?= e(mb_strtoupper(ProyectoAula::momentoLabel((string)$proyecto['momento']))) ?></span>
                            <h2><?= e($proyecto['titulo']) ?></h2>
                            <p><?= e((string)($proyecto['descripcion'] ?? '')) ?></p>
                        </div>
                        <span class="badge <?= e($proyecto['estado']) ?>"><?= e(ucfirst((string)$proyecto['estado'])) ?></span>
                    </div>
                    <div class="pa-active-meta">
                        <span>📅 <?= e(ProyectoAula::periodoTexto($proyecto)) ?></span>
                        <span>👨‍🏫 <?= e($proyecto['docente_nombre'] ?? 'Docente') ?></span>
                        <span>🎓 <?= (int)$proyecto['grado'] ?>° "<?= e($proyecto['seccion']) ?>"</span>
                    </div>
                </section>

                <?php if ($est !== null): ?>
                    <section class="data-section">
                        <div style="padding:1.2rem;display:flex;gap:1.2rem;align-items:center;flex-wrap:wrap;background:<?= e($est['bg_color']) ?>;border-radius:12px;border:1px solid rgba(0,0,0,.05);">
                            <div style="font-size:2.2rem;"><?= e($est['icon']) ?></div>
                            <div style="flex:1;">
                                <h3 style="margin:0;color:<?= e($est['color']) ?>;"><?= e($est['nombre']) ?> — <?= number_format((float)$reporte['promedio'], 2) ?>/20</h3>
                                <p style="margin:.3rem 0 0;color:<?= e($est['color']) ?>;"><?= e($est['message']) ?></p>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <div class="kpi-grid" style="margin:1.5rem 0;">
                    <div class="kpi-card"><h3>Promedio del Momento</h3><p class="number"><?= $reporte['promedio'] !== null ? number_format((float)$reporte['promedio'], 2) . '/20' : '—' ?></p></div>
                    <div class="kpi-card"><h3>Asignaturas</h3><p class="number"><?= count($reporte['asignaturas']) ?></p></div>
                    <div class="kpi-card"><h3>Talleres entregados</h3><p class="number"><?= $alumno !== null ? (int)$alumno['entregados'] . ' / ' . (int)$alumno['talleres'] : '—' ?></p></div>
                    <div class="kpi-card"><h3>Notas registradas</h3><p class="number"><?= $alumno !== null ? (int)$alumno['evaluados'] : 0 ?></p></div>
                </div>

                <section class="data-section">
                    <div class="table-header"><h2>Resumen por Asignatura</h2></div>
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
                            <?php if ($alumno === null || count($alumno['detalle']) === 0): ?>
                                <tr><td colspan="5" style="text-align:center;color:#5a7a6a;">Sin asignaturas publicadas en este momento.</td></tr>
                            <?php endif; ?>
                            <?php foreach (($alumno['detalle'] ?? []) as $d): ?>
                                <tr>
                                    <td><strong><?= e($d['titulo']) ?></strong></td>
                                    <td><?= (int)$d['talleres'] ?></td>
                                    <td><?= (int)$d['entregados'] ?></td>
                                    <td><?= (int)$d['evaluados'] ?></td>
                                    <td><strong><?= $d['promedio'] !== null ? number_format((float)$d['promedio'], 2) : '—' ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:#f6fbf6;border-top:2px solid var(--primary);">
                                <td colspan="4" style="text-align:right;font-weight:700;">⭐ PROMEDIO DEL MOMENTO</td>
                                <td><strong style="font-size:1.1rem;color:var(--primary);"><?= $reporte['promedio'] !== null ? number_format((float)$reporte['promedio'], 2) . '/20' : '—' ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </section>

                <section class="data-section">
                    <div class="table-header"><h2>Talleres del Momento y su Estado</h2></div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
                                <th>Taller</th>
                                <th>Fecha límite</th>
                                <th>Estado</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reporte['talleres']) === 0): ?>
                                <tr><td colspan="5" style="text-align:center;color:#5a7a6a;">Aún no hay talleres en este momento.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($reporte['talleres'] as $t): ?>
                                <tr>
                                    <td><?= e($t['asignatura']) ?></td>
                                    <td><strong><?= e($t['taller']) ?></strong></td>
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

                <section class="data-section">
                    <div class="table-header"><h2>📜 Últimas Evaluaciones del Momento</h2></div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
                                <th>Taller</th>
                                <th>Calificación</th>
                                <th>Escala</th>
                                <th>Observaciones</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($ultimas) === 0): ?>
                                <tr><td colspan="6" style="text-align:center;color:#5a7a6a;">Aún no tienes evaluaciones en este momento.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($ultimas as $ev): ?>
                                <tr>
                                    <td><?= e($ev['asignatura_titulo']) ?></td>
                                    <td><?= e($ev['taller_titulo']) ?></td>
                                    <td><strong><?= number_format((float)$ev['puntaje'], 1) ?>/20</strong></td>
                                    <td><?= e($ev['escala_letra']) ?> — <?= e($ev['estatus']) ?></td>
                                    <td style="max-width:280px;"><?= e((string)($ev['observaciones'] ?? '—')) ?></td>
                                    <td><?= e(date('d/m/Y', strtotime($ev['fecha_evaluacion']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php else: ?>
                <section class="data-section">
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">Este momento no está disponible para tu cohorte.</p>
                </section>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
