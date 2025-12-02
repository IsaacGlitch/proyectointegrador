<?php
// Incluir la conexión a la base de datos
require_once 'config/conexion.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos del usuario
$usuario_data = [];
try {
    $stmt = $pdo->prepare("SELECT nombre, apellidos, email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener datos del usuario: " . $e->getMessage();
    exit;
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
        exit;
    }
}

// Procesar el pago
$pago_exitoso = false;
$numero_orden = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_pago'])) {
    // Validar datos de la tarjeta (simulado)
    $nombre_tarjeta = trim($_POST['nombre_tarjeta']);
    $numero_tarjeta = trim($_POST['numero_tarjeta']);
    $fecha_expiracion = trim($_POST['fecha_expiracion']);
    $cvv = trim($_POST['cvv']);
    
    // Limpiar número de tarjeta (quitar espacios)
    $numero_tarjeta_limpio = str_replace(' ', '', $numero_tarjeta);
    
    // Validar fecha de expiración
    $fecha_valida = false;
    if (preg_match('/^(\d{2})\/(\d{2})$/', $fecha_expiracion, $matches)) {
        $mes = (int)$matches[1];
        $año = (int)$matches[2] + 2000; // Convertir YY a YYYY
        $fecha_actual = new DateTime();
        $fecha_tarjeta = new DateTime();
        $fecha_tarjeta->setDate($año, $mes, 1);
        $fecha_tarjeta->modify('last day of this month'); // Último día del mes
        
        if ($mes >= 1 && $mes <= 12 && $fecha_tarjeta >= $fecha_actual) {
            $fecha_valida = true;
        }
    }
    
    // Validaciones específicas
    $errores = [];
    
    if (empty($nombre_tarjeta)) {
        $errores[] = "El nombre en la tarjeta es requerido";
    }
    
    if (strlen($numero_tarjeta_limpio) !== 16) {
        $errores[] = "El número de tarjeta debe tener exactamente 16 dígitos";
    } elseif (!ctype_digit($numero_tarjeta_limpio)) {
        $errores[] = "El número de tarjeta solo debe contener números";
    }
    
    if (empty($fecha_expiracion)) {
        $errores[] = "La fecha de expiración es requerida";
    } elseif (!preg_match('/^(\d{2})\/(\d{2})$/', $fecha_expiracion)) {
        $errores[] = "La fecha de expiración debe tener el formato MM/AA";
    } elseif (!$fecha_valida) {
        preg_match('/^(\d{2})\/(\d{2})$/', $fecha_expiracion, $matches);
        $mes = (int)$matches[1];
        if ($mes < 1 || $mes > 12) {
            $errores[] = "El mes debe estar entre 01 y 12";
        } else {
            $errores[] = "La tarjeta está vencida o la fecha es inválida";
        }
    }
    
    if (strlen($cvv) !== 3) {
        $errores[] = "El CVV debe tener exactamente 3 dígitos";
    } elseif (!ctype_digit($cvv)) {
        $errores[] = "El CVV solo debe contener números";
    }
    
    // Si no hay errores, procesar el pago
    if (empty($errores)) {
        try {
            // Simular procesamiento de pago (siempre exitoso para la demo)
            // Generar número de orden aleatorio
            $numero_orden = rand(100000, 999999);
            
            // Actualizar stock de las películas vendidas
            foreach ($peliculas_carrito as $item) {
                $stmt = $pdo->prepare("UPDATE peliculas SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['cantidad'], $item['pelicula']['id']]);
            }
            
            // Guardar datos de la compra en sesión para el PDF
            $_SESSION['compra_datos'] = [
                'numero_orden' => $numero_orden,
                'fecha' => date('Y-m-d H:i:s'),
                'usuario' => $usuario_data,
                'peliculas' => $peliculas_carrito,
                'total' => $total,
                'items_total' => $items_carrito
            ];
            
            // Limpiar el carrito
            $_SESSION['carrito'] = [];
            
            $pago_exitoso = true;
            
        } catch (PDOException $e) {
            $errores[] = "Error al procesar el pago: " . $e->getMessage();
        }
    } else {
        // Mostrar todos los errores
        $error_pago = implode('<br>', $errores);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Cinepoint</title>
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>

    <?php include('navbar.php'); ?>

    <main>
        <div class="checkout-container">
            <?php if ($pago_exitoso): ?>
                <div class="pago-exitoso" style="flex: 1;">
                    <i class='bx bx-check-circle'></i>
                    <h2>¡Pago Exitoso!</h2>
                    <p>Tu compra se ha procesado correctamente.</p>
                    <p><strong>Número de orden:</strong> #<?php echo str_pad($numero_orden, 6, '0', STR_PAD_LEFT); ?></p>
                    <p><strong>Total pagado:</strong> S/ <?php echo number_format($total, 2); ?></p>
                    
                    <div class="botones-exitoso">
                        <a href="generar_pdf.php" class="btn-accion btn-pdf" target="_blank">
                            <i class='bx bx-download'></i> Imprimir Boleta
                        </a>
                        <a href="index.php" class="btn-accion btn-inicio">
                            <i class='bx bx-home'></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="checkout-main">
                    <!-- Información del Usuario -->
                    <div class="checkout-card">
                        <div class="card-header">
                            <h2><i class='bx bx-user'></i> Información de Facturación</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nombre:</label>
                                    <input type="text" value="<?php echo htmlspecialchars($usuario_data['nombre']); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Apellidos:</label>
                                    <input type="text" value="<?php echo htmlspecialchars($usuario_data['apellidos']); ?>" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Correo Electrónico:</label>
                                <input type="email" value="<?php echo htmlspecialchars($usuario_data['email']); ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div class="checkout-card">
                        <div class="card-header">
                            <h2><i class='bx bx-credit-card'></i> Información de Pago</h2>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error_pago)): ?>
                                <div class="alert alert-error">
                                    <strong>Error:</strong>
                                    <?php echo $error_pago; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="formPago">
                                <div class="form-group">
                                    <label>Nombre en la Tarjeta:</label>
                                    <input type="text" name="nombre_tarjeta" required placeholder="Como aparece en la tarjeta"
                                           value="<?php echo isset($_POST['nombre_tarjeta']) ? htmlspecialchars($_POST['nombre_tarjeta']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label>Número de Tarjeta:</label>
                                    <input type="text" name="numero_tarjeta" required placeholder="1234 5678 9012 3456" 
                                           maxlength="19" id="numeroTarjeta"
                                           value="<?php echo isset($_POST['numero_tarjeta']) ? htmlspecialchars($_POST['numero_tarjeta']) : ''; ?>">
                                    <div class="payment-icons">
                                        <img src="img/visa.png" alt="Visa" class="payment-icon-img">
                                        <img src="img/mastercard.png" alt="Mastercard" class="payment-icon-img">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Fecha de Expiración:</label>
                                        <input type="text" name="fecha_expiracion" required placeholder="MM/AA" 
                                               maxlength="5" id="fechaExpiracion"
                                               value="<?php echo isset($_POST['fecha_expiracion']) ? htmlspecialchars($_POST['fecha_expiracion']) : ''; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>CVV:</label>
                                        <input type="text" name="cvv" required placeholder="123" maxlength="3" id="cvv"
                                               value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>">
                                    </div>
                                </div>

                                <input type="hidden" name="procesar_pago" value="1">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <div class="resumen-card">
                        <div class="resumen-header">
                            <h3 class="resumen-titulo">Resumen del Pedido</h3>
                        </div>
                        
                        <div class="resumen-body">
                            <!-- Items del carrito -->
                            <?php foreach ($peliculas_carrito as $item): ?>
                                <div class="resumen-item">
                                    <div class="item-resumen-info">
                                        <div class="item-resumen-titulo">
                                            <?php echo htmlspecialchars($item['pelicula']['titulo']); ?>
                                        </div>
                                        <div class="item-resumen-cantidad">
                                            Cantidad: <?php echo $item['cantidad']; ?>
                                        </div>
                                    </div>
                                    <div class="item-resumen-precio">
                                        S/ <?php echo number_format($item['subtotal'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div style="margin-top: 20px;">
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
                            </div>
                            
                            <button type="submit" form="formPago" class="btn-pagar">
                                <i class='bx bx-credit-card'></i> PAGAR AHORA
                            </button>
                            
                            <a href="carrito.php" class="btn-volver">
                                <i class='bx bx-arrow-back'></i> Volver al Carrito
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

        // Formatear número de tarjeta
        document.getElementById('numeroTarjeta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            
            if (formattedValue.length <= 19) {
                e.target.value = formattedValue;
            }
        });

        // Formatear fecha de expiración
        document.getElementById('fechaExpiracion').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
            
            // Validar mes en tiempo real
            if (value.length >= 2) {
                const mes = parseInt(value.substring(0, 2));
                if (mes > 12 || mes < 1) {
                    e.target.style.borderColor = '#dc3545';
                } else {
                    e.target.style.borderColor = '#ddd';
                }
            }
        });

        // Solo números para CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Validación del formulario (solo para experiencia de usuario, el servidor hace la validación real)
        document.getElementById('formPago').addEventListener('submit', function(e) {
            // Remover validaciones JavaScript ya que el servidor maneja todo
            // Solo mostrar indicador de carga
            const submitBtn = document.querySelector('.btn-pagar');
            submitBtn.textContent = 'Procesando...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>