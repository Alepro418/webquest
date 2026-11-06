<?php
declare(strict_types=1);

/**
 * pa.php - Endpoint de configuración de Proyectos de Aula (docente).
 */
require __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$user   = current_user() ?? [];

switch ($action) {
    case 'save_pa':
        ProyectoController::save($user, $_POST);
        break;
    case 'set_estado_pa':
        ProyectoController::setEstado($user, $_POST);
        break;
    case 'activar_pa':
        ProyectoController::activar($user, $_POST);
        break;
    case 'delete_pa':
        ProyectoController::delete($user, $_POST);
        break;
    default:
        flash('error', 'Acción no válida.');
        redirect(site_url('/public/teaching/pa.php'));
}
