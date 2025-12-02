<?php
// Incluir la conexión a la base de datos
require_once 'config/conexion.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $id_pelicula = (int)$_POST['id_pelicula'];
        
        switch ($_POST['accion']) {
            case 'aumentar':
                // Verificar stock antes de aumentar
                try {
                    $stmt = $pdo->prepare("SELECT stock FROM peliculas WHERE id = ?");
                    $stmt->execute([$id_pelicula]);
                    $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($pelicula && $_SESSION['carrito'][$id_pelicula] < $pelicula['stock']) {
                        $_SESSION['carrito'][$id_pelicula]++;
                    }
                } catch (PDOException $e) {
                    // Error silencioso
                }
                break;
                
            case 'disminuir':
                if (isset($_SESSION['carrito'][$id_pelicula])) {
                    $_SESSION['carrito'][$id_pelicula]--;
                    if ($_SESSION['carrito'][$id_pelicula] <= 0) {
                        unset($_SESSION['carrito'][$id_pelicula]);
                    }
                }
                break;
                
            case 'eliminar':
                if (isset($_SESSION['carrito'][$id_pelicula])) {
                    unset($_SESSION['carrito'][$id_pelicula]);
                }
                break;
                
            case 'vaciar':
                $_SESSION['carrito'] = [];
                break;
        }
        
        // Redireccionar para evitar reenvío de formulario
        header('Location: carrito.php');
        exit;
    }
}

// Obtener películas del carrito
$peliculas_carrito = [];
$total = 0;
$items_carrito = 0;

if (!empty($_SESSION['carrito'])) {
    $ids = implode(',', array_keys($_SESSION['carrito']));
    try {
        $stmt = $pdo->query("SELECT * FROM peliculas WHERE id IN ($ids)");
        $peliculas_bd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($peliculas_bd as $pelicula) {
            $cantidad = $_SESSION['carrito'][$pelicula['id']];
            $subtotal = $pelicula['precio'] * $cantidad;
            
            $peliculas_carrito[] = [
                'pelicula' => $pelicula,
                'cantidad' => $cantidad,
                'subtotal' => $subtotal
            ];
            
            $total += $subtotal;
            $items_carrito += $cantidad;
        }
    } catch (PDOException $e) {
        echo "Error al obtener películas del carrito: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - Cinepoint</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/carrito.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>

    <?php include('navbar.php'); ?>

    <main>
        <div class="carrito-container">
            <?php if (empty($peliculas_carrito)): ?>
                <div class="carrito-vacio" style="flex: 1;">
                    <i class='bx bx-cart-x'></i>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Descubre nuestras increíbles películas y comienza a llenar tu carrito!</p>
                    <a href="peliculas.php" class="btn-seguir-comprando">
                        Ver Películas
                    </a>
                </div>
            <?php else: ?>
                <div class="carrito-main">
                    <div class="carrito-header">
                        <h1><i class='bx bx-cart'></i> Mi Carrito</h1>
                        <p class="items-count">(<?php echo $items_carrito; ?> unidades)</p>
                    </div>

                    <div class="tabla-header">
                        <div>PELICULA</div>
                        <div>PRECIO</div>
                        <div>CANTIDAD</div>
                        <div>TOTAL</div>
                        <div></div>
                    </div>

                    <div class="carrito-items">
                        <?php foreach ($peliculas_carrito as $item): ?>
                            <div class="carrito-item">
                                <div class="item-producto">
                                    <img src="<?php echo htmlspecialchars($item['pelicula']['imagen']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['pelicula']['titulo']); ?>" 
                                         class="item-imagen">
                                    <div class="item-info">
                                        <div class="item-titulo">
                                            <?php echo htmlspecialchars($item['pelicula']['titulo']); ?>
                                        </div>
                                        <div class="item-genero">
                                            <?php echo htmlspecialchars($item['pelicula']['genero']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="item-precio">
                                    S/ <?php echo number_format($item['pelicula']['precio'], 2); ?>
                                </div>
                                
                                <div class="cantidad-controles">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="accion" value="disminuir">
                                        <input type="hidden" name="id_pelicula" value="<?php echo $item['pelicula']['id']; ?>">
                                        <button type="submit" class="btn-cantidad">−</button>
                                    </form>
                                    
                                    <span class="cantidad-numero"><?php echo $item['cantidad']; ?></span>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="accion" value="aumentar">
                                        <input type="hidden" name="id_pelicula" value="<?php echo $item['pelicula']['id']; ?>">
                                        <button type="submit" class="btn-cantidad">+</button>
                                    </form>
                                </div>
                                
                                <div class="item-total">
                                    S/ <?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_pelicula" value="<?php echo $item['pelicula']['id']; ?>">
                                    <button type="submit" class="btn-eliminar" 
                                            onclick="return confirm('¿Estás seguro de eliminar esta película del carrito?')">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="carrito-sidebar">
                    <div class="resumen-card">
                        <div class="resumen-header">
                            <h3 class="resumen-titulo">Resumen del Pedido</h3>
                        </div>
                        
                        <div class="resumen-body">
                            <div class="resumen-linea">
                                <span>Subtotal (<?php echo $items_carrito; ?> artículos):</span>
                                <span>S/ <?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <div class="resumen-linea">
                                <span>Descuento:</span>
                                <span>S/ 0.00</span>
                            </div>
                            
                            <div class="resumen-total">
                                <span>Total:</span>
                                <span>S/ <?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <a href="checkout.php" class="btn-continuar">
                                <i class="bx bx-check-circle"></i> CONTINUAR
                            </a>
                            
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="accion" value="vaciar">
                                <button type="submit" class="btn-vaciar" 
                                        onclick="return confirm('¿Estás seguro de vaciar todo el carrito?')">
                                    <i class='bx bx-trash'></i> VACIAR CARRITO
                                </button>
                            </form>
                            
                            <a href="peliculas.php" class="btn-seguir-comprando">
                                <i class='bx bx-arrow-back'></i> Seguir Comprando
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Pie de página -->
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
        // Función para dropdown del usuario
        function toggleDropdown() {
            const dropdown = document.getElementById("dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener("click", function (event) {
            const icon = document.querySelector(".user-icon");
            const dropdown = document.getElementById("dropdown");

            if (dropdown && icon && !icon.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });

        // Animaciones suaves para los botones
        document.querySelectorAll('.btn-cantidad').forEach(btn => {
            btn.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Efecto de hover para los items del carrito
        document.querySelectorAll('.carrito-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#fafafa';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'white';
            });
        });
    </script>
</body>
</html>