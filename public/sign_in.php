<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/style.css">
    <title>Iniciar Sesión - Webquest</title>
    <style>
        .login-container {
            max-width: 400px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: var(--sidebar-color);
            font-size: 1.8rem;
        }
        .login-header p {
            color: #64748b;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #475569;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        /* Wrapper del campo de contraseña */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            flex: 1;
            padding-right: 2.8rem; /* espacio para el botón */
        }
        .toggle-password {
            position: absolute;
            right: 0.6rem;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.25rem;
            line-height: 1;
            color: inherit;
        }
        .toggle-password:hover {
            opacity: 0.7;
        }
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: var(--sidebar-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .login-btn:hover {
            background: #0f172a;
            transform: translateY(-2px);
        }
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        .alert-success {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
            color: #166534;
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="login-container">
            <div class="login-header">
                <h1> Iniciar Sesión</h1>
                <p>Ingresa tus credenciales para acceder</p>
            </div>

            <!-- Mensajes de error/éxito (PHP los llenará después) -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <form action="../src/core/auth.php?action=login" method="post">
                <div class="form-group">
                    <label for="username">👤 Usuario</label>
                    <input type="text" id="username" name="username" placeholder="Ingresa tu usuario" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">🔑 Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                        <button type="button"
                                id="toggle-password"
                                class="toggle-password"
                                aria-label="Mostrar contraseña"
                                aria-pressed="false">👁️</button>
                    </div>
                </div>

                <button type="submit" class="login-btn">Ingresar</button>
            </form>

            <div class="footer-text">
                <p><a href="recover.php">¿Olvidaste tu contraseña?</a></p>
                <p>¿No tienes cuenta? <a href="sign_up.php">Regístrate aquí</a></p>
            </div>
        </div>

        <!-- Pie de página con fecha -->
        <div style="text-align: center; margin-top: 2rem; color: #94a3b8;">
            <?php include 'functions/date.php'; ?>
        </div>

        <!-- Script reutilizable: mostrar/ocultar contraseña -->
        <script src="js/password.js"></script>
    </main>
</body>
</html>