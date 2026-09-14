<?php
if (!defined('WEBQUEST_BOOTSTRAPPED')) {
    require dirname(__DIR__, 2) . '/src/core/bootstrap.php';
}

$user = current_user();
$base = webroot_url();

$navItems = [];
if ($user !== null && $user['rol'] === 'docente') {
    $navItems = [
        ['index.php', 'Panel General'],
        ['subjects.php', 'Asignaturas'],
        ['assignments.php', 'Entregas'],
        ['reports.php', 'Reportes'],
        ['settings.php', 'Configuración'],
    ];
} elseif ($user !== null) {
    $navItems = [
        ['index.php', 'Panel General'],
        ['subjects.php', 'Asignaturas'],
        ['assignments.php', 'Entregas'],
        ['reports.php', 'Reportes'],
        ['settings.php', 'Configuración'],
        ['profile.php', 'Mi Perfil'],
    ];
}
$currentFile = basename($_SERVER['PHP_SELF'] ?? '');
?>
<aside class="sidebar">
    <div class="logo"><a href="<?= e($base . ($user !== null ? '/public/' . $user['rol'] . '/index.php' : '/public/index.php')) ?>" style="color:inherit;text-decoration:none;">🌿 WEBQUEST <span>Eco</span></a></div>

    <?php if ($user !== null): ?>
        <div class="sidebar-user">
            <div class="avatar-mini"><?= e(strtoupper(substr($user['nombre'], 0, 2))) ?></div>
            <div class="sidebar-user-name"><?= e($user['nombre']) ?></div>
            <div class="sidebar-user-role"><?= $user['rol'] === 'docente' ? '👨‍🏫 Docente' : '🎒 Estudiante' ?></div>
        </div>
    <?php endif; ?>

    <nav>
        <?php foreach ($navItems as [$href, $label]): ?>
            <a href="<?= e($href) ?>" <?= $currentFile === $href ? 'class="active"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="menu-spacer"></div>

    <div class="sidebar-footer">
        <?php if ($user !== null): ?>
            <span class="sidebar-user-meta">
                👤 <?= e($user['username'] ?? '') ?>
                <?php if (!empty($user['grado'])): ?> • <?= e($user['grado']) ?>°<?= e($user['seccion'] ?? '') ?><?php endif; ?>
            </span>
            <a href="<?= e($base) ?>/src/core/auth.php?action=logout" class="btn-logout">🚪 Cerrar Sesión</a>
        <?php else: ?>
            <a href="<?= e($base) ?>/public/sign_in.php" class="btn-login">Iniciar Sesión</a>
            <a href="<?= e($base) ?>/public/sign_up.php" class="btn-register">Registrarse</a>
        <?php endif; ?>
    </div>
</aside>