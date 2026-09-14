<?php
declare(strict_types=1);

/**
 * Database.php - Conexión singleton mediante PDO (MySQL/MariaDB).
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $db = Config::get('database');
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'] ?? 'localhost',
                $db['port'] ?? 3306,
                $db['database'] ?? 'webquest',
                $db['charset'] ?? 'utf8mb4'
            );
            self::$instance = new PDO($dsn, $db['username'] ?? 'root', $db['password'] ?? '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    public static function pdo(): PDO
    {
        return self::connection();
    }

    /** Ejecuta una sentencia preparada y devuelve el id insertado. */
    public static function execute(string $sql, array $params = []): ?int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $id = (int) self::connection()->lastInsertId();
        return $id > 0 ? $id : null;
    }

    /** Devuelve todas las filas de una consulta. */
    public static function rows(string $sql, array $params = []): array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Devuelve una única fila. */
    public static function row(string $sql, array $params = []): ?array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Devuelve el valor de una única columna. */
    public static function scalar(string $sql, array $params = []): mixed
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}