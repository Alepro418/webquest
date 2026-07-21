<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Entregas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .assignment-status {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .assignment-status.aprobado {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .assignment-status.revisando {
            background: #fff3e0;
            color: #e65100;
        }

        .assignment-status.pendiente {
            background: #f1f5f9;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📬 Mis Entregas</h1>
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
                    <p class="number">3</p>
                </div>
                <div class="kpi-card">
                    <h3>✅ Entregadas</h3>
                    <p class="number">8</p>
                </div>
                <div class="kpi-card">
                    <h3>⭐ Calificadas</h3>
                    <p class="number">5</p>
                </div>
                <div class="kpi-card alert">
                    <h3>📊 Promedio</h3>
                    <p class="number">16.5</p>
                </div>
            </section>

            <!-- Tabla de entregas -->
            <section class="data-section">
                <div class="table-header">
                    <h2>Historial de entregas</h2>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Asignatura</th>
                            <th>Taller</th>
                            <th>Fecha entrega</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ciencias Naturales - 4to</td>
                            <td>Ecosistemas</td>
                            <td>28/06/2026</td>
                            <td><span class="assignment-status aprobado">✅ Aprobado</span></td>
                            <td>18.5</td>
                            <td><button class="btn-sm" onclick="verFeedback(1)">👁️ Ver feedback</button></td>
                        </tr>
                        <tr>
                            <td>El Reino Animal - 5to</td>
                            <td>Vertebrados</td>
                            <td>25/06/2026</td>
                            <td><span class="assignment-status revisando">🔄 Revisando</span></td>
                            <td>—</td>
                            <td><button class="btn-sm" onclick="verFeedback(2)">👁️ Ver feedback</button></td>
                        </tr>
                        <tr>
                            <td>La Célula - 6to</td>
                            <td>Organelos</td>
                            <td>20/06/2026</td>
                            <td><span class="assignment-status aprobado">✅ Aprobado</span></td>
                            <td>19.0</td>
                            <td><button class="btn-sm" onclick="verFeedback(3)">👁️ Ver feedback</button></td>
                        </tr>
                        <tr>
                            <td>Ciencias Naturales - 4to</td>
                            <td>Cadena Alimenticia</td>
                            <td>—</td>
                            <td><span class="assignment-status pendiente">⏳ Pendiente</span></td>
                            <td>—</td>
                            <td><button class="btn-sm" onclick="subirPendiente(4)">📤 Subir</button></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        function verFeedback(id) {
            alert('👁️ Ver feedback de la entrega ' + id);
        }

        function subirPendiente(id) {
            alert('📤 Subir evidencia para el taller pendiente ' + id);
        }
    </script>
</body>
</html>