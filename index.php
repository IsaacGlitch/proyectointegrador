<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 

// Incluir la conexión a la base de datos
require_once 'config/conexion.php';

try {
    // Obtener películas con imágenes de carrusel
    $sql_carrusel = "SELECT id, titulo, carrusel FROM peliculas WHERE carrusel IS NOT NULL AND carrusel != '' ORDER BY id DESC LIMIT 5";
    $stmt_carrusel = $pdo->prepare($sql_carrusel);
    $stmt_carrusel->execute();
    $peliculas_carrusel = $stmt_carrusel->fetchAll();
    
    // Obtener las últimas 5 películas agregadas al sistema
    $sql_peliculas = "SELECT id, titulo, imagen FROM peliculas ORDER BY id DESC LIMIT 5";
    $stmt_peliculas = $pdo->prepare($sql_peliculas);
    $stmt_peliculas->execute();
    $ultimas_peliculas = $stmt_peliculas->fetchAll();
    
} catch (Exception $e) {
    // En caso de error, usar arrays vacíos
    $peliculas_carrusel = [];
    $ultimas_peliculas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinepoint</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/bot.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>

    <!-- Incluir tu navegación aquí -->
    <?php include 'navbar.php'; ?>

    <!-- Carrusel de imágenes destacadas - Ahora dinámico -->
    <div class="carousel" id="slides">
        <?php if (!empty($peliculas_carrusel)): ?>
            <div class="slides">
                <?php foreach ($peliculas_carrusel as $index => $pelicula): ?>
                    <img src="<?php echo htmlspecialchars($pelicula['carrusel']); ?>" 
                         alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" 
                         <?php echo $index === 0 ? 'class="active"' : ''; ?>
                         onerror="this.style.display='none';" />
                <?php endforeach; ?>
            </div>
            <!-- Botones para navegar entre imágenes del carrusel -->
            <?php if (count($peliculas_carrusel) > 1): ?>
                <button class="nav-btn prev">&#10094;</button>
                <button class="nav-btn next">&#10095;</button>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-content">
                <p>Aún no hay imágenes de carrusel disponibles</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sección con fondo destacado y título -->
    <div class="zona-fondo">
        <div class="seccion-contenedor">
          <h2 class="seccion-titulo">Últimas películas en estreno</h2>
          <p class="seccion-subtitulo">
            Vive la mejor experiencia de películas en <b>Cinepoint</b>
          </p>
        </div>
    </div>

    <!-- Sección que muestra las últimas películas agregadas -->
    <section class="peliculas">
        <?php if (!empty($ultimas_peliculas)): ?>
            <?php foreach ($ultimas_peliculas as $pelicula): ?>
                <div class="pelicula" onclick="window.location.href='detalles.php?id=<?php echo $pelicula['id']; ?>'">
                    <img src="<?php echo htmlspecialchars($pelicula['imagen']); ?>" 
                         alt="Carátula de <?php echo htmlspecialchars($pelicula['titulo']); ?>"
                         onerror="this.src='img/default-movie.jpg';">
                    <p><?php echo htmlspecialchars($pelicula['titulo']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-content">
                <p>Aún no hay películas disponibles</p>
            </div>
        <?php endif; ?>
    </section>

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

    <!-- Chatbot flotante -->
    <div id="chatbot" style="display:none;">
        <!-- Botón para abrir/cerrar el chatbot -->
        <button id="chatbot-toggle">💬</button>

        <!-- Ventana del chatbot -->
        <div id="chatbot-window" style="display: none;">
            <div id="chatbot-header">
                <span>Chat Bot Cinepoint</span>
                <!-- Botón para cerrar el chatbot -->
                <button id="chatbot-close">&times;</button>
            </div>
            <!-- Área donde se mostrarán los mensajes -->
            <div id="chatbot-messages" style="overflow-y:auto; flex:1; padding:10px; background:#f4f4f4;"></div>
            <!-- Input para que el usuario escriba mensajes -->
            <input type="text" id="userInput" placeholder="Escribe tu mensaje..." style="border:none; border-top:1px solid #ccc; padding:10px; font-size:16px; outline:none;" />
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/carousel.js"></script>
    <script src="js/bot.js"></script>
</body>
</html>