<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$id = (int)($_GET['id'] ?? 0);
$subject = Asignatura::find($id);
if ($subject === null || (int)$subject['id_docente'] !== (int)$user['id']) {
    flash('error', 'Asignatura no encontrada o sin permiso.');
    redirect(site_url('/public/teaching/subjects.php'));
}

$talleres  = Taller::byAsignatura($id);
$recursos  = Recurso::byAsignatura($id);

$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($subject['titulo']) ?> - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .subject-hero {
            background: var(--white);
            padding: 1.5rem 2rem;
            border-radius: 12px;
            border-left: 6px solid var(--primary);
            margin-bottom: 2rem;
        }
        .subject-hero h1 { color: var(--sidebar-color); margin: 0; }
        .subject-hero p { color: #5a7a6a; margin: 0.3rem 0 0; }
        .toolbar { display:flex; gap: .8rem; align-items:center; flex-wrap:wrap; }
        .toolbar .btn-primary { margin-left:auto; }
        .taller-item, .recurso-item {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-bottom: .8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .taller-item h4, .recurso-item h4 { color: var(--sidebar-color); margin: 0 0 .2rem; }
        .taller-meta { font-size:.82rem; color:#5a7a6a; display:flex; gap:1rem; flex-wrap:wrap; }
        .badge-etiqueta { font-size:.7rem; padding:.2rem .7rem; border-radius:15px; font-weight:600; }
        .tag-enlace { background:#dbeafe;color:#1e40af; }
        .tag-video { background:#fce7f3;color:#9d174d; }
        .tag-documento { background:#e8f5e9;color:#1b5e20; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1><a href="subjects.php" style="color:inherit;text-decoration:none;">📚 Asignaturas</a> / Detalle</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <div class="subject-hero">
                <h1><?= e($subject['titulo']) ?></h1>
                <p><?= e($subject['descripcion']) ?></p>
                <div style="margin-top:.8rem;display:flex;gap:1.5rem;font-size:.9rem;color:#5a7a6a;flex-wrap:wrap;">
                    <span>🎓 <?= (int)$subject['grado'] ?>° <?= e($subject['seccion']) ?></span>
                    <span>📝 <?= count($talleres) ?> Talleres</span>
                    <span>🔗 <?= count($recursos) ?> Recursos</span>
                    <span class="badge <?= $subject['estado'] === 'publicada' ? 'publicada' : 'borrador' ?>">
                        <?= $subject['estado'] === 'publicada' ? 'Publicada' : 'Borrador' ?>
                    </span>
                </div>
            </div>

            <!-- Talleres -->
            <section class="data-section">
                <div class="table-header">
                    <h2>📝 Talleres</h2>
                    <button class="btn-primary" onclick="abrirTaller()">➕ Nuevo Taller</button>
                </div>

                <?php if (count($talleres) === 0): ?>
                    <p style="color:#5a7a6a;text-align:center;">Sin talleres aún. Crea el primero para tus estudiantes.</p>
                <?php endif; ?>

                <?php foreach ($talleres as $t): ?>
                    <?php
                        $entregas = (int) Database::scalar('SELECT COUNT(*) FROM Entregas_Taller WHERE id_taller = :t', [':t' => (int)$t['id_taller']]);
                        $evaluadas = (int) Database::scalar(
                            'SELECT COUNT(*) FROM Evaluaciones ev JOIN Entregas_Taller et ON et.id_entrega = ev.id_entrega WHERE et.id_taller = :t',
                            [':t' => (int)$t['id_taller']]
                        );
                    ?>
                    <div class="taller-item">
                        <div>
                            <h4><?= $t['unidad'] ? '📚 U' . e($t['unidad']) . ': ' : '' ?><?= e($t['titulo']) ?></h4>
                            <p style="color:#5a7a6a;font-size:.9rem;margin:.2rem 0;"><?= e($t['descripcion']) ?></p>
                            <div class="taller-meta">
                                <?php if ($t['fecha_limite']): ?><span>📅 Entrega: <?= e(date('d/m/Y', strtotime($t['fecha_limite']))) ?></span><?php endif; ?>
                                <span>📤 <?= $entregas ?> entregas</span>
                                <span>⭐ <?= $evaluadas ?> evaluadas</span>
                            </div>
                        </div>
                        <div style="display:flex;gap:.5rem;">
                            <a class="btn-sm" href="taller.php?id=<?= (int)$t['id_taller'] ?>">📥 Revisar Entregas</a>
                            <button class="btn-sm" onclick="abrirTaller(<?= (int)$t['id_taller'] ?>, '<?= e($t['titulo'], true) ?>', '<?= e($t['descripcion'], true) ?>', '<?= e((string)$t['unidad'], true) ?>', '<?= e((string)$t['fecha_limite'], true) ?>')">✏️</button>
                            <form method="post" action="../src/core/subject.php" onsubmit="return confirm('¿Eliminar este taller y sus entregas?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_taller">
                                <input type="hidden" name="id" value="<?= (int)$t['id_taller'] ?>">
                                <button class="btn-sm" style="color:#b3261e;">🗑️</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Recursos -->
            <section class="data-section">
                <div class="table-header">
                    <h2>🔗 Recursos Multimedia</h2>
                    <button class="btn-primary" onclick="abrirRecurso()">➕ Nuevo Recurso</button>
                </div>

                <?php if (count($recursos) === 0): ?>
                    <p style="color:#5a7a6a;text-align:center;">Aún no has agregado recursos a esta asignatura.</p>
                <?php endif; ?>

                <?php foreach ($recursos as $r): ?>
                    <div class="recurso-item">
                        <div>
                            <h4><?= e($r['titulo']) ?></h4>
                            <p style="color:#5a7a6a;font-size:.82rem;margin:.2rem 0;word-break:break-all;">
                                <?php if ($r['tipo'] === 'documento'): ?>📄 Public/uploads/... (documento adjunto)<?php else: ?>🌐 <?= e($r['url_o_ruta']) ?><?php endif; ?>
                            </p>
                        </div>
                        <div style="display:flex;gap:.5rem;align-items:center;">
                            <span class="badge-etiqueta tag-<?= e($r['tipo']) ?>">
                                <?= $r['tipo'] === 'enlace' ? 'Enlace' : ($r['tipo'] === 'video' ? 'Video' : 'Documento') ?>
                            </span>
                            <a class="btn-sm" href="<?= e(asset_url($r['url_o_ruta'])) ?>" target="_blank" rel="noopener">🔎 Abrir</a>
                            <form method="post" action="../src/core/subject.php" onsubmit="return confirm('¿Eliminar este recurso?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_recurso">
                                <input type="hidden" name="id" value="<?= (int)$r['id_recurso'] ?>">
                                <button class="btn-sm" style="color:#b3261e;">🗑️</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        </main>
    </div>

    <!-- Modal Taller -->
    <div class="modal" id="modalTaller">
        <div class="modal-content">
            <h3 id="tallerTitulo">Nuevo Taller</h3>
            <form method="post" action="../src/core/subject.php" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_taller">
                <input type="hidden" name="id" id="taller_id" value="0">
                <input type="hidden" name="asignatura_id" value="<?= (int)$subject['id_asignatura'] ?>">
                <div class="form-row" style="gap:1rem;">
                    <div class="form-group">
                        <label>Unidad</label>
                        <input type="number" id="taller_unidad" name="unidad" min="1" max="<?= (int)Config::get('academic.subject.max_units', 8) ?>" placeholder="Nº">
                    </div>
                    <div class="form-group">
                        <label>Fecha límite</label>
                        <input type="date" id="taller_fecha" name="fecha_limite">
                    </div>
                </div>
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" id="taller_nombre" name="titulo" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea id="taller_desc" name="descripcion" rows="3"></textarea>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1rem;">
                    <button class="btn-primary" style="flex:2;">💾 Guardar</button>
                    <button type="button" class="btn-sm" style="flex:1;" onclick="document.getElementById('modalTaller').classList.remove('active')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Recurso -->
    <div class="modal" id="modalRecurso">
        <div class="modal-content">
            <h3>Nuevo Recurso</h3>
            <form method="post" action="../src/core/subject.php" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_recurso">
                <input type="hidden" name="asignatura_id" value="<?= (int)$subject['id_asignatura'] ?>">
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select id="tipo_recurso" name="tipo" onchange="alternarRecurso()">
                        <option value="enlace">Enlace web</option>
                        <option value="video">Video</option>
                        <option value="documento">Documento</option>
                    </select>
                </div>
                <div class="form-group" id="recurso_url">
                    <label>📎 URL</label>
                    <input type="text" name="url" placeholder="https://...">
                </div>
                <div class="form-group hidden" id="recurso_archivo" style="display:none;">
                    <label>📄 Subir documento</label>
                    <input type="file" name="archivo" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1rem;">
                    <button class="btn-primary" style="flex:2;">💾 Guardar</button>
                    <button type="button" class="btn-sm" style="flex:1;" onclick="document.getElementById('modalRecurso').classList.remove('active')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirTaller(id = 0, titulo = '', desc = '', unidad = '', fecha = '') {
            document.getElementById('tallerTitulo').textContent = id ? 'Editar Taller' : 'Nuevo Taller';
            document.getElementById('taller_id').value = id;
            document.getElementById('taller_nombre').value = titulo;
            document.getElementById('taller_desc').value = desc;
            document.getElementById('taller_unidad').value = unidad || '';
            document.getElementById('taller_fecha').value = fecha || '';
            document.getElementById('modalTaller').classList.add('active');
        }

        function abrirRecurso() {
            document.getElementById('modalRecurso').classList.add('active');
            alternarRecurso();
        }

        function alternarRecurso() {
            const tipo = document.getElementById('tipo_recurso').value;
            document.getElementById('recurso_url').style.display      = tipo === 'documento' ? 'none' : 'block';
            document.getElementById('recurso_archivo').style.display = tipo === 'documento' ? 'block' : 'none';
        }
    </script>
</body>
</html>