<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
   $_SESSION['carrito'] = [];
}

// Contar items en carrito
$items_carrito = array_sum($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navegación CinePoint</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <nav class="nav-container">
        <a href="index.php" class="logo-link">
            <img src="img/cinepoint.png" alt="Logo" class="logo">
        </a>

        <ul class="nav-list">
            <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
            <li class="nav-item"><a href="peliculas.php" class="nav-link">Peliculas</a></li>
            <li class="nav-item"><a href="cines.php" class="nav-link">Cines</a></li>
        </ul>

        <button class="hamburger" aria-label="Menú navegación">&#9776;</button>
        
        <!-- Sección derecha con Mi cuenta y Carrito -->
        <div class="nav-right">
            <!-- Botón Mi cuenta -->
            <?php if (isset($_SESSION['usuario_nombre'])): ?>
                <a href="profile.php" class="account-link">
                    <i class='bx bx-user'></i>
                    <span>Mi cuenta</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="account-link">
                    <i class='bx bx-user'></i>
                    <span>Mi cuenta</span>
                </a>
            <?php endif; ?>

            <!-- Carrito -->
            <div class="cart-icon" onclick="window.location.href='carrito.php'">
                <i class='bx bx-cart'></i>
                <span class="cart-count" id="cart-count"><?php echo $items_carrito; ?></span>
            </div>
        </div>
    </nav>

    <!-- Overlay para cerrar menú -->
    <div class="menu-overlay"></div>

    <script>
        // JavaScript para el menú hamburguesa
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger');
            const navList = document.querySelector('.nav-list');
            const overlay = document.querySelector('.menu-overlay');
            const body = document.body;

            // Función para abrir menú
            function openMenu() {
                navList.classList.add('active');
                overlay.classList.add('active');
                body.style.overflow = 'hidden';
            }

            // Función para cerrar menú
            function closeMenu() {
                navList.classList.remove('active');
                overlay.classList.remove('active');
                body.style.overflow = '';
            }

            // Event listeners
            hamburger.addEventListener('click', openMenu);
            overlay.addEventListener('click', closeMenu);

            // Cerrar menú al hacer click en el botón X
            navList.addEventListener('click', function(e) {
                const rect = navList.getBoundingClientRect();
                const closeButtonArea = {
                    left: rect.right - 60,
                    right: rect.right - 20,
                    top: rect.top + 15,
                    bottom: rect.top + 55
                };

                if (e.clientX >= closeButtonArea.left && 
                    e.clientX <= closeButtonArea.right && 
                    e.clientY >= closeButtonArea.top && 
                    e.clientY <= closeButtonArea.bottom) {
                    closeMenu();
                }
            });

            // Cerrar menú al hacer click en un enlace
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', closeMenu);
            });

            // Cerrar menú con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMenu();
                }
            });
        });
    </script>
</body>
</html>