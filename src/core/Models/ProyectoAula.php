<?php
declare(strict_types=1);

/**
 * ProyectoAula - Modelo de la tabla Proyectos_Aula.
 *
 * Un Proyecto de Aula (PA) representa un momento pedagógico (1er, 2do o 3er
 * momento) de un grado y sección durante un año escolar. Es la raíz de la
 * jerarquía académica:
 *
 *     Proyecto de Aula -> Asignatura -> Taller -> Entrega -> Evaluación
 *
 * Reglas de negocio implementadas aquí:
 *   - Un momento (por docente, grado, sección y año escolar) tiene un solo PA.
 *   - Las fechas del momento deben caber dentro del año escolar y fecha_fin
 *     debe ser posterior a fecha_inicio.
 *   - Sin un PA «publicada» el docente no puede crear ni editar contenido.
 *   - Un PA «cerrada» no admite nuevas entregas de los estudiantes.
 */
final class ProyectoAula
{
    public const ESTADOS  = ['borrador', 'publicada', 'cerrada'];
    public const MOMENTOS = ['1', '2', '3'];

    /** Mensajes oficiales de bloqueo (cambio.txt §5.1 y §5.2). */
    public const MSG_SIN_PA_ASIGNATURA = 'Para crear asignaturas, primero debe configurar un Proyecto de Aula para el momento pedagógico actual.';
    public const MSG_SIN_PA_TALLER      = 'No puede crear talleres sin un Proyecto de Aula configurado. Por favor, configure el PA del momento antes de continuar.';

    // ------------------------------------------------------------------
    // Consultas
    // ------------------------------------------------------------------
    public static function find(int $id): ?array
    {
        return Database::row(
            'SELECT p.*, u.nombre_completo AS docente_nombre
             FROM Proyectos_Aula p
             LEFT JOIN Usuarios u ON u.id_usuario = p.id_docente
             WHERE p.id_proyecto = :id LIMIT 1',
            [':id' => $id]
        );
    }

    /** Todos los PA de un docente, ordenados por momento y fecha de creación. */
    public static function byDocente(int $docenteId): array
    {
        return Database::rows(
            'SELECT * FROM Proyectos_Aula
             WHERE id_docente = :d
             ORDER BY momento, fecha_creacion DESC',
            [':d' => $docenteId]
        );
    }

    /** PA del docente con el número de asignaturas y talleres asociados. */
    public static function byDocenteWithStats(int $docenteId): array
    {
        return Database::rows(
            'SELECT p.*,
                (SELECT COUNT(*) FROM Asignaturas a WHERE a.id_proyecto = p.id_proyecto) AS total_asignaturas,
                (SELECT COUNT(*) FROM Talleres t
                    JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
                    WHERE a.id_proyecto = p.id_proyecto) AS total_talleres,
                (SELECT COUNT(*) FROM Entregas_Taller et
                    JOIN Talleres t ON t.id_taller = et.id_taller
                    JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
                    WHERE a.id_proyecto = p.id_proyecto) AS total_entregas
             FROM Proyectos_Aula p
             WHERE p.id_docente = :d
             ORDER BY p.momento, p.fecha_creacion DESC',
            [':d' => $docenteId]
        );
    }

    /** PA de un momento concreto para una cohorte (grado + sección). */
    public static function delMomento(int $docenteId, string $momento, int $grado, string $seccion, string $anioEscolar): ?array
    {
        return Database::row(
            'SELECT * FROM Proyectos_Aula
             WHERE id_docente = :d AND momento = :m AND grado = :g AND seccion = :s AND anio_escolar = :a
             LIMIT 1',
            [':d' => $docenteId, ':m' => $momento, ':g' => $grado, ':s' => $seccion, ':a' => $anioEscolar]
        );
    }

    /**
     * Proyecto de Aula activo del docente (el que gobierna la creación de
     * contenido y la resolución de la cohorte de trabajo).
     *
     * Prioridad:
     *   1. El PA seleccionado en esta sesión (Configuración -> «Activar»).
     *   2. El PA publicado cuyo periodo contiene la fecha de hoy.
     *   3. El primer PA publicado.
     *   4. El primer PA configurado.
     */
    public static function activo(int $docenteId): ?array
    {
        $todos = self::byDocente($docenteId);
        if (count($todos) === 0) {
            return null;
        }

        $seleccionado = (int)($_SESSION['pa_activo'] ?? 0);
        if ($seleccionado > 0) {
            foreach ($todos as $pa) {
                if ((int)$pa['id_proyecto'] === $seleccionado) {
                    return $pa;
                }
            }
        }

        $hoy = date('Y-m-d');
        foreach ($todos as $pa) {
            if ($pa['estado'] === 'publicada' && self::enPeriodo($pa, $hoy)) {
                return $pa;
            }
        }
        foreach ($todos as $pa) {
            if ($pa['estado'] === 'publicada') {
                return $pa;
            }
        }
        return $todos[0];
    }

