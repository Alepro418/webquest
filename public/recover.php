<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
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
        
        .recover-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .recover-header h1 {
            color: var(--sidebar-color);
            font-size: 1.8rem;
        }
        
        .recover-header p {
            color: #64748b;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #475569;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: var(--sidebar-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #0f172a;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            padding: 0.8rem 1.5rem;
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .alert-success {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
            color: #166534;
        }
        
        .alert-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        
        .security-question {
            background: #f8fafc;
            padding: 1.2rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--sidebar-color);
        }
        
        .security-question p {
            font-weight: 600;
            color: var(--sidebar-color);
            margin-bottom: 0.5rem;
        }
        
        .security-question small {
            color: #64748b;
            display: block;
            margin-top: 0.3rem;
        }
        
        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            padding: 0 1rem;
        }
        
        .progress-step {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background: #f1f5f9;
            margin: 0 0.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #64748b;
        }
        
        .progress-step.active {
            background: var(--sidebar-color);
            color: white;
            font-weight: 600;
        }
        
        .progress-step.completed {
            background: #166534;
            color: white;
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .role-badge.docente {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .role-badge.estudiante {
            background: #dcfce7;
            color: #166534;
        }
        
        .hidden {
            display: none;
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

            <!-- Indicador de progreso -->
            <div class="progress-indicator" id="progressIndicator">
                <div class="progress-step active" id="step1-indicator">1. Verificar Usuario</div>
                <div class="progress-step" id="step2-indicator">2. Validar Identidad</div>
                <div class="progress-step" id="step3-indicator">3. Nueva Contraseña</div>
            </div>

            <!-- Mensajes de error/éxito -->
            <div id="error-container"></div>

            <!-- PASO 1: Formulario de usuario -->
            <div id="step1-content">
                <form id="step1-form" onsubmit="return verifyUser(event)">
                    <div class="form-group">
                        <label for="username">👤 Nombre de Usuario</label>
                        <input type="text" id="username" name="username" placeholder="Ingresa tu nombre de usuario" required autofocus>
                        <small style="color: #64748b; display: block; margin-top: 0.3rem;">Ingresa el usuario con el que te registraste</small>
                    </div>
                    
                    <button type="submit" class="btn-primary" id="verifyBtn">
                        <span id="verify-text">Verificar Usuario</span>
                        <span id="verify-spinner" style="display: none;">⌛ Verificando...</span>
                    </button>
                </form>
            </div>

            <!-- PASO 2: Formulario según rol (se muestra dinámicamente) -->
            <div id="step2-content" class="hidden">
                <!-- Contenido para DOCENTE -->
                <div id="docente-content" class="hidden">
                    <div class="alert alert-info">
                        <strong>📧 Verificación para Docente</strong>
                        <p>Te enviaremos un enlace de recuperación a tu correo electrónico registrado.</p>
                    </div>
                    
                    <form id="docente-form" onsubmit="return recoverDocente(event)">
                        <input type="hidden" id="docente-username" name="username">
                        
                        <div class="form-group">
                            <label for="docente-email">📧 Correo Electrónico</label>
                            <input type="email" id="docente-email" name="email" placeholder="tucorreo@ejemplo.com" required>
                            <small style="color: #64748b;">Ingresa el correo que usaste al registrarte</small>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                            <button type="button" class="btn-secondary" onclick="goToStep1()">← Atrás</button>
                            <button type="submit" class="btn-primary" style="flex: 1;">Enviar Enlace</button>
                        </div>
                    </form>
                </div>

                <!-- Contenido para ESTUDIANTE (5 preguntas) -->
                <div id="estudiante-content" class="hidden">
                    <div class="alert alert-info">
                        <strong>❓ Verificación para Estudiante</strong>
                        <p>Responde las 5 preguntas de seguridad que configuraste al registrarte.</p>
                    </div>
                    
                    <form id="estudiante-form" onsubmit="return verifyStudentAnswers(event)">
                        <input type="hidden" id="estudiante-username" name="username">
                        
                        <!-- Las preguntas se cargarán dinámicamente aquí -->
                        <div id="questions-container"></div>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                            <button type="button" class="btn-secondary" onclick="goToStep1()">← Atrás</button>
                            <button type="submit" class="btn-primary" style="flex: 1;">Verificar Respuestas</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PASO 3: Nueva Contraseña -->
            <div id="step3-content" class="hidden">
                <form id="reset-password-form" onsubmit="return resetPassword(event)">
                    <input type="hidden" id="reset-username" name="username">
                    <input type="hidden" id="reset-token" name="token">
                    
                    <div class="alert alert-success">
                        <strong>✅ Identidad verificada</strong>
                        <p>Ahora puedes crear una nueva contraseña.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="new-password">🔑 Nueva Contraseña</label>
                        <input type="password" id="new-password" name="new_password" placeholder="Mínimo 8 caracteres" required onkeyup="checkPasswordStrength()">
                        
                        <!-- Medidor de fortaleza -->
                        <div style="margin-top: 0.5rem;">
                            <div style="height: 8px; background: #e2e8f0; border-radius: 4px;">
                                <div id="reset-strength-bar" style="height: 8px; width: 0%; background: #ef4444; border-radius: 4px; transition: width 0.3s;"></div>
                            </div>
                            <span id="reset-strength-text" style="font-size: 0.8rem;">Débil</span>
                        </div>
                        
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">
                            Requisitos: 8 caracteres, mayúscula, minúscula, número y carácter especial
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">✅ Confirmar Contraseña</label>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Repite tu contraseña" required onkeyup="checkPasswordMatch()">
                        <span id="reset-password-match" style="font-size: 0.8rem;"></span>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn-secondary" onclick="goToStep2()">← Atrás</button>
                        <button type="submit" class="btn-primary" style="flex: 1;">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>

            <div class="back-link">
                <a href="sign_in.php">← Volver a Iniciar Sesión</a>
            </div>
        </div>

        <!-- Pie de página con fecha -->
        <div style="text-align: center; margin-top: 2rem; color: #94a3b8;">
            <?php include 'functions/date.php'; ?>
        </div>
    </main>

    <script>
        // Variables globales
        let currentStep = 1;
        let currentRole = null;
        let currentUsername = null;
        
        // PASO 1: Verificar usuario
        async function verifyUser(event) {
            event.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            
            if (!username) {
                showError('Por favor ingresa un nombre de usuario');
                return false;
            }
            
            // Mostrar loading
            document.getElementById('verify-text').style.display = 'none';
            document.getElementById('verify-spinner').style.display = 'inline';
            
            try {
                // Simular llamada AJAX (esto luego será reemplazado por fetch real)
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // SIMULACIÓN: Aquí iría la llamada real al backend
                // Por ahora, usamos datos de ejemplo
                const response = await mockVerifyUser(username);
                
                if (response.success) {
                    currentRole = response.role;
                    currentUsername = username;
                    
                    // Actualizar UI según rol
                    if (response.role === 'docente') {
                        document.getElementById('docente-username').value = username;
                        showStep2Docente();
                    } else {
                        document.getElementById('estudiante-username').value = username;
                        // Cargar preguntas del estudiante
                        loadStudentQuestions(username);
                    }
                    
                    // Actualizar indicador de progreso
                    updateProgress(2);
                    
                } else {
                    showError('Usuario no encontrado. Verifica el nombre ingresado.');
                }
                
            } catch (error) {
                showError('Error al verificar usuario. Intenta de nuevo.');
            } finally {
                // Ocultar loading
                document.getElementById('verify-text').style.display = 'inline';
                document.getElementById('verify-spinner').style.display = 'none';
            }
            
            return false;
        }
        
        // SIMULACIÓN: Función temporal para pruebas
        async function mockVerifyUser(username) {
            // Esto es solo para pruebas - luego será reemplazado por llamada real
            const testUsers = {
                'profesor': { success: true, role: 'docente' },
                'juanito': { success: true, role: 'estudiante' },
                'maria': { success: true, role: 'estudiante' }
            };
            
            return testUsers[username.toLowerCase()] || { success: false };
        }
        
        // Cargar preguntas del estudiante
        async function loadStudentQuestions(username) {
            try {
                // Simular carga de preguntas
                const questions = [
                    "¿Cómo se llama tu mascota?",
                    "¿Cuál es tu comida favorita?",
                    "¿En qué ciudad naciste?",
                    "¿Quién es tu superhéroe favorito?",
                    "¿Cuál es tu color favorito?"
                ];
                
                const container = document.getElementById('questions-container');
                container.innerHTML = '';
                
                questions.forEach((question, index) => {
                    const questionHtml = `
                        <div class="security-question">
                            <p>❓ Pregunta ${index + 1}: ${question}</p>
                            <input type="text" 
                                   id="answer-${index + 1}" 
                                   name="answer_${index + 1}" 
                                   placeholder="Tu respuesta" 
                                   required 
                                   style="width: 100%; padding: 0.8rem; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <small>Responde exactamente como lo hiciste al registrarte</small>
                        </div>
                    `;
                    container.innerHTML += questionHtml;
                });
                
                showStep2Estudiante();
                
            } catch (error) {
                showError('Error al cargar preguntas de seguridad');
            }
        }
        
        // Verificar respuestas del estudiante
        async function verifyStudentAnswers(event) {
            event.preventDefault();
            
            const answers = [];
            for (let i = 1; i <= 5; i++) {
                const answer = document.getElementById(`answer-${i}`).value.trim();
                if (!answer) {
                    showError(`Por favor responde la pregunta ${i}`);
                    return false;
                }
                answers.push(answer);
            }
            
            try {
                // Simular verificación de respuestas
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // SIMULACIÓN: Aquí iría la validación real
                const allCorrect = true; // Esto cambiará con la validación real
                
                if (allCorrect) {
                    showStep3();
                    updateProgress(3);
                } else {
                    showError('Una o más respuestas son incorrectas');
                }
                
            } catch (error) {
                showError('Error al verificar respuestas');
            }
            
            return false;
        }
        
        // Recuperación para docente
        async function recoverDocente(event) {
            event.preventDefault();
            
            const email = document.getElementById('docente-email').value;
            
            if (!email.includes('@') || !email.includes('.')) {
                showError('Ingresa un correo electrónico válido');
                return false;
            }
            
            try {
                // Simular envío de correo
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                showSuccess('Se ha enviado un enlace de recuperación a tu correo electrónico');
                
                // Redirigir al login después de 3 segundos
                setTimeout(() => {
                    window.location.href = 'sign_in.php';
                }, 3000);
                
            } catch (error) {
                showError('Error al enviar el correo');
            }
            
            return false;
        }
        
        // Restablecer contraseña
        async function resetPassword(event) {
            event.preventDefault();
            
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Validaciones
            if (newPassword.length < 8) {
                showError('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
            
            if (!/[A-Z]/.test(newPassword)) {
                showError('La contraseña debe tener al menos una mayúscula');
                return false;
            }
            
            if (!/[a-z]/.test(newPassword)) {
                showError('La contraseña debe tener al menos una minúscula');
                return false;
            }
            
            if (!/[0-9]/.test(newPassword)) {
                showError('La contraseña debe tener al menos un número');
                return false;
            }
            
            if (!/[!@#$%^&*]/.test(newPassword)) {
                showError('La contraseña debe tener al menos un carácter especial (!@#$%^&*)');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                showError('Las contraseñas no coinciden');
                return false;
            }
            
            try {
                // Simular actualización
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                showSuccess('¡Contraseña actualizada exitosamente! Redirigiendo al login...');
                
                setTimeout(() => {
                    window.location.href = 'sign_in.php';
                }, 2000);
                
            } catch (error) {
                showError('Error al actualizar la contraseña');
            }
            
            return false;
        }
        
        // Funciones de navegación
        function showStep2Docente() {
            document.getElementById('step1-content').classList.add('hidden');
            document.getElementById('step2-content').classList.remove('hidden');
            document.getElementById('docente-content').classList.remove('hidden');
            document.getElementById('estudiante-content').classList.add('hidden');
            currentStep = 2;
        }
        
        function showStep2Estudiante() {
            document.getElementById('step1-content').classList.add('hidden');
            document.getElementById('step2-content').classList.remove('hidden');
            document.getElementById('estudiante-content').classList.remove('hidden');
            document.getElementById('docente-content').classList.add('hidden');
            currentStep = 2;
        }
        
        function showStep3() {
            document.getElementById('step2-content').classList.add('hidden');
            document.getElementById('step3-content').classList.remove('hidden');
            document.getElementById('reset-username').value = currentUsername;
            currentStep = 3;
        }
        
        function goToStep1() {
            document.getElementById('step1-content').classList.remove('hidden');
            document.getElementById('step2-content').classList.add('hidden');
            document.getElementById('step3-content').classList.add('hidden');
            document.getElementById('error-container').innerHTML = '';
            updateProgress(1);
            currentStep = 1;
        }
        
        function goToStep2() {
            document.getElementById('step2-content').classList.remove('hidden');
            document.getElementById('step3-content').classList.add('hidden');
            updateProgress(2);
            currentStep = 2;
        }
        
        function updateProgress(step) {
            // Actualizar indicadores visuales
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step${i}-indicator`);
                if (i < step) {
                    indicator.className = 'progress-step completed';
                } else if (i === step) {
                    indicator.className = 'progress-step active';
                } else {
                    indicator.className = 'progress-step';
                }
            }
        }
        
        // Funciones de utilidad
        function showError(message) {
            document.getElementById('error-container').innerHTML = `
                <div class="alert alert-error">${message}</div>
            `;
            setTimeout(() => {
                document.getElementById('error-container').innerHTML = '';
            }, 5000);
        }
        
        function showSuccess(message) {
            document.getElementById('error-container').innerHTML = `
                <div class="alert alert-success">${message}</div>
            `;
        }
        
        // Medidor de fortaleza de contraseña
        function checkPasswordStrength() {
            const password = document.getElementById('new-password').value;
            const strengthBar = document.getElementById('reset-strength-bar');
            const strengthText = document.getElementById('reset-strength-text');
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*]/.test(password)) strength++;
            
            strengthBar.className = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthBar.style.width = '20%';
                    strengthBar.style.background = '#ef4444';
                    strengthText.textContent = 'Débil';
                    break;
                case 2:
                case 3:
                    strengthBar.style.width = '60%';
                    strengthBar.style.background = '#f59e0b';
                    strengthText.textContent = 'Media';
                    break;
                case 4:
                case 5:
                    strengthBar.style.width = '100%';
                    strengthBar.style.background = '#10b981';
                    strengthText.textContent = 'Fuerte';
                    break;
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('new-password').value;
            const confirm = document.getElementById('confirm-password').value;
            const matchSpan = document.getElementById('reset-password-match');
            
            if (confirm === '') {
                matchSpan.textContent = '';
            } else if (password === confirm) {
                matchSpan.textContent = '✅ Las contraseñas coinciden';
                matchSpan.style.color = '#166534';
            } else {
                matchSpan.textContent = '❌ Las contraseñas no coinciden';
                matchSpan.style.color = '#991b1b';
            }
        }
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Cualquier inicialización necesaria
        });
    </script>
</body>
</html>