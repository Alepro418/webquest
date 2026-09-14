<?php
declare(strict_types=1);

/**
 * Asignatura - Modelo de la tabla Asignaturas.
 */
final class Asignatura
{
    public static function find(int $id): ?array
    {
        return Database::row(
            'SELECT a.*, u.nombre_completo AS docente_nombre
             FROM Asignaturas a
             LEFT JOIN Usuarios u ON u.id_usuario = a.id_docente
             WHERE a.id_asignatura = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public static function byDocente(int $docenteId): array
    {
        return Database::rows(
            'SELECT * FROM Asignaturas WHERE id_docente = :d ORDER BY fecha_creacion DESC',
            [':d' => $docenteId]
        );
    }

    /** Asignaturas de un docente con sus métricas (talleres, entregas, promedio). */
    public static function byDocenteWithStats(int $docenteId): array
    {
        return Database::rows(
            'SELECT a.*,
                (SELECT COUNT(*) FROM Talleres t WHERE t.id_asignatura = a.id_asignatura) AS total_talleres,
                (SELECT COUNT(*) FROM Talleres t JOIN Entregas_Taller et ON et.id_taller = t.id_taller
                    WHERE t.id_asignatura = a.id_asignatura) AS total_entregas
             FROM Asignaturas a
             WHERE a.id_docente = :d
             ORDER BY a.fecha_creacion DESC',
            [':d' => $docenteId]
        );
    }

    /** Asignaturas publicadas disponibles para un estudiante (por grado + sección). */
    public static function forStudent(int $grado, string $seccion): array
    {
        return Database::rows(
            'SELECT * FROM Asignaturas
             WHERE estado = \'publicada\' AND grado = :g AND seccion = :s
             ORDER BY titulo',
            [':g' => $grado, ':s' => $seccion]
        );
    }

    public static function create(string $titulo, string $descripcion, int $grado, string $seccion, string $estado, int $docenteId): int
    {
        return (int) Database::execute(
            'INSERT INTO Asignaturas (titulo, descripcion, grado, seccion, estado, id_docente)
             VALUES (:t, :d, :g, :s, :e, :doc)',
            [
                ':t' => $titulo,
                ':d' => $descripcion,
                ':g' => $grado,
                ':s' => $seccion,
                ':e' => $estado,
                ':doc' => $docenteId,
            ]
        );
    }

    public static function update(int $id, string $titulo, string $descripcion, int $grado, string $seccion, string $estado): void
    {
        Database::execute(
            'UPDATE Asignaturas SET titulo = :t, descripcion = :d, grado = :g, seccion = :s, estado = :e
             WHERE id_asignatura = :id',
            [':t' => $titulo, ':d' => $descripcion, ':g' => $grado, ':s' => $seccion, ':e' => $estado, ':id' => $id]
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