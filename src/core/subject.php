<?php
declare(strict_types=1);

/**
 * subject.php - Endpoint de gestión de asignaturas, talleres y recursos (docente).
 */
require __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$user   = current_user() ?? [];

switch ($action) {
    case 'save_subject':
        SubjectController::saveSubject($user, $_POST);
        break;
    case 'set_estado':
        SubjectController::setEstado($user, $_POST);
        break;
    case 'delete_subject':
        SubjectController::deleteSubject($user, $_POST);
        break;
    case 'save_taller':
        SubjectController::saveTaller($user, $_POST);
        break;
    case 'delete_taller':
        SubjectController::deleteTaller($user, $_POST);
        break;
    case 'save_recurso':
        SubjectController::saveRecurso($user, $_POST, $_FILES);
        break;
    case 'delete_recurso':
        SubjectController::deleteRecurso($user, $_POST);
        break;
    default:
        flash('error', 'Acción no válida.');
        redirect(site_url('/public/teaching/subjects.php'));
}