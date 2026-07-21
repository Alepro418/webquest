<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignatura - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .subject-header {
            background: var(--white);
            padding: 1.5rem 2rem;
            border-radius: 12px;
            border-left: 6px solid var(--primary);
            margin-bottom: 2rem;
        }

        .subject-header h1 {
            color: var(--sidebar-color);
            margin: 0;
        }

        .subject-header p {
            color: #5a7a6a;
            margin: 0.3rem 0 0;
        }

        .talleres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .taller-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            transition: transform 0.2s;
        }

        .taller-card:hover {
            transform: translateY(-3px);
        }

        .taller-card .estado-taller {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .taller-card .estado-taller.pendiente {
            background: #fff3e0;
            color: #e65100;
        }

        .taller-card .estado-taller.completado {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .taller-card .estado-taller.atrasado {
            background: #fce4ec;
            color: #c62828;
        }

        .taller-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-subir {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-subir:hover {
            background: var(--primary-hover);
        }

        .btn-ver {
            background: #e8f5e9;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-ver:hover {
            background: #c8e6c9;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <!-- Cabecera de la asignatura -->
            <div class="subject-header">
                <h1>🌿 Ciencias Naturales - 4to</h1>
                <p>Seres vivos, ecosistemas y cuerpo humano</p>
                <div style="margin-top: 0.8rem; display: flex; gap: 1.5rem; font-size: 0.9rem; color: #5a7a6a;">
                    <span>📚 4 Unidades</span>
                    <span>📝 8 Talleres</span>
                    <span>⭐ Progreso: 75%</span>
                </div>
            </div>

            <!-- Listado de talleres -->
            <section class="data-section">
                <div class="table-header">
                    <h2>📝 Talleres</h2>
                    <span style="color: #5a7a6a; font-size: 0.9rem;">Completa los talleres en tu libreta y sube tu evidencia</span>
                </div>

                <div class="talleres-grid">
                    <!-- Taller 1 -->
                    <div class="taller-card">
                        <h4>🌱 Unidad 1: Ecosistemas</h4>
                        <p style="font-size: 0.9rem; color: #5a7a6a;">Identifica los elementos de un ecosistema</p>
                        <div style="margin: 0.6rem 0; display: flex; gap: 0.8rem; font-size: 0.8rem; color: #5a7a6a;">
                            <span>📅 Entrega: 10/07/2026</span>
                            <span class="estado-taller completado">✅ Completado</span>
                        </div>
                        <div class="taller-actions">
                            <button class="btn-ver" onclick="verTaller(1)">👁️ Ver</button>
                        </div>
                    </div>

                    <!-- Taller 2 -->
                    <div class="taller-card">
                        <h4>🌿 Unidad 2: Cadena Alimenticia</h4>
                        <p style="font-size: 0.9rem; color: #5a7a6a;">Dibuja una cadena alimenticia de tu región</p>
                        <div style="margin: 0.6rem 0; display: flex; gap: 0.8rem; font-size: 0.8rem; color: #5a7a6a;">
                            <span>📅 Entrega: 17/07/2026</span>
                            <span class="estado-taller pendiente">⏳ Pendiente</span>
                        </div>
                        <div class="taller-actions">
                            <button class="btn-subir" onclick="subirEvidencia(2)">📤 Subir evidencia</button>
                        </div>
                    </div>

                    <!-- Taller 3 -->
                    <div class="taller-card">
                        <h4>🧬 Unidad 3: La Célula</h4>
                        <p style="font-size: 0.9rem; color: #5a7a6a;">Dibuja y señala las partes de una célula animal</p>
                        <div style="margin: 0.6rem 0; display: flex; gap: 0.8rem; font-size: 0.8rem; color: #5a7a6a;">
                            <span>📅 Entrega: 24/07/2026</span>
                            <span class="estado-taller pendiente">⏳ Pendiente</span>
                        </div>
                        <div class="taller-actions">
                            <button class="btn-subir" onclick="subirEvidencia(3)">📤 Subir evidencia</button>
                        </div>
                    </div>

                    <!-- Taller 4 -->
                    <div class="taller-card">
                        <h4>🧪 Unidad 4: Experimentos</h4>
                        <p style="font-size: 0.9rem; color: #5a7a6a;">Realiza un experimento de germinación</p>
                        <div style="margin: 0.6rem 0; display: flex; gap: 0.8rem; font-size: 0.8rem; color: #5a7a6a;">
                            <span>📅 Entrega: 31/07/2026</span>
                            <span class="estado-taller pendiente">⏳ Pendiente</span>
                        </div>
                        <div class="taller-actions">
                            <button class="btn-subir" onclick="subirEvidencia(4)">📤 Subir evidencia</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        function subirEvidencia(id) {
            alert('📤 Simulación: Abrir selector de archivos para el taller ' + id);
        }

        function verTaller(id) {
            alert('👁️ Ver detalles del taller ' + id);
        }
    </script>
</body>
</html>