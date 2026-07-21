<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>Gestión de Cohorte: Ciencias Naturales</h1>
                <div class="user-info">Docente: Juan Pérez</div>
            </header>

            <!-- Indicadores Clave de Rendimiento (KPI) -->
            <section class="kpi-grid">
                <div class="kpi-card">
                    <h3>Total Estudiantes</h3>
                    <p class="number">32</p>
                </div>
                <div class="kpi-card">
                    <h3>Entregas Hoy</h3>
                    <p class="number">12</p>
                </div>
                <div class="kpi-card">
                    <h3>Promedio Grupal</h3>
                    <p class="number">16.5</p>
                </div>
                <div class="kpi-card alert">
                    <h3>Por Mejorar</h3>
                    <p class="number">4</p>
                </div>
            </section>

            <!-- Listado de estudiantes con filtros -->
            <section class="data-section">
                <div class="table-header">
                    <h2>Listado de Estudiantes</h2>
                    <div class="filters">
                        <select>
                            <option>Todos los Grados</option>
                        </select>
                        <button class="btn-primary">Filtrar</button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Grado</th>
                            <th>Promedio</th>
                            <th>Estatus Motivacional</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>María García</td>
                            <td>4to Grado</td>
                            <td>19.2</td>
                            <td>
                                <span class="status st-sobresaliente">Sobresaliente</span>
                            </td>
                            <td>
                                <button class="btn-action">Ver Perfil</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Carlos Ruiz</td>
                            <td>4to Grado</td>
                            <td>14.5</td>
                            <td>
                                <span class="status st-bueno">Buen Estudiante</span>
                            </td>
                            <td>
                                <button class="btn-action">Ver Perfil</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Luis Pineda</td>
                            <td>4to Grado</td>
                            <td>10.8</td>
                            <td>
                                <span class="status st-mejorar">Vamos a Mejorar</span>
                            </td>
                            <td>
                                <button class="btn-action">Ver Perfil</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>