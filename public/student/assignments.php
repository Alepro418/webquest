<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$entregas = Entrega::byStudent((int)$user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Entregas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📤 Historial de Entregas</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <section class="data-section">
                <div class="table-header">
                    <h2>Tus entregas registradas</h2>
                </div>

                <?php if (count($entregas) === 0): ?>
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">
                        Aún no has entregado ninguna evidencia. Visita <a href="subjects.php" style="color:var(--primary);">tus asignaturas</a> para empezar.
                    </p>
                <?php endif; ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Asignatura</th>
                            <th>Taller</th>
                            <th>Fecha de Envío</th>
                            <th>Evidencia</th>
                            <th>Calificación</th>
                            <th>Comentario del Docente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entregas as $en): ?>
                            <tr>
                                <td><?= e($en['asignatura_titulo']) ?><br><small style="color:#5a7a6a;"><?= (int)$en['grado'] ?>° <?= e($en['seccion']) ?></small></td>
                                <td><?= e($en['taller_titulo']) ?></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($en['fecha_envio']))) ?></td>
                                <td>
                                    <a class="btn-sm btn-detail" href="<?= e(asset_url($en['ruta_fotografia'])) ?>" target="_blank" rel="noopener">🔍 Ver foto</a>
                                </td>
                                <td>
                                    <?php if ($en['puntaje'] !== null): ?>
                                        <span class="status st-bueno">⭐ <?= number_format((float)$en['puntaje'], 1) ?> • <?= e($en['escala_letra']) ?></span>
                                    <?php else: ?>
                                        <span class="status" style="background:#fef3c7;color:#92400e;">⏳ En revisión</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:260px;">
                                    <?= e((string)($en['observaciones'] ?? '—')) ?>
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