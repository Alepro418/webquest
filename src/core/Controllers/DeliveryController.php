<?php
declare(strict_types=1);

/**
 * DeliveryController - Entrega de evidencias (estudiante) y evaluación (docente).
 *
 * Las entregas quedan limitadas al periodo activo del Proyecto de Aula: solo
 * se aceptan mientras el PA esté publicado y la fecha actual esté dentro de
 * su rango de inicio/fin (cambio.txt §4.2 y §8).
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

        $volver = site_url('/public/student/subject.php?id=' . (int)$taller['id_asignatura']);

        if ($taller['asignatura_estado'] !== 'publicada') {
            flash('error', 'Este taller pertenece a una asignatura no publicada.');
            redirect($volver);
        }

        // El Proyecto de Aula debe estar publicado y vigente para recibir entregas.
        if ($taller['proyecto_estado'] === 'cerrada') {
            flash('error', 'El Proyecto de Aula del ' . ProyectoAula::momentoLabel((string)$taller['momento']) . ' está cerrado: no se aceptan nuevas entregas.');
            redirect($volver);
        }
        if ($taller['proyecto_estado'] !== 'publicada') {
            flash('error', 'El Proyecto de Aula del ' . ProyectoAula::momentoLabel((string)$taller['momento']) . ' aún no está publicado.');
            redirect($volver);
        }
        if (!ProyectoAula::enPeriodo($taller)) {
            $inicio = $taller['fecha_inicio'] ? date('d/m/Y', strtotime((string)$taller['fecha_inicio'])) : '—';
            $fin    = $taller['fecha_fin'] ? date('d/m/Y', strtotime((string)$taller['fecha_fin'])) : '—';
            flash('error', "La entrega solo está habilitada dentro del periodo del Proyecto de Aula ({$inicio} - {$fin}).");
            redirect($volver);
        }

        // El estudiante solo accede a talleres de su cohorte
        if ((int)$taller['grado'] !== (int)$user['grado'] || $taller['seccion'] !== $user['seccion']) {
            flash('error', 'No tienes acceso a este taller.');
            redirect(site_url('/public/student/subjects.php'));
        }

        if (Entrega::byTallerAndStudent($tallerId, (int)$user['id']) !== null) {
            flash('error', 'Ya entregaste evidencia para este taller.');
            redirect($volver);
        }

        if (empty($files['evidencia']['name'])) {
            flash('error', 'Debes adjuntar una fotografía de tu libreta.');
            redirect($volver);
        }

        $result = Upload::save($files['evidencia'], Upload::ENTREGAS, true);
        if (!$result['ok']) {
            flash('error', $result['error']);
            redirect($volver);
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
        if ($taller === null || (int)$taller['id_docente'] !== (int)$user['id']) {
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
