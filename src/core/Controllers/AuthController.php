<?php
declare(strict_types=1);

/**
 * AuthController - Registro, inicio de sesión, recuperación y cambio de contraseña.
 */
final class AuthController
{
    // ------------------------------------------------------------------
    // Registro
    // ------------------------------------------------------------------
    public static function register(array $input): never
    {
        csrf_check();

        $role = $input['role'] ?? '';
        $nombre   = trim((string)($input['name'] ?? ''));
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if (!in_array($role, ['docente', 'estudiante'], true)) {
            flash('error', 'Debes seleccionar un rol válido.');
            redirect(site_url('/public/sign_up.php'));
        }
        if (mb_strlen($nombre) < 3) {
            flash('error', 'El nombre debe tener al menos 3 caracteres.');
            redirect(site_url('/public/sign_up.php'));
        }
        if (mb_strlen($username) < 4) {
            flash('error', 'El nombre de usuario debe tener al menos 4 caracteres.');
            redirect(site_url('/public/sign_up.php'));
        }
        if (Usuario::existsByUsername($username)) {
            flash('error', 'El nombre de usuario ya está en uso.');
            redirect(site_url('/public/sign_up.php'));
        }

        $passwordError = self::validatePasswordPolicy($password);
        if ($passwordError !== null) {
            flash('error', $passwordError);
            redirect(site_url('/public/sign_up.php'));
        }

        // Validaciones específicas por rol
        if ($role === 'docente') {
            $email = trim((string)($input['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Debes ingresar un correo electrónico válido.');
                redirect(site_url('/public/sign_up.php'));
            }
        } else {
            $q = [];
            for ($i = 1; $i <= 5; $i++) {
                $pregunta  = (string)($input["pregunta{$i}"] ?? '');
                $respuesta = trim((string)($input["respuesta{$i}"] ?? ''));
                $label     = PreguntaSeguridad::PREGUNTAS[$pregunta] ?? '';
                if ($label === '' || mb_strlen($respuesta) < 2) {
                    flash('error', 'Completa todas las preguntas de seguridad (respuestas de al menos 2 caracteres).');
                    redirect(site_url('/public/sign_up.php'));
                }
                $q[] = ['pregunta' => $label, 'respuesta' => password_hash(strtolower($respuesta), PASSWORD_ARGON2ID)];
            }
            if (count(array_unique(array_column($q, 'pregunta'))) !== 5) {
                flash('error', 'No puedes repetir la misma pregunta de seguridad.');
                redirect(site_url('/public/sign_up.php'));
            }
        }

        $hash = password_hash($password, PASSWORD_ARGON2ID);

        try {
            $id = Usuario::create($username, $hash, $role, $nombre, ['email' => $email ?? null]);
            if ($role === 'estudiante') {
                PreguntaSeguridad::create($id, $q);
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            flash('error', 'Ocurrió un problema al registrar. Inténtalo de nuevo.');
            redirect(site_url('/public/sign_up.php'));
        }

        flash('success', '¡Registro exitoso! Mientras tanto puedes iniciar sesión.');
        redirect(site_url('/public/sign_in.php'));
    }

    // ------------------------------------------------------------------
    // Inicio de sesión
    // ------------------------------------------------------------------
    public static function login(array $input): never
    {
        csrf_check();

        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');

        $user = Usuario::byUsername($username);

        if ($user === null || !self::verifyPassword($user, $password)) {
            if ($user !== null) {
                self::handleFailedAttempt((int)$user['id_usuario']);
            }
            flash('error', 'Usuario o contraseña incorrectos.');
            redirect(site_url('/public/sign_in.php'));
        }

        if (Usuario::isBlocked($user)) {
            $minutes = max(1, (int)ceil((strtotime($user['bloqueado_hasta']) - time()) / 60));
            flash('error', "Cuenta bloqueada por intentos fallidos. Intenta en {$minutes} min.");
            redirect(site_url('/public/sign_in.php'));
        }

        Usuario::resetAttempts((int)$user['id_usuario']);

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => (int)$user['id_usuario'],
            'username' => $user['nombre_usuario'],
            'rol'      => $user['rol'],
            'nombre'   => $user['nombre_completo'],
            'email'    => $user['email'],
            'grado'    => $user['grado'],
            'seccion'  => $user['seccion'],
            'registrado'=> $user['fecha_registro'],
        ];

        redirect(site_url($user['rol'] === 'docente'
            ? '/public/teaching/index.php'
            : '/public/student/index.php'));
    }

    public static function logout(): never
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        redirect(site_url('/public/sign_in.php'));
    }

