<?php
declare(strict_types=1);

/**
 * ProyectoController - Configuración de los Proyectos de Aula por momento.
 *
 * Implementa las reglas de configuración de cambio.txt §4.2:
 *   - fechas dentro del año escolar y fecha_fin > fecha_inicio;
 *   - un solo PA por momento, grado, sección y año escolar;
 *   - estados borrador -> publicada -> cerrada.
 */
final class ProyectoController
{
    public static function save(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $data = [
            'titulo'        => trim((string)($input['titulo'] ?? '')),
            'descripcion'   => trim((string)($input['descripcion'] ?? '')),
            'momento'       => (string)($input['momento'] ?? '1'),
            'anio_escolar'  => trim((string)($input['anio_escolar'] ?? '')),
            'grado'         => (int)($input['grado'] ?? 0),
            'seccion'       => strtoupper(substr(trim((string)($input['seccion'] ?? '')), 0, 1)),
            'fecha_inicio'  => trim((string)($input['fecha_inicio'] ?? '')),
            'fecha_fin'     => trim((string)($input['fecha_fin'] ?? '')),
            'estado'        => in_array(($input['estado'] ?? 'borrador'), ProyectoAula::ESTADOS, true)
                ? (string)$input['estado'] : 'borrador',
            'id_docente'    => (int)$user['id'],
        ];

        $error = self::validar($data, $id, (int)$user['id']);
        if ($error !== null) {
            flash('error', $error);
            redirect(site_url('/public/teaching/pa.php' . ($id > 0 ? '?editar=' . $id : '?nuevo=' . $data['momento'])));
        }

        if ($id > 0) {
            $pa = ProyectoAula::find($id);
            if ($pa === null || (int)$pa['id_docente'] !== (int)$user['id']) {
                flash('error', 'Proyecto de Aula no encontrado o sin permiso.');
                redirect(site_url('/public/teaching/pa.php'));
            }
            ProyectoAula::update($id, $data);
            flash('success', 'Proyecto de Aula actualizado: ' . $data['titulo'] . '.');
        } else {
            $id = ProyectoAula::create($data);
            flash('success', 'Proyecto de Aula creado para el ' . ProyectoAula::momentoLabel($data['momento']) . '.');
        }

        if ($data['estado'] === 'publicada') {
            ProyectoAula::setActivo($id);
        }

        redirect(site_url('/public/teaching/pa.php#momento-' . $data['momento']));
    }

    /** Cambia el estado del PA (publicar, cerrar o devolver a borrador). */
    public static function setEstado(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $estado = (string)($input['estado'] ?? '');

        $pa = ProyectoAula::find($id);
        if ($pa === null || (int)$pa['id_docente'] !== (int)$user['id']) {
            flash('error', 'Proyecto de Aula no encontrado o sin permiso.');
            redirect(site_url('/public/teaching/pa.php'));
        }
        if (!in_array($estado, ProyectoAula::ESTADOS, true)) {
            flash('error', 'Estado no válido.');
            redirect(site_url('/public/teaching/pa.php'));
        }
        if ($estado === 'publicada' && (empty($pa['fecha_inicio']) || empty($pa['fecha_fin']))) {
            flash('error', 'Antes de publicar el PA debes configurar las fechas de inicio y fin del momento.');
            redirect(site_url('/public/teaching/pa.php#momento-' . $pa['momento']));
        }

        ProyectoAula::setEstado($id, $estado);
        if ($estado === 'publicada') {
            ProyectoAula::setActivo($id);
        }
        // Al cerrar el PA se mantiene como momento seleccionado: así queda
        // bloqueada la creación de contenido hasta configurar el siguiente.

        $mensajes = [
            'borrador'  => 'El PA volvió a estado borrador.',
            'publicada' => 'El PA se publicó: ya puedes crear asignaturas y talleres.',
            'cerrada'   => 'El PA se cerró: no se aceptarán nuevas entregas de los estudiantes.',
        ];
        flash('success', $mensajes[$estado]);
        redirect(site_url('/public/teaching/pa.php#momento-' . $pa['momento']));
    }

