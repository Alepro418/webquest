<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <title>Registro</title>
</head>
<body>
    <main class="main-content">
        <h1>Registro de Nuevo Usuario</h1>
        <div class="date-info"><?php include 'functions/date.php' ?></div>

        <div class="role-selection">
            <button class="role-btn" onclick="setRole('estudiante')" id="btn-estudiante">Estudiante</button>
            <button class="role-btn" onclick="setRole('docente')" id="btn-docente">Docente</button>
        </div>

        <!-- Mensaje de seguridad para estudiantes -->
        <div id="student-warning" class="info-message warning">
            <strong>¡Importante!</strong> El registro del estudiante debe hacerse en compañia de algún adulto, tutor o responsable.
            <strong>Bajo ningún motivo se le pedirá al menor ingresar alguna dirección de correo electrónico</strong> o información que comprometa su seguridad o privacidad.
            Por tal motivo se le recomienda al tutor anotar las preguntas de seguridad en caso de olvido de contraseña.
        </div>

        <section class="data-section" style="max-width: 900px; margin: 2rem auto;">
            <div class="table-header">
                <h2>Datos Personales</h2>
            </div>

            <form action="../src/core/auth.php" method="post" class="form-registro-horizontal" id="registerForm" onsubmit="return validateForm(event)">
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

                <!-- Fila 2: Email (solo docentes) y Contraseña -->
                <div class="form-row">
    <div id="email-field" class="form-group hidden">
        <label for="email" class="required">Correo Electrónico</label>
        <input type="email" name="email" id="email" placeholder="ejemplo@correo.com">
    </div>

    <div class="form-group">
        <label for="password" class="required">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required onkeyup="checkPasswordStrength()">
        <div class="password-strength">
            <div class="strength-bar"><div class="strength-bar-fill" id="strength-bar"></div></div>
            <span id="strength-text"></span>
        </div>
    </div>
</div>

                <!-- Preguntas de seguridad solo para estudiantes - Diseño Horizontal -->
                <div id="security-questions" class="security-questions-horizontal hidden">
                    <h4>🔐 Preguntas de Seguridad</h4>
                    <p class="security-info">
                        Elige 3 preguntas y respuestas que recordarás fácilmente. 
                        Las usarás para recuperar tu contraseña.
                    </p>

                    <!-- Pregunta 1 -->
                    <div class="security-row">
                        <div class="question-select">
                            <label for="pregunta1" class="required">Pregunta 1</label>
                            <select id="pregunta1" name="pregunta1">
                                <option value="">Selecciona...</option>
                                <option value="mascota">¿Cómo se llama tu mascota?</option>
                                <option value="comida">¿Cuál es tu comida favorita?</option>
                                <option value="juego">¿Cuál es tu juego favorito?</option>
                                <option value="superheroe">¿Quién es tu superhéroe favorito?</option>
                                <option value="color">¿Cuál es tu color favorito?</option>
                                <option value="libro">¿Cuál es tu libro favorito?</option>
                            </select>
                        </div>
                        <div class="question-answer">
                            <label for="respuesta1" class="required">Respuesta 1</label>
                            <input type="text" id="respuesta1" name="respuesta1" placeholder="Tu respuesta">
                        </div>
                    </div>

                    <!-- Pregunta 2 -->
                    <div class="security-row">
                        <div class="question-select">
                            <label for="pregunta2" class="required">Pregunta 2</label>
                            <select id="pregunta2" name="pregunta2">
                                <option value="">Selecciona...</option>
                                <option value="mascota">¿Cómo se llama tu mascota?</option>
                                <option value="comida">¿Cuál es tu comida favorita?</option>
                                <option value="juego">¿Cuál es tu juego favorito?</option>
                                <option value="superheroe">¿Quién es tu superhéroe favorito?</option>
                                <option value="color">¿Cuál es tu color favorito?</option>
                                <option value="libro">¿Cuál es tu libro favorito?</option>
                            </select>
                        </div>
                        <div class="question-answer">
                            <label for="respuesta2" class="required">Respuesta 2</label>
                            <input type="text" id="respuesta2" name="respuesta2" placeholder="Tu respuesta">
                        </div>
                    </div>

                    <!-- Pregunta 3 -->
                    <div class="security-row">
                        <div class="question-select">
                            <label for="pregunta3" class="required">Pregunta 3</label>
                            <select id="pregunta3" name="pregunta3">
                                <option value="">Selecciona...</option>
                                <option value="mascota">¿Cómo se llama tu mascota?</option>
                                <option value="comida">¿Cuál es tu comida favorita?</option>
                                <option value="juego">¿Cuál es tu juego favorito?</option>
                                <option value="superheroe">¿Quién es tu superhéroe favorito?</option>
                                <option value="color">¿Cuál es tu color favorito?</option>
                                <option value="libro">¿Cuál es tu libro favorito?</option>
                            </select>
                        </div>
                        <div class="question-answer">
                            <label for="respuesta3" class="required">Respuesta 3</label>
                            <input type="text" id="respuesta3" name="respuesta3" placeholder="Tu respuesta">
                        </div>
                    </div>
                

                    <!-- Pregunta 4 -->
                    <div class="security-row">
                        <div class="question-select">
                            <label for="pregunta4" class="required">Pregunta 4</label>
                            <select id="pregunta4" name="pregunta4">
                                <option value="">Selecciona...</option>
                                <option value="mascota">¿Cómo se llama tu mascota?</option>
                                <option value="comida">¿Cuál es tu comida favorita?</option>
                                <option value="juego">¿Cuál es tu juego favorito?</option>
                                <option value="superheroe">¿Quién es tu superhéroe favorito?</option>
                                <option value="color">¿Cuál es tu color favorito?</option>
                                <option value="libro">¿Cuál es tu libro favorito?</option>
                            </select>
                        </div>
                        <div class="question-answer">
                            <label for="respuesta4" class="required">Respuesta 4</label>
                            <input type="text" id="respuesta4" name="respuesta4" placeholder="Tu respuesta">
                        </div>
                    </div>

                    <!-- Pregunta 5 -->
                    <div class="security-row">
                        <div class="question-select">
                            <label for="pregunta5" class="required">Pregunta 5</label>
                            <select id="pregunta5" name="pregunta5">
                                <option value="">Selecciona...</option>
                                <option value="mascota">¿Cómo se llama tu mascota?</option>
                                <option value="comida">¿Cuál es tu comida favorita?</option>
                                <option value="juego">¿Cuál es tu juego favorito?</option>
                                <option value="superheroe">¿Quién es tu superhéroe favorito?</option>
                                <option value="color">¿Cuál es tu color favorito?</option>
                                <option value="libro">¿Cuál es tu libro favorito?</option>
                            </select>
                        </div>
                        <div class="question-answer">
                            <label for="respuesta5" class="required">Respuesta 5</label>
                            <input type="text" id="respuesta5" name="respuesta5" placeholder="Tu respuesta">
                        </div>
                    </div>
                </div>

                <!-- Botón de registro centrado -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Registrarse</button>
                </div>
            </form>

            <div class="footer-text">
                <p>¿Ya tienes cuenta? <a href="sign_in.php">Inicia sesión</a></p>
            </div>
        </section>

        <script src="js/record.js"></script>
    </main>
</body>
</html>