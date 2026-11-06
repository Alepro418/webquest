<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$docenteId = (int)$user['id'];
$proyectos = ProyectoAula::byDocenteWithStats($docenteId);
$activo    = ProyectoAula::activo($docenteId);
$bloqueo   = ProyectoAula::bloqueoContenido($activo);

$porMomento = ['1' => [], '2' => [], '3' => []];
foreach ($proyectos as $p) {
    $porMomento[(string)$p['momento']][] = $p;
}

$abrirNuevo  = (string)($_GET['nuevo'] ?? '');
$abrirEditar = (int)($_GET['editar'] ?? 0);

$paraJs = [];
foreach ($proyectos as $p) {
    $paraJs[] = [
        'id'           => (int)$p['id_proyecto'],
        'titulo'       => (string)$p['titulo'],
        'descripcion'  => (string)($p['descripcion'] ?? ''),
        'momento'      => (string)$p['momento'],
        'anio_escolar' => (string)$p['anio_escolar'],
        'grado'        => (int)$p['grado'],
        'seccion'      => (string)$p['seccion'],
        'fecha_inicio' => (string)($p['fecha_inicio'] ?? ''),
        'fecha_fin'    => (string)($p['fecha_fin'] ?? ''),
        'estado'       => (string)$p['estado'],
    ];
}
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

