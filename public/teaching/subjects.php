<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$subjects = Asignatura::byDocenteWithStats((int)$user['id']);

$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asignaturas - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>📚 Mis Asignaturas</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="🔍 Buscar asignatura..." onkeyup="filtrar()">
                <button class="btn-primary" onclick="abrirModal()">➕ Nueva Asignatura</button>
            </div>

            <div class="subjects-grid" id="gridAsignaturas">
                <?php if (count($subjects) === 0): ?>
                    <div id="sinResultados" style="grid-column:1/-1;text-align:center;padding:2rem;color:#5a7a6a;">
                        <p>Aún no has creado asignaturas. Pulsa "Nueva Asignatura" para comenzar.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($subjects as $a): ?>
                    <div class="subject-card" data-nombre="<?= e(mb_strtolower($a['titulo'])) ?>" data-estado="<?= e($a['estado']) ?>">
                        <div class="subject-header">
                            <h3 class="subject-title"><?= e($a['titulo']) ?></h3>
                            <span class="badge <?= $a['estado'] === 'publicada' ? 'publicada' : 'borrador' ?>">
                                <?= $a['estado'] === 'publicada' ? 'Publicada' : 'Borrador' ?>
                            </span>
                        </div>
                        <p style="color:#64748b;font-size:0.9rem;"><?= e($a['descripcion']) ?></p>
                        <div class="subject-meta">
                            <span>🎓 <?= (int)$a['grado'] ?>° <?= e($a['seccion']) ?></span>
                            <span>📝 <?= (int)$a['total_talleres'] ?> Talleres</span>
                            <span>📤 <?= (int)$a['total_entregas'] ?> Entregas</span>
                        </div>
                        <div class="subject-actions">
                            <a class="btn-sm" href="subject.php?id=<?= (int)$a['id_asignatura'] ?>">👁️ Ver</a>
                            <button class="btn-sm" onclick="editar(<?= (int)$a['id_asignatura'] ?>, '<?= e($a['titulo'], true) ?>', '<?= e($a['descripcion'], true) ?>', <?= (int)$a['grado'] ?>, '<?= e($a['seccion']) ?>', '<?= e($a['estado']) ?>')">✏️ Editar</button>
                            <form method="post" action="../src/core/subject.php" style="display:inline;" onsubmit="return confirm('¿Publicar / guardar esta asignatura?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="set_estado">
                                <input type="hidden" name="id" value="<?= (int)$a['id_asignatura'] ?>">
                                <input type="hidden" name="estado" value="<?= $a['estado'] === 'publicada' ? 'borrador' : 'publicada' ?>">
                                <button class="btn-sm"><?= $a['estado'] === 'publicada' ? '📄 Borrador' : '✅ Publicar' ?></button>
                            </form>
                            <form method="post" action="../src/core/subject.php" style="display:inline;" onsubmit="return confirm('¿Eliminar esta asignatura y sus talleres?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$a['id_asignatura'] ?>">
                                <input type="hidden" name="action" value="delete_subject">
                                <button class="btn-sm" style="color:#b3261e;">🗑️</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="sinResultados" style="display:none;text-align:center;padding:2rem;">
                <p>🔍 No se encontraron asignaturas</p>
            </div>
        </main>
    </div>

    <div class="modal" id="modal">
        <div class="modal-content">
            <h3 id="modalTitulo">Crear Asignatura</h3>
            <form method="post" action="../src/core/subject.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_subject">
                <input type="hidden" name="id" id="f_id" value="0">
                <div class="form-group">
                    <label for="f_titulo">Título</label>
                    <input type="text" id="f_titulo" name="titulo" placeholder="Ej. Ciencias Naturales - 4to" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="f_descripcion">Descripción</label>
                    <textarea id="f_descripcion" name="descripcion" rows="3" placeholder="Temática general de la asignatura..." required></textarea>
                </div>
                <div class="form-row" style="gap:1rem;">
                    <div class="form-group">
                        <label for="f_grado">Grado</label>
                        <select id="f_grado" name="grado">
                            <?php foreach (Config::get('academic.grados', []) as $g): ?>
                                <option value="<?= $g ?>">Grado <?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f_seccion">Sección</label>
                        <select id="f_seccion" name="seccion">
                            <?php foreach (Config::get('academic.secciones', []) as $sec): ?>
                                <option value="<?= $sec ?>"><?= $sec ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f_estado">Estado</label>
                        <select id="f_estado" name="estado">
                            <option value="borrador">Borrador</option>
                            <option value="publicada">Publicada</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                    <button class="btn-primary" style="flex:2;">💾 Guardar</button>
                    <button type="button" class="btn-sm" style="flex:1;" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let filtroActual = 'todas';

        function filtrar() {
            const texto = document.getElementById('searchInput').value.toLowerCase();
            const tarjetas = document.querySelectorAll('.subject-card');
            let visibles = 0;
            tarjetas.forEach(t => {
                const nombre = t.getAttribute('data-nombre');
                const estado = t.getAttribute('data-estado');
                const coincideTexto = nombre.includes(texto);
                const coincideFiltro = filtroActual === 'todas' || estado === filtroActual;
                t.style.display = (coincideTexto && coincideFiltro) ? 'block' : 'none';
                if (coincideTexto && coincideFiltro) visibles++;
            });
            document.getElementById('sinResultados').style.display = visibles === 0 ? 'block' : 'none';
        }

        function abrirModal() {
            document.getElementById('modalTitulo').textContent = 'Crear Asignatura';
            document.getElementById('f_id').value = 0;
            document.getElementById('f_titulo').value = '';
            document.getElementById('f_descripcion').value = '';
            document.getElementById('f_grado').value = 4;
            document.getElementById('f_seccion').value = 'A';
            document.getElementById('f_estado').value = 'borrador';
            document.getElementById('modal').classList.add('active');
        }

        function editar(id, titulo, descripcion, grado, seccion, estado) {
            document.getElementById('modalTitulo').textContent = 'Editar Asignatura';
            document.getElementById('f_id').value = id;
            document.getElementById('f_titulo').value = titulo;
            document.getElementById('f_descripcion').value = descripcion;
            document.getElementById('f_grado').value = grado;
            document.getElementById('f_seccion').value = seccion;
            document.getElementById('f_estado').value = estado;
            document.getElementById('modal').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modal').classList.remove('active');
        }
    </script>
</body>
</html>