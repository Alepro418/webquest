<?php
declare(strict_types=1);

/**
 * Taller - Modelo de la tabla Talleres.
 */
final class Taller
{
    public static function find(int $id): ?array
    {
        return Database::row(
            'SELECT t.*, a.titulo AS asignatura_titulo, a.id_docente
             FROM Talleres t
             JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
             WHERE t.id_taller = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public static function byAsignatura(int $asignaturaId): array
    {
        return Database::rows(
            'SELECT * FROM Talleres WHERE id_asignatura = :a ORDER BY unidad, fecha_limite, id_taller',
            [':a' => $asignaturaId]
        );
    }

    public static function countByAsignatura(int $asignaturaId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM Talleres WHERE id_asignatura = :a',
            [':a' => $asignaturaId]
        );
    }

    public static function create(int $asignaturaId, string $titulo, string $descripcion, ?string $unidad, ?string $fechaLimite): int
    {
        return (int) Database::execute(
            'INSERT INTO Talleres (id_asignatura, titulo, descripcion, unidad, fecha_limite)
             VALUES (:a, :t, :d, :u, :l)',
            [
                ':a' => $asignaturaId,
                ':t' => $titulo,
                ':d' => $descripcion,
                ':u' => $unidad !== '' ? $unidad : null,
                ':l' => $fechaLimite !== '' ? $fechaLimite : null,
            ]
        );
    }

    public static function update(int $id, string $titulo, string $descripcion, ?string $unidad, ?string $fechaLimite): void
    {
        Database::execute(
            'UPDATE Talleres SET titulo = :t, descripcion = :d, unidad = :u, fecha_limite = :l WHERE id_taller = :id',
            [
                ':t' => $titulo,
                ':d' => $descripcion,
                ':u' => $unidad !== '' ? $unidad : null,
                ':l' => $fechaLimite !== '' ? $fechaLimite : null,
                ':id' => $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM Talleres WHERE id_taller = :id', [':id' => $id]);
    }
}