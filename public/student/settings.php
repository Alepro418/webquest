<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$flashSuccess = flash('success');
$flashError = flash('error');
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

            <section class="data-section">
                <div class="table-header"><h2>🔒 Cambiar Contraseña</h2></div>
                <form method="post" action="../src/core/auth.php?action=change_password">
                    <?= csrf_field() ?>
                    <div style="padding:1.2rem;max-width:520px;">
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
        </main>
    </div>
</body>
</html>