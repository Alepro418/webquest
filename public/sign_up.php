<?php
require dirname(__DIR__) . '/src/core/bootstrap.php';
$flashError = flash('error');
$flashSuccess = flash('success');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Registro</title>
</head>
<body>
    <main class="main-content">
        <h1>Registro de Nuevo Usuario</h1>
        <div class="date-info"><?php include 'functions/date.php' ?></div>

        <?php if ($flashError): ?>
            <div class="alert alert-error" style="max-width:900px;margin:1rem auto;">
                <?= e($flashError) ?>
            </div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" style="max-width:900px;margin:1rem auto;">
                <?= e($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <div class="role-selection">
            <button type="button" class="role-btn active" onclick="setRole('estudiante')" id="btn-estudiante">Estudiante</button>
            <button type="button" class="role-btn" onclick="setRole('docente')" id="btn-docente">Docente</button>
        </div>

        <!-- Contenedor dinámico para la advertencia de estudiantes -->
        <div id="warning-container"></div>

        <section class="data-section" style="max-width: 900px; margin: 2rem auto;">
            <div class="table-header">
                <h2>Datos Personales</h2>
            </div>

            <form action="../src/core/auth.php?action=register" method="post" class="form-registro-horizontal" id="registerForm" onsubmit="return validateForm(event)">
                <?= csrf_field() ?>
                <input type="hidden" name="role" id="role-field" value="estudiante">
                <!-- Fila 1: Nombre y Usuario -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="required">Nombre Completo</label>
                        <input type="text" id="name" name="name" placeholder="Ej. Pedro Pérez" required>
                    </div>

                    <div class="form-group">
                        <label for="username" class="required">Nombre de Usuario</label>
                        <input type="text" id="username" name="username" placeholder="Ej. El Mencho" required>
                    </div>
                </div>

                <!-- Fila 2: Email (dinámico) y Contraseña -->
                <div class="form-row" id="second-row">
                    <!-- El campo de email se insertará aquí dinámicamente -->
                    <div id="email-container"></div>

                    <div class="form-group">
                        <label for="password" class="required">Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Mínimo 8 caracteres" required>
                            <button type="button"
                                id="toggle-password"
                                class="toggle-password"
                                aria-label="Mostrar contraseña"
                                aria-pressed="false">👁️</button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar"><div class="strength-bar-fill" id="strength-bar"></div></div>
                            <span id="strength-text"></span>
                        </div>
                    </div>
                </div>

                <!-- Contenedor dinámico para las preguntas de seguridad (fuera del form-row) -->
                <div id="security-container"></div>

                <!-- Botón de registro centrado -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Registrarse</button>
                </div>
            </form>

            <div class="footer-text">
                <p>¿Ya tienes cuenta? <a href="sign_in.php">Inicia sesión</a></p>
            </div>
        </section>

        <script src="js/security-questions.js"></script>
        <script src="js/password.js"></script>
        <script src="js/record.js"></script>
    </main>
</body>
</html>