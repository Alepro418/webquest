<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$subjects = Asignatura::byDocente((int)$user['id']);
$entregas = Entrega::byDocente((int)$user['id']);

$asignaturaFilter = (int)($_GET['asignatura'] ?? 0);
$estadoFilter = $_GET['estado'] ?? '';
if ($asignaturaFilter > 0) {
    $entregas = array_values(array_filter($entregas, fn($e) => (int)$e['id_asignatura'] === $asignaturaFilter));
}
if ($estadoFilter === 'pendiente') {
    $entregas = array_values(array_filter($entregas, fn($e) => $e['puntaje'] === null));
} elseif ($estadoFilter === 'evaluado') {
    $entregas = array_values(array_filter($entregas, fn($e) => $e['puntaje'] !== null));
}

$pendientes = count(array_filter($entregas, fn($e) => $e['puntaje'] === null));
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Entregas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📥 Gestión de Entregas</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <div class="kpi-grid" style="margin-bottom:1.5rem;">
                <div class="kpi-card"><h3>Total Entregas</h3><p class="number"><?= count($entregas) ?></p></div>
                <div class="kpi-card" style="border-left:4px solid #f59e0b;"><h3>Pendientes por Evaluar</h3><p class="number"><?= $pendientes ?></p></div>
                <div class="kpi-card"><h3>Evaluadas</h3><p class="number"><?= count($entregas) - $pendientes ?></p></div>
            </div>

            <section class="data-section">
                <div class="table-header">
                    <h2>Entregas Registradas</h2>
                    <div class="filter-bar" style="margin:0;">
                        <form method="get" action="assignments.php" style="display:flex;gap:.5rem;align-items:center;">
                            <select name="asignatura" onchange="this.form.submit()">
                                <option value="0">Todas las asignaturas</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= (int)$s['id_asignatura'] ?>" <?= $asignaturaFilter === (int)$s['id_asignatura'] ? 'selected' : '' ?>><?= e($s['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="estado" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?= $estadoFilter === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                                <option value="evaluado" <?= $estadoFilter === 'evaluado' ? 'selected' : '' ?>>Evaluadas</option>
                            </select>
                        </form>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Asignatura</th>
                            <th>Taller</th>
                            <th>Fecha de Envío</th>
                            <th>Calificación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($entregas) === 0): ?>
                            <tr><td colspan="7" style="text-align:center;color:#5a7a6a;">No hay entregas con los filtros actuales.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($entregas as $en): ?>
                            <tr>
                                <td><strong><?= e($en['estudiante_nombre']) ?></strong><br><small style="color:#5a7a6a;">@<?= e($en['nombre_usuario']) ?></small></td>
                                <td><?= e($en['asignatura_titulo']) ?><br><small style="color:#5a7a6a;"><?= (int)$en['grado'] ?>° <?= e($en['seccion']) ?></small></td>
                                <td><?= e($en['taller_titulo']) ?></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($en['fecha_envio']))) ?></td>
                                <td>
                                    <?php if ($en['puntaje'] !== null): ?>
                                        <strong><?= number_format((float)$en['puntaje'], 1) ?></strong>
                                        <span style="font-size:.75rem;color:#5a7a6a;">(<?= e($en['escala_letra']) ?>)</span>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($en['puntaje'] !== null): ?>
                                        <span class="status st-bueno"><?= e($en['estatus']) ?></span>
                                    <?php else: ?>
                                        <span class="status" style="background:#fef3c7;color:#92400e;">⏳ Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="btn-sm btn-detail" href="<?= e(asset_url($en['ruta_fotografia'])) ?>" target="_blank" rel="noopener">🔍 Ver foto</a>
                                    <a class="btn-sm btn-detail" href="taller.php?id=<?= (int)$en['id_taller'] ?>"><?= $en['puntaje'] === null ? '✏️ Calificar' : '📝 Detalle' ?></a>
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