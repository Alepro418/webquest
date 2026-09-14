<?php
declare(strict_types=1);

/**
 * auth.php - Endpoint de autenticación.
 * Recibe las peticiones de los formularios de login, registro y recuperación.
 */
require __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'register');

switch ($action) {
    case 'login':
        AuthController::login($_POST);
        break;

    case 'register':
        AuthController::register($_POST);
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'verify_user':
        AuthController::verifyUser($_POST);
        break;

    case 'request_reset':
        AuthController::requestResetDocente($_POST);
        break;

    case 'verify_answers':
        AuthController::verifyAnswers($_POST);
        break;

    case 'reset_password':
        AuthController::resetPassword($_POST);
        break;

    case 'change_password':
        AuthController::changePassword(current_user() ?? [], $_POST);
        break;

    default:
        flash('error', 'Acción no válida.');
        redirect(site_url('/public/index.php'));
}