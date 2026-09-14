<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$subjects = Asignatura::forStudent((int)$user['grado'], $user['seccion']);
$promedios = [];
$porAsignatura = [];
foreach ($subjects as $s) {
    $prog = Reporte::progresoEstudianteEnAsignatura((int)$user['id'], $s);
    $porAsignatura[] = [
        'titulo'    => $s['titulo'],
        'total'     => $prog['total'],
        'entregados'=> $prog['entregados'],
        'evaluados' => $prog['evaluados'],
        'promedio'  => $prog['promedio'],
    ];
    if ($prog['promedio'] !== null) {
        $promedios[] = (float)$prog['promedio'];
    }
}
$promedioGlobal = count($promedios) > 0 ? round(array_sum($promedios) / count($promedios), 1) : null;
$ultimas = Evaluacion::lastByStudent((int)$user['id'], 5);
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
                <h1>📊 Mi Rendimiento</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($promedioGlobal !== null): ?>
                <?php $est = Reporte::estatusConfig($promedioGlobal); ?>
                <section class="data-section">
                    <div style="padding:1.2rem;display:flex;gap:1.2rem;align-items:center;flex-wrap:wrap;background:<?= e($est['bg_color']) ?>;border-radius:12px;border:1px solid rgba(0,0,0,.05);">
                        <div style="font-size:2.2rem;"><?= e($est['icon']) ?></div>
                        <div style="flex:1;">
                            <h3 style="margin:0;color:<?= e($est['color']) ?>;"><?= e($est['nombre']) ?> — <?= number_format($promedioGlobal, 1) ?>/20</h3>
                            <p style="margin:.3rem 0 0;color:<?= e($est['color']) ?>;"><?= e($est['message']) ?></p>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

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
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($porAsignatura) === 0): ?>
                            <tr><td colspan="6" style="text-align:center;color:#5a7a6a;">Sin asignaturas publicadas para tu cohorte.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($porAsignatura as $r): ?>
                            <?php $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null; ?>
                            <tr>
                                <td><strong><?= e($r['titulo']) ?></strong></td>
                                <td><?= $r['total'] ?></td>
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
                </table>
            </section>

            <section class="data-section">
                <div class="table-header"><h2>📜 Últimas Evaluaciones</h2></div>
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
                            <tr><td colspan="6" style="text-align:center;color:#5a7a6a;">Aún no tienes evaluaciones.</td></tr>
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
        </main>
    </div>
</body>
</html>