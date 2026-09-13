<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .reports-tabs {
            display: flex;
            gap: 0.5rem;
            margin: 1.5rem 0 2rem;
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 0.5rem;
        }

        .reports-tabs button {
            padding: 0.6rem 1.5rem;
            border: none;
            background: transparent;
            font-weight: 600;
            color: #5a7a6a;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s;
        }

        .reports-tabs button.active {
            background: var(--primary);
            color: white;
        }

        .reports-tabs button:hover:not(.active) {
            background: #e8f5e9;
        }

        .report-panel {
            display: none;
        }

        .report-panel.active {
            display: block;
        }

        .student-search {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .student-search input {
            flex: 1;
            padding: 0.7rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: 8px;
            min-width: 200px;
            background: #fafffa;
        }

        .student-search input:focus {
            border-color: var(--primary);
            outline: none;
        }

        .student-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-light);
            margin-top: 1rem;
        }

        .student-card h4 {
            color: var(--sidebar-color);
            margin-bottom: 0.5rem;
        }

        .progress-bar {
            height: 20px;
            background: #e8f5e9;
            border-radius: 10px;
            overflow: hidden;
            margin: 0.5rem 0;
        }

        .progress-bar .fill {
            height: 100%;
            background: var(--primary);
            border-radius: 10px;
            transition: width 0.5s;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .detail-item {
            background: #f6fbf6;
            padding: 0.8rem 1rem;
            border-radius: 8px;
        }

        .detail-item label {
            font-size: 0.8rem;
            color: #5a7a6a;
        }

        .detail-item .value {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--sidebar-color);
        }

        .general-table th {
            background: #f6fbf6;
        }

        @media (max-width: 768px) {
            .detail-grid {
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
                <h1>📈 Reportes Académicos</h1>
                <div class="date-info">
                    <?php 
                        date_default_timezone_set('America/Caracas');
                        echo date('d/m/Y');
                    ?>
                </div>
            </header>

            <!-- Pestañas -->
            <div class="reports-tabs">
                <button class="active" onclick="cambiarTab(this, 'individual')">👤 Rendimiento Individual</button>
                <button onclick="cambiarTab(this, 'general')">📊 Rendimiento General</button>
            </div>

            <!-- PANEL 1: Rendimiento Individual -->
            <div id="panel-individual" class="report-panel active">
                <div class="student-search">
                    <input type="text" id="buscarEstudiante" placeholder="🔍 Buscar estudiante por nombre..." onkeyup="buscarEstudiante()">
                    <button class="btn-primary" onclick="buscarEstudiante()">Buscar</button>
                </div>

                <div id="resultadoEstudiante">
                    <!-- Aquí se mostrará el estudiante encontrado -->
                    <div class="student-card" id="tarjetaEstudiante">
                        <h4>👤 María García</h4>
                        <p><strong>Grado:</strong> 4to Grado | <strong>Promedio:</strong> 19.2</p>
                        
                        <div class="progress-bar">
                            <div class="fill" style="width: 96%;"></div>
                        </div>
                        <p style="font-size: 0.9rem; color: #5a7a6a;">Rendimiento: 96%</p>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Entregas realizadas</label>
                                <div class="value">8 / 10</div>
                            </div>
                            <div class="detail-item">
                                <label>Calificación más alta</label>
                                <div class="value">20</div>
                            </div>
                            <div class="detail-item">
                                <label>Calificación más baja</label>
                                <div class="value">15</div>
                            </div>
                            <div class="detail-item">
                                <label>Estatus motivacional</label>
                                <div class="value"><span class="status st-sobresaliente">Sobresaliente</span></div>
                            </div>
                        </div>

                        <hr style="margin: 1rem 0;">

                        <h5 style="color: var(--sidebar-color);">📋 Entregas recientes</h5>
                        <table class="data-table" style="margin-top: 0.5rem;">
                            <thead>
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Fecha</th>
                                    <th>Calificación</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ciencias Naturales 4to</td>
                                    <td>28/06/2026</td>
                                    <td>19</td>
                                    <td>Excelente trabajo</td>
                                </tr>
                                <tr>
                                    <td>El Reino Animal - 5to</td>
                                    <td>25/06/2026</td>
                                    <td>17</td>
                                    <td>Muy buen análisis</td>
                                </tr>
                                <tr>
                                    <td>La Célula - 6to</td>
                                    <td>20/06/2026</td>
                                    <td>20</td>
                                    <td>¡Perfecto!</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: Rendimiento General -->
            <div id="panel-general" class="report-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h3 style="color: var(--sidebar-color);">📊 Resumen de la Sección</h3>
                        <p style="color: #5a7a6a;">4to Grado - Ciencias Naturales</p>
                    </div>
                    <div>
                        <button class="btn-primary" onclick="exportarReporte()">📤 Exportar PDF</button>
                    </div>
                </div>

                <!-- KPIs generales -->
                <div class="kpi-grid" style="margin-bottom: 1.5rem;">
                    <div class="kpi-card">
                        <h3>👥 Total Estudiantes</h3>
                        <p class="number">32</p>
                    </div>
                    <div class="kpi-card">
                        <h3>📊 Promedio General</h3>
                        <p class="number">16.8</p>
                    </div>
                    <div class="kpi-card">
                        <h3>✅ Tasa de Entrega</h3>
                        <p class="number">87%</p>
                    </div>
                    <div class="kpi-card alert">
                        <h3>⚠️ Por Mejorar</h3>
                        <p class="number">5</p>
                    </div>
                </div>

                <!-- Tabla general -->
                <table class="data-table general-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Estudiante</th>
                            <th>Entregas</th>
                            <th>Promedio</th>
                            <th>Estatus</th>
                            <th>Progreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>María García</td>
                            <td>8/10</td>
                            <td>19.2</td>
                            <td><span class="status st-sobresaliente">Sobresaliente</span></td>
                            <td>
                                <div class="progress-bar" style="height: 8px; width: 120px;">
                                    <div class="fill" style="width: 96%;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Carlos Ruiz</td>
                            <td>7/10</td>
                            <td>16.5</td>
                            <td><span class="status st-bueno">Buen Estudiante</span></td>
                            <td>
                                <div class="progress-bar" style="height: 8px; width: 120px;">
                                    <div class="fill" style="width: 82%; background: #f59e0b;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Luis Pineda</td>
                            <td>6/10</td>
                            <td>12.8</td>
                            <td><span class="status st-mejorar">Vamos a Mejorar</span></td>
                            <td>
                                <div class="progress-bar" style="height: 8px; width: 120px;">
                                    <div class="fill" style="width: 64%; background: #f59e0b;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Ana Torres</td>
                            <td>5/10</td>
                            <td>10.5</td>
                            <td><span class="status st-mejorar">Vamos a Mejorar</span></td>
                            <td>
                                <div class="progress-bar" style="height: 8px; width: 120px;">
                                    <div class="fill" style="width: 52%; background: #ef4444;"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Cambiar entre pestañas
        function cambiarTab(btn, tab) {
            // Quitar active de todos los botones y paneles
            document.querySelectorAll('.reports-tabs button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.report-panel').forEach(p => p.classList.remove('active'));

            // Activar el botón clickeado
            btn.classList.add('active');

            // Activar el panel correspondiente
            document.getElementById('panel-' + tab).classList.add('active');
        }

        // Buscar estudiante (simulación)
        function buscarEstudiante() {
            const input = document.getElementById('buscarEstudiante').value.toLowerCase();
            const tarjeta = document.getElementById('tarjetaEstudiante');

            if (input === '' || input === 'maria') {
                tarjeta.style.display = 'block';
            } else {
                tarjeta.style.display = 'none';
                // Mostrar mensaje de "no encontrado"
                if (input.length > 2) {
                    alert('🔍 Estudiante no encontrado. Intenta con otro nombre.');
                }
            }
        }

        // Exportar reporte (simulación)
        function exportarReporte() {
            alert('📤 Reporte exportado a PDF (simulación).');
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar tarjeta por defecto
            document.getElementById('tarjetaEstudiante').style.display = 'block';
        });
    </script>
</body>
</html>