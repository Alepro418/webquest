<?php
declare(strict_types=1);

/**
 * SubjectController - Gestión docente de asignaturas, talleres y recursos.
 */
final class SubjectController
{
    // ------------------------------------------------------------------
    // Asignaturas
    // ------------------------------------------------------------------
    public static function saveSubject(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id         = (int)($input['id'] ?? 0);
        $titulo     = trim((string)($input['titulo'] ?? ''));
        $descripcion = trim((string)($input['descripcion'] ?? ''));
        $grado      = (int)($input['grado'] ?? 0);
        $seccion    = strtoupper(substr(trim((string)($input['seccion'] ?? 'A')), 0, 1));
        $estado     = ($input['estado'] ?? 'borrador') === 'publicada' ? 'publicada' : 'borrador';

        $maxTitle = (int)Config::get('academic.subject.max_title_length', 100);
        $minDesc  = (int)Config::get('academic.subject.min_description_length', 0);
        $grados   = Config::get('academic.grados', [1, 2, 3, 4, 5, 6]);
        $secciones = Config::get('academic.secciones', ['A', 'B', 'C', 'D', 'E']);

        if (mb_strlen($titulo) < 5 || mb_strlen($titulo) > $maxTitle) {
            flash('error', "El título debe tener entre 5 y {$maxTitle} caracteres.");
            redirect(site_url('/public/teaching/subjects.php'));
        }
        if (mb_strlen($descripcion) < $minDesc) {
            flash('error', 'La descripción es demasiado corta.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        if (!in_array($grado, $grados, true) || !in_array($seccion, $secciones, true)) {
            flash('error', 'Grado o sección no válidos.');
            redirect(site_url('/public/teaching/subjects.php'));
        }

        if ($id > 0) {
            $own = Asignatura::find($id);
            if ($own === null || (int)$own['id_docente'] !== (int)$user['id']) {
                flash('error', 'No tienes permiso para editar esta asignatura.');
                redirect(site_url('/public/teaching/subjects.php'));
            }
            Asignatura::update($id, $titulo, $descripcion, $grado, $seccion, $estado);
            flash('success', 'Asignatura actualizada correctamente.');
        } else {
            $id = Asignatura::create($titulo, $descripcion, $grado, $seccion, $estado, (int)$user['id']);
            flash('success', 'Asignatura creada correctamente.');
        }
        redirect(site_url('/public/teaching/subjects.php'));
    }

    public static function setEstado(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $own = Asignatura::find($id);
        if ($own === null || (int)$own['id_docente'] !== (int)$user['id']) {
            flash('error', 'Asignatura no encontrada o sin permiso.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        $estado = ($input['estado'] ?? '') === 'publicada' ? 'publicada' : 'borrador';
        Asignatura::setEstado($id, $estado);
        flash('success', $estado === 'publicada' ? 'Asignatura publicada.' : 'Asignatura guardada como borrador.');
        redirect(site_url('/public/teaching/subjects.php'));
    }

    public static function deleteSubject(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $own = Asignatura::find($id);
        if ($own === null || (int)$own['id_docente'] !== (int)$user['id']) {
            flash('error', 'Asignatura no encontrada o sin permiso.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        Asignatura::delete($id);
        flash('success', 'Asignatura eliminada.');
        redirect(site_url('/public/teaching/subjects.php'));
    }

    // ------------------------------------------------------------------
    // Talleres
    // ------------------------------------------------------------------
    public static function saveTaller(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id          = (int)($input['id'] ?? 0);
        $asignaturaId = (int)($input['asignatura_id'] ?? 0);
        $titulo      = trim((string)($input['titulo'] ?? ''));
        $descripcion = trim((string)($input['descripcion'] ?? ''));
        $unidad      = trim((string)($input['unidad'] ?? ''));
        $fechaLimite = trim((string)($input['fecha_limite'] ?? ''));

        if (!self::owns($user, $asignaturaId)) {
            flash('error', 'No tienes permiso sobre esta asignatura.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        if (mb_strlen($titulo) < 3) {
            flash('error', 'El título del taller debe tener al menos 3 caracteres.');
            redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
        }
        if ($fechaLimite !== '' && strtotime($fechaLimite) === false) {
            flash('error', 'La fecha límite no es válida.');
            redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
        }

        if ($id > 0) {
            $taller = Taller::find($id);
            if ($taller === null || (int)$taller['id_asignatura'] !== $asignaturaId) {
                flash('error', 'Taller no encontrado.');
                redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
            }
            Taller::update($id, $titulo, $descripcion, $unidad, $fechaLimite);
            flash('success', 'Taller actualizado.');
        } else {
            Taller::create($asignaturaId, $titulo, $descripcion, $unidad, $fechaLimite);
            flash('success', 'Taller creado.');
        }
        redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
    }

    public static function deleteTaller(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $taller = Taller::find($id);
        if ($taller === null || !self::owns($user, (int)$taller['id_asignatura'])) {
            flash('error', 'Taller no encontrado o sin permiso.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        Taller::delete($id);
        flash('success', 'Taller eliminado.');
        redirect(site_url("/public/teaching/subject.php?id={$taller['id_asignatura']}"));
    }

    // ------------------------------------------------------------------
    // Recursos
    // ------------------------------------------------------------------
    public static function saveRecurso(array $user, array $input, array $files): never
    {
        csrf_check();
        require_login('docente');

        $asignaturaId = (int)($input['asignatura_id'] ?? 0);
        $titulo = trim((string)($input['titulo'] ?? ''));
        $tipo   = $input['tipo'] ?? 'enlace';
        $url    = trim((string)($input['url'] ?? ''));

        if (!self::owns($user, $asignaturaId)) {
            flash('error', 'No tienes permiso sobre esta asignatura.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        if (!in_array($tipo, ['enlace', 'video', 'documento'], true)) {
            flash('error', 'Tipo de recurso no válido.');
            redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
        }
        if (mb_strlen($titulo) < 3) {
            flash('error', 'El título del recurso debe tener al menos 3 caracteres.');
            redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
        }

        // Recursos de archivo se suben como documento a public/uploads/recursos
        if (!empty($files['archivo']['name'])) {
            $result = Upload::save($files['archivo'], Upload::RECURSOS, false);
            if (!$result['ok']) {
                flash('error', $result['error']);
                redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
            }
            $url = $result['path'];
        }

        if ($url === '') {
            flash('error', 'Ingresa un enlace o sube un documento.');
            redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
        }

        Recurso::create($asignaturaId, $titulo, $tipo, $url);
        flash('success', 'Recurso agregado.');
        redirect(site_url("/public/teaching/subject.php?id={$asignaturaId}"));
    }

    public static function deleteRecurso(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $recurso = Database::row('SELECT * FROM Recursos WHERE id_recurso = :id', [':id' => $id]);
        if ($recurso === null || !self::owns($user, (int)$recurso['id_asignatura'])) {
            flash('error', 'Recurso no encontrado o sin permiso.');
            redirect(site_url('/public/teaching/subjects.php'));
        }
        Recurso::delete($id);
        flash('success', 'Recurso eliminado.');
        redirect(site_url("/public/teaching/subject.php?id={$recurso['id_asignatura']}"));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    private static function owns(array $user, int $asignaturaId): bool
    {
        $a = Asignatura::find($asignaturaId);
        return $a !== null && (int)$a['id_docente'] === (int)$user['id'];
    }
}