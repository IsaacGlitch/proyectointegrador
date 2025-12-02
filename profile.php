<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_nombre'])) {
    header('Location: login.php');
    exit();
}

// Incluir conexión a la base de datos
require_once 'config/conexion.php';

// Procesar cambio de contraseña
$mensaje = '';
$error = '';

if ($_POST && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    try {
        // Obtener la contraseña actual de la base de datos
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE email = ?");
        $stmt->execute([$_SESSION['usuario_email']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            $error = 'Usuario no encontrado en la base de datos';
        } elseif ($password_actual !== $usuario['password']) {
            $error = 'La contraseña actual es incorrecta';
        } elseif (strlen($password_nueva) < 5) {
            $error = 'La nueva contraseña debe tener al menos 5 caracteres';
        } elseif ($password_nueva !== $confirmar_password) {
            $error = 'Las contraseñas nuevas no coinciden';
        } else {
            // Actualizar la contraseña en la base de datos (texto plano)
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $resultado = $stmt->execute([$password_nueva, $_SESSION['usuario_email']]);
            
            if ($resultado) {
                // Actualizar también la sesión si guardas la contraseña ahí
                $_SESSION['usuario_password'] = $password_nueva;
                $mensaje = 'Contraseña cambiada exitosamente';
            } else {
                $error = 'Error al actualizar la contraseña';
            }
        }
        
    } catch(PDOException $e) {
        $error = 'Error de conexión: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Cinepoint</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <style>

    </style>
</head>
<body>
    <!-- Incluir tu navegación aquí -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Header del perfil -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class='bx bx-user'></i>
            </div>
            <div class="profile-name">
                <?= htmlspecialchars($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellidos']) ?>
            </div>
            <div class="profile-email">
                <?= htmlspecialchars($_SESSION['usuario_email']) ?>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <i class='bx bx-check-circle'></i> <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- Información personal -->
            <div class="info-card">
                <h2 class="card-title">
                    <i class='bx bx-info-circle'></i>
                    Información Personal
                </h2>
                
                <div class="info-item">
                    <div class="info-label">Nombre completo</div>
                    <div class="info-value"><?= htmlspecialchars($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellidos']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Correo electrónico</div>
                    <div class="info-value"><?= htmlspecialchars($_SESSION['usuario_email']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Estado de la cuenta</div>
                    <div class="info-value">
                        <span style="color: #28a745; font-weight: bold;">
                            <i class='bx bx-check-circle'></i> Activa
                        </span>
                    </div>
                </div>
            </div>

            <!-- Cambiar contraseña -->
            <div class="password-card">
                <h2 class="card-title">
                    <i class='bx bx-lock-alt'></i>
                    Cambiar Contraseña
                </h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="password_actual">Contraseña actual</label>
                        <input type="password" id="password_actual" name="password_actual" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password_nueva">Nueva contraseña</label>
                        <input type="password" id="password_nueva" name="password_nueva" class="form-input" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirmar_password">Confirmar nueva contraseña</label>
                        <input type="password" id="confirmar_password" name="confirmar_password" class="form-input" required>
                    </div>
                    
                    <button type="submit" name="cambiar_password" class="btn btn-primary">
                        <i class='bx bx-save'></i> Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>

        <!-- Acciones -->
        <div class="actions">
            <a href="index.php" class="btn btn-secondary">
                <i class='bx bx-home'></i> Volver al Inicio
            </a>
            <a href="logout.php" class="btn btn-primary">
                <i class='bx bx-log-out'></i> Cerrar Sesión
            </a>
        </div>
    </div>

        <!-- Pie de página con información y enlaces -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-logo">
                <h4>Cinepoint</h4>
                <p>Tu mejor opción para los mejores estrenos del cine.</p>
            </div>

            <div class="footer-links">
                <h4>Atención al cliente</h4>
                <ul>
                    <li><a href="#">Quiénes somos</a></li>
                    <li><a href="#">Trabaja con nosotros</a></li>
                    <li><a href="#">Libro de reclamaciones</a></li>
                    <li><a href="#">Términos y condiciones</a></li>
                </ul>
            </div>

            <div class="footer-social">
                <h4>Síguenos en:</h4>
                <div class="social-icons">
                    <a href="#"><img src="img/facebook.png" alt="Facebook"></a>
                    <a href="#"><img src="img/instagram.png" alt="Instagram"></a>
                    <a href="#"><img src="img/twitter.png" alt="Twitter"></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Cinepoint. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script>
        // Validar que las contraseñas coincidan en tiempo real
        document.getElementById('confirmar_password').addEventListener('input', function() {
            const password = document.getElementById('password_nueva').value;
            const confirm = this.value;
            
            if (password !== confirm) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Limpiar mensajes después de 5 segundos
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>