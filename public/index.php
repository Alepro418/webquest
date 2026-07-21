<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Webquest</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 1) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>Plataforma Educativa</h1>
                <div class="date-info"><?php include 'functions/date.php' ?></div>
            </header>

            <section class="welcome-card">
                <div class="welcome-content">
                    <h2>¡Hola, explorador! 👋</h2>
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

                    <div class="cta-group">
                        <p>¿Listo para comenzar tu aventura científica?</p>
                        <a href="login.php" class="btn-primary">Entrar al Sistema</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>