    // ------------------------------------------------------------------
    // Recuperación de contraseña
    // ------------------------------------------------------------------
    public static function verifyUser(array $input): never
    {
        $username = trim((string)($input['username'] ?? ''));
        $user = Usuario::byUsername($username);

        if ($user === null) {
            flash('error', 'No encontramos ese usuario. Verifica el nombre ingresado.');
            redirect(site_url('/public/recover.php'));
        }

        $_SESSION['recovery'] = ['user_id' => (int)$user['id_usuario'], 'role' => $user['rol']];

        $url = site_url('/public/recover.php') . '?step=2';
        if ($user['rol'] === 'docente') {
            $url .= '&type=docente';
        } else {
            $url .= '&type=estudiante';
        }
        redirect($url);
    }

    public static function requestResetDocente(array $input): never
    {
        csrf_check();
        $recovery = $_SESSION['recovery'] ?? null;
        $user = $recovery ? Usuario::byId((int)$recovery['user_id']) : null;

        if ($user === null || $user['rol'] !== 'docente') {
            flash('error', 'La sesión de recuperación expiró. Comienza de nuevo.');
            redirect(site_url('/public/recover.php'));
        }

        $email = trim((string)($input['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strcasecmp((string)$user['email'], $email) !== 0) {
            flash('error', 'El correo no coincide con el registrado para este docente.');
            redirect(site_url('/public/recover.php?step=2&type=docente'));
        }

        if ((bool)Config::get('mail.enabled', false)) {
            // Envío real de correo electrónico (requiere credenciales SMTP configuradas).
            $token = PasswordReset::create((int)$user['id_usuario'])['token'];
            @mail($email, 'Recuperación de acceso - Webquest', "Tu enlace de recuperación es " . site_url('/public/recover.php') . "?step=3&token=" . $token);
            flash('success', 'Te enviamos un enlace de recuperación a tu correo.');
            redirect(site_url('/public/sign_in.php'));
        }

        // Sin SMTP: se genera un código temporal que se muestra al docente (modo local/demo).
        $token = PasswordReset::create((int)$user['id_usuario'])['token'];
        flash('info', 'Como el envío de correo está desactivado en este entorno, se generó un código de recuperación de uso único.');
        redirect(site_url('/public/recover.php?step=3&token=' . $token . '&mode=code'));
    }

    public static function verifyAnswers(array $input): never
    {
        csrf_check();
        $recovery = $_SESSION['recovery'] ?? null;
        $user = $recovery ? Usuario::byId((int)$recovery['user_id']) : null;

        if ($user === null || $user['rol'] !== 'estudiante') {
            flash('error', 'La sesión de recuperación expiró. Comienza de nuevo.');
            redirect(site_url('/public/recover.php'));
        }

        $answers = [];
        for ($i = 1; $i <= 5; $i++) {
            $answers[$i] = (string)($input["answer_{$i}"] ?? '');
        }

        if (!PreguntaSeguridad::verifyAnswers((int)$user['id_usuario'], $answers)) {
            flash('error', 'Una o más respuestas son incorrectas. Inténtalo de nuevo.');
            redirect(site_url('/public/recover.php?step=2&type=estudiante'));
        }

        $_SESSION['recovery']['verified'] = true;
        redirect(site_url('/public/recover.php?step=3'));
    }

    public static function resetPassword(array $input): never
    {
        csrf_check();
        $recovery = $_SESSION['recovery'] ?? null;
        $user = $recovery ? Usuario::byId((int)$recovery['user_id']) : null;

        if ($user === null) {
            flash('error', 'La sesión de recuperación expiró. Comienza de nuevo.');
            redirect(site_url('/public/recover.php'));
        }

        $token = (string)($input['token'] ?? '');

        // Para docentes se exige el token generado; para estudiantes la verificación de identidad.
        if ($user['rol'] === 'docente') {
            if ($token === '') {
                flash('error', 'Debes completar la verificación del docente antes de continuar.');
                redirect(site_url('/public/recover.php'));
            }
            $owner = PasswordReset::usuarioDeToken($token);
            if ($owner === null || (int)$owner['id_usuario'] !== (int)$user['id_usuario']) {
                flash('error', 'El código de recuperación es inválido o caducó.');
                redirect(site_url('/public/recover.php?step=2&type=docente'));
            }
        }

        $newPassword = (string)($input['new_password'] ?? '');
        $confirm     = (string)($input['confirm_password'] ?? '');

        if ($newPassword !== $confirm) {
            flash('error', 'Las contraseñas no coinciden.');
            redirect(site_url('/public/recover.php?step=3'));
        }

        $passwordError = self::validatePasswordPolicy($newPassword);
        if ($passwordError !== null) {
            flash('error', $passwordError);
            redirect(site_url('/public/recover.php?step=3'));
        }

        Usuario::updatePassword((int)$user['id_usuario'], password_hash($newPassword, PASSWORD_ARGON2ID));
        Usuario::resetAttempts((int)$user['id_usuario']);

        if ($token !== '') {
            PasswordReset::markUsed($token);
        }
        unset($_SESSION['recovery']);

        flash('success', '¡Contraseña actualizada exitosamente! Ya puedes iniciar sesión.');
        redirect(site_url('/public/sign_in.php'));
    }

    // ------------------------------------------------------------------
    // Cambio de contraseña (usuario autenticado)
    // ------------------------------------------------------------------
    public static function changePassword(array $user, array $input): never
    {
        csrf_check();
        require_login($user['rol'] ?? null);

        $actual = (string)($input['pass_actual'] ?? '');
        $nueva  = (string)($input['pass_nueva'] ?? '');
        $confirm = (string)($input['pass_confirm'] ?? '');

        $dbUser = Usuario::byId((int)$user['id']);
        if ($dbUser === null || !password_verify($actual, $dbUser['password'])) {
            flash('error', 'La contraseña actual es incorrecta.');
            redirect(site_url('/public/' . ($user['rol'] === 'docente' ? 'teaching' : 'student') . '/settings.php'));
        }
        if ($nueva !== $confirm) {
            flash('error', 'Las contraseñas no coinciden.');
            redirect(site_url('/public/' . ($user['rol'] === 'docente' ? 'teaching' : 'student') . '/settings.php'));
        }
        $passwordError = self::validatePasswordPolicy($nueva);
        if ($passwordError !== null) {
            flash('error', $passwordError);
            redirect(site_url('/public/' . ($user['rol'] === 'docente' ? 'teaching' : 'student') . '/settings.php'));
        }

        Usuario::updatePassword((int)$user['id'], password_hash($nueva, PASSWORD_ARGON2ID));
        flash('success', 'Contraseña actualizada correctamente.');
        redirect(site_url('/public/' . ($user['rol'] === 'docente' ? 'teaching' : 'student') . '/settings.php'));
    }

    // ------------------------------------------------------------------
    // Helpers privados
    // ------------------------------------------------------------------
    private static function validatePasswordPolicy(string $password): ?string
    {
        $policy  = Config::get('security.password', []);
        $min     = (int)($policy['min_length'] ?? 8);

        if (mb_strlen($password) < $min) {
            return "La contraseña debe tener al menos {$min} caracteres.";
        }
        if (!empty($policy['require_uppercase']) && !preg_match('/[A-Z]/', $password)) {
            return 'La contraseña debe incluir al menos una mayúscula.';
        }
        if (!empty($policy['require_lowercase']) && !preg_match('/[a-z]/', $password)) {
            return 'La contraseña debe incluir al menos una minúscula.';
        }
        if (!empty($policy['require_numbers']) && !preg_match('/[0-9]/', $password)) {
            return 'La contraseña debe incluir al menos un número.';
        }
        if (!empty($policy['require_special']) && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'La contraseña debe incluir al menos un carácter especial.';
        }
        return null;
    }

    private static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password']);
    }

    private static function handleFailedAttempt(int $userId): void
    {
        $max = (int)Config::get('security.login.max_attempts', 3);
        $minutes = (int)Config::get('security.login.block_time', 30);

        $attempts = (int)Database::scalar(
            'SELECT intentos_fallidos FROM Usuarios WHERE id_usuario = :id',
            [':id' => $userId]
        );
        $attempts++;

        if ($attempts >= $max) {
            Database::execute(
                'UPDATE Usuarios SET intentos_fallidos = :a, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL :m MINUTE) WHERE id_usuario = :id',
                [':a' => $attempts, ':m' => $minutes, ':id' => $userId]
            );
        } else {
            Database::execute(
                'UPDATE Usuarios SET intentos_fallidos = :a WHERE id_usuario = :id',
                [':a' => $attempts, ':id' => $userId]
            );
        }
    }
}