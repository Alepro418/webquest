<?php
require dirname(__DIR__) . '/src/core/bootstrap.php';

$step = $_GET['step'] ?? 1;
$type = $_GET['type'] ?? (($_SESSION['recovery']['role'] ?? null) === 'docente' ? 'docente' : 'estudiante');
$tokenMode = ($_GET['mode'] ?? '') === 'code';

$recoveryUser = isset($_SESSION['recovery']['user_id']) ? Usuario::byId((int)$_SESSION['recovery']['user_id']) : null;
$questions = [];
if ($recoveryUser !== null && $recoveryUser['rol'] === 'estudiante') {
    $questions = PreguntaSeguridad::questionsOfUser((int)$recoveryUser['id_usuario']);
}
$token = (string)($_GET['token'] ?? '');

$flashError = flash('error');
$flashSuccess = flash('success');
$flashInfo = flash('info');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Recuperar Contraseña - Webquest</title>
    <style>
        .recover-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .recover-header { text-align: center; margin-bottom: 2rem; }
        .recover-header h1 { color: var(--sidebar-color); font-size: 1.8rem; }
        .recover-header p { color: #64748b; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569; }
        .form-group input {
            width: 100%; padding: 0.8rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .btn-primary {
            width: 100%; padding: 1rem; background: var(--sidebar-color); color: white; border: none;
            border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s;
        }
        .btn-primary:hover { background: #0f172a; transform: translateY(-2px); }
        .btn-secondary {
            padding: 0.8rem 1.5rem; background: #f1f5f9; color: #475569; border: none; border-radius: 8px;
            font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover { background: #e2e8f0; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; }
        .alert-success { background: #dcfce7; border-left: 4px solid #22c55e; color: #166534; }
        .alert-info { background: #dbeafe; border-left: 4px solid #3b82f6; color: #1e40af; }
        .security-question {
            background: #f8fafc; padding: 1.2rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid var(--sidebar-color);
        }
        .security-question p { font-weight: 600; color: var(--sidebar-color); margin-bottom: 0.5rem; }
        .security-question small { color: #64748b; display: block; margin-top: 0.3rem; }
        .progress-indicator { display: flex; justify-content: space-between; margin: 2rem 0; padding: 0 1rem; }
        .progress-step {
            flex: 1; text-align: center; padding: 0.5rem; background: #f1f5f9; margin: 0 0.25rem;
            border-radius: 8px; font-size: 0.9rem; color: #64748b;
        }
        .progress-step.active { background: var(--sidebar-color); color: white; font-weight: 600; }
        .progress-step.completed { background: #166534; color: white; }
        .back-link { text-align: center; margin-top: 2rem; }
        .back-link a { color: var(--primary); text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .role-badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .role-badge.docente { background: #dbeafe; color: #1e40af; }
        .role-badge.estudiante { background: #dcfce7; color: #166534; }
        .hidden { display: none; }
        .recover-token {
            background: #fffbeb; border: 2px dashed #f59e0b; padding: 1rem; border-radius: 8px;
            font-family: monospace; font-size: 1.1rem; text-align: center; margin: 1rem 0; word-break: break-all;
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="recover-container">
            <div class="recover-header">
                <h1>🔐 Recuperar Contraseña</h1>
                <p>Te ayudaremos a restablecer tu acceso</p>
            </div>

            <div class="progress-indicator" id="progressIndicator">
                <div class="progress-step <?= (int)$step >= 1 ? 'active' : '' ?><?= (int)$step > 1 ? ' completed' : '' ?>" id="step1-indicator">1. Verificar Usuario</div>
                <div class="progress-step <?= (int)$step == 2 ? 'active' : '' ?><?= (int)$step > 2 ? ' completed' : '' ?>" id="step2-indicator">2. Validar Identidad</div>
                <div class="progress-step <?= (int)$step >= 3 ? 'active' : '' ?>" id="step3-indicator">3. Nueva Contraseña</div>
            </div>

            <?php if ($flashError): ?>
                <div class="alert alert-error"><?= e($flashError) ?></div>
            <?php endif; ?>
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success"><?= e($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashInfo): ?>
                <div class="alert alert-info"><?= e($flashInfo) ?></div>
            <?php endif; ?>

            <?php if ((int)$step === 1): ?>
                <!-- PASO 1: usuario -->
                <form action="../src/core/auth.php?action=verify_user" method="post">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="username">👤 Nombre de Usuario</label>
                        <input type="text" id="username" name="username" placeholder="Ingresa tu nombre de usuario" required autofocus>
                        <small style="color:#64748b;display:block;margin-top:0.3rem;">Ingresa el usuario con el que te registraste</small>
                    </div>
                    <button type="submit" class="btn-primary">Verificar Usuario</button>
                </form>

            <?php elseif ((int)$step === 2 && $type === 'docente'): ?>
                <!-- PASO 2 DOCENTE: correo -->
                <div class="alert alert-info">
                    <strong>📧 Verificación para Docente</strong> <span class="role-badge docente">Docente</span>
                    <p>El correo se comparará con el registrado en tu cuenta.</p>
                </div>
                <form action="../src/core/auth.php?action=request_reset" method="post">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="docente-email">📧 Correo Electrónico</label>
                        <input type="email" id="docente-email" name="email" placeholder="tucorreo@ejemplo.com" required autofocus>
                        <small style="color:#64748b;">Ingresa el correo que usaste al registrarte</small>
                    </div>
                    <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                        <a class="btn-secondary" href="recover.php">← Atrás</a>
                        <button type="submit" class="btn-primary" style="flex:1;">Continuar</button>
                    </div>
                </form>

            <?php elseif ((int)$step === 2 && $type === 'estudiante' && $recoveryUser !== null): ?>
                <!-- PASO 2 ESTUDIANTE: 5 preguntas -->
                <div class="alert alert-info">
                    <strong>❓ Verificación para Estudiante</strong> <span class="role-badge estudiante">Estudiante</span>
                    <p>Responde las 5 preguntas de seguridad que configuraste al registrarte.</p>
                </div>
                <form action="../src/core/auth.php?action=verify_answers" method="post">
                    <?= csrf_field() ?>
                    <?php foreach ($questions as $i => $question): ?>
                        <div class="security-question">
                            <p>❓ Pregunta <?= $i ?>: <?= e($question) ?></p>
                            <input type="text" id="answer-<?= $i ?>" name="answer_<?= $i ?>" placeholder="Tu respuesta" required style="width:100%;padding:0.8rem;border:2px solid #e2e8f0;border-radius:8px;">
                            <small>Responde exactamente como lo hiciste al registrarte</small>
                        </div>
                    <?php endforeach; ?>
                    <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                        <a class="btn-secondary" href="recover.php">← Atrás</a>
                        <button type="submit" class="btn-primary" style="flex:1;">Verificar Respuestas</button>
                    </div>
                </form>

            <?php elseif ((int)$step === 3 && $recoveryUser !== null): ?>
                <!-- PASO 3: nueva contraseña -->
                <div class="alert alert-success">
                    <strong>✅ Identidad verificada</strong>
                    <p>Ahora puedes crear una nueva contraseña, <?= e($recoveryUser['nombre_completo']) ?>.</p>
                </div>

                <?php if ($tokenMode && $token !== ''): ?>
                    <div class="alert alert-info">
                        <strong>✂️ Código de recuperación</strong>
                        <p>El envío de correo está desactivado en este entorno local. Usa este código en el campo del formulario:</p>
                    </div>
                    <div class="recover-token"><?= e($token) ?></div>
                <?php endif; ?>

                <form action="../src/core/auth.php?action=reset_password" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <?php if ($recoveryUser['rol'] === 'docente'): ?>
                        <div class="form-group">
                            <label for="recovery-token">🔑 Código de recuperación</label>
                            <input type="text" id="recovery-token" name="token_input" value="<?= e($token) ?>" readonly>
                            <small style="color:#64748b;">Código generado por el sistema (copiado automáticamente).</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="new-password">🔑 Nueva Contraseña</label>
                        <input type="password" id="new-password" name="new_password" placeholder="Mínimo 8 caracteres" required onkeyup="checkPasswordStrength()">
                        <div style="font-size:0.8rem;color:#64748b;margin-top:0.5rem;">Requisitos: 8 caracteres, mayúscula, minúscula, número y carácter especial</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm-password">✅ Confirmar Contraseña</label>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Repite tu contraseña" required onkeyup="checkPasswordMatch()">
                        <span id="reset-password-match" style="font-size:0.8rem;"></span>
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                        <button type="button" class="btn-secondary" onclick="history.back()">← Atrás</button>
                        <button type="submit" class="btn-primary" style="flex:1;">Actualizar Contraseña</button>
                    </div>
                </form>

            <?php else: ?>
                <div class="alert alert-error">La sesión de recuperación expiró. Comienza de nuevo.</div>
                <a class="btn-secondary" href="recover.php" style="width:100%;text-align:center;">Volver al inicio</a>
            <?php endif; ?>

            <div class="back-link">
                <a href="sign_in.php">← Volver a Iniciar Sesión</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem; color: #94a3b8;">
            <?php include 'functions/date.php'; ?>
        </div>
    </main>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('new-password').value;
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*]/.test(password)) strength++;

            const bar = document.getElementById('reset-strength-bar');
            const text = document.getElementById('reset-strength-text');
            if (!bar || !text) return;

            const niveles = ['Débil', 'Débil', 'Baja', 'Media', 'Fuerte', 'Muy fuerte'];
            const colores = ['#ef4444', '#ef4444', '#f59e0b', '#f59e0b', '#10b981', '#10b981'];
            bar.style.width = (strength * 20) + '%';
            bar.style.background = colores[strength] || '#ef4444';
            text.textContent = niveles[strength] || 'Débil';
            text.style.color = colores[strength] || '#ef4444';

            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            const password = document.getElementById('new-password').value;
            const confirm = document.getElementById('confirm-password').value;
            const span = document.getElementById('reset-password-match');
            if (!span) return;
            if (confirm === '') {
                span.textContent = '';
            } else if (password === confirm) {
                span.textContent = '✅ Las contraseñas coinciden';
                span.style.color = '#166534';
            } else {
                span.textContent = '❌ Las contraseñas no coinciden';
                span.style.color = '#991b1b';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const place = document.getElementById('new-password');
            if (place) {
                const wrapper = place.parentElement;
                const meter = document.createElement('div');
                meter.style.marginTop = '0.5rem';
                meter.innerHTML = `
                    <div style="height:8px;background:#e2e8f0;border-radius:4px;">
                        <div id="reset-strength-bar" style="height:8px;width:0%;background:#ef4444;border-radius:4px;transition:width 0.3s;"></div>
                    </div>
                    <span id="reset-strength-text" style="font-size:0.8rem;">Débil</span>`;
                wrapper.appendChild(meter);
            }
        });
    </script>
</body>
</html>