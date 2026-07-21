<aside class="sidebar">
    <div class="logo">🌿 WEBQUEST <span>Eco</span></div>
    
    <nav>
        <a href="index.php" <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : '' ?>>Panel General</a>
        <a href="subjects.php" <?= basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'class="active"' : '' ?>>Asignaturas</a>
        <a href="assignments.php" <?= basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'class="active"' : '' ?>>Entregas</a>
        <a href="reports.php" <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'class="active"' : '' ?>>Reportes</a>
        <a href="settings.php" <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : '' ?>>Configuración</a>
    </nav>
    
    <div class="menu-spacer"></div>

    <div class="sidebar-footer">
        <a href="sign_in.php" class="btn-login">Iniciar Sesión</a>
        <a href="sign_up.php" class="btn-register">Registrarse</a>
    </div>
</aside>