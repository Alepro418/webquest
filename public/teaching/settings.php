<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$flashSuccess = flash('success');
$flashError = flash('error');
$flashInfo = flash('info');

$paActivo = ProyectoAula::activo((int)$user['id']);
$bloqueo  = ProyectoAula::bloqueoContenido($paActivo);
$proyectos = ProyectoAula::byDocenteWithStats((int)$user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>⚙️ Configuración de la Cuenta</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>
            <?php if ($flashInfo): ?><div class="alert" style="background:#e0f2fe;border-left:4px solid #0ea5e9;color:#0c4a6e;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashInfo) ?></div><?php endif; ?>

            <!-- Configuración del Proyecto de Aula por momento -->
            <section class="data-section" style="margin-top:0;">
                <div class="table-header">
                    <h2>🗂️ Configuración de Proyectos de Aula</h2>
                    <a class="btn-primary" href="pa.php">⚙️ Ir a Configuración</a>
                </div>
                <div style="padding:0 1.2rem 1.2rem;">
                    <?php if ($paActivo === null): ?>
                        <p style="color:#92400e;background:#fef3c7;border-left:4px solid #f59e0b;padding:.8rem 1rem;border-radius:8px;">
                            ⚠️ Aún no has configurado ningún Proyecto de Aula. Sin un PA publicado no podrás crear asignaturas ni talleres.
                        </p>
                    <?php else: ?>
                        <p><strong>Momento activo:</strong> <?= e(ProyectoAula::momentoLabel((string)$paActivo['momento'])) ?> · <?= e($paActivo['titulo']) ?></p>
                        <p><strong>Periodo:</strong> <?= e(ProyectoAula::periodoTexto($paActivo)) ?> (<?= e(ProyectoAula::estadoPeriodo($paActivo)) ?>)</p>
                        <p><strong>Grado y sección:</strong> <?= (int)$paActivo['grado'] ?>° "<?= e($paActivo['seccion']) ?>" · <strong>Año escolar:</strong> <?= e($paActivo['anio_escolar']) ?></p>
                        <p><strong>Estado:</strong> <?= e(ucfirst((string)$paActivo['estado'])) ?></p>
                        <?php if ($bloqueo !== null): ?>
                            <p style="color:#92400e;background:#fef3c7;border-left:4px solid #f59e0b;padding:.8rem 1rem;border-radius:8px;">
                                ⛔ <?= e(ProyectoAula::mensajeBloqueo($bloqueo)) ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p style="color:#5a7a6a;font-size:.92rem;">
                        Tienes <?= count($proyectos) ?> Proyecto<?= count($proyectos) === 1 ? '' : 's' ?> de Aula configurado<?= count($proyectos) === 1 ? '' : 's' ?>.
                        Desde allí puedes asignar el PA de cada momento, sus fechas, el grado y la sección, y publicarlo o cerrarlo.
                    </p>
                </div>
            </section>

            <div class="settings-grid">
                <!-- Perfil -->
                <section class="data-section">
                    <div class="table-header"><h2>👤 Mi Perfil</h2></div>
                    <div style="padding:1.2rem;">
                        <p><strong>Nombre:</strong> <?= e($user['nombre']) ?></p>
                        <p><strong>Usuario:</strong> @<?= e($user['username']) ?></p>
                        <p><strong>Rol:</strong> Docente</p>
                        <p><strong>Correo:</strong> <?= e($user['email'] ?? '—') ?></p>
                        <p><strong>Miembro desde:</strong> <?= e(date('d/m/Y', strtotime($user['registrado']))) ?></p>
                    </div>
                </section>

                <!-- Cambiar contraseña -->
                <section class="data-section">
                    <div class="table-header"><h2>🔒 Cambiar Contraseña</h2></div>
                    <form method="post" action="../../src/core/auth.php?action=change_password">
                        <?= csrf_field() ?>
                        <div style="padding:1.2rem;">
                            <div class="form-group">
                                <label for="pass_actual">Contraseña actual</label>
                                <input type="password" id="pass_actual" name="pass_actual" required>
                            </div>
                            <div class="form-group">
                                <label for="pass_nueva">Nueva contraseña</label>
                                <input type="password" id="pass_nueva" name="pass_nueva" required minlength="<?= (int)Config::get('security.password.min_length', 8) ?>">
                                <small style="color:#5a7a6a;">Mínimo <?= (int)Config::get('security.password.min_length', 8) ?> caracteres con mayúscula, número y símbolo.</small>
                            </div>
                            <div class="form-group">
                                <label for="pass_confirm">Confirmar nueva contraseña</label>
                                <input type="password" id="pass_confirm" name="pass_confirm" required>
                            </div>
                            <button class="btn-primary">💾 Guardar Contraseña</button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>
</html>