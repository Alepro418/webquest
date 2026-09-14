<?php
declare(strict_types=1);

/**
 * bootstrap.php - Punto de entrada común de la aplicación.
 * Carga configuración, sesión, helpers y autoloader de modelos/controladores.
 *
 * Rutas de inclusión desde las vistas:
 *   public/*.php            -> require dirname(__DIR__, 1) . '/core/bootstrap.php'
 *   public/student|teaching -> require dirname(__DIR__, 2) . '/core/bootstrap.php'
 *   src/core/auth.php       -> require __DIR__ . '/bootstrap.php'
 */

if (defined('WEBQUEST_BOOTSTRAPPED')) {
    return;
}
define('WEBQUEST_BOOTSTRAPPED', true);

define('WEBQUEST_ROOT', dirname(__DIR__, 2));
define('WEBQUEST_SRC', dirname(__DIR__));          // /src
define('WEBQUEST_CORE', __DIR__);                  // /src/core
define('WEBQUEST_PUBLIC', WEBQUEST_ROOT . '/public');

// ------------------------------------------------------------------
// Configuración
// ------------------------------------------------------------------
require_once WEBQUEST_CORE . '/Config.php';
Config::load(WEBQUEST_ROOT . '/config.json');

// Clases base usadas por el resto de la aplicación
require_once WEBQUEST_CORE . '/Database.php';
require_once WEBQUEST_CORE . '/Upload.php';

// ------------------------------------------------------------------
// Sesión (config: security.session)
// ------------------------------------------------------------------
$sessionCfg = Config::get('security.session', []);
if (session_status() === PHP_SESSION_NONE) {
    session_name($sessionCfg['name'] ?? 'webquest_session');
    session_set_cookie_params([
        'lifetime' => (int)($sessionCfg['lifetime'] ?? 7200),
        'path'     => '/',
        'secure'   => !empty($sessionCfg['secure']),
        'httponly' => (bool)($sessionCfg['httponly'] ?? true),
        'samesite' => $sessionCfg['samesite'] ?? 'Strict',
    ]);
    session_start();
}

// ------------------------------------------------------------------
// Zona horaria y locale
// ------------------------------------------------------------------
date_default_timezone_set(Config::get('project.timezone', 'America/Caracas'));
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// ------------------------------------------------------------------
// Autoloader simple para modelos y controladores
// ------------------------------------------------------------------
spl_autoload_register(static function (string $class): void {
    $file = WEBQUEST_CORE . "/Models/{$class}.php";
    if (is_file($file)) {
        require_once $file;
        return;
    }
    $file = WEBQUEST_CORE . "/Controllers/{$class}.php";
    if (is_file($file)) {
        require_once $file;
        return;
    }
    $file = WEBQUEST_ROOT . "/src/db/{$class}.php";
    if (is_file($file)) {
        require_once $file;
    }
});

// ------------------------------------------------------------------
// Helpers globales
// ------------------------------------------------------------------

/** Escapa texto para HTML. */
function e(mixed $value, bool $double = true): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $double);
}

/** URL base de la aplicación (ej. /webquest). */
function webroot_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $script = str_replace('\\', '/', $script);
    if (preg_match('#^(.*/)(public|src)/.*$#', $script, $m)) {
        $base = rtrim($m[1], '/');
    } else {
        $base = '';
    }
    return $base;
}

/** Convierte una ruta almacenada (ej. templates/... o public/...) a URL absoluta del sitio. */
function asset_url(string $path): string
{
    $path = trim($path === '' ? '' : (string)$path);
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return webroot_url() . '/' . $path;
}

/** URL absoluta desde la raíz: url('/public/student/index.php'). */
function site_url(string $path): string
{
    return webroot_url() . '/' . ltrim($path, '/');
}

/** Redirección HTTP. */
function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

/** Mensajes flash en sesión. */
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

/** Token CSRF. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto CSRF para formularios. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!hash_equals(csrf_token(), (string)$sent)) {
        http_response_code(419);
        exit('Sesión expirada o petición no válida. Vuelve a intentarlo.');
    }
}

/** Usuario actualmente autenticado o null. */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Corta la respuesta si el usuario no está autenticado (o no tiene el rol exigido). */
function require_login(?string $role = null): array
{
    $user = current_user();
    if ($user === null) {
        flash('error', 'Debes iniciar sesión para acceder.');
        redirect(site_url('/public/sign_in.php'));
    }
    if ($role !== null && $user['rol'] !== $role) {
        flash('error', 'No tienes permiso para acceder a esta sección.');
        redirect(site_url('/public/sign_in.php'));
    }
    return $user;
}