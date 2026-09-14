<?php
declare(strict_types=1);

/**
 * Usuario - Modelo de la tabla Usuarios.
 */
final class Usuario
{
    public static function byUsername(string $username): ?array
    {
        return Database::row(
            'SELECT * FROM Usuarios WHERE nombre_usuario = :u LIMIT 1',
            [':u' => $username]
        );
    }

    public static function byId(int $id): ?array
    {
        return Database::row(
            'SELECT * FROM Usuarios WHERE id_usuario = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public static function existsByUsername(string $username): bool
    {
        $id = Database::scalar(
            'SELECT id_usuario FROM Usuarios WHERE nombre_usuario = :u LIMIT 1',
            [':u' => $username]
        );
        return $id !== false && $id !== null;
    }

    /**
     * Crea un usuario. $extra puede incluir email, grado y seccion.
     * Devuelve el id del usuario creado.
     */
    public static function create(string $username, string $passwordHash, string $role, string $nombreCompleto, array $extra = []): int
    {
        return (int) Database::execute(
            'INSERT INTO Usuarios (nombre_usuario, password, rol, nombre_completo, email, grado, seccion)
             VALUES (:u, :p, :r, :n, :e, :g, :s)',
            [
                ':u' => $username,
                ':p' => $passwordHash,
                ':r' => $role,
                ':n' => $nombreCompleto,
                ':e' => $extra['email'] ?? null,
                ':g' => $extra['grado'] ?? null,
                ':s' => $extra['seccion'] ?? null,
            ]
        );
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        Database::execute(
            'UPDATE Usuarios SET password = :p WHERE id_usuario = :id',
            [':p' => $passwordHash, ':id' => $id]
        );
    }

    public static function isBlocked(array $user): bool
    {
        $until = $user['bloqueado_hasta'] ?? null;
        if ($until === null) {
            return false;
        }
        return strtotime($until) > time();
    }

    public static function registerFailedAttempt(int $id): void
    {
        Database::execute(
            'UPDATE Usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id_usuario = :id',
            [':id' => $id]
        );
    }

    public static function resetAttempts(int $id): void
    {
        Database::execute(
            'UPDATE Usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = :id',
            [':id' => $id]
        );
    }

    /** Los estudiantes de una cohorte (grado + sección). */
    public static function studentsByGrade(int $grado, string $seccion): array
    {
        return Database::rows(
            'SELECT * FROM Usuarios
             WHERE rol = \'estudiante\' AND grado = :g AND seccion = :s
             ORDER BY nombre_completo',
            [':g' => $grado, ':s' => $seccion]
        );
    }

    /** Todos los estudiantes del sistema. */
    public static function allStudents(): array
    {
        return Database::rows(
            'SELECT * FROM Usuarios WHERE rol = \'estudiante\' ORDER BY grado, seccion, nombre_completo'
        );
    }
}