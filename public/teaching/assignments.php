<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📬 Entregas de Estudiantes</h1>
                <div class="date-info">
                    <?php 
                        date_default_timezone_set('America/Caracas');
                        echo date('d/m/Y');
                    ?>
                </div>
            </header>

            <!-- KPIs rápidos -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <h3>📤 Pendientes</h3>
                    <p class="number">8</p>
                </div>
                <div class="kpi-card">
                    <h3>✅ Revisadas</h3>
                    <p class="number">24</p>
                </div>
                <div class="kpi-card">
                    <h3>⭐ Calificadas</h3>
                    <p class="number">18</p>
                </div>
                <div class="kpi-card alert">
                    <h3>⏰ Atrasadas</h3>
                    <p class="number">3</p>
                </div>
            </section>

            <!-- Barra de filtros -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="🔍 Buscar estudiante o asignatura..." onkeyup="filtrar()">

                <select id="filterSubject" onchange="filtrar()">
                    <option value="todas">📚 Todas las asignaturas</option>
                    <option value="ciencias4">Ciencias Naturales 4to</option>
                    <option value="animales">El Reino Animal - 5to</option>
                    <option value="celula">La Célula - 6to</option>
                </select>

                <select id="filterStatus" onchange="filtrar()">
                    <option value="todas">📋 Todos los estados</option>
                    <option value="pendiente">⏳ Pendiente</option>
                    <option value="revisada">👀 Revisada</option>
                    <option value="calificada">⭐ Calificada</option>
                    <option value="atrasada">⏰ Atrasada</option>
                </select>

                <button class="btn-primary" onclick="resetFiltros()">🔄 Limpiar</button>
            </div>

            <!-- Tabla de entregas -->
            <section class="data-section">
                <div class="table-header">
                    <h2>Listado de Entregas</h2>
                    <span id="contadorEntregas" style="color: #5a7a6a; font-size: 0.9rem;">Mostrando 0 entregas</span>
                </div>

                <table class="data-table" id="tablaEntregas">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Asignatura</th>
                            <th>Fecha Entrega</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        <!-- Los datos se cargarán con JavaScript (simulación) -->
                    </tbody>
                </table>

                <!-- Mensaje sin resultados -->
                <div id="sinResultados" style="display: none; text-align: center; padding: 2rem;">
                    <p>🔍 No se encontraron entregas con esos filtros</p>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para ver detalle / calificar -->
    <div class="modal" id="modalDetalle">
        <div class="modal-content">
            <h3 id="modalTitulo">📄 Detalle de Entrega</h3>
            <div id="detalleContenido">
                <p><strong>Estudiante:</strong> <span id="detalleEstudiante"></span></p>
                <p><strong>Asignatura:</strong> <span id="detalleAsignatura"></span></p>
                <p><strong>Fecha:</strong> <span id="detalleFecha"></span></p>
                <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                <p><strong>Comentario del estudiante:</strong></p>
                <p id="detalleComentario" style="background: #f6fbf6; padding: 0.8rem; border-radius: 8px; border-left: 4px solid var(--primary);"></p>
                <hr style="margin: 1rem 0;">
                <div class="form-group">
                    <label for="calificacionInput">⭐ Calificación (0-20)</label>
                    <input type="number" id="calificacionInput" min="0" max="20" step="0.5" value="15">
                </div>
                <div class="form-group">
                    <label for="feedbackInput">📝 Feedback para el estudiante</label>
                    <textarea id="feedbackInput" rows="3" placeholder="Escribe aquí tu retroalimentación..."></textarea>
                </div>
                <div style="display: flex; gap: 0.8rem; margin-top: 1rem;">
                    <button class="btn-primary" style="flex:2;" onclick="guardarCalificacion()">💾 Guardar Calificación</button>
                    <button class="btn-sm" style="flex:1;" onclick="cerrarModal()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // DATOS DE EJEMPLO (simulación)
        // ============================================
        const entregas = [
            { id: 1, estudiante: 'María García', asignatura: 'Ciencias Naturales 4to', fecha: '2026-06-28', estado: 'pendiente', calificacion: null, comentario: 'Adjunto mi trabajo sobre el ciclo del agua.' },
            { id: 2, estudiante: 'Carlos Ruiz', asignatura: 'El Reino Animal - 5to', fecha: '2026-06-27', estado: 'revisada', calificacion: 16, comentario: 'Espero que esté bien.' },
            { id: 3, estudiante: 'Luis Pineda', asignatura: 'La Célula - 6to', fecha: '2026-06-25', estado: 'calificada', calificacion: 19, comentario: 'Me esforcé mucho.' },
            { id: 4, estudiante: 'Ana Torres', asignatura: 'Ciencias Naturales 4to', fecha: '2026-06-20', estado: 'atrasada', calificacion: null, comentario: 'No pude entregar antes.' },
            { id: 5, estudiante: 'Pedro Gómez', asignatura: 'El Reino Animal - 5to', fecha: '2026-06-29', estado: 'pendiente', calificacion: null, comentario: 'Subo mi trabajo.' },
            { id: 6, estudiante: 'Sofía Ramírez', asignatura: 'La Célula - 6to', fecha: '2026-06-26', estado: 'revisada', calificacion: 14, comentario: '¿Está correcto?' },
        ];

        let filtroActual = { texto: '', asignatura: 'todas', estado: 'todas' };
        let entregaSeleccionada = null;

        // ============================================
        // RENDERIZAR TABLA
        // ============================================
        function renderizarTabla() {
            const tbody = document.getElementById('cuerpoTabla');
            const sinResultados = document.getElementById('sinResultados');
            const contador = document.getElementById('contadorEntregas');

            // Aplicar filtros
            const filtradas = entregas.filter(e => {
                const coincideTexto = e.estudiante.toLowerCase().includes(filtroActual.texto) ||
                                     e.asignatura.toLowerCase().includes(filtroActual.texto);
                const coincideAsignatura = filtroActual.asignatura === 'todas' || e.asignatura === filtroActual.asignatura;
                const coincideEstado = filtroActual.estado === 'todas' || e.estado === filtroActual.estado;
                return coincideTexto && coincideAsignatura && coincideEstado;
            });

            // Actualizar contador
            contador.textContent = `Mostrando ${filtradas.length} entregas`;

            if (filtradas.length === 0) {
                tbody.innerHTML = '';
                sinResultados.style.display = 'block';
                return;
            }
            sinResultados.style.display = 'none';

            // Generar filas
            tbody.innerHTML = filtradas.map(e => `
                <tr>
                    <td><strong>${e.estudiante}</strong></td>
                    <td>${e.asignatura}</td>
                    <td>${formatearFecha(e.fecha)}</td>
                    <td>${renderizarEstado(e.estado)}</td>
                    <td>${e.calificacion !== null ? e.calificacion : '—'}</td>
                    <td>
                        <button class="btn-sm" onclick="verDetalle(${e.id})">👁️ Ver</button>
                        ${e.estado !== 'calificada' ? `<button class="btn-sm" onclick="calificar(${e.id})">⭐ Calificar</button>` : ''}
                    </td>
                </tr>
            `).join('');
        }

        // ============================================
        // FUNCIONES DE APOYO
        // ============================================
        function formatearFecha(fecha) {
            const d = new Date(fecha);
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function renderizarEstado(estado) {
            const map = {
                'pendiente': '<span class="status st-mejorar">⏳ Pendiente</span>',
                'revisada': '<span class="status st-bueno">👀 Revisada</span>',
                'calificada': '<span class="status st-sobresaliente">⭐ Calificada</span>',
                'atrasada': '<span class="status" style="background:#fce4ec;color:#c62828;">⏰ Atrasada</span>'
            };
            return map[estado] || estado;
        }

        // ============================================
        // FILTROS
        // ============================================
        function filtrar() {
            filtroActual.texto = document.getElementById('searchInput').value.toLowerCase();
            filtroActual.asignatura = document.getElementById('filterSubject').value;
            filtroActual.estado = document.getElementById('filterStatus').value;
            renderizarTabla();
        }

        function resetFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterSubject').value = 'todas';
            document.getElementById('filterStatus').value = 'todas';
            filtroActual = { texto: '', asignatura: 'todas', estado: 'todas' };
            renderizarTabla();
        }

        // ============================================
        // MODAL - VER DETALLE / CALIFICAR
        // ============================================
        function verDetalle(id) {
            const e = entregas.find(el => el.id === id);
            if (!e) return;
            entregaSeleccionada = e;

            document.getElementById('modalTitulo').textContent = '📄 Detalle de Entrega';
            document.getElementById('detalleEstudiante').textContent = e.estudiante;
            document.getElementById('detalleAsignatura').textContent = e.asignatura;
            document.getElementById('detalleFecha').textContent = formatearFecha(e.fecha);
            document.getElementById('detalleEstado').innerHTML = renderizarEstado(e.estado);
            document.getElementById('detalleComentario').textContent = e.comentario || 'Sin comentario';
            document.getElementById('calificacionInput').value = e.calificacion !== null ? e.calificacion : '';
            document.getElementById('feedbackInput').value = '';

            document.getElementById('modalDetalle').classList.add('active');
        }

        function calificar(id) {
            verDetalle(id);
            // Enfocar el campo de calificación
            setTimeout(() => {
                document.getElementById('calificacionInput').focus();
            }, 300);
        }

        function cerrarModal() {
            document.getElementById('modalDetalle').classList.remove('active');
            entregaSeleccionada = null;
        }

        function guardarCalificacion() {
            if (!entregaSeleccionada) return;

            const calif = parseFloat(document.getElementById('calificacionInput').value);
            const feedback = document.getElementById('feedbackInput').value.trim();

            if (isNaN(calif) || calif < 0 || calif > 20) {
                alert('⚠️ Ingresa una calificación válida entre 0 y 20.');
                return;
            }

            // Actualizar en el arreglo
            const idx = entregas.findIndex(e => e.id === entregaSeleccionada.id);
            if (idx !== -1) {
                entregas[idx].calificacion = calif;
                entregas[idx].estado = 'calificada';
            }

            alert(`✅ Calificación guardada: ${calif}\n${feedback ? 'Feedback: ' + feedback : ''}`);
            cerrarModal();
            renderizarTabla();
        }

        // ============================================
        // INICIALIZAR
        // ============================================
        document.addEventListener('DOMContentLoaded', renderizarTabla);
    </script>
</body>
</html>