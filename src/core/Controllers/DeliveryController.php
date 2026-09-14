<?php
declare(strict_types=1);

/**
 * DeliveryController - Entrega de evidencias (estudiante) y evaluación (docente).
 */
final class DeliveryController
{
    /** Subida de evidencia fotográfica de la libreta (estudiante). */
    public static function submit(array $user, array $input, array $files): never
    {
        csrf_check();
        require_login('estudiante');

        $tallerId = (int)($input['taller_id'] ?? 0);
        $comentario = trim((string)($input['comentario'] ?? ''));

        $taller = Taller::find($tallerId);
        if ($taller === null) {
            flash('error', 'El taller no existe.');
            redirect(site_url('/public/student/subjects.php'));
        }

        $asignatura = Asignatura::find((int)$taller['id_asignatura']);
        if ($asignatura === null || $asignatura['estado'] !== 'publicada') {
            flash('error', 'Este taller pertenece a una asignatura no publicada.');
            redirect(site_url('/public/student/subjects.php'));
        }
        // El estudiante solo accede a talleres de su cohorte
        if ((int)$asignatura['grado'] !== (int)$user['grado'] || $asignatura['seccion'] !== $user['seccion']) {
            flash('error', 'No tienes acceso a este taller.');
            redirect(site_url('/public/student/subjects.php'));
        }

        if (Entrega::byTallerAndStudent($tallerId, (int)$user['id']) !== null) {
            flash('error', 'Ya entregaste evidencia para este taller.');
            redirect(site_url('/public/student/subject.php?id=' . $asignatura['id_asignatura']));
        }

        if (empty($files['evidencia']['name'])) {
            flash('error', 'Debes adjuntar una fotografía de tu libreta.');
            redirect(site_url('/public/student/subject.php?id=' . $asignatura['id_asignatura']));
        }

        $result = Upload::save($files['evidencia'], Upload::ENTREGAS, true);
        if (!$result['ok']) {
            flash('error', $result['error']);
            redirect(site_url('/public/student/subject.php?id=' . $asignatura['id_asignatura']));
        }

        Entrega::create((int)$user['id'], $tallerId, $result['path'], $comentario);
        flash('success', '¡Evidencia enviada! Tu docente la revisará pronto.');
        redirect(site_url('/public/student/assignments.php'));
    }

    /** Evaluación de una entrega por el docente (0-20 + observaciones). */
    public static function evaluate(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $entregaId = (int)($input['entrega_id'] ?? 0);
        $puntaje   = (float)str_replace(',', '.', (string)($input['puntaje'] ?? ''));
        $observaciones = trim((string)($input['observaciones'] ?? ''));

        $entrega = Entrega::byId($entregaId);
        if ($entrega === null) {
            flash('error', 'La entrega no existe.');
            redirect(site_url('/public/teaching/assignments.php'));
        }

        $taller = Taller::find((int)$entrega['id_taller']);
        $asignatura = Asignatura::find((int)$taller['id_asignatura']);
        if ($asignatura === null || (int)$asignatura['id_docente'] !== (int)$user['id']) {
            flash('error', 'No tienes permiso para evaluar esta entrega.');
            redirect(site_url('/public/teaching/assignments.php'));
        }

        $max = (float)Config::get('academic.evaluation.max_score', 20);
        if (!is_finite($puntaje) || $puntaje < 0 || $puntaje > $max) {
            flash('error', "El puntaje debe estar entre 0 y " . (int)$max . ".");
            redirect(site_url('/public/teaching/assignments.php'));
        }
        if (mb_strlen($observaciones) < 2) {
            flash('error', 'Añade una observación para el estudiante.');
            redirect(site_url('/public/teaching/assignments.php'));
        }

        $existing = Evaluacion::byEntrega($entregaId);
        if ($existing !== null) {
            Evaluacion::update($existing['id_evaluacion'], $puntaje, $observaciones);
            flash('success', 'Evaluación actualizada.');
        } else {
            Evaluacion::create($entregaId, $puntaje, $observaciones);
            flash('success', 'Evaluación guardada y notificada al estudiante.');
        }

        redirect(site_url('/public/teaching/assignments.php'));
    }
}