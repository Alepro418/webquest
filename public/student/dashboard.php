<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asignaturas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Estilos específicos para el panel del estudiante */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3a2f, #2d5a3d);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .welcome-banner h1 {
            font-size: 1.8rem;
            margin: 0;
        }

        .welcome-banner .estatus-motivacional {
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
        }

        .progress-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .progress-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--sidebar-color);
        }

        .progress-card .label {
            color: #5a7a6a;
            font-size: 0.85rem;
        }

        .subjects-grid-student {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .subject-card-student {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-light);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .subject-card-student:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }

        .subject-card-student h3 {
            color: var(--sidebar-color);
            margin-bottom: 0.3rem;
        }

        .subject-card-student p {
            color: #5a7a6a;
            font-size: 0.9rem;
            margin: 0.3rem 0;
        }

        .subject-card-student .progress-bar {
            height: 6px;
            background: #e8f5e9;
            border-radius: 4px;
            margin: 0.8rem 0;
            overflow: hidden;
        }

        .subject-card-student .progress-bar .fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
            transition: width 0.5s;
        }

        .subject-card-student .footer-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #5a7a6a;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <!-- Banner de bienvenida -->
            <div class="welcome-banner">
                <div>
                    <h1>🌿 ¡Hola, Estudiante!</h1>
                    <p style="margin: 0.3rem 0 0; opacity: 0.8;">Sigue explorando el mundo de las Ciencias Naturales</p>
                </div>
                <div class="estatus-motivacional">
                    ⭐ Estatus: <strong>Buen Estudiante</strong>
                </div>
            </div>

            <!-- KPIs del estudiante -->
            <div class="progress-container">
                <div class="progress-card">
                    <div class="number">4</div>
                    <div class="label">Asignaturas activas</div>
                </div>
                <div class="progress-card">
                    <div class="number">6</div>
                    <div class="label">Talleres completados</div>
                </div>
                <div class="progress-card">
                    <div class="number">2</div>
                    <div class="label">Pendientes por entregar</div>
                </div>
                <div class="progress-card" style="border-left-color: #f9a825;">
                    <div class="number">16.5</div>
                    <div class="label">Promedio general</div>
                </div>
            </div>

            <!-- Listado de asignaturas -->
            <section class="data-section">
                <div class="table-header">
                    <h2>📚 Mis Asignaturas</h2>
                    <span style="color: #5a7a6a; font-size: 0.9rem;">Haz clic en una para ver los talleres</span>
                </div>

                <div class="subjects-grid-student">
                    <!-- Asignatura 1 -->
                    <div class="subject-card-student" onclick="verAsignatura(1)">
                        <h3>🌿 Ciencias Naturales - 4to</h3>
                        <p>Seres vivos, ecosistemas y cuerpo humano</p>
                        <div class="progress-bar">
                            <div class="fill" style="width: 75%;"></div>
                        </div>
                        <div class="footer-card">
                            <span>📝 6/8 talleres</span>
                            <span>⭐ 17.2</span>
                        </div>
                    </div>

                    <!-- Asignatura 2 -->
                    <div class="subject-card-student" onclick="verAsignatura(2)">
                        <h3>🦁 El Reino Animal - 5to</h3>
                        <p>Vertebrados, invertebrados y hábitats</p>
                        <div class="progress-bar">
                            <div class="fill" style="width: 45%;"></div>
                        </div>
                        <div class="footer-card">
                            <span>📝 3/6 talleres</span>
                            <span>⭐ 14.5</span>
                        </div>
                    </div>

                    <!-- Asignatura 3 -->
                    <div class="subject-card-student" onclick="verAsignatura(3)">
                        <h3>💧 El Ciclo del Agua - 4to</h3>
                        <p>Evaporación, condensación y precipitación</p>
                        <div class="progress-bar">
                            <div class="fill" style="width: 20%;"></div>
                        </div>
                        <div class="footer-card">
                            <span>📝 1/4 talleres</span>
                            <span>⭐ —</span>
                        </div>
                    </div>

                    <!-- Asignatura 4 -->
                    <div class="subject-card-student" onclick="verAsignatura(4)">
                        <h3>🔬 La Célula - 6to</h3>
                        <p>Estructura celular y organelos</p>
                        <div class="progress-bar">
                            <div class="fill" style="width: 90%;"></div>
                        </div>
                        <div class="footer-card">
                            <span>📝 7/7 talleres</span>
                            <span>⭐ 19.0</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        function verAsignatura(id) {
            // Redirigir a la vista de la asignatura (aún no creada)
            window.location.href = 'subject.php?id=' + id;
        }
    </script>
</body>
</html>