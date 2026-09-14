<?php
require dirname(__DIR__) . '/src/core/bootstrap.php';
$user = current_user();
if ($user !== null) {
    redirect(site_url($user['rol'] === 'docente' ? '/public/teaching/index.php' : '/public/student/index.php'));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Webquest</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .landing {
            max-width: 1000px;
            margin: 0 auto;
            padding: 3rem 2rem;
            text-align: center;
        }
        .landing .logo-lg {
            font-size: 3rem;
            font-weight: 700;
            color: var(--sidebar-color);
            margin-bottom: 0.5rem;
        }
        .landing p {
            color: #4a6a5a;
            line-height: 1.7;
            font-size: 1.1rem;
            max-width: 720px;
            margin: 0 auto 2rem;
        }
        .landing-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        .btn-ghost {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s;
        }
        .btn-ghost:hover { background: var(--primary); color: white; }
        .landing-footer {
            margin-top: 3rem;
            color: #94a3b8;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="landing">
            <div class="logo-lg">🌿 WEBQUEST</div>
            <p>Bienvenido a la <strong>Webquest de Ciencias Naturales</strong>. Aquí podrás aprender sobre el mundo que te rodea, completar desafíos en tu libreta y subir tus evidencias para que tu profesor las evalúe.</p>

            <div class="features-mini-grid">
                <div class="feature-item">
                    <span class="icon">📖</span>
                    <h4>Aprende</h4>
                    <p>Recursos multimedia y lecciones interactivas.</p>
                </div>
                <div class="feature-item">
                    <span class="icon">📝</span>
                    <h4>Practica</h4>
                    <p>Realiza tus tareas en tu libreta física.</p>
                </div>
                <div class="feature-item">
                    <span class="icon">🚀</span>
                    <h4>Mejora</h4>
                    <p>Sigue tu estatus motivacional y progresa.</p>
                </div>
            </div>

            <div class="landing-buttons">
                <a href="sign_in.php" class="btn-primary">Entrar al Sistema</a>
                <a href="sign_up.php" class="btn-ghost">Registrarse</a>
            </div>

            <div class="landing-footer">
                <?php include 'functions/date.php'; ?>
            </div>
        </div>
    </main>
</body>
</html>