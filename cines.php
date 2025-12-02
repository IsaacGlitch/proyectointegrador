<?php 
// Incluir la conexión a la base de datos
require_once 'config/conexion.php';

// Consultar las ciudades disponibles
try {
    $ciudadesSql = "SELECT DISTINCT ciudad FROM cines ORDER BY ciudad ASC";
    $ciudadesStmt = $pdo->query($ciudadesSql);
    $ciudades = $ciudadesStmt->fetchAll();
} catch (PDOException $e) {
    echo "Error al obtener ciudades: " . $e->getMessage();
    $ciudades = [];
}

// Filtrar cines por ciudad si se seleccionó una
$ciudadFiltro = isset($_GET['ciudad']) ? $_GET['ciudad'] : '';

try {
    $sql = "SELECT * FROM cines";
    if ($ciudadFiltro) {
        $sql .= " WHERE ciudad = :ciudad";
    }
    $sql .= " ORDER BY creado DESC";  

    $stmt = $pdo->prepare($sql);

    if ($ciudadFiltro) {
        $stmt->bindParam(':ciudad', $ciudadFiltro, PDO::PARAM_STR);
    }

    $stmt->execute();
    $cines = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error al obtener cines: " . $e->getMessage();
    $cines = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cines - Cinepoint</title>
    <link rel="stylesheet" href="css/cines.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>

    <?php include('navbar.php'); ?>

    <main>
        <h1>Cines</h1>

        <!-- Pestañas superiores -->
        <div class="tabs">
            <button class="active" onclick="mostrarTodosCines()">Todas las sucursales</button>
        </div>

        <!-- Contenedor principal: filtros + cines -->
        <div class="content">
            <!-- Filtros (columna izquierda) - Mismo estilo que peliculas.php -->
            <div class="filters">
                <p class="titulo-filtro">
                    <span>Filtrar por:</span>
                </p>

                <div class="categorias">
                    <button class="filtro-titulo" onclick="toggleOpciones('ciudad-opciones')">
                        <span>Ciudad</span>
                        <span class="plus">+</span>
                    </button>
                    <div id="ciudad-opciones" class="opciones oculto">
                        <p onclick="mostrarTodosCines()">Todos</p>
                        <?php
                        foreach ($ciudades as $ciudad) {
                            echo "<p onclick=\"filtrarPorCiudad('" . htmlspecialchars($ciudad['ciudad']) . "')\">" . htmlspecialchars($ciudad['ciudad']) . "</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Sección principal que muestra la lista de cines disponibles -->
            <div class="cines-list" id="cines-container">
                <?php 
                if (count($cines) > 0): 
                    foreach ($cines as $cine): ?>
                        <div class="card" data-ciudad="<?php echo htmlspecialchars($cine['ciudad']); ?>">
                            <!-- Imagen del cine -->
                            <img src="<?php echo htmlspecialchars($cine['imagen']); ?>" 
                                 alt="Cinepoint <?php echo htmlspecialchars($cine['ciudad']); ?>" />
                            
                            <!-- Botones que aparecen al hacer hover -->
                            <div class="cinema-actions">
                                <a href="detalle.php?id=<?php echo $cine['id']; ?>" class="btn-action btn-info-action">
                                    Ver Detalles
                                </a>
                            </div>

                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($cine['nombre']); ?></h3>
                                <div class="location"><?php echo htmlspecialchars($cine['direccion']); ?></div>
                                <div class="ciudad-info">
                                    <strong>Ciudad:</strong> <?php echo htmlspecialchars($cine['ciudad']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; 
                else: ?>
                    <p>No hay cines registrados en este momento.</p>
                <?php endif; ?>
            </div>
        </div>
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

        // Función para mostrar todos los cines
        function mostrarTodosCines() {
            const cines = document.querySelectorAll('.card');
            cines.forEach(cine => {
                cine.style.display = 'flex';
            });
        }

        // Función para filtrar por ciudad
        function filtrarPorCiudad(ciudad) {
            const cines = document.querySelectorAll('.card');
            cines.forEach(cine => {
                const ciudadCard = cine.getAttribute('data-ciudad');
                if (ciudadCard === ciudad) {
                    cine.style.display = 'flex';
                } else {
                    cine.style.display = 'none';
                }
            });
        }

        // Agregar efectos de hover a las tarjetas de cines
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>