<?php
declare(strict_types=1);

/**
 * Asignatura - Modelo de la tabla Asignaturas.
 *
 * Una asignatura pertenece siempre a un Proyecto de Aula (id_proyecto). El
 * grado, la sección y el momento pedagógico se heredan del PA, por lo que no
 * se guardan en esta tabla.
 */
final class Asignatura
{
    /** Columnas del PA que las vistas siguen usando como si fueran propias. */
    private const SELECT_BASE =
        'SELECT a.*,
                p.titulo       AS proyecto_titulo,
                p.momento,
                p.grado,
                p.seccion,
                p.id_docente,
                p.anio_escolar,
                p.estado       AS proyecto_estado,
                p.fecha_inicio,
                p.fecha_fin,
                u.nombre_completo AS docente_nombre
         FROM Asignaturas a
         JOIN Proyectos_Aula p ON p.id_proyecto = a.id_proyecto
         LEFT JOIN Usuarios u ON u.id_usuario = p.id_docente';

    public static function find(int $id): ?array
    {
        return Database::row(
            self::SELECT_BASE . ' WHERE a.id_asignatura = :id LIMIT 1',
            [':id' => $id]
        );
    }

    /** Asignaturas de todos los PA de un docente. */
    public static function byDocente(int $docenteId): array
    {
        return Database::rows(
            self::SELECT_BASE . ' WHERE p.id_docente = :d ORDER BY p.momento, a.fecha_creacion DESC',
            [':d' => $docenteId]
        );
    }

    /** Asignaturas de un Proyecto de Aula concreto. */
    public static function byProyecto(int $idProyecto): array
    {
        return Database::rows(
            self::SELECT_BASE . ' WHERE a.id_proyecto = :p ORDER BY a.titulo',
            [':p' => $idProyecto]
        );
    }

    /** Asignaturas de un docente con sus métricas (talleres, entregas). */
    public static function byDocenteWithStats(int $docenteId): array
    {
        return Database::rows(
            'SELECT a.*,
                p.titulo AS proyecto_titulo, p.momento, p.grado, p.seccion, p.id_docente,
                p.estado AS proyecto_estado, p.anio_escolar, p.fecha_inicio, p.fecha_fin,
                (SELECT COUNT(*) FROM Talleres t WHERE t.id_asignatura = a.id_asignatura) AS total_talleres,
                (SELECT COUNT(*) FROM Talleres t JOIN Entregas_Taller et ON et.id_taller = t.id_taller
                    WHERE t.id_asignatura = a.id_asignatura) AS total_entregas
             FROM Asignaturas a
             JOIN Proyectos_Aula p ON p.id_proyecto = a.id_proyecto
             WHERE p.id_docente = :d
             ORDER BY p.momento, a.fecha_creacion DESC',
            [':d' => $docenteId]
        );
    }

    /**
     * Asignaturas publicadas disponibles para un estudiante (por grado + sección).
     * Se incluyen las de PA publicados y cerrados: un momento cerrado se puede
     * consultar, aunque ya no admita entregas.
     */
    public static function forStudent(int $grado, ?string $seccion): array
    {
        $seccion = strtoupper(trim((string)$seccion));
        if ($grado <= 0 || $seccion === '') {
            return [];
        }
        return Database::rows(
            self::SELECT_BASE .
            ' WHERE a.estado = \'publicada\'
                AND p.estado IN (\'publicada\', \'cerrada\')
                AND p.grado = :g AND p.seccion = :s
              ORDER BY p.momento, a.titulo',
            [':g' => $grado, ':s' => $seccion]
        );
    }

    /** Asignaturas publicadas de un estudiante dentro de un PA concreto. */
    public static function forStudentEnProyecto(int $estudianteGrado, string $estudianteSeccion, int $idProyecto): array
    {
        return Database::rows(
            self::SELECT_BASE .
            ' WHERE a.id_proyecto = :p AND a.estado = \'publicada\'
                AND p.estado IN (\'publicada\', \'cerrada\')
                AND p.grado = :g AND p.seccion = :s
              ORDER BY a.titulo',
            [':p' => $idProyecto, ':g' => $estudianteGrado, ':s' => $estudianteSeccion]
        );
    }

    /** Proyectos de Aula publicados o cerrados de una cohorte (historial de momentos). */
    public static function proyectosDeCohorte(int $grado, string $seccion): array
    {
        return Database::rows(
            'SELECT * FROM Proyectos_Aula
             WHERE grado = :g AND seccion = :s AND estado IN (\'publicada\', \'cerrada\')
             ORDER BY momento',
            [':g' => $grado, ':s' => $seccion]
        );
    }

    public static function create(int $idProyecto, string $titulo, string $descripcion, string $estado): int
    {
        return (int) Database::execute(
            'INSERT INTO Asignaturas (id_proyecto, titulo, descripcion, estado)
             VALUES (:p, :t, :d, :e)',
            [
                ':p' => $idProyecto,
                ':t' => $titulo,
                ':d' => $descripcion,
                ':e' => $estado,
            ]
        );
    }

    public static function update(int $id, string $titulo, string $descripcion, string $estado): void
    {
        Database::execute(
            'UPDATE Asignaturas SET titulo = :t, descripcion = :d, estado = :e
             WHERE id_asignatura = :id',
            [':t' => $titulo, ':d' => $descripcion, ':e' => $estado, ':id' => $id]
        );
    }

    public static function setEstado(int $id, string $estado): void
    {
        Database::execute('UPDATE Asignaturas SET estado = :e WHERE id_asignatura = :id', [':e' => $estado, ':id' => $id]);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM Asignaturas WHERE id_asignatura = :id', [':id' => $id]);
    }
}
