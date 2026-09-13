<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Rendimiento - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .my-report-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .my-report-container .student-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid var(--border-light);
            margin-top: 1.5rem;
        }

        .my-report-container .student-card h2 {
            color: var(--sidebar-color);
            margin-bottom: 0.5rem;
        }

        .my-report-container .progress-bar {
            height: 24px;
            background: #e8f5e9;
            border-radius: 12px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .my-report-container .progress-bar .fill {
            height: 100%;
            background: var(--primary);
            border-radius: 12px;
            transition: width 0.8s;
        }

        .my-report-container .detail-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .my-report-container .detail-item {
            background: #f6fbf6;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .my-report-container .detail-item label {
            font-size: 0.8rem;
            color: #5a7a6a;
            display: block;
        }

        .my-report-container .detail-item .value {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--sidebar-color);
        }

        .my-report-container .motivational-message {
            background: #e8f5e9;
            padding: 1.2rem;
            border-radius: 10px;
            border-left: 5px solid var(--primary);
            margin: 1.5rem 0;
        }

        .my-report-container .motivational-message strong {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .my-report-container .detail-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📊 Mi Rendimiento</h1>
                <div class="date-info">
                    <?php 
                        date_default_timezone_set('America/Caracas');
                        echo date('d/m/Y');
                    ?>
                </div>
            </header>

            <div class="my-report-container">
                <!-- Tarjeta de rendimiento -->
                <div class="student-card">
                    <h2>👤 María García</h2>
                    <p style="color: #5a7a6a;">4to Grado - Ciencias Naturales</p>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                        <span style="font-weight: 600;">Promedio general: 19.2</span>
                        <span class="status st-sobresaliente">⭐ Sobresaliente</span>
                    </div>

                    <div class="progress-bar">
                        <div class="fill" style="width: 96%;"></div>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>📝 Entregas realizadas</label>
                            <div class="value">8/10</div>
                        </div>
                        <div class="detail-item">
                            <label>⭐ Calificación más alta</label>
                            <div class="value">20</div>
                        </div>
                        <div class="detail-item">
                            <label>📉 Calificación más baja</label>
                            <div class="value">15</div>
                        </div>
                        <div class="detail-item">
                            <label>🏆 Posición en el aula</label>
                            <div class="value">#1</div>
                        </div>
                    </div>

                    <div class="motivational-message">
                        <strong>🌟 ¡Sigue así, María!</strong> Has demostrado un excelente desempeño en Ciencias Naturales. 
                        Tu constancia y dedicación te están llevando muy lejos. ¡No bajes los brazos!
                    </div>

                    <hr style="margin: 1.5rem 0;">

                    <h4 style="color: var(--sidebar-color);">📋 Historial de entregas</h4>
                    <table class="data-table" style="margin-top: 0.8rem;">
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
                                <td style="color: var(--primary);">Excelente trabajo</td>
                            </tr>
                            <tr>
                                <td>El Reino Animal - 5to</td>
                                <td>25/06/2026</td>
                                <td>17</td>
                                <td style="color: var(--primary);">Muy buen análisis</td>
                            </tr>
                            <tr>
                                <td>La Célula - 6to</td>
                                <td>20/06/2026</td>
                                <td>20</td>
                                <td style="color: var(--primary);">¡Perfecto!</td>
                            </tr>
                            <tr>
                                <td>Ciencias Naturales 4to</td>
                                <td>15/06/2026</td>
                                <td>18</td>
                                <td style="color: #f59e0b;">Bien, pero revisa la conclusión</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="text-align: center; margin-top: 1.5rem;">
                        <button class="btn-primary" onclick="alert('📄 Reporte exportado (simulación)')">📤 Exportar mi reporte</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>