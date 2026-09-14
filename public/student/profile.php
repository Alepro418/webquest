<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$subjects = Asignatura::forStudent((int)$user['grado'], $user['seccion']);
$entregas = Entrega::byStudent((int)$user['id']);
$promedio = Evaluacion::promedioDeEstudiante((int)$user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>👤 Mi Perfil</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <div style="background:var(--white);border-radius:14px;border:1px solid var(--border-light);padding:2rem;max-width:560px;margin-bottom:1.5rem;">
                <div style="display:flex;gap:1.2rem;align-items:center;">
                    <div class="avatar-mini profile" style="width:74px;height:74px;font-size:1.8rem;"><?= e(mb_strtoupper(mb_substr(trim($user['nombre']), 0, 1))) ?></div>
                    <div>
                        <h2 style="margin:0;color:var(--sidebar-color);"><?= e($user['nombre']) ?></h2>
                        <p style="margin:.2rem 0;color:#5a7a6a;">@<?= e($user['username']) ?></p>
                    </div>
                </div>
                <hr style="border:none;border-top:1px solid var(--border-light);margin:1.4rem 0;">
                <p><strong>Rol:</strong> Estudiante</p>
                <p><strong>Cohorte:</strong> <?= (int)$user['grado'] ?>° "<?= e($user['seccion']) ?>"</p>
                <p><strong>Asignaturas publicadas:</strong> <?= count($subjects) ?></p>
                <p><strong>Entregas realizadas:</strong> <?= count($entregas) ?></p>
                <p><strong>Promedio general:</strong> <?= $promedio !== null ? number_format($promedio, 1) . '/20' : '—' ?></p>
                <p><strong>Miembro desde:</strong> <?= e(date('d/m/Y', strtotime($user['registrado']))) ?></p>
            </div>
        </main>
    </div>
</body>
</html>