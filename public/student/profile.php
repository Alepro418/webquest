<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Webquest</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-card {
            max-width: 600px;
            margin: 0 auto;
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }

        .profile-card .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: bold;
            margin: 0 auto 1.5rem;
        }

        .profile-card .form-group {
            margin-bottom: 1.2rem;
        }

        .profile-card .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.3rem;
        }

        .profile-card .form-group input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: 8px;
            background: #fafffa;
            font-size: 1rem;
        }

        .profile-card .form-group input:focus {
            border-color: var(--primary);
            outline: none;
        }

        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }

        .btn-save:hover {
            background: var(--primary-hover);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include dirname(__DIR__, 2) . '/template/sidebar1.php'; ?>

        <main class="main-content">
            <header class="top-bar">
                <h1>👤 Mi Perfil</h1>
                <div class="date-info">
                    <?php 
                        date_default_timezone_set('America/Caracas');
                        echo date('d/m/Y');
                    ?>
                </div>
            </header>

            <div class="profile-card">
                <div class="avatar">MG</div>

                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" value="María García">
                </div>
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" value="maria.garcia@estudiante.edu">
                </div>
                <div class="form-group">
                    <label>Grado</label>
                    <input type="text" value="4to Grado" disabled style="background: #f1f5f9;">
                </div>
                <div class="form-group">
                    <label>Estatus motivacional</label>
                    <input type="text" value="🌟 Sobresaliente" disabled style="background: #f1f5f9;">
                </div>

                <button class="btn-save" onclick="guardarPerfil()">💾 Guardar cambios</button>
            </div>
        </main>
    </div>

    <script>
        function guardarPerfil() {
            alert('✅ Perfil actualizado (simulación)');
        }
    </script>
</body>
</html>