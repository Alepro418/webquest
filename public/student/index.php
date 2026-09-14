<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$subjects = Asignatura::forStudent((int)$user['grado'], $user['seccion']);
$entregas = Entrega::byStudent((int)$user['id']);
$promedio = Evaluacion::promedioDeEstudiante((int)$user['id']);

$progresos = [];
foreach ($subjects as $s) {
    $progresos[$s['id_asignatura']] = Reporte::progresoEstudianteEnAsignatura((int)$user['id'], $s);
}

$evaluadas = count(array_filter($entregas, fn($e) => $e['puntaje'] !== null));
$pendientes = count(array_filter($entregas, fn($e) => $e['puntaje'] === null));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Estudiante - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>🌿 ¡Hola, <?= e(explode(' ', $user['nombre'])[0]) ?>!</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <!-- Estatus motivacional destacado -->
            <?php if ($promedio !== null): ?>
                <?php $est = Reporte::estatusConfig((float)$promedio); ?>
                <section class="motivacion-card" style="display:flex;gap:1.2rem;align-items:center;background:<?= e($est['bg_color']) ?>;background-size:cover;padding:1.5rem 2rem;border-radius:14px;margin-bottom:1.5rem;border:1px solid rgba(0,0,0,.05);">
                    <div style="font-size:2.5rem;"><?= e($est['icon']) ?></div>
                    <div>
                        <h2 style="color:<?= e($est['color']) ?>;margin:0;"><?= e($est['nombre']) ?></h2>
                        <p style="color:<?= e($est['color']) ?>;margin:.3rem 0 0;"><?= e($est['message']) ?>. Tu promedio actual es <strong><?= number_format((float)$promedio, 1) ?>/20</strong>.</p>
                    </div>
                </section>
            <?php else: ?>
                <section class="data-section">
                    <div style="padding:1rem 0;text-align:center;color:#5a7a6a;">
                        <p style="margin:0;">🌱 Aún no tienes evaluaciones. <a href="subjects.php" style="color:var(--primary);">Explora tus asignaturas</a> y entrega tus talleres para empezar tu progreso.</p>
                    </div>
                </section>
            <?php endif; ?>

            <!-- KPIs -->
            <section class="kpi-grid">
                <div class="kpi-card"><h3>📚 Asignaturas</h3><p class="number"><?= count($subjects) ?></p></div>
                <div class="kpi-card"><h3>📤 Entregas</h3><p class="number"><?= count($entregas) ?></p></div>
                <div class="kpi-card"><h3>✅ Evaluadas</h3><p class="number"><?= $evaluadas ?></p></div>
                <div class="kpi-card"><h3>⏳ Pendientes</h3><p class="number"><?= $pendientes ?></p></div>
            </section>

            <!-- Asignaturas y progreso -->
            <section class="data-section">
                <div class="table-header">
                    <h2>Mis Asignaturas</h2>
                </div>

                <?php if (count($subjects) === 0): ?>
                    <p style="text-align:center;color:#5a7a6a;padding:1.5rem;">Tu docente aún no ha publicado asignaturas para tu cohorte (<?= (int)$user['grado'] ?>° "<?= e($user['seccion']) ?>").</p>
                <?php endif; ?>

                <div class="subjects-grid">
                    <?php foreach ($subjects as $s): ?>
                        <?php $prog = $progresos[$s['id_asignatura']]; ?>
                        <div class="subject-card">
                            <div class="subject-header">
                                <h3 class="subject-title"><?= e($s['titulo']) ?></h3>
                                <span class="badge publicada"><?= (int)$s['grado'] ?>° <?= e($s['seccion']) ?></span>
                            </div>
                            <p style="color:#64748b;font-size:.9rem;"><?= e($s['descripcion']) ?></p>
                            <div class="subject-meta">
                                <span>📝 <?= $prog['total'] ?> Talleres</span>
                                <span>📤 <?= $prog['entregados'] ?>/<?= $prog['total'] ?> Entregados</span>
                                <span>⭐ <?= $prog['promedio'] !== null ? number_format($prog['promedio'], 1) : '—' ?>/20</span>
                            </div>
                            <?php if ($prog['total'] > 0): ?>
                                <div style="height:8px;background:#e2e8f0;border-radius:5px;overflow:hidden;margin:0.6rem 0;">
                                    <div style="width:<?= (int)round($prog['entregados'] * 100 / $prog['total']) ?>%;height:100%;background:var(--primary);"></div>
                                </div>
                            <?php endif; ?>
                            <div class="subject-actions">
                                <a class="btn-sm" href="subject.php?id=<?= (int)$s['id_asignatura'] ?>">👁️ Ver Asignatura</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>