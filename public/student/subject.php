<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('estudiante');

$id = (int)($_GET['id'] ?? 0);
$asignatura = Asignatura::find($id);
if ($asignatura === null || $asignatura['estado'] !== 'publicada'
    || (int)$asignatura['grado'] !== (int)$user['grado'] || $asignatura['seccion'] !== $user['seccion']) {
    flash('error', 'Asignatura no disponible para tu cohorte.');
    redirect(site_url('/public/student/subjects.php'));
}

$prog = Reporte::progresoEstudianteEnAsignatura((int)$user['id'], $asignatura);
$recursos = Recurso::byAsignatura($id);

$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($asignatura['titulo']) ?> - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .taller-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-left: 5px solid var(--primary);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
            margin-bottom: 1.2rem;
        }
        .taller-card.completo { border-left-color: #22c55e; }
        .taller-card.entregado { border-left-color: #f59e0b; }
        .taller-head { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem; }
        .recurso-list { list-style:none; padding:0; margin:.5rem 0 0; }
        .recurso-list li { padding:.45rem 0; border-bottom:1px dashed var(--border-light); display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
        .info-banner { background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:10px;padding:.9rem 1.2rem;margin-bottom:1.5rem;font-size:.92rem; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1><a href="subjects.php" style="color:inherit;text-decoration:none;">📚 Asignaturas</a> / <?= e($asignatura['titulo']) ?></h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <div style="background:var(--white);padding:1.4rem 1.6rem;border-radius:12px;border-left:6px solid var(--primary);margin-bottom:1.5rem;">
                <h2 style="margin:0;color:var(--sidebar-color);"><?= e($asignatura['titulo']) ?></h2>
                <p style="color:#5a7a6a;margin:.4rem 0 0;"><?= e($asignatura['descripcion']) ?></p>
                <div style="margin-top:.8rem;display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.9rem;color:#5a7a6a;">
                    <span>👨‍🏫 <?= e($asignatura['docente_nombre']) ?></span>
                    <span>📝 <?= $prog['total'] ?> Talleres</span>
                    <span>📤 <?= $prog['entregados'] ?>/<?= $prog['total'] ?> Entregados</span>
                    <span>⭐ <?= $prog['promedio'] !== null ? number_format($prog['promedio'], 1) : '—' ?>/20</span>
                </div>
            </div>

            <section class="data-section">
                <div class="table-header"><h2>📝 Talleres y Entregas</h2></div>

                <?php if ($prog['total'] === 0): ?>
                    <p style="text-align:center;color:#5a7a6a;padding:1.5rem;">Tu docente aún no ha cargado talleres en esta asignatura.</p>
                <?php endif; ?>

                <?php foreach ($prog['talleres'] as $t): ?>
                    <?php
                        $entregado = $t['entrega'] !== null;
                        $evaluado  = $t['evaluacion'] !== null;
                        $clase = $evaluado ? 'completo' : ($entregado ? 'entregado' : '');
                        $vencido = $t['fecha_limite'] && strtotime($t['fecha_limite']) < time();
                    ?>
                    <div class="taller-card <?= $clase ?>">
                        <div class="taller-head">
                            <div>
                                <h3 style="margin:0;color:var(--sidebar-color);">
                                    <?= $t['unidad'] ? '📚 Unidad ' . e($t['unidad']) . ': ' : '' ?><?= e($t['titulo']) ?>
                                </h3>
                                <p style="color:#5a7a6a;margin:.3rem 0;"><?= e($t['descripcion']) ?></p>
                                <?php if ($t['fecha_limite']): ?>
                                    <p style="font-size:.85rem;color:<?= $vencido && !$entregado ? '#b3261e' : '#5a7a6a' ?>;margin:.2rem 0;">
                                        📅 Fecha límite: <?= e(date('d/m/Y', strtotime($t['fecha_limite']))) ?>
                                        <?php if ($vencido): ?> (ha vencido)<?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="text-align:right;">
                                <?php if ($evaluado): ?>
                                    <span class="status st-bueno">⭐ <?= number_format((float)$t['evaluacion']['puntaje'], 1) ?> • <?= e($t['evaluacion']['estatus']) ?></span><br>
                                    <small style="color:#5a7a6a;"><?= e($t['evaluacion']['observaciones'] ?? '') ?></small>
                                <?php elseif ($entregado): ?>
                                    <span class="status" style="background:#fef3c7;color:#92400e;">📤 Entregado — en revisión</span>
                                <?php else: ?>
                                    <span class="status" style="background:#f1f5f9;color:#475569;">⏳ Pendiente</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($evaluado): ?>
                            <p style="margin:.6rem 0 0;font-size:.85rem;color:#5a7a6a;">💬 <?= e($t['evaluacion']['observaciones'] ?? '') ?></p>
                        <?php elseif (!$entregado): ?>
                            <?php if ($vencido): ?>
                                <p style="margin:.8rem 0 0;color:#b3261e;font-size:.88rem;">⚠️ El plazo de entrega venció. Consulta a tu docente.</p>
                            <?php else: ?>
                                <details style="margin-top:.8rem;">
                                    <summary style="cursor:pointer;color:var(--primary);font-weight:600;">📤 Entregar evidencia</summary>
                                    <form method="post" action="../src/core/delivery.php?action=submit" enctype="multipart/form-data" style="margin-top:.7rem;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="taller_id" value="<?= (int)$t['id_taller'] ?>">
                                        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;">
                                            <div style="flex:1;min-width:200px;">
                                                <label style="font-weight:600;font-size:.85rem;">📸 Foto de tu libreta</label>
                                                <input type="file" name="evidencia" accept="image/*" required>
                                            </div>
                                            <div style="flex:2;min-width:220px;">
                                                <label style="font-weight:600;font-size:.85rem;">💬 Comentario (opcional)</label>
                                                <input type="text" name="comentario" placeholder="Breve nota para tu docente...">
                                            </div>
                                            <button class="btn-primary">📤 Enviar</button>
                                        </div>
                                    </form>
                                </details>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="data-section">
                <div class="table-header"><h2>🔗 Recursos de la Asignatura</h2></div>
                <?php if (count($recursos) === 0): ?>
                    <p style="text-align:center;color:#5a7a6a;padding:1rem;">Sin recursos por ahora.</p>
                <?php endif; ?>
                <ul class="recurso-list">
                    <?php foreach ($recursos as $r): ?>
                        <li>
                            <span>
                                <?php if ($r['tipo'] === 'video'): ?>🎬<?php elseif ($r['tipo'] === 'documento'): ?>📄<?php else: ?>🌐<?php endif; ?>
                                <?= e($r['titulo']) ?>
                            </span>
                            <a class="btn-sm" href="<?= e(asset_url($r['url_o_ruta'])) ?>" target="_blank" rel="noopener">🔎 Abrir</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>