    /** Marca el PA activo de la sesión (solo el dueño debería llamar a esto). */
    public static function setActivo(?int $idProyecto): void
    {
        if ($idProyecto === null || $idProyecto <= 0) {
            unset($_SESSION['pa_activo']);
            return;
        }
        $_SESSION['pa_activo'] = $idProyecto;
    }

    // ------------------------------------------------------------------
    // Reglas / validaciones
    // ------------------------------------------------------------------
    /** ¿Se puede crear o editar contenido con este PA? */
    public static function puedeGestionarContenido(?array $pa): bool
    {
        return $pa !== null && $pa['estado'] === 'publicada';
    }

    /**
     * Motivo por el que el contenido está bloqueado, o null si se puede editar.
     * Valores: 'sin_pa' | 'borrador' | 'cerrada' | null.
     */
    public static function bloqueoContenido(?array $pa): ?string
    {
        if ($pa === null) {
            return 'sin_pa';
        }
        if ($pa['estado'] === 'borrador') {
            return 'borrador';
        }
        if ($pa['estado'] === 'cerrada') {
            return 'cerrada';
        }
        return null;
    }

    /** Mensaje de advertencia asociado al bloqueo. $contexto: 'asignatura' | 'taller'. */
    public static function mensajeBloqueo(string $bloqueo, string $contexto = 'asignatura'): string
    {
        $esTaller = $contexto === 'taller';
        if ($bloqueo === 'sin_pa') {
            return $esTaller ? self::MSG_SIN_PA_TALLER : self::MSG_SIN_PA_ASIGNATURA;
        }
        if ($bloqueo === 'borrador') {
            return $esTaller
                ? 'El Proyecto de Aula del momento actual está en borrador. Publícalo para habilitar la creación de talleres.'
                : 'El Proyecto de Aula del momento actual está en borrador. Publícalo para habilitar la creación de asignaturas.';
        }
        return $esTaller
            ? 'El Proyecto de Aula del momento actual está cerrado. No se pueden crear ni editar talleres.'
            : 'El Proyecto de Aula del momento actual está cerrado. No se pueden crear ni editar asignaturas.';
    }

    /** ¿La fecha indicada (por defecto hoy) cae dentro del periodo del PA? */
    public static function enPeriodo(array $pa, ?string $fecha = null): bool
    {
        $inicio = $pa['fecha_inicio'] ?? null;
        $fin    = $pa['fecha_fin'] ?? null;
        if (empty($inicio) || empty($fin)) {
            return false;
        }
        $fecha = $fecha ?? date('Y-m-d');
        return $fecha >= $inicio && $fecha <= $fin;
    }

    /** Etiqueta legible del momento: «1er momento». */
    public static function momentoLabel(string $momento): string
    {
        $map = Config::get('academic.momentos', []);
        return (string)($map[$momento] ?? ($momento . 'er momento'));
    }

    /** Año escolar en curso según configuración (formato «2025-2026»). */
    public static function anioEscolarActual(): string
    {
        $configurado = (string)Config::get('academic.proyecto_aula.anio_escolar_actual', '');
        if (preg_match('/^\d{4}-\d{4}$/', $configurado) === 1) {
            return $configurado;
        }
        // El año escolar venezolano arranca en septiembre y termina en julio.
        $anio = (int)date('Y');
        $mes  = (int)date('n');
        $inicio = $mes >= 8 ? $anio : $anio - 1;
        return $inicio . '-' . ($inicio + 1);
    }

    /**
     * Rango de fechas válido para un año escolar: 01/09/inicio -> 31/07/(inicio+1).
     * Devuelve null si el formato no es «YYYY-YYYY» consecutivo.
     */
    public static function rangoAnioEscolar(string $anioEscolar): ?array
    {
        if (preg_match('/^(\d{4})-(\d{4})$/', $anioEscolar, $m) !== 1) {
            return null;
        }
        $inicio = (int)$m[1];
        $fin    = (int)$m[2];
        if ($fin !== $inicio + 1) {
            return null;
        }
        $mesInicio = (int)Config::get('academic.proyecto_aula.mes_inicio', 9);
        $mesFin    = (int)Config::get('academic.proyecto_aula.mes_fin', 7);
        return [
            'inicio' => sprintf('%04d-%02d-01', $inicio, $mesInicio),
            'fin'    => sprintf('%04d-%02d-%02d', $fin, $mesFin, (int)date('t', mktime(0, 0, 0, $mesFin, 1, $fin))),
        ];
    }

