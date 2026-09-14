<?php
declare(strict_types=1);

/**
 * Entrega - Modelo de la tabla Entregas_Taller.
 */
final class Entrega
{
    public static function byId(int $id): ?array
    {
        return Database::row(
            'SELECT * FROM Entregas_Taller WHERE id_entrega = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public static function byTallerAndStudent(int $tallerId, int $estudianteId): ?array
    {
        return Database::row(
            'SELECT * FROM Entregas_Taller WHERE id_taller = :t AND id_estudiante = :s LIMIT 1',
            [':t' => $tallerId, ':s' => $estudianteId]
        );
    }

    /**
     * Entregas de un taller con datos del estudiante y su evaluación.
     */
    public static function byTaller(int $tallerId): array
    {
        return Database::rows(
            'SELECT et.*, u.nombre_completo AS estudiante_nombre, u.nombre_usuario, u.grado, u.seccion,
                    ev.id_evaluacion, ev.puntaje, ev.escala_letra, ev.observaciones, ev.estatus, ev.fecha_evaluacion
             FROM Entregas_Taller et
             JOIN Usuarios u ON u.id_usuario = et.id_estudiante
             LEFT JOIN Evaluaciones ev ON ev.id_entrega = et.id_entrega
             WHERE et.id_taller = :t
             ORDER BY et.fecha_envio DESC',
            [':t' => $tallerId]
        );
    }

    /**
     * Todas las entregas de un docente (sus asignaturas) para el panel de gestión.
     */
    public static function byDocente(int $docenteId): array
    {
        return Database::rows(
            'SELECT et.*, u.nombre_completo AS estudiante_nombre, u.nombre_usuario,
                    t.titulo AS taller_titulo, a.titulo AS asignatura_titulo, a.grado, a.seccion,
                    ev.id_evaluacion, ev.puntaje, ev.escala_letra, ev.estatus
             FROM Entregas_Taller et
             JOIN Talleres t ON t.id_taller = et.id_taller
             JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
             JOIN Usuarios u ON u.id_usuario = et.id_estudiante
             LEFT JOIN Evaluaciones ev ON ev.id_entrega = et.id_entrega
             WHERE a.id_docente = :d
             ORDER BY et.fecha_envio DESC',
            [':d' => $docenteId]
        );
    }

    /** Entregas de un estudiante con taller/asignatura/evaluación. */
    public static function byStudent(int $estudianteId): array
    {
        return Database::rows(
            'SELECT et.*, t.titulo AS taller_titulo, t.fecha_limite,
                    a.titulo AS asignatura_titulo, a.grado, a.seccion,
                    ev.id_evaluacion, ev.puntaje, ev.escala_letra, ev.observaciones, ev.estatus, ev.fecha_evaluacion
             FROM Entregas_Taller et
             JOIN Talleres t ON t.id_taller = et.id_taller
             JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
             LEFT JOIN Evaluaciones ev ON ev.id_entrega = et.id_entrega
             WHERE et.id_estudiante = :s
             ORDER BY et.fecha_envio DESC',
            [':s' => $estudianteId]
        );
    }

    public static function create(int $estudianteId, int $tallerId, string $ruta, ?string $comentario): int
    {
        return (int) Database::execute(
            'INSERT INTO Entregas_Taller (id_estudiante, id_taller, ruta_fotografia, comentario)
             VALUES (:s, :t, :r, :c)',
            [
                ':s' => $estudianteId,
                ':t' => $tallerId,
                ':r' => $ruta,
                ':c' => $comentario !== '' ? $comentario : null,
            ]
        );
    }

    /** Total de talleres completados (con evaluación) por un estudiante. */
    public static function countEvaluatedByStudent(int $estudianteId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*)
             FROM Entregas_Taller et JOIN Evaluaciones ev ON ev.id_entrega = et.id_entrega
             WHERE et.id_estudiante = :s',
            [':s' => $estudianteId]
        );
    }
}