    /** Marca un PA como el momento de trabajo actual. */
    public static function activar(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $pa = ProyectoAula::find($id);
        if ($pa === null || (int)$pa['id_docente'] !== (int)$user['id']) {
            flash('error', 'Proyecto de Aula no encontrado o sin permiso.');
            redirect(site_url('/public/teaching/pa.php'));
        }

        ProyectoAula::setActivo($id);
        flash('success', 'Trabajando en el ' . ProyectoAula::momentoLabel((string)$pa['momento']) . ' — ' . $pa['titulo'] . '.');
        redirect(site_url('/public/teaching/pa.php#momento-' . $pa['momento']));
    }

    public static function delete(array $user, array $input): never
    {
        csrf_check();
        require_login('docente');

        $id = (int)($input['id'] ?? 0);
        $pa = ProyectoAula::find($id);
        if ($pa === null || (int)$pa['id_docente'] !== (int)$user['id']) {
            flash('error', 'Proyecto de Aula no encontrado o sin permiso.');
            redirect(site_url('/public/teaching/pa.php'));
        }

        ProyectoAula::delete($id);
        if ((int)($_SESSION['pa_activo'] ?? 0) === $id) {
            ProyectoAula::setActivo(null);
        }
        flash('success', 'Proyecto de Aula eliminado junto con sus asignaturas y talleres.');
        redirect(site_url('/public/teaching/pa.php'));
    }

    // ------------------------------------------------------------------
    // Validación
    // ------------------------------------------------------------------
    /** Devuelve el primer error encontrado o null si los datos son válidos. */
    private static function validar(array $data, int $id, int $docenteId): ?string
    {
        $minTitulo = (int)Config::get('academic.proyecto_aula.min_titulo_length', 5);
        $maxTitulo = (int)Config::get('academic.proyecto_aula.max_titulo_length', 150);

        if (mb_strlen($data['titulo']) < $minTitulo || mb_strlen($data['titulo']) > $maxTitulo) {
            return "El título del Proyecto de Aula debe tener entre {$minTitulo} y {$maxTitulo} caracteres.";
        }
        if (!in_array($data['momento'], ProyectoAula::MOMENTOS, true)) {
            return 'Debes seleccionar un momento pedagógico válido (1er, 2do o 3er momento).';
        }
        if (!in_array($data['grado'], Config::get('academic.grados', [1, 2, 3, 4, 5, 6]), true)) {
            return 'El grado seleccionado no es válido.';
        }
        if (!in_array($data['seccion'], Config::get('academic.secciones', ['A', 'B', 'C', 'D', 'E']), true)) {
            return 'La sección seleccionada no es válida.';
        }

        $rango = ProyectoAula::rangoAnioEscolar($data['anio_escolar']);
        if ($rango === null) {
            return 'El año escolar debe tener el formato «2025-2026» (dos años consecutivos).';
        }

        $inicio = self::fechaValida($data['fecha_inicio']);
        $fin    = self::fechaValida($data['fecha_fin']);
        if ($inicio === null || $fin === null) {
            return 'Debes indicar la fecha de inicio y la fecha de fin del momento (formato AAAA-MM-DD).';
        }
        if ($fin <= $inicio) {
            return 'La fecha de fin debe ser posterior a la fecha de inicio.';
        }
        if ($inicio < $rango['inicio'] || $fin > $rango['fin']) {
            return 'Las fechas del momento deben estar dentro del año escolar '
                . $data['anio_escolar'] . ' (' . date('d/m/Y', strtotime($rango['inicio']))
                . ' - ' . date('d/m/Y', strtotime($rango['fin'])) . ').';
        }

        if (ProyectoAula::existeDuplicado($docenteId, $data['momento'], $data['grado'], $data['seccion'], $data['anio_escolar'], $id)) {
            return 'Ya existe un Proyecto de Aula para ese momento, grado y sección en el año escolar indicado.';
        }

        return null;
    }

    private static function fechaValida(string $valor): ?string
    {
        $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        if ($dt === false || $dt->format('Y-m-d') !== $valor) {
            return null;
        }
        return $valor;
    }
}
