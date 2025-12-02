<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 

// Incluir la conexión a la base de datos
require_once 'config/conexion.php';

// Verificar si se recibió el ID del cine
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: cines.php');
    exit();
}

$cine_id = (int)$_GET['id'];

// Consultar los detalles del cine específico
$sql = "SELECT * FROM cines WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $cine_id, PDO::PARAM_INT);
$stmt->execute();
$cine = $stmt->fetch();

// Si no se encuentra el cine, redirigir
if (!$cine) {
    header('Location: cines.php');
    exit();
}

// Consultar otros cines de la misma ciudad (máximo 3)
$sql_relacionados = "SELECT * FROM cines WHERE ciudad = :ciudad AND id != :id ORDER BY creado DESC LIMIT 3";
$stmt_relacionados = $pdo->prepare($sql_relacionados);
$stmt_relacionados->bindParam(':ciudad', $cine['ciudad'], PDO::PARAM_STR);
$stmt_relacionados->bindParam(':id', $cine_id, PDO::PARAM_INT);
$stmt_relacionados->execute();
$cines_relacionados = $stmt_relacionados->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cine['nombre']); ?> - Cinepoint</title>
    <link rel="stylesheet" href="css/detalle.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <meta name="description" content="Información completa sobre <?php echo htmlspecialchars($cine['nombre']); ?> - Dirección, horarios, contacto y compra de entradas.">
</head>
<body>
    
    <?php include('navbar.php'); ?>

    <main>
        <!-- Información principal del cine -->
        <section class="cine-hero">
            <div class="hero-content">
                <div class="hero-image">
                    <img src="<?php echo htmlspecialchars($cine['imagen']); ?>" 
                         alt="<?php echo htmlspecialchars($cine['nombre']); ?>" 
                         onerror="this.src='img/cine-default.jpg'">
                </div>
                <div class="hero-info">
                    <h1><?php echo htmlspecialchars($cine['nombre']); ?></h1>
                    <div class="location-badge">
                        <span class="location-icon">📍</span>
                        <?php echo htmlspecialchars($cine['ciudad']); ?>
                    </div>
                    <p class="hero-description">
                        Disfruta de los mejores estrenos en nuestro cine ubicado en <?php echo htmlspecialchars($cine['ciudad']); ?>. 
                        Tecnología de última generación para una experiencia cinematográfica única.
                    </p>
                </div>
            </div>
        </section>

        <!-- Información del cine -->
        <section class="cine-details">
            <div class="details-container">
                <div class="details-grid">
                    
                    <!-- Información de contacto -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>📞 Contacto</h2>
                        </div>
                        <div class="card-content">
                            <div class="info-item">
                                <strong>Teléfono:</strong>
                                <a href="tel:<?php echo htmlspecialchars($cine['telefono']); ?>" class="phone-link">
                                    <?php echo htmlspecialchars($cine['telefono']); ?>
                                </a>
                            </div>
                            <div class="info-item">
                                <strong>Dirección:</strong>
                                <address><?php echo htmlspecialchars($cine['direccion']); ?></address>
                            </div>
                        </div>
                    </div>

                    <!-- Horarios -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>🕒 Horarios</h2>
                        </div>
                        <div class="card-content">
                            <div class="schedule-info">
                                <div class="schedule-item">
                                    <span class="schedule-day">Todos los días:</span>
                                    <span class="schedule-time"><?php echo htmlspecialchars($cine['horario_atencion']); ?></span>
                                </div>
                                <div class="schedule-note">
                                    <small>* Horarios pueden variar en días festivos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>🎬 Servicios</h2>
                        </div>
                        <div class="card-content">
                            <div class="services-grid">
                                <div class="service-item">
                                    <span class="service-icon">🎬</span>
                                    <span>Proyección Digital</span>
                                </div>
                                <div class="service-item">
                                    <span class="service-icon">🔊</span>
                                    <span>Sonido Dolby</span>
                                </div>
                                <div class="service-item">
                                    <span class="service-icon">🎫</span>
                                    <span>Venta de Entradas</span>
                                </div>
                                <div class="service-item">
                                    <span class="service-icon">♿</span>
                                    <span>Accesibilidad</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Botones de acción -->
        <section class="action-buttons">
            <div class="buttons-container">
                <button class="btn-primary" onclick="window.location.href='peliculas.php'">
                    🎫 Comprar Entradas
                </button>
                <button class="btn-secondary" onclick="window.location.href='tel:<?php echo htmlspecialchars($cine['telefono']); ?>'">
                    📞 Llamar
                </button>
                <button class="btn-secondary" onclick="window.location.href='cines.php'">
                    ← Volver a Cines
                </button>
            </div>
        </section>
    </main>

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
        // Funcionalidad del menú hamburguesa
        document.querySelector('.hamburger').addEventListener('click', function() {
            const navList = document.querySelector('.nav-list');
            navList.classList.toggle('show');
        });
    </script>

</body>
</html>