<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asignaturas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Solo lo más importante */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.2rem;
            margin: 1.5rem 0;
        }
        
        .subject-card {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            border: 1px solid #e2e8f0;
        }
        
        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
        }
        
        .subject-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            background: #f1f5f9;
        }
        
        .badge.publicada { background: #dcfce7; color: #166534; }
        .badge.borrador { background: #f1f5f9; color: #475569; }
        
        .subject-meta {
            display: flex;
            gap: 1rem;
            margin: 0.8rem 0;
            font-size: 0.8rem;
            color: #64748b;
        }
        
        .subject-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .btn-sm:hover {
            background: #f8fafc;
        }
        
        .btn-primary {
            background: #1e293b;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }
        
        .filter-bar input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            min-width: 200px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 0.4rem 1rem;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 20px;
            cursor: pointer;
        }
        
        .filter-tab.active {
            background: #1e293b;
            color: white;
            border-color: #1e293b;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
        }
        
        .modal-content input,
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 0.5rem;
            margin: 0.3rem 0 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        
        .modal-content textarea {
            min-height: 80px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <!-- Contenido -->
        <main class="main-content">
            <header class="top-bar">
                <h1>📚 Mis Asignaturas</h1>
                <div class="date-info">
                    <?php 
                        date_default_timezone_set('America/Caracas');
                        echo date('d/m/Y');
                    ?>
                </div>
            </header>

            <!-- Barra de búsqueda y filtros -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="🔍 Buscar asignatura..." onkeyup="filtrar()">
                
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filtrarPor('todas')">📋 Todas</button>
                    <button class="filter-tab" onclick="filtrarPor('publicada')">✅ Publicadas</button>
                    <button class="filter-tab" onclick="filtrarPor('borrador')">📝 Borradores</button>
                </div>
                
                <button class="btn-primary" onclick="abrirModal()">➕ Nueva</button>
            </div>

            <!-- Grid de asignaturas -->
            <div class="subjects-grid" id="gridAsignaturas">
                <!-- Asignatura 1 -->
                <div class="subject-card" data-estado="publicada" data-nombre="Ciencias Naturales 4to">
                    <div class="subject-header">
                        <h3 class="subject-title">🌿 Ciencias Naturales - 4to</h3>
                        <span class="badge publicada">Publicada</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem;">Seres vivos, ecosistemas y cuerpo humano</p>
                    <div class="subject-meta">
                        <span>📚 4 Unidades</span>
                        <span>📝 8 Talleres</span>
                    </div>
                    <div class="subject-actions">
                        <button class="btn-sm" onclick="ver(1)">👁️ Ver</button>
                        <button class="btn-sm" onclick="editar(1)">✏️ Editar</button>
                        <button class="btn-sm" onclick="eliminar(1)">🗑️</button>
                    </div>
                </div>

                <!-- Asignatura 2 -->
                <div class="subject-card" data-estado="publicada" data-nombre="El Reino Animal">
                    <div class="subject-header">
                        <h3 class="subject-title">🦁 El Reino Animal - 5to</h3>
                        <span class="badge publicada">Publicada</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem;">Vertebrados, invertebrados y hábitats</p>
                    <div class="subject-meta">
                        <span>📚 3 Unidades</span>
                        <span>📝 6 Talleres</span>
                    </div>
                    <div class="subject-actions">
                        <button class="btn-sm" onclick="ver(2)">👁️ Ver</button>
                        <button class="btn-sm" onclick="editar(2)">✏️ Editar</button>
                        <button class="btn-sm" onclick="eliminar(2)">🗑️</button>
                    </div>
                </div>

                <!-- Asignatura 3 (Borrador) -->
                <div class="subject-card" data-estado="borrador" data-nombre="El Ciclo del Agua">
                    <div class="subject-header">
                        <h3 class="subject-title">💧 El Ciclo del Agua - 4to</h3>
                        <span class="badge borrador">Borrador</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem;">Evaporación, condensación y precipitación</p>
                    <div class="subject-meta">
                        <span>📚 2 Unidades</span>
                        <span>📝 4 Talleres</span>
                    </div>
                    <div class="subject-actions">
                        <button class="btn-sm" onclick="editar(3)">✏️ Editar</button>
                        <button class="btn-sm" onclick="publicar(3)">✅ Publicar</button>
                        <button class="btn-sm" onclick="eliminar(3)">🗑️</button>
                    </div>
                </div>

                <!-- Asignatura 4 -->
                <div class="subject-card" data-estado="publicada" data-nombre="La Célula">
                    <div class="subject-header">
                        <h3 class="subject-title">🔬 La Célula - 6to</h3>
                        <span class="badge publicada">Publicada</span>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem;">Estructura celular y organelos</p>
                    <div class="subject-meta">
                        <span>📚 3 Unidades</span>
                        <span>📝 5 Talleres</span>
                    </div>
                    <div class="subject-actions">
                        <button class="btn-sm" onclick="ver(4)">👁️ Ver</button>
                        <button class="btn-sm" onclick="editar(4)">✏️ Editar</button>
                        <button class="btn-sm" onclick="eliminar(4)">🗑️</button>
                    </div>
                </div>
            </div>

            <!-- Mensaje sin resultados -->
            <div id="sinResultados" style="display: none; text-align: center; padding: 3rem;">
                <p>🔍 No se encontraron asignaturas</p>
                <button class="btn-primary" onclick="resetFiltros()">Limpiar filtros</button>
            </div>
        </main>
    </div>

    <!-- Modal para crear/editar -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <h3 id="modalTitulo">Crear Asignatura</h3>
            
            <input type="text" id="nombre" placeholder="Título de la asignatura">
            
            <textarea id="descripcion" placeholder="Descripción breve..."></textarea>
            
            <select id="grado">
                <option value="4">4to Grado</option>
                <option value="5">5to Grado</option>
                <option value="6">6to Grado</option>
            </select>
            
            <select id="estadoInicial">
                <option value="borrador">Guardar como borrador</option>
                <option value="publicada">Publicar ahora</option>
            </select>
            
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button class="btn-primary" style="flex: 2;" onclick="guardar()">Guardar</button>
                <button class="btn-sm" style="flex: 1;" onclick="cerrarModal()">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación -->
    <div class="modal" id="modalEliminar">
        <div class="modal-content">
            <h3>🗑️ Confirmar</h3>
            <p>¿Eliminar esta asignatura?</p>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn-primary" style="background: #ef4444;" onclick="confirmarEliminar()">Sí, eliminar</button>
                <button class="btn-sm" onclick="cerrarModalEliminar()">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        // Variables
        let filtroActual = 'todas';
        let idEliminar = null;
        let idEditando = null;

        // Filtrar por texto
        function filtrar() {
            const texto = document.getElementById('searchInput').value.toLowerCase();
            const tarjetas = document.querySelectorAll('.subject-card');
            let visibles = 0;

            tarjetas.forEach(t => {
                const nombre = t.getAttribute('data-nombre').toLowerCase();
                const estado = t.getAttribute('data-estado');
                
                const coincideTexto = nombre.includes(texto);
                const coincideFiltro = filtroActual === 'todas' || estado === filtroActual;
                
                if (coincideTexto && coincideFiltro) {
                    t.style.display = 'block';
                    visibles++;
                } else {
                    t.style.display = 'none';
                }
            });

            document.getElementById('sinResultados').style.display = visibles === 0 ? 'block' : 'none';
        }

        // Filtrar por estado
        function filtrarPor(estado) {
            filtroActual = estado;
            
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            
            filtrar();
        }

        // Reset filtros
        function resetFiltros() {
            document.getElementById('searchInput').value = '';
            filtroActual = 'todas';
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            document.querySelector('.filter-tab').classList.add('active');
            filtrar();
        }

        // Modal crear/editar
        function abrirModal() {
            idEditando = null;
            document.getElementById('modalTitulo').textContent = 'Crear Asignatura';
            document.getElementById('nombre').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('grado').value = '4';
            document.getElementById('estadoInicial').value = 'borrador';
            document.getElementById('modal').classList.add('active');
        }

        function editar(id) {
            idEditando = id;
            document.getElementById('modalTitulo').textContent = 'Editar Asignatura';
            document.getElementById('nombre').value = 'Asignatura ' + id;
            document.getElementById('descripcion').value = 'Descripción de ejemplo';
            document.getElementById('modal').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modal').classList.remove('active');
        }

        function guardar() {
            const datos = {
                nombre: document.getElementById('nombre').value,
                descripcion: document.getElementById('descripcion').value,
                grado: document.getElementById('grado').value,
                estado: document.getElementById('estadoInicial').value
            };
            
            if (!datos.nombre || !datos.descripcion) {
                alert('Completa todos los campos');
                return;
            }
            
            alert(idEditando ? 'Asignatura actualizada' : 'Asignatura creada');
            cerrarModal();
        }

        // Eliminar
        function eliminar(id) {
            idEliminar = id;
            document.getElementById('modalEliminar').classList.add('active');
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminar').classList.remove('active');
            idEliminar = null;
        }

        function confirmarEliminar() {
            alert('Asignatura eliminada');
            cerrarModalEliminar();
        }

        // Acciones
        function ver(id) {
            alert('Ver detalles de asignatura ' + id);
        }

        function publicar(id) {
            if (confirm('¿Publicar esta asignatura?')) {
                alert('Asignatura publicada');
            }
        }
    </script>
</body>
</html>