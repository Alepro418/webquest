/**
 * record.js
 * Responsabilidad: cambio de rol, validación general y envío del formulario de registro.
 *
 * Depende de:
 *   - security-questions.js  (buildSecurityQuestionsHTML, collectSecurityQuestions)
 *   - password.js            (calculatePasswordScore)
 * Ambos deben cargarse ANTES de este archivo.
 *
 * Expone:
 *   - setRole(role)
 *   - validateForm(event)
 */

let currentRole = 'estudiante';

// --- Plantillas HTML ---
const warningHTML = `
    <div id="student-warning" class="info-message warning">
        <strong>¡Importante!</strong> El registro del estudiante debe hacerse en compañía de algún adulto, tutor o responsable.
        <strong>Bajo ningún motivo se le pedirá al menor ingresar alguna dirección de correo electrónico</strong> o información que comprometa su seguridad o privacidad.
        Por tal motivo se le recomienda al tutor anotar las preguntas de seguridad en caso de olvido de contraseña.
    </div>
`;

const emailFieldHTML = `
    <div class="form-group" id="email-field">
        <label for="email" class="required">Correo Electrónico</label>
        <input type="email" name="email" id="email" placeholder="ejemplo@correo.com" required>
    </div>
`;

// --- Cambio de rol ---
function setRole(role) {
    currentRole = role;

    // Actualizar botones
    document.getElementById('btn-estudiante').classList.remove('active');
    document.getElementById('btn-docente').classList.remove('active');
    document.getElementById(`btn-${role}`).classList.add('active');

    // Sincronizar campo oculto con el servidor
    const roleField = document.getElementById('role-field');
    if (roleField) {
        roleField.value = role;
    }

    const warningContainer  = document.getElementById('warning-container');
    const emailContainer    = document.getElementById('email-container');
    const securityContainer = document.getElementById('security-container');

    // Limpiar todo
    warningContainer.innerHTML  = '';
    emailContainer.innerHTML    = '';
    securityContainer.innerHTML = '';

    if (role === 'estudiante') {
        warningContainer.innerHTML  = warningHTML;
        securityContainer.innerHTML = buildSecurityQuestionsHTML();
    } else {
        emailContainer.innerHTML = emailFieldHTML;
    }
}

// --- Validación y envío ---
function validateForm(event) {
    const nombre   = document.getElementById('name').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (nombre.length < 3) {
        event.preventDefault();
        alert('El nombre debe tener al menos 3 caracteres');
        return false;
    }

    if (username.length < 4) {
        event.preventDefault();
        alert('El nombre de usuario debe tener al menos 4 caracteres');
        return false;
    }

    if (password.length < 8) {
        event.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres');
        return false;
    }

    const puntos = calculatePasswordScore(password);
    if (puntos < 3) {
        event.preventDefault();
        alert('La contraseña es demasiado débil. Usa mayúsculas, minúsculas, números y un símbolo.');
        return false;
    }

    if (currentRole === 'docente') {
        const emailInput = document.getElementById('email');
        const email = emailInput ? emailInput.value.trim() : '';

        if (!email.includes('@') || !email.includes('.')) {
            event.preventDefault();
            alert('Por favor, ingresa un correo electrónico válido');
            return false;
        }
        return true;
    }

    // Estudiante: preguntas de seguridad
    const result = collectSecurityQuestions();
    if (!result.ok) {
        event.preventDefault();
        alert(result.message);
        return false;
    }

    // Las respuestas ya están en el formulario (name="respuesta1..5", name="pregunta1..5"),
    // así que el formulario se envía de forma normal al servidor.
    return true;
}

// --- Inicialización ---
document.addEventListener('DOMContentLoaded', function () {
    setRole('estudiante');
});