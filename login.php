<?php
session_start();
require_once 'config/conexion.php';

$login_error = '';
$register_error = '';
$register_success = '';
$show_register = false;

// Procesar formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $login_error = 'Por favor, completa todos los campos.';
    } else {
        // Consulta con PDO
        $sql = "SELECT * FROM usuarios WHERE email = :email AND password = :password";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_apellidos'] = $usuario['apellidos'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_password'] = $usuario['password'];

                header("Location: peliculas.php");
                exit();
            } else {
                $login_error = 'Cuenta incorrecta. Verifica tu email o contraseña.';
            }
        } catch (PDOException $e) {
            $login_error = 'Error en la consulta: ' . $e->getMessage();
        }
    }
}

// Procesar formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    $show_register = true;
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validar que todos los campos estén llenos
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($confirm_password)) {
        $register_error = 'Por favor, complete todos los campos.';
    }
    // Verificar que las contraseñas coincidan
    elseif ($password !== $confirm_password) {
        $register_error = 'Las contraseñas no coinciden.';
    }
    else {
        // Verificar si el email ya existe
        $check_sql = "SELECT email FROM usuarios WHERE email = :email";
        try {
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                $register_error = 'El email ya está registrado.';
            } else {
                // Preparar la consulta de inserción (sin fecha_nacimiento)
                $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) 
                        VALUES (:nombre, :apellidos, :email, :password)";

                try {
                    // Preparar la sentencia SQL
                    $stmt = $pdo->prepare($sql);

                    // Vincular los parámetros
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':apellidos', $apellidos);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $password);

                    // Ejecutar la consulta
                    if ($stmt->execute()) {
                        $register_success = 'Registro exitoso. Ahora puedes iniciar sesión.';
                        $show_register = false;
                    } else {
                        $register_error = 'Error al registrar usuario.';
                    }
                } catch (PDOException $e) {
                    $register_error = 'Error al registrar: ' . $e->getMessage();
                }
            }
        } catch (PDOException $e) {
            $register_error = 'Error al verificar email: ' . $e->getMessage();
        }
    }
}

// Verificar si el usuario ya está logueado
if (isset($_SESSION['usuario_nombre'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Cinepoint</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
            text-align: left;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <!-- Incluir tu navegación aquí -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="left">
            <!-- Formulario de Inicio de Sesión -->
            <form id="login-form" method="POST" class="<?php echo (!$show_register) ? 'active' : ''; ?>">
                <input type="hidden" name="action" value="login">
                
                <?php if (!empty($login_error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($register_success)): ?>
                    <div class="message success"><?php echo htmlspecialchars($register_success); ?></div>
                <?php endif; ?>
                
                <h2>Inicia Sesión</h2>

                <div class="input-icon">
                    <i class='bx bx-envelope'></i>
                    <input type="email" placeholder="Email" name="email" required />
                </div>
                <div class="input-icon">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" placeholder="Password" name="password" required />
                    <button type="button" class="toggle-password" aria-label="Mostrar contraseña">&#128274;</button>
                </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>

            <!-- Formulario de Registro -->
            <form id="register-form" method="POST" class="<?php echo ($show_register) ? 'active' : ''; ?>">
                <input type="hidden" name="action" value="register">
                
                <?php if (!empty($register_error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>
                
                <h2>Registro</h2>
                
                <div class="input-icon">
                    <i class='bx bx-user'></i>
                    <input type="text" placeholder="Nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required />
                </div>
                <div class="input-icon">
                    <i class='bx bx-user-plus'></i>
                    <input type="text" placeholder="Apellidos" name="apellidos" value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>" required />
                </div>
                <div class="input-icon">
                    <i class='bx bx-envelope'></i>
                    <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />
                </div>
                <div class="input-icon">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" placeholder="Password" name="password" required />
                    <button type="button" class="toggle-password" aria-label="Mostrar contraseña">&#128274;</button>
                </div>
                <div class="input-icon">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" placeholder="Confirm Password" name="confirm_password" required />
                    <button type="button" class="toggle-password" aria-label="Mostrar contraseña">&#128274;</button>
                </div>
                <button type="submit" class="btn-submit">Regístrate</button>
            </form>
        </div>

        <div class="right">
            <!-- Panel lateral con mensaje y botón para alternar entre formularios -->
            <h2 id="right-title"><?php echo ($show_register) ? '¡Únete a nosotros!' : '¡Bienvenido de nuevo!'; ?></h2>
            <p id="right-text"><?php echo ($show_register) ? '¿Ya tienes cuenta?' : '¿Aún no tienes cuenta?'; ?></p>
            <button id="toggle-btn" class="btn-toggle"><?php echo ($show_register) ? 'Inicia Sesión' : 'Regístrate'; ?></button>
        </div>
    </div>

    <script>
        // Referencias a los formularios y elementos para alternar
        const registerForm = document.getElementById('register-form');
        const loginForm = document.getElementById('login-form');
        const toggleBtn = document.getElementById('toggle-btn');
        const rightTitle = document.getElementById('right-title');
        const rightText = document.getElementById('right-text');

        // Variable para saber qué formulario se muestra (inicializar según PHP)
        let showingRegister = <?php echo ($show_register) ? 'true' : 'false'; ?>;

        // Función para alternar entre formulario login y registro
        toggleBtn.addEventListener('click', () => {
            if (showingRegister) {
                // Mostrar formulario de login
                registerForm.classList.remove('active');
                loginForm.classList.add('active');
                rightTitle.textContent = "¡Bienvenido de nuevo!";
                rightText.textContent = "¿Aún no tienes cuenta?";
                toggleBtn.textContent = "Regístrate";
                showingRegister = false;
            } else {
                // Mostrar formulario de registro
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
                rightTitle.textContent = "¡Únete a nosotros!";
                rightText.textContent = "¿Ya tienes cuenta?";
                toggleBtn.textContent = "Inicia Sesión";
                showingRegister = true;
            }
        });

        // Funcionalidad para mostrar/ocultar contraseña en ambos formularios
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const input = button.previousElementSibling; // Input justo antes del botón
                if (input.type === 'password') {
                    input.type = 'text';
                    button.setAttribute('aria-label', 'Ocultar contraseña');
                    button.innerHTML = '&#128275;'; // Icono para "contraseña visible"
                } else {
                    input.type = 'password';
                    button.setAttribute('aria-label', 'Mostrar contraseña');
                    button.innerHTML = '&#128274;'; // Icono para "contraseña oculta"
                }
            });
        });
    </script>
</body>
</html>