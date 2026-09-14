<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$subjects = Asignatura::byDocente((int)$user['id']);
$asignaturaId = (int)($_GET['asignatura'] ?? 0);
if ($asignaturaId === 0 && count($subjects) > 0) {
    $asignaturaId = (int)$subjects[0]['id_asignatura'];
}

$estudianteFilter = (int)($_GET['estudiante'] ?? 0);
$reporte = null;
$asignatura = null;

if ($asignaturaId > 0) {
    $asignatura = Asignatura::find($asignaturaId);
    if ($asignatura !== null && (int)$asignatura['id_docente'] !== (int)$user['id']) {
        $asignatura = null;
    }
}

if ($asignatura !== null) {
    $rows = Reporte::studentsByDocenteCohorte((int)$user['id']);
    $cohorte = array_values(array_filter($rows, fn($s) => (int)$s['grado'] === (int)$asignatura['grado'] && $s['seccion'] === $asignatura['seccion']));

    $reporte = [];
    foreach ($cohorte as $s) {
        $prog = Reporte::progresoEstudianteEnAsignatura((int)$s['id_usuario'], $asignatura);
        $reporte[] = [
            'id_usuario' => (int)$s['id_usuario'],
            'nombre'     => $s['nombre_completo'],
            'total'      => $prog['total'],
            'entregados' => $prog['entregados'],
            'evaluados'  => $prog['evaluados'],
            'promedio'   => $prog['promedio'],
        ];
    }
    usort($reporte, fn($a, $b) => $a['nombre'] <=> $b['nombre']);

    if ($estudianteFilter > 0) {
        $reporte = array_values(array_filter($reporte, fn($r) => $r['id_usuario'] === $estudianteFilter));
    }
}

$export = $_GET['export'] ?? '';
if ($export === 'csv' && $asignatura !== null && $reporte !== null) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $asignatura['titulo']) . '.csv"');
    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Asignatura', $asignatura['titulo'], 'Grado', (int)$asignatura['grado'] . '° ' . $asignatura['seccion']]);
    fputcsv($out, []);
    fputcsv($out, ['Estudiante', 'Talleres Totales', 'Entregados', 'Evaluados', 'Promedio', 'Estatus']);
    foreach ($reporte as $r) {
        $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null;
        fputcsv($out, [
            $r['nombre'],
            $r['total'],
            $r['entregados'],
            $r['evaluados'],
            $r['promedio'] !== null ? number_format($r['promedio'], 1) : 'Sin evaluaciones',
            $est['nombre'] ?? '—',
        ]);
    }
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
                <h1>📊 Reportes de Rendimiento</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>

            <div class="filter-bar">
                <form method="get" action="reports.php" style="display:flex;gap:.5rem;align-items:center;width:100%;">
                    <select name="asignatura" onchange="this.form.submit()">
                        <?php if (count($subjects) === 0): ?>
                            <option value="0">Aún no tienes asignaturas</option>
                        <?php endif; ?>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id_asignatura'] ?>" <?= $asignaturaId === (int)$s['id_asignatura'] ? 'selected' : '' ?>><?= e($s['titulo']) ?> (<?= (int)$s['grado'] ?>° <?= e($s['seccion']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($reporte !== null): ?>
                        <select name="estudiante" onchange="this.form.submit()">
                            <option value="0">Todos los estudiantes</option>
                            <?php foreach (Reporte::studentsByDocenteCohorte((int)$user['id']) as $r): ?>
                                <option value="<?= (int)$r['id_usuario'] ?>" <?= $estudianteFilter === (int)$r['id_usuario'] ? 'selected' : '' ?>><?= e($r['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <?php if ($asignatura !== null && $reporte !== null): ?>
                        <a class="btn-primary" href="reports.php?asignatura=<?= $asignaturaId ?>&estudiante=<?= $estudianteFilter ?>&export=csv" style="margin-left:auto;">⬇️ Exportar CSV</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($asignatura === null): ?>
                <section class="data-section">
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">Crea asignaturas para generar reportes de rendimiento.</p>
                </section>
            <?php else: ?>
                <section class="data-section">
                    <div class="table-header">
                        <h2><?= e($asignatura['titulo']) ?> — Cohorte <?= (int)$asignatura['grado'] ?>° <?= e($asignatura['seccion']) ?></h2>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Talleres Totales</th>
                                <th>Entregados</th>
                                <th>Evaluados</th>
                                <th>Promedio</th>
                                <th>Estatus Motivacional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte as $r): ?>
                                <?php $est = $r['promedio'] !== null ? Reporte::estatusConfig((float)$r['promedio']) : null; ?>
                                <tr>
                                    <td><strong><?= e($r['nombre']) ?></strong></td>
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
            <?php endif; ?>
        </main>
    </div>
</body>
</html>