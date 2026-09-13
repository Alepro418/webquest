/**
 * security-questions.js
 * Responsabilidad: generar, recolectar y validar el bloque HTML
 * de las preguntas de seguridad para el registro de estudiantes.
 *
 * Depende de: nada. Es autocontenido.
 * Expone:
 *   - SECURITY_QUESTIONS_COUNT
 *   - buildSecurityQuestionsHTML()
 *   - collectSecurityQuestions()
 */

const SECURITY_QUESTIONS_COUNT = 5;

// Opciones disponibles para los <select>
const QUESTION_OPTIONS = [
    { value: 'mascota',    label: '¿Cómo se llama tu mascota?' },
    { value: 'comida',     label: '¿Cuál es tu comida favorita?' },
    { value: 'juego',      label: '¿Cuál es tu juego favorito?' },
    { value: 'superheroe', label: '¿Quién es tu superhéroe favorito?' },
    { value: 'color',      label: '¿Cuál es tu color favorito?' },
    { value: 'libro',      label: '¿Cuál es tu libro favorito?' }
];

/**
 * Genera el HTML completo del bloque de preguntas de seguridad.
 * @returns {string}
 */
function buildSecurityQuestionsHTML() {
    const rows = [];

    for (let i = 1; i <= SECURITY_QUESTIONS_COUNT; i++) {
        const optionsHTML = QUESTION_OPTIONS
            .map(opt => `<option value="${opt.value}">${opt.label}</option>`)
            .join('');

        rows.push(`
            <div class="security-row">
                <div class="question-select">
                    <label for="pregunta${i}" class="required">Pregunta ${i}</label>
                    <select id="pregunta${i}" name="pregunta${i}" required>
                        <option value="">Selecciona...</option>
                        ${optionsHTML}
                    </select>
                </div>
                <div class="question-answer">
                    <label for="respuesta${i}" class="required">Respuesta ${i}</label>
                    <input type="text" id="respuesta${i}" name="respuesta${i}" placeholder="Tu respuesta" required>
                </div>
            </div>
        `);
    }

    return `
        <div id="security-questions" class="security-questions-horizontal">
            <h4>🔐 Preguntas de Seguridad</h4>
            <p class="security-info">
                Elige ${SECURITY_QUESTIONS_COUNT} preguntas y respuestas que recordarás fácilmente.
                Las usarás para recuperar tu contraseña.
            </p>
            ${rows.join('')}
        </div>
    `;
}

/**
 * Recolecta y valida las respuestas del bloque de preguntas de seguridad.
 * Reglas:
 *   - Todas las preguntas y respuestas deben estar completas.
 *   - Cada respuesta debe tener al menos 2 caracteres.
 *   - No se puede repetir la misma pregunta.
 *
 * @returns {{ ok: true, data: Array<{pregunta:string, respuesta:string}> }
 *          | { ok: false, message: string }}
 */
function collectSecurityQuestions() {
    const data = [];

    for (let i = 1; i <= SECURITY_QUESTIONS_COUNT; i++) {
        const preguntaEl  = document.getElementById(`pregunta${i}`);
        const respuestaEl = document.getElementById(`respuesta${i}`);

        if (!preguntaEl || !respuestaEl) {
            return { ok: false, message: 'No se encontraron las preguntas de seguridad.' };
        }

        const pregunta  = preguntaEl.value;
        const respuesta = respuestaEl.value.trim();

        if (!pregunta || !respuesta) {
            return { ok: false, message: 'Por favor, completa todas las preguntas de seguridad.' };
        }

        if (respuesta.length < 2) {
            return { ok: false, message: 'Cada respuesta debe tener al menos 2 caracteres.' };
        }

        data.push({ pregunta, respuesta });
    }

    // Validar preguntas duplicadas
    const seleccionadas = data.map(d => d.pregunta);
    if (new Set(seleccionadas).size !== seleccionadas.length) {
        return { ok: false, message: 'No puedes repetir la misma pregunta de seguridad.' };
    }

    return { ok: true, data };
}