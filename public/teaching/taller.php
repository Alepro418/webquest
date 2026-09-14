<?php
require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
$user = require_login('docente');

$taller = Taller::find((int)($_GET['id'] ?? 0));
if ($taller === null) {
    flash('error', 'Taller no encontrado.');
    redirect(site_url('/public/teaching/subjects.php'));
}
$asignatura = Asignatura::find((int)$taller['id_asignatura']);
if ($asignatura === null || (int)$asignatura['id_docente'] !== (int)$user['id']) {
    flash('error', 'No tienes permiso para ver este taller.');
    redirect(site_url('/public/teaching/subjects.php'));
}

$entregas = Entrega::byTaller((int)$taller['id_taller']);

$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($taller['titulo']) ?> - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .entrega-card {
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1.2rem;
        }
        .entrega-head { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem; margin-bottom:.8rem; }
        .evidencia-img {
            width: 100%; max-height: 380px; object-fit: contain;
            background: #f6fbf6; border-radius: 8px; border: 1px solid var(--border-light);
            margin: .6rem 0;
        }
        .scale-option { display:inline-block; padding:.35rem .8rem; border-radius:20px; font-size:.8rem; font-weight:600; margin-right:.3rem; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1><a href="subject.php?id=<?= (int)$asignatura['id_asignatura'] ?>" style="color:inherit;text-decoration:none;"><?= e($asignatura['titulo']) ?></a> / <?= e($taller['titulo']) ?></h1>
                <div class="date-info"><?php include dirname(__DIR__, 2) . '/public/functions/date.php'; ?></div>
            </header>

            <?php if ($flashSuccess): ?><div class="alert" style="background:#dcfce7;border-left:4px solid #22c55e;color:#166534;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert" style="background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b;padding:0.8rem 1.2rem;border-radius:8px;margin-bottom:1rem;"><?= e($flashError) ?></div><?php endif; ?>

            <div style="margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;color:#5a7a6a;">
                <span>📝 <?= e($taller['descripcion']) ?></span>
                <?php if ($taller['fecha_limite']): ?><span>📅 Límite: <?= e(date('d/m/Y', strtotime($taller['fecha_limite']))) ?></span><?php endif; ?>
                <span class="status st-bueno"><?= count($entregas) ?> entregas</span>
            </div>

            <?php if (count($entregas) === 0): ?>
                <section class="data-section"><p style="text-align:center;color:#5a7a6a;">Aún no hay entregas para este taller.</p></section>
            <?php endif; ?>

            <?php foreach ($entregas as $e): ?>
                <div class="entrega-card">
                    <div class="entrega-head">
                        <div>
                            <strong>🎒 <?= e($e['estudiante_nombre']) ?></strong>
                            <span style="color:#5a7a6a;font-size:.85rem;"> (<?= e($e['nombre_usuario']) ?>) • <?= e(date('d/m/Y H:i', strtotime($e['fecha_envio']))) ?></span>
                        </div>
                        <?php if ($e['puntaje'] !== null): ?>
                            <div>
                                <span class="scale-option" style="background:<?= e(Reporte::estatusConfig((float)$e['puntaje'])['bg_color']) ?>;color:<?= e(Reporte::estatusConfig((float)$e['puntaje'])['color']) ?>;">
                                    <?= e($e['escala_letra']) ?> • <?= number_format((float)$e['puntaje'], 1) ?> • <?= e($e['estatus']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($e['comentario']): ?>
                        <p style="background:#f6fbf6;padding:.6rem .9rem;border-radius:8px;border-left:4px solid var(--primary);font-size:.9rem;">
                            💬 <?= e($e['comentario']) ?>
                        </p>
                    <?php endif; ?>

                    <a href="<?= e(asset_url($e['ruta_fotografia'])) ?>" target="_blank">
                        <img class="evidencia-img" src="<?= e(asset_url($e['ruta_fotografia'])) ?>" alt="Evidencia de <?= e($e['estudiante_nombre']) ?>"
                             onerror="this.parentElement.outerHTML='<p style=&#39;color:#991b1b;&#39;>⚠️ Imagen de evidencia no disponible.</p>'">
                    </a>

                    <?php if ((int)($e['puntaje'] ?? -1) < 0 || $e['puntaje'] === null): ?>
                        <form method="post" action="../src/core/delivery.php?action=evaluate" style="margin-top:.8rem;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="entrega_id" value="<?= (int)$e['id_entrega'] ?>">
                            <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
                                <div style="flex:0 0 180px;">
                                    <label style="font-weight:600;font-size:.85rem;">⭐ Calificación (0-20)</label>
                                    <input type="number" name="puntaje" min="0" max="20" step="0.5" value="<?= isset($e['puntaje']) ? (float)$e['puntaje'] : 15 ?>" required>
                                </div>
                                <div style="flex:1;min-width:220px;">
                                    <label style="font-weight:600;font-size:.85rem;">📝 Observaciones</label>
                                    <textarea name="observaciones" rows="2" placeholder="Feedback pedagógico para el estudiante" required></textarea>
                                </div>
                                <button class="btn-primary">💾 Calificar</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <details style="margin-top:.8rem;">
                            <summary style="cursor:pointer;color:var(--primary);font-weight:600;">🗒️ Ver observaciones</summary>
                            <p style="background:#f6fbf6;padding:.8rem 1rem;border-radius:8px;border-left:4px solid var(--primary);margin-top:.5rem;">
                                <?= e((string)($e['observaciones'] ?? 'Sin observaciones.')) ?>
                            </p>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>