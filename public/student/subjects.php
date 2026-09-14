<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$subjects = Asignatura::forStudent((int)$user['grado'], $user['seccion']);
$progresos = [];
foreach ($subjects as $s) {
    $progresos[$s['id_asignatura']] = Reporte::progresoEstudianteEnAsignatura((int)$user['id'], $s);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asignaturas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📚 Mis Asignaturas</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <section class="data-section">
                <div class="table-header">
                    <h2>Asignaturas publicadas para tu cohorte (<?= (int)$user['grado'] ?>° "<?= e($user['seccion']) ?>")</h2>
                </div>

                <?php if (count($subjects) === 0): ?>
                    <p style="text-align:center;color:#5a7a6a;padding:2rem;">Tu docente aún no ha publicado asignaturas. Vuelve más tarde.</p>
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
                                <span>⭐ <?= $prog['promedio'] !== null ? number_format($prog['promedio'], 1) : '—' ?>/20</span>
                            </div>
                            <div class="subject-actions">
                                <a class="btn-sm" href="subject.php?id=<?= (int)$s['id_asignatura'] ?>">👁️ Ver Talleres y Entregar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>