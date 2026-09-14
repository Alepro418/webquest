<?php
declare(strict_types=1);

/**
 * Recurso - Modelo de la tabla Recursos.
 */
final class Recurso
{
    public static function byAsignatura(int $asignaturaId): array
    {
        return Database::rows(
            'SELECT * FROM Recursos WHERE id_asignatura = :a ORDER BY id_recurso DESC',
            [':a' => $asignaturaId]
        );
    }

    public static function create(int $asignaturaId, string $titulo, string $tipo, string $url): int
    {
        return (int) Database::execute(
            'INSERT INTO Recursos (id_asignatura, titulo, tipo, url_o_ruta) VALUES (:a, :t, :tp, :u)',
            [':a' => $asignaturaId, ':t' => $titulo, ':tp' => $tipo, ':u' => $url]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM Recursos WHERE id_recurso = :id', [':id' => $id]);
    }
}