$flashSuccess = flash('success');
$flashError   = flash('error');
$anioDefecto  = ProyectoAula::anioEscolarActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto de Aula - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>🗂️ Configuración · Proyecto de Aula</h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <!-- Momento activo -->
            <?php if ($activo === null): ?>
                <section class="pa-warning">
                    <h3>⚠️ ATENCIÓN</h3>
                    <p>Todavía no has configurado ningún Proyecto de Aula. Sin un PA publicado no podrás crear asignaturas ni talleres.</p>
                    <button class="btn-primary" onclick="nuevoPA('1')">➕ Configurar el 1er momento</button>
                </section>
            <?php else: ?>
                <?php
                    $estadoPeriodo = ProyectoAula::estadoPeriodo($activo);
                    $estadoActivo  = $activo['estado'];
                    $activoStats   = $activo;
                    foreach ($proyectos as $p) {
                        if ((int)$p['id_proyecto'] === (int)$activo['id_proyecto']) {
                            $activoStats = $p;
                            break;
                        }
                    }
                ?>
                <section class="pa-active">
                    <div class="pa-active-head">
                        <div>
                            <span class="pa-eyebrow">MOMENTO ACTIVO</span>
                            <h2><?= e(ProyectoAula::momentoLabel((string)$activo['momento'])) ?> · <?= e($activo['titulo']) ?></h2>
                            <p><?= e((string)($activo['descripcion'] ?? '')) ?></p>
                        </div>
                        <span class="badge <?= e($estadoActivo) ?>"><?= e(ucfirst($estadoActivo)) ?></span>
                    </div>
                    <div class="pa-active-meta">
                        <span>🎓 <?= (int)$activo['grado'] ?>° "<?= e($activo['seccion']) ?>"</span>
                        <span>📅 <?= e(ProyectoAula::periodoTexto($activo)) ?></span>
                        <span>⏱️ <?= e($estadoPeriodo) ?></span>
                        <span>📚 <?= (int)($activoStats['total_asignaturas'] ?? 0) ?> asignaturas</span>
                        <span>📝 <?= (int)($activoStats['total_talleres'] ?? 0) ?> talleres</span>
                    </div>
                    <?php if ($bloqueo !== null): ?>
                        <p class="pa-active-alert">⛔ <?= e(ProyectoAula::mensajeBloqueo($bloqueo)) ?></p>
                    <?php else: ?>
                        <div class="pa-active-actions">
                            <a class="btn-primary" href="subjects.php">📚 Ir a mis asignaturas</a>
                            <a class="btn-sm" href="reports.php?proyecto=<?= (int)$activo['id_proyecto'] ?>">📊 Ver reportes del momento</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Reglas -->
            <section class="data-section">
                <div class="table-header"><h2>📋 Reglas de configuración</h2></div>
                <ul class="rules-list">
                    <li>Un Proyecto de Aula debe estar configurado y <strong>publicado</strong> antes de crear asignaturas o talleres.</li>
                    <li>Un momento no puede tener más de un Proyecto de Aula activo por grado, sección y año escolar.</li>
                    <li>Las fechas de inicio y fin deben estar dentro del año escolar activo.</li>
                    <li>La fecha de fin debe ser posterior a la fecha de inicio.</li>
                    <li>Un PA <strong>cerrado</strong> no acepta nuevas entregas; se puede consultar en los reportes.</li>
                </ul>
            </section>

            <!-- Momentos -->
            <?php foreach (['1', '2', '3'] as $momento): ?>
                <?php $lista = $porMomento[$momento]; ?>
                <section class="data-section" id="momento-<?= $momento ?>">
                    <div class="table-header">
                        <h2><?= e(ProyectoAula::momentoLabel($momento)) ?></h2>
                        <button class="btn-primary" onclick="nuevoPA('<?= $momento ?>')">➕ Asignar PA</button>
                    </div>

                    <?php if (count($lista) === 0): ?>
                        <p class="pa-empty">⚪ Sin configurar. Asigna un Proyecto de Aula para este momento.</p>
                    <?php endif; ?>

                    <div class="pa-grid">
                        <?php foreach ($lista as $p): ?>
                            <?php
                                $esActivo = $activo !== null && (int)$activo['id_proyecto'] === (int)$p['id_proyecto'];
                                $periodo  = ProyectoAula::estadoPeriodo($p);
                            ?>
                            <article class="pa-card <?= $esActivo ? 'activo' : '' ?>">
                                <div class="pa-card-head">
                                    <div>
                                        <h3><?= e($p['titulo']) ?></h3>
                                        <span class="pa-sub"><?= (int)$p['grado'] ?>° "<?= e($p['seccion']) ?>" · <?= e($p['anio_escolar']) ?></span>
                                    </div>
                                    <span class="badge <?= e($p['estado']) ?>"><?= e(ucfirst($p['estado'])) ?></span>
                                </div>

                                <?php if (!empty($p['descripcion'])): ?>
                                    <p class="pa-desc"><?= e($p['descripcion']) ?></p>
                                <?php endif; ?>

                                <div class="pa-meta">
                                    <span>📅 <?= e(ProyectoAula::periodoTexto($p)) ?></span>
                                    <span>⏱️ <?= e($periodo) ?></span>
                                    <span>📚 <?= (int)$p['total_asignaturas'] ?> asignaturas</span>
                                    <span>📝 <?= (int)$p['total_talleres'] ?> talleres</span>
                                    <span>📤 <?= (int)$p['total_entregas'] ?> entregas</span>
                                </div>

                                <div class="pa-actions">
                                    <?php if ($esActivo): ?>
                                        <span class="status st-bueno">✅ Momento activo</span>
                                    <?php else: ?>
                                        <form method="post" action="../../src/core/pa.php">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="activar_pa">
                                            <input type="hidden" name="id" value="<?= (int)$p['id_proyecto'] ?>">
                                            <button class="btn-sm">🎯 Activar</button>
                                        </form>
                                    <?php endif; ?>

                                    <button class="btn-sm" onclick="editarPA(<?= (int)$p['id_proyecto'] ?>)">✏️ Editar</button>

                                    <?php if ($p['estado'] === 'borrador'): ?>
                                        <form method="post" action="../../src/core/pa.php" onsubmit="return confirm('¿Publicar este Proyecto de Aula? Se habilitará la creación de asignaturas y talleres.')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="set_estado_pa">
                                            <input type="hidden" name="id" value="<?= (int)$p['id_proyecto'] ?>">
                                            <input type="hidden" name="estado" value="publicada">
                                            <button class="btn-sm">✅ Publicar</button>
                                        </form>
                                    <?php elseif ($p['estado'] === 'publicada'): ?>
                                        <form method="post" action="../../src/core/pa.php" onsubmit="return confirm('¿Cerrar este Proyecto de Aula? No se aceptarán nuevas entregas.')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="set_estado_pa">
                                            <input type="hidden" name="id" value="<?= (int)$p['id_proyecto'] ?>">
                                            <input type="hidden" name="estado" value="cerrada">
                                            <button class="btn-sm">🔒 Cerrar</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="../../src/core/pa.php" onsubmit="return confirm('¿Reabrir este Proyecto de Aula como borrador?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="set_estado_pa">
                                            <input type="hidden" name="id" value="<?= (int)$p['id_proyecto'] ?>">
                                            <input type="hidden" name="estado" value="borrador">
                                            <button class="btn-sm">↩️ Reabrir</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" action="../../src/core/pa.php" onsubmit="return confirm('¿Eliminar este Proyecto de Aula? Se eliminarán también sus asignaturas, talleres y entregas.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_pa">
                                        <input type="hidden" name="id" value="<?= (int)$p['id_proyecto'] ?>">
                                        <button class="btn-sm" style="color:#b3261e;">🗑️</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </main>
    </div>

    <!-- Modal PA -->
    <div class="modal" id="modalPA">
        <div class="modal-content">
            <h3 id="paModalTitulo">Asignar Proyecto de Aula</h3>
            <form method="post" action="../../src/core/pa.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_pa">
                <input type="hidden" name="id" id="pa_id" value="0">

                <div class="form-group">
                    <label for="pa_titulo">Título del Proyecto de Aula</label>
                    <input type="text" id="pa_titulo" name="titulo" required minlength="5" maxlength="150" placeholder="Ej. Ecosistemas Terrestres">
                </div>
                <div class="form-group">
                    <label for="pa_descripcion">Descripción pedagógica</label>
                    <textarea id="pa_descripcion" name="descripcion" rows="3" placeholder="Contenidos y propósito del momento..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pa_momento">Momento pedagógico</label>
                        <select id="pa_momento" name="momento">
                            <?php foreach (['1', '2', '3'] as $m): ?>
                                <option value="<?= $m ?>"><?= e(ProyectoAula::momentoLabel($m)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pa_anio">Año escolar</label>
                        <input type="text" id="pa_anio" name="anio_escolar" required pattern="\d{4}-\d{4}" value="<?= e($anioDefecto) ?>" placeholder="2025-2026">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pa_grado">Grado</label>
                        <select id="pa_grado" name="grado">
                            <?php foreach (Config::get('academic.grados', []) as $g): ?>
                                <option value="<?= (int)$g ?>">Grado <?= (int)$g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pa_seccion">Sección</label>
                        <select id="pa_seccion" name="seccion">
                            <?php foreach (Config::get('academic.secciones', []) as $sec): ?>
                                <option value="<?= e($sec) ?>"><?= e($sec) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pa_inicio">Fecha de inicio del momento</label>
                        <input type="date" id="pa_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="pa_fin">Fecha de fin del momento</label>
                        <input type="date" id="pa_fin" name="fecha_fin" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pa_estado">Estado</label>
                    <select id="pa_estado" name="estado">
                        <option value="borrador">Borrador (no habilita contenido)</option>
                        <option value="publicada">Publicada (habilita contenido)</option>
                        <option value="cerrada">Cerrada (sin nuevas entregas)</option>
                    </select>
                </div>
                <div style="display:flex;gap:.5rem;margin-top:1rem;">
                    <button class="btn-primary" style="flex:2;">💾 Guardar</button>
                    <button type="button" class="btn-sm" style="flex:1;" onclick="cerrarModalPA()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const PROYECTOS = <?= json_encode($paraJs, $jsonFlags) ?>;

        function nuevoPA(momento) {
            document.getElementById('paModalTitulo').textContent = 'Asignar Proyecto de Aula';
            document.getElementById('pa_id').value = 0;
            document.getElementById('pa_titulo').value = '';
            document.getElementById('pa_descripcion').value = '';
            document.getElementById('pa_momento').value = momento || '1';
            document.getElementById('pa_anio').value = <?= json_encode($anioDefecto, $jsonFlags) ?>;
            document.getElementById('pa_grado').value = '4';
            document.getElementById('pa_seccion').value = 'A';
            document.getElementById('pa_inicio').value = '';
            document.getElementById('pa_fin').value = '';
            document.getElementById('pa_estado').value = 'borrador';
            document.getElementById('modalPA').classList.add('active');
        }

        function editarPA(id) {
            const p = PROYECTOS.find(x => x.id === id);
            if (!p) return;
            document.getElementById('paModalTitulo').textContent = 'Editar Proyecto de Aula';
            document.getElementById('pa_id').value = p.id;
            document.getElementById('pa_titulo').value = p.titulo;
            document.getElementById('pa_descripcion').value = p.descripcion;
            document.getElementById('pa_momento').value = p.momento;
            document.getElementById('pa_anio').value = p.anio_escolar;
            document.getElementById('pa_grado').value = p.grado;
            document.getElementById('pa_seccion').value = p.seccion;
            document.getElementById('pa_inicio').value = p.fecha_inicio;
            document.getElementById('pa_fin').value = p.fecha_fin;
            document.getElementById('pa_estado').value = p.estado;
            document.getElementById('modalPA').classList.add('active');
        }

        function cerrarModalPA() {
            document.getElementById('modalPA').classList.remove('active');
        }

        document.getElementById('modalPA').addEventListener('click', function (ev) {
            if (ev.target === this) cerrarModalPA();
        });

        <?php if ($abrirNuevo !== '' && in_array($abrirNuevo, ['1', '2', '3'], true)): ?>
            nuevoPA('<?= e($abrirNuevo) ?>');
        <?php elseif ($abrirEditar > 0): ?>
            editarPA(<?= $abrirEditar ?>);
        <?php endif; ?>
    </script>
</body>
</html>
