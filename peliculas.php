<?php
// Incluir la conexión a la base de datos
require_once 'config/conexion.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Obtener todas las películas de la base de datos
try {
    $stmt = $pdo->query("SELECT * FROM peliculas ORDER BY creado DESC");
    $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener películas: " . $e->getMessage();
    $peliculas = [];
}

// Contar items en carrito
$items_carrito = array_sum($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Películas - Cinepoint</title>
    <link rel="stylesheet" href="css/peliculas.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>

    <?php include('navbar.php'); ?>

    <main>
        <h1>Películas</h1>

        <!-- Pestañas superiores -->
        <div class="tabs">
            <button class="active" onclick="mostrarTodasPeliculas()">En cartelera</button>
        </div>

        <!-- Contenedor principal: filtros + películas -->
        <div class="content">
            <!-- Filtros (columna izquierda) -->
            <div class="filters">
                <p class="titulo-filtro">
                    <span>Filtrar por:</span>
                </p>

                <div class="categorias">
                    <button class="filtro-titulo" onclick="toggleOpciones('genero-opciones')">
                        <span>Género</span>
                        <span class="plus">+</span>
                    </button>
                    <div id="genero-opciones" class="opciones oculto">
                        <p onclick="mostrarTodasPeliculas()">Todos</p>
                        <?php
                        // Obtener géneros únicos de la base de datos
                        try {
                            $stmt = $pdo->query("SELECT DISTINCT genero FROM peliculas WHERE genero IS NOT NULL ORDER BY genero");
                            $generos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($generos as $genero) {
                                echo "<p onclick=\"filtrarPorGenero('$genero')\">$genero</p>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error al cargar géneros</p>";
                        }
                        ?>
                    </div>

                    <button class="filtro-titulo" onclick="toggleOpciones('precio-opciones')">
                        <span>Precio</span>
                        <span class="plus">+</span>
                    </button>
                    <div id="precio-opciones" class="opciones oculto">
                        <p onclick="mostrarTodasPeliculas()">Todos</p>
                        <p onclick="filtrarPorPrecio(0, 30)">S/ 0 - S/ 30</p>
                        <p onclick="filtrarPorPrecio(30, 50)">S/ 30 - S/ 50</p>
                        <p onclick="filtrarPorPrecio(50, 100)">S/ 50+</p>
                    </div>
                </div>
            </div>

<!-- Listado de películas -->
<div class="movies-list" id="movies-container">
    <?php foreach ($peliculas as $pelicula): ?>
        <div class="movie-card" 
             data-genero="<?php echo htmlspecialchars($pelicula['genero']); ?>"
             data-precio="<?php echo $pelicula['precio']; ?>"
             onclick="window.location.href='detalles.php?id=<?php echo $pelicula['id']; ?>'">
            
            <div class="movie-poster-wrapper">
                <img src="<?php echo htmlspecialchars($pelicula['imagen']); ?>" 
                     alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" 
                     class="movie-poster">
                
                <!-- Botones que aparecen al hacer hover -->
                <div class="movie-actions">
                    <?php if ($pelicula['stock'] > 0): ?>
                        <button onclick="agregarAlCarrito(<?php echo $pelicula['id']; ?>, '<?php echo addslashes($pelicula['titulo']); ?>'); event.stopPropagation();" 
                                class="btn-action btn-agregar">
                            Comprar
                        </button>
                    <?php else: ?>
                        <span class="btn-action" style="background-color: #666; cursor: not-allowed;">
                            Agotado
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="movie-title">
                <?php echo htmlspecialchars($pelicula['titulo']); ?>
            </div>
            
            <div class="movie-info">
                <div><strong>Género:</strong> <?php echo htmlspecialchars($pelicula['genero']); ?></div>
                <div><strong>Duración:</strong> <?php echo htmlspecialchars($pelicula['duracion']); ?></div>
                <div class="precio">Precio: S/ <?php echo number_format($pelicula['precio'], 2); ?></div>
                <div class="stock <?php echo $pelicula['stock'] <= 0 ? 'agotado' : ''; ?>">
                    <?php 
                    if ($pelicula['stock'] <= 0) {
                        echo "¡Agotado!";
                    } else {
                        echo "Disponibles: " . $pelicula['stock'];
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
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
        // Función para mostrar/ocultar opciones de filtros
        function toggleOpciones(id) {
            const opciones = document.getElementById(id);
            const boton = opciones.previousElementSibling;
            const plus = boton.querySelector('.plus');
            
            if (opciones.classList.contains('oculto')) {
                opciones.classList.remove('oculto');
                plus.textContent = '-';
            } else {
                opciones.classList.add('oculto');
                plus.textContent = '+';
            }
        }

        // Función para mostrar todas las películas
        function mostrarTodasPeliculas() {
            const peliculas = document.querySelectorAll('.movie-card');
            peliculas.forEach(pelicula => {
                pelicula.style.display = 'flex';
            });
        }

        // Función para filtrar por género
        function filtrarPorGenero(genero) {
            const peliculas = document.querySelectorAll('.movie-card');
            peliculas.forEach(pelicula => {
                const generoCard = pelicula.getAttribute('data-genero');
                if (generoCard === genero) {
                    pelicula.style.display = 'flex';
                } else {
                    pelicula.style.display = 'none';
                }
            });
        }

        // Función para filtrar por precio
        function filtrarPorPrecio(min, max) {
            const peliculas = document.querySelectorAll('.movie-card');
            peliculas.forEach(pelicula => {
                const precio = parseFloat(pelicula.getAttribute('data-precio'));
                if (precio >= min && precio <= max) {
                    pelicula.style.display = 'flex';
                } else {
                    pelicula.style.display = 'none';
                }
            });
        }

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
                    document.getElementById('cart-count').textContent = data.total_items;
                    
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

        // Función para cerrar el modal de éxito
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

        // Función para dropdown del usuario
        function toggleDropdown() {
            const dropdown = document.getElementById("dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        document.addEventListener("click", function (event) {
            const icon = document.querySelector(".user-icon");
            const dropdown = document.getElementById("dropdown");

            if (dropdown && icon && !icon.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });

        // Agregar efectos de hover a las tarjetas de películas
        document.querySelectorAll('.movie-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>