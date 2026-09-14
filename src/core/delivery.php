<?php
declare(strict_types=1);

/**
 * delivery.php - Endpoint de entregas de evidencias y evaluación.
 */
require __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$user   = current_user() ?? [];

switch ($action) {
    case 'submit':
        DeliveryController::submit($user, $_POST, $_FILES);
        break;
    case 'evaluate':
        DeliveryController::evaluate($user, $_POST);
        break;
    default:
        flash('error', 'Acción no válida.');
        redirect(site_url('/public/index.php'));
}