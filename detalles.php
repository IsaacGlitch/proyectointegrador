<?php
// IMPORTANTE: Iniciar la sesión ANTES de cualquier otra cosa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once 'config/conexion.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar si se ha pasado un ID de película
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: peliculas.php');
    exit();
}

$id_pelicula = $_GET['id'];

// Obtener los detalles de la película
try {
    $stmt = $pdo->prepare("SELECT * FROM peliculas WHERE id = ?");
    $stmt->execute([$id_pelicula]);
    $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pelicula) {
        header('Location: peliculas.php');
        exit();
    }
} catch (PDOException $e) {
    echo "Error al obtener la película: " . $e->getMessage();
    exit();
}

// Contar items en carrito
$items_carrito = array_sum($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pelicula['titulo']); ?> - Cinepoint</title>
    <link rel="stylesheet" href="css/detalles.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>
    <?php include('navbar.php'); ?>

    <main>
        <div class="detalles-container">
            <!-- Sección del poster -->
            <div class="poster-section">
                <img src="<?php echo htmlspecialchars($pelicula['imagen']); ?>" 
                     alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" 
                     class="poster-large">
                
                <div class="stock-badge <?php echo $pelicula['stock'] > 0 ? 'stock-disponible' : 'stock-agotado'; ?>">
                    <?php echo $pelicula['stock'] > 0 ? "Disponible ({$pelicula['stock']} entradas)" : "¡Agotado!"; ?>
                </div>
            </div>

            <!-- Sección de información -->
            <div class="info-section">
                <h1 class="titulo-pelicula"><?php echo htmlspecialchars($pelicula['titulo']); ?></h1>

                <div class="precio-destacado">
                    Precio: S/ <?php echo number_format($pelicula['precio'], 2); ?>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Género</div>
                        <div class="info-value"><?php echo htmlspecialchars($pelicula['genero']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Duración</div>
                        <div class="info-value"><?php echo htmlspecialchars($pelicula['duracion']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Precio</div>
                        <div class="info-value">S/ <?php echo number_format($pelicula['precio'], 2); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Entradas disponibles</div>
                        <div class="info-value"><?php echo $pelicula['stock']; ?></div>
                    </div>
                </div>

                <?php if (!empty($pelicula['descripcion'])): ?>
                <div class="descripcion">
                    <h3>Sinopsis</h3>
                    <p><?php echo nl2br(htmlspecialchars($pelicula['descripcion'])); ?></p>
                </div>
                <?php endif; ?>

                <div class="acciones">
                    <?php if ($pelicula['stock'] > 0): ?>
                        <button onclick="agregarAlCarrito(<?php echo $pelicula['id']; ?>, '<?php echo addslashes($pelicula['titulo']); ?>')" class="btn btn-comprar">
                            <i class='bx bx-cart'></i>
                            Comprar
                        </button>
                    <?php else: ?>
                        <button class="btn btn-comprar" disabled>
                            <i class='bx bx-x'></i>
                            Agotado
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($pelicula['trailer'])): ?>
                        <button class="btn btn-trailer" onclick="abrirTrailer()">
                            <i class='bx bx-play'></i>
                            Ver Trailer
                        </button>
                    <?php endif; ?>

                    <a href="peliculas.php" class="btn btn-volver">
                        <i class='bx bx-arrow-back'></i>
                        Volver a Películas
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de confirmación de éxito -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-icon success">
                ✓
            </div>
            <div class="modal-title">¡Película agregada correctamente!</div>
            <div class="modal-text" id="modalText">
                La película ha sido agregada a tu carrito.
            </div>
            <div class="modal-buttons">
                <button class="modal-btn btn-continue" onclick="cerrarModal()">
                    Seguir comprando
                </button>
                <a href="carrito.php" class="modal-btn btn-cart">
                    Ir al carrito
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de sin stock -->
    <div class="modal-overlay" id="modalSinStock">
        <div class="modal-content">
            <div class="modal-icon error">
                ✕
            </div>
            <div class="modal-title">¡Sin stock disponible!</div>
            <div class="modal-text" id="modalSinStockText">
                Lo sentimos, no hay más stock disponible para esta película.
            </div>
            <div class="modal-buttons">
                <button class="modal-btn btn-continue" onclick="cerrarModalSinStock()">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para trailer -->
    <?php if (!empty($pelicula['trailer'])): ?>
    <div id="trailerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarTrailer()">&times;</span>
            <div class="trailer-container">
                <iframe id="trailerFrame" class="trailer-video" src="" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
        // Función para agregar película al carrito
        function agregarAlCarrito(idPelicula, tituloPelicula) {
            fetch('agregar_carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_pelicula=' + idPelicula
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador del carrito
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.total_items;
                    }
                    
                    // Mostrar modal de confirmación de éxito
                    document.getElementById('modalText').textContent = `"${tituloPelicula}" ha sido agregada a tu carrito.`;
                    document.getElementById('modalOverlay').style.display = 'flex';
                } else {
                    // Mostrar modal de sin stock en lugar de alert
                    let mensaje = data.message;
                    if (mensaje.includes('stock')) {
                        mensaje = `Lo sentimos, "${tituloPelicula}" ya no tiene stock disponible.`;
                    }
                    document.getElementById('modalSinStockText').textContent = mensaje;
                    document.getElementById('modalSinStock').style.display = 'flex';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Mostrar modal de error en lugar de alert
                document.getElementById('modalSinStockText').textContent = 'Ocurrió un error al procesar tu solicitud. Intenta nuevamente.';
                document.getElementById('modalSinStock').style.display = 'flex';
            });
        }

        // Función para cerrar el modal de confirmación de éxito
        function cerrarModal() {
            document.getElementById('modalOverlay').style.display = 'none';
        }

        // Función para cerrar el modal de sin stock
        function cerrarModalSinStock() {
            document.getElementById('modalSinStock').style.display = 'none';
        }

        // Cerrar modales al hacer clic fuera de ellos
        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        document.getElementById('modalSinStock').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalSinStock();
            }
        });

        // Función para abrir el modal del trailer
        function abrirTrailer() {
            const modal = document.getElementById('trailerModal');
            const iframe = document.getElementById('trailerFrame');
            const trailerUrl = '<?php echo addslashes($pelicula['trailer']); ?>';
            
            // Convertir URL de YouTube a embed si es necesario
            let embedUrl = trailerUrl;
            if (trailerUrl.includes('youtube.com/watch?v=')) {
                const videoId = trailerUrl.split('v=')[1].split('&')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            } else if (trailerUrl.includes('youtu.be/')) {
                const videoId = trailerUrl.split('youtu.be/')[1].split('?')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            }
            
            iframe.src = embedUrl;
            modal.style.display = 'block';
        }

        // Función para cerrar el modal del trailer
        function cerrarTrailer() {
            const modal = document.getElementById('trailerModal');
            const iframe = document.getElementById('trailerFrame');
            
            modal.style.display = 'none';
            iframe.src = '';
        }

        // Cerrar modal del trailer al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('trailerModal');
            if (event.target == modal) {
                cerrarTrailer();
            }
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarTrailer();
            }
        });

    </script>
</body>
</html>