    /** Periodo legible: «16/09/2025 - 15/12/2025». */
    public static function periodoTexto(array $pa): string
    {
        $inicio = $pa['fecha_inicio'] ?? null;
        $fin    = $pa['fecha_fin'] ?? null;
        $fmt = static fn(?string $f): string => empty($f) ? '—' : date('d/m/Y', strtotime($f));
        return $fmt($inicio) . ' - ' . $fmt($fin);
    }

    /** Estado del periodo respecto a hoy: «En curso», «Por iniciar» o «Finalizado». */
    public static function estadoPeriodo(array $pa): string
    {
        $hoy = date('Y-m-d');
        if (empty($pa['fecha_inicio']) || empty($pa['fecha_fin'])) {
            return 'Sin fechas';
        }
        if ($hoy < $pa['fecha_inicio']) {
            return 'Por iniciar';
        }
        if ($hoy > $pa['fecha_fin']) {
            return 'Finalizado';
        }
        return 'En curso';
    }

    // ------------------------------------------------------------------
    // Escritura
    // ------------------------------------------------------------------
    /**
     * Crea un PA. $data acepta: titulo, descripcion, momento, anio_escolar,
     * grado, seccion, id_docente, fecha_inicio, fecha_fin, estado.
     */
    public static function create(array $data): int
    {
        return (int)Database::execute(
            'INSERT INTO Proyectos_Aula
                (titulo, descripcion, momento, anio_escolar, grado, seccion, id_docente, fecha_inicio, fecha_fin, estado)
             VALUES (:t, :d, :m, :a, :g, :s, :doc, :fi, :ff, :e)',
            [
                ':t'   => $data['titulo'],
                ':d'   => $data['descripcion'] !== '' ? $data['descripcion'] : null,
                ':m'   => $data['momento'],
                ':a'   => $data['anio_escolar'],
                ':g'   => $data['grado'],
                ':s'   => $data['seccion'],
                ':doc' => $data['id_docente'],
                ':fi'  => $data['fecha_inicio'] !== '' ? $data['fecha_inicio'] : null,
                ':ff'  => $data['fecha_fin'] !== '' ? $data['fecha_fin'] : null,
                ':e'   => $data['estado'],
            ]
        );
    }

    public static function update(int $id, array $data): void
    {
        Database::execute(
            'UPDATE Proyectos_Aula
             SET titulo = :t, descripcion = :d, momento = :m, anio_escolar = :a, grado = :g,
                 seccion = :s, fecha_inicio = :fi, fecha_fin = :ff, estado = :e
             WHERE id_proyecto = :id',
            [
                ':t'   => $data['titulo'],
                ':d'   => $data['descripcion'] !== '' ? $data['descripcion'] : null,
                ':m'   => $data['momento'],
                ':a'   => $data['anio_escolar'],
                ':g'   => $data['grado'],
                ':s'   => $data['seccion'],
                ':fi'  => $data['fecha_inicio'] !== '' ? $data['fecha_inicio'] : null,
                ':ff'  => $data['fecha_fin'] !== '' ? $data['fecha_fin'] : null,
                ':e'   => $data['estado'],
                ':id'  => $id,
            ]
        );
    }

    public static function setEstado(int $id, string $estado): void
    {
        Database::execute(
            'UPDATE Proyectos_Aula SET estado = :e WHERE id_proyecto = :id',
            [':e' => $estado, ':id' => $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM Proyectos_Aula WHERE id_proyecto = :id', [':id' => $id]);
    }

    /** ¿Ya existe otro PA para ese momento/cohorte/año? (excluye $exceptoId). */
    public static function existeDuplicado(int $docenteId, string $momento, int $grado, string $seccion, string $anioEscolar, int $exceptoId = 0): bool
    {
        $id = Database::scalar(
            'SELECT id_proyecto FROM Proyectos_Aula
             WHERE id_docente = :d AND momento = :m AND grado = :g AND seccion = :s AND anio_escolar = :a
               AND id_proyecto <> :id
             LIMIT 1',
            [':d' => $docenteId, ':m' => $momento, ':g' => $grado, ':s' => $seccion, ':a' => $anioEscolar, ':id' => $exceptoId]
        );
        return $id !== false && $id !== null;
    }
}
