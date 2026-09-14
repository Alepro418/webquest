<?php
declare(strict_types=1);

/**
 * Config.php - Carga y acceso a la configuración del sistema (config.json).
 */
final class Config
{
    private static ?array $data = null;

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException("No se encontró el archivo de configuración: $path");
        }
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new RuntimeException('El archivo de configuración es inválido.');
        }
        self::$data = $data;
    }

    /**
     * Devuelve un valor usando notación de puntos: Config::get('database.host')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$data;
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }
}