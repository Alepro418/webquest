/**
 * password.js
 * Responsabilidad: mostrar/ocultar contraseña y calcular su fortaleza (5 niveles).
 *
 * Depende de: elementos #password, #toggle-password,
 *             #strength-bar, #strength-text en el DOM.
 * Expone:
 *   - togglePasswordVisibility()
 *   - calculatePasswordScore(pass)
 *   - getPasswordLevel(puntos)
 *   - checkPasswordStrength(pass?)
 */

// --- Mostrar / Ocultar contraseña ---
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn     = document.getElementById('toggle-password');

    if (!passwordInput || !toggleBtn) return;

    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';

    toggleBtn.textContent = isHidden ? '🙈' : '👁️';
    toggleBtn.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
    toggleBtn.setAttribute('aria-pressed', String(isHidden));
}

// --- Niveles de fortaleza ---
const PASSWORD_LEVELS = [
    { label: 'Débil',      color: '#ef4444' }, // 0 puntos
    { label: 'Baja',       color: '#f59e0b' }, // 1 punto
    { label: 'Media',      color: '#f59e0b' }, // 2 puntos
    { label: 'Fuerte',     color: '#10b981' }, // 3 puntos
    { label: 'Muy fuerte', color: '#10b981' }  // 4-5 puntos
];

/**
 * Calcula los puntos de la contraseña (0 a 5).
 * @param {string} pass
 * @returns {number}
 */
function calculatePasswordScore(pass) {
    let puntos = 0;
    if (pass.length >= 8)             puntos++;
    if (/[a-z]/.test(pass))           puntos++;
    if (/[A-Z]/.test(pass))           puntos++;
    if (/[0-9]/.test(pass))           puntos++;
    if (/[!@#$%^&*._\-]/.test(pass))  puntos++;
    return puntos;
}

/**
 * Devuelve el nivel (label, color) según los puntos.
 * @param {number} puntos
 * @returns {{ label: string, color: string }}
 */
function getPasswordLevel(puntos) {
    return PASSWORD_LEVELS[puntos] || PASSWORD_LEVELS[0];
}

/**
 * Actualiza la barra y el texto de fortaleza en el DOM.
 * @param {string} [pass] - Opcional; si no se pasa, se toma del input.
 */
function checkPasswordStrength(pass) {
    const passwordInput = document.getElementById('password');
    const strengthBar   = document.getElementById('strength-bar');
    const strengthText  = document.getElementById('strength-text');

    if (!passwordInput || !strengthBar || !strengthText) return;

    const value = (typeof pass === 'string') ? pass : passwordInput.value;

    // Estado vacío
    if (value.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.style.background = 'transparent';
        strengthText.textContent = '';
        return;
    }

    const puntos = calculatePasswordScore(value);
    const nivel  = getPasswordLevel(puntos);

    strengthBar.style.width = (puntos * 20) + '%';
    strengthBar.style.background = nivel.color;

    strengthText.textContent = nivel.label;
    strengthText.style.color = nivel.color;
}

// --- Inicialización ---
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const toggleBtn     = document.getElementById('toggle-password');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', togglePasswordVisibility);
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
        checkPasswordStrength(); // estado inicial
    }
});