<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$kpis    = Reporte::kpisDocente((int)$user['id']);
$students = Reporte::studentsByDocenteCohorte((int)$user['id']);

$gradoFilter = $_GET['grado'] ?? '';
$seccionFilter = $_GET['seccion'] ?? '';
if ($gradoFilter !== '') {
    $students = array_values(array_filter($students, fn($s) => (int)$s['grado'] === (int)$gradoFilter));
}
if ($seccionFilter !== '') {
    $students = array_values(array_filter($students, fn($s) => $s['seccion'] === $seccionFilter));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel General - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>🌿 Panel General: Cohorte</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1.5rem;"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('error')): ?>
                <div class="alert alert-error" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1.5rem;"><?= e($msg) ?></div>
            <?php endif; ?>

            <!-- Indicadores Clave de Rendimiento (KPI) -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <h3>Total Estudiantes</h3>
                    <p class="number"><?= $kpis['total_estudiantes'] ?></p>
                </div>
                <div class="kpi-card">
                    <h3>Entregas Hoy</h3>
                    <p class="number"><?= $kpis['entregas_hoy'] ?></p>
                </div>
                <div class="kpi-card">
                    <h3>Promedio Grupal</h3>
                    <p class="number"><?= $kpis['promedio_grupal'] ?></p>
                </div>
                <div class="kpi-card alert">
                    <h3>Por Mejorar</h3>
                    <p class="number"><?= $kpis['por_mejorar'] ?></p>
                </div>
            </section>

            <!-- Listado de estudiantes con filtros -->
            <section class="data-section">
                <div class="table-header">
                    <h2>Listado de Estudiantes</h2>
                    <div class="filter-bar" style="margin:0;">
                        <form method="get" action="index.php" style="display:flex;gap:0.5rem;align-items:center;">
                            <select name="grado" onchange="this.form.submit()">
                                <option value="">Todos los Grados</option>
                                <?php foreach (Config::get('academic.grados', []) as $g): ?>
                                    <option value="<?= $g ?>" <?= (int)$gradoFilter === (int)$g ? 'selected' : '' ?>>Grado <?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="seccion" onchange="this.form.submit()">
                                <option value="">Todas las Secciones</option>
                                <?php foreach (Config::get('academic.secciones', []) as $sec): ?>
                                    <option value="<?= $sec ?>" <?= $seccionFilter === $sec ? 'selected' : '' ?>>Sección <?= $sec ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Cohorte</th>
                            <th>Entregas</th>
                            <th>Promedio</th>
                            <th>Estatus Motivacional</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) === 0): ?>
                            <tr><td colspan="6" style="text-align:center;color:#5a7a6a;">No hay estudiantes en tu cohorte.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($students as $s): ?>
                            <?php $est = $s['promedio'] !== null ? Reporte::estatusConfig($s['promedio']) : null; ?>
                            <tr>
                                <td><strong><?= e($s['nombre_completo']) ?></strong></td>
                                <td><?= (int)$s['grado'] ?>° <?= e($s['seccion']) ?></td>
                                <td><?= $s['entregas'] ?></td>
                                <td><?= $s['promedio'] !== null ? number_format($s['promedio'], 1) : '—' ?></td>
                                <td>
                                    <?php if ($est !== null): ?>
                                        <span class="status" style="background:<?= e($est['bg_color']) ?>;color:<?= e($est['color']) ?>;">
                                            <?= e($est['icon']) ?> <?= e($est['nombre']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status" style="background:#f1f5f9;color:#475569;">Sin evaluaciones</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="reports.php?estudiante=<?= (int)$s['id_usuario'] ?>" class="btn-action">Ver Reporte</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>