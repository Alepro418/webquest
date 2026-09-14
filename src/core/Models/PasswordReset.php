<?php
declare(strict_types=1);

/**
 * PasswordReset - Modelo de la tabla Password_Resets (recuperación de acceso).
 */
final class PasswordReset
{
    public static function create(int $userId): array
    {
        $token    = bin2hex(random_bytes(32));
        $lifetime = 60 * 60; // 1 hora
        Database::execute(
            'DELETE FROM Password_Resets WHERE id_usuario = :u OR expiracion < NOW()',
            [':u' => $userId]
        );
        Database::execute(
            'INSERT INTO Password_Resets (id_usuario, token, expiracion) VALUES (:u, :t, DATE_ADD(NOW(), INTERVAL :l SECOND))',
            [':u' => $userId, ':t' => $token, ':l' => $lifetime]
        );
        return [
            'token'     => $token,
            'expira_en' => $lifetime / 60,
        ];
    }

    /** Devuelve el usuario al que pertenece un token válido (no usado y no expirado). */
    public static function usuarioDeToken(string $token): ?array
    {
        $row = Database::row(
            'SELECT r.id_reset, r.id_usuario, u.nombre_usuario
             FROM Password_Resets r
             JOIN Usuarios u ON u.id_usuario = r.id_usuario
             WHERE r.token = :t AND r.usado = 0 AND r.expiracion > NOW() LIMIT 1',
            [':t' => $token]
        );
        return $row ?: null;
    }

    public static function markUsed(string $token): void
    {
        Database::execute('UPDATE Password_Resets SET usado = 1 WHERE token = :t', [':t' => $token]);
    }
}