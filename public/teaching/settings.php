<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Estilos específicos para la página de configuración */
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .settings-card {
            background: var(--white);
            padding: 1.8rem;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .settings-card h3 {
            color: var(--sidebar-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 0.6rem;
        }

        .settings-card .form-group {
            margin-bottom: 1.2rem;
        }

        .settings-card .form-group label {
            font-weight: 600;
            color: var(--text-main);
            display: block;
            margin-bottom: 0.3rem;
        }

        .settings-card .form-group input,
        .settings-card .form-group select {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: 8px;
            background: #fafffa;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .settings-card .form-group input:focus,
        .settings-card .form-group select:focus {
            border-color: var(--primary);
            outline: none;
        }

        /* Avatar */
        .avatar-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .avatar-placeholder {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .avatar-actions button {
            background: #e8f5e9;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-right: 0.5rem;
            transition: background 0.2s;
        }

        .avatar-actions button:hover {
            background: #c8e6c9;
        }

        /* Toggles */
        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f0f7f0;
        }

        .toggle-group:last-child {
            border-bottom: none;
        }

        .toggle-group .toggle-label {
            font-weight: 500;
            color: var(--text-main);
        }

        .toggle-group .toggle-desc {
            font-size: 0.8rem;
            color: #5a7a6a;
        }

        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
            background: #ccc;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s;
            flex-shrink: 0;
        }

        .toggle-switch.active {
            background: var(--primary);
        }

        .toggle-switch .toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-switch.active .toggle-slider {
            transform: translateX(20px);
        }

        /* Botón guardar */
        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .btn-save:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>⚙️ Configuración</h1>
                <div class="date-info">
                    <?php 
                        date_default_timezone_set('America/Caracas');
                        echo date('d/m/Y');
                    ?>
                </div>
            </header>

            <div class="settings-grid">
                <!-- COLUMNA IZQUIERDA -->
                <div>
                    <!-- Perfil del Docente -->
                    <div class="settings-card">
                        <h3>👤 Perfil del Docente</h3>

                        <div class="avatar-section">
                            <div class="avatar-placeholder">JP</div>
                            <div class="avatar-actions">
                                <button onclick="cambiarFoto()">📷 Cambiar foto</button>
                                <button onclick="eliminarFoto()">🗑️ Eliminar</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" value="Juan Pérez">
                        </div>
                        <div class="form-group">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" value="juan.perez@escuela.edu">
                        </div>
                        <div class="form-group">
                            <label for="especialidad">Especialidad</label>
                            <select id="especialidad">
                                <option value="ciencias">Ciencias Naturales</option>
                                <option value="biologia">Biología</option>
                                <option value="quimica">Química</option>
                                <option value="fisica">Física</option>
                            </select>
                        </div>
                        <button class="btn-save" onclick="guardarPerfil()">💾 Guardar cambios</button>
                    </div>

                    <!-- Preferencias de Notificaciones -->
                    <div class="settings-card" style="margin-top: 1.5rem;">
                        <h3>🔔 Notificaciones</h3>

                        <div class="toggle-group">
                            <div>
                                <div class="toggle-label">Nuevas entregas</div>
                                <div class="toggle-desc">Recibir alerta cuando un estudiante entregue</div>
                            </div>
                            <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                <div class="toggle-slider"></div>
                            </div>
                        </div>

                        <div class="toggle-group">
                            <div>
                                <div class="toggle-label">Recordatorios</div>
                                <div class="toggle-desc">Recordatorios de entregas pendientes</div>
                            </div>
                            <div class="toggle-switch" onclick="toggleSwitch(this)">
                                <div class="toggle-slider"></div>
                            </div>
                        </div>

                        <div class="toggle-group">
                            <div>
                                <div class="toggle-label">Reportes semanales</div>
                                <div class="toggle-desc">Resumen por correo cada semana</div>
                            </div>
                            <div class="toggle-switch active" onclick="toggleSwitch(this)">
                                <div class="toggle-slider"></div>
                            </div>
                        </div>

                        <button class="btn-save" onclick="guardarNotificaciones()">💾 Guardar preferencias</button>
                    </div>
                </div>

                <!-- COLUMNA DERECHA -->
                <div>
                    <!-- Cambio de Contraseña -->
                    <div class="settings-card">
                        <h3>🔑 Cambiar Contraseña</h3>

                        <div class="form-group">
                            <label for="pass_actual">Contraseña actual</label>
                            <input type="password" id="pass_actual" placeholder="Ingresa tu contraseña actual">
                        </div>
                        <div class="form-group">
                            <label for="pass_nueva">Nueva contraseña</label>
                            <input type="password" id="pass_nueva" placeholder="Mínimo 8 caracteres" onkeyup="checkStrength()">
                            <div style="margin-top: 0.4rem;">
                                <div style="height: 6px; background: #e2e8f0; border-radius: 4px;">
                                    <div id="strength-bar" style="height: 6px; width: 0%; background: #ef4444; border-radius: 4px; transition: width 0.3s;"></div>
                                </div>
                                <span id="strength-text" style="font-size: 0.8rem; color: #5a7a6a;">Débil</span>
                            </div>
                            <div style="font-size: 0.75rem; color: #5a7a6a; margin-top: 0.3rem;">
                                Requisitos: 8 caracteres, mayúscula, minúscula, número y carácter especial (!@#$%^&*)
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pass_confirm">Confirmar nueva contraseña</label>
                            <input type="password" id="pass_confirm" placeholder="Repite la nueva contraseña" onkeyup="checkMatch()">
                            <span id="pass-match" style="font-size: 0.8rem;"></span>
                        </div>

                        <button class="btn-save" onclick="cambiarContrasena()">🔒 Cambiar contraseña</button>
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="settings-card" style="margin-top: 1.5rem;">
                        <h3>🌐 Opciones adicionales</h3>

                        <div class="form-group">
                            <label for="idioma">Idioma</label>
                            <select id="idioma">
                                <option value="es" selected>Español</option>
                                <option value="en">Inglés</option>
                                <option value="pt">Portugués</option>
                            </select>
                        </div>

                        <div class="toggle-group">
                            <div>
                                <div class="toggle-label">Tema oscuro</div>
                                <div class="toggle-desc">Activar modo oscuro (experimental)</div>
                            </div>
                            <div class="toggle-switch" onclick="toggleSwitch(this)">
                                <div class="toggle-slider"></div>
                            </div>
                        </div>

                        <button class="btn-save" onclick="guardarOpciones()">💾 Guardar opciones</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // ============================================
        // FUNCIONES DE INTERACCIÓN (SIMULADAS)
        // ============================================

        // ----- TOGGLES -----
        function toggleSwitch(el) {
            el.classList.toggle('active');
        }

        // ----- PERFIL -----
        function cambiarFoto() {
            alert('📷 Simulación: Abrir selector de archivos para cambiar foto.');
        }

        function eliminarFoto() {
            if (confirm('¿Eliminar la foto de perfil?')) {
                alert('Foto eliminada (simulación).');
            }
        }

        function guardarPerfil() {
            const nombre = document.getElementById('nombre').value.trim();
            const email = document.getElementById('email').value.trim();
            const especialidad = document.getElementById('especialidad').value;

            if (!nombre || !email) {
                alert('⚠️ Nombre y correo son obligatorios.');
                return;
            }
            if (!email.includes('@') || !email.includes('.')) {
                alert('⚠️ Correo electrónico no válido.');
                return;
            }

            alert('✅ Datos del perfil guardados (simulación).');
        }

        // ----- NOTIFICACIONES -----
        function guardarNotificaciones() {
            const toggles = document.querySelectorAll('.toggle-switch');
            const estados = Array.from(toggles).map(t => t.classList.contains('active') ? 'activada' : 'desactivada');
            alert('✅ Preferencias de notificaciones guardadas:\n' + estados.join('\n'));
        }

        // ----- CAMBIO DE CONTRASEÑA -----
        function checkStrength() {
            const pass = document.getElementById('pass_nueva').value;
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');

            let puntos = 0;
            if (pass.length >= 8) puntos++;
            if (/[a-z]/.test(pass)) puntos++;
            if (/[A-Z]/.test(pass)) puntos++;
            if (/[0-9]/.test(pass)) puntos++;
            if (/[!@#$%^&*]/.test(pass)) puntos++;

            const niveles = ['Débil', 'Baja', 'Media', 'Fuerte', 'Muy fuerte'];
            const colores = ['#ef4444', '#f59e0b', '#f59e0b', '#10b981', '#10b981'];

            bar.style.width = (puntos * 20) + '%';
            bar.style.background = colores[puntos] || '#ef4444';
            text.textContent = niveles[puntos] || 'Débil';
            text.style.color = colores[puntos] || '#ef4444';

            // También verificar coincidencia
            checkMatch();
        }

        function checkMatch() {
            const pass = document.getElementById('pass_nueva').value;
            const confirm = document.getElementById('pass_confirm').value;
            const span = document.getElementById('pass-match');

            if (confirm === '') {
                span.textContent = '';
                return;
            }
            if (pass === confirm) {
                span.textContent = '✅ Las contraseñas coinciden';
                span.style.color = '#166534';
            } else {
                span.textContent = '❌ No coinciden';
                span.style.color = '#991b1b';
            }
        }

        function cambiarContrasena() {
            const actual = document.getElementById('pass_actual').value;
            const nueva = document.getElementById('pass_nueva').value;
            const confirm = document.getElementById('pass_confirm').value;

            if (!actual || !nueva || !confirm) {
                alert('⚠️ Completa todos los campos.');
                return;
            }
            if (nueva.length < 8) {
                alert('⚠️ La nueva contraseña debe tener al menos 8 caracteres.');
                return;
            }
            if (nueva !== confirm) {
                alert('⚠️ Las contraseñas no coinciden.');
                return;
            }

            alert('✅ Contraseña cambiada correctamente (simulación).');
            // Limpiar campos
            document.getElementById('pass_actual').value = '';
            document.getElementById('pass_nueva').value = '';
            document.getElementById('pass_confirm').value = '';
            document.getElementById('strength-bar').style.width = '0%';
            document.getElementById('strength-text').textContent = 'Débil';
            document.getElementById('pass-match').textContent = '';
        }

        // ----- OPCIONES ADICIONALES -----
        function guardarOpciones() {
            const idioma = document.getElementById('idioma').value;
            const tema = document.querySelector('.toggle-switch:last-child').classList.contains('active') ? 'oscuro' : 'claro';
            alert(`✅ Opciones guardadas:\nIdioma: ${idioma}\nTema: ${tema}`);
        }

        // Inicializar (opcional)
        document.addEventListener('DOMContentLoaded', function() {
            // Forzar que el toggle de tema oscuro no afecte realmente (es solo interfaz)
        });
    </script>
</body>
</html>