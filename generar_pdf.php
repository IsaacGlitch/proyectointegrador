<?php
// Incluir la conexión a la base de datos
require_once 'config/conexion.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Configurar zona horaria de Perú (UTC-5)
date_default_timezone_set('America/Lima');

// Verificar si hay datos de compra en la sesión
if (!isset($_SESSION['compra_datos'])) {
    header('Location: index.php');
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos de la compra
$compra = $_SESSION['compra_datos'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta - #<?php echo str_pad($compra['numero_orden'], 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
        }
        
        .boleta {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .titulo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info div {
            width: 48%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .precio {
            text-align: right;
        }
        
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        .botones {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            background: #d9000d;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #b8000a;
        }
    </style>
</head>
<body>
    <div class="botones no-print">
        <button class="btn" onclick="window.print()">Imprimir Boleta</button>
        <a href="index.php" class="btn">Volver al Inicio</a>
    </div>

    <div class="boleta">
        <div class="header">
            <div class="titulo">CINEPOINT</div>
            <div>BOLETA DE VENTA ELECTRÓNICA</div>
            <div><strong>N° B001-<?php echo str_pad($compra['numero_orden'], 6, '0', STR_PAD_LEFT); ?></strong></div>
        </div>

        <div class="info">
            <div>
                <strong>Cliente:</strong><br>
                <?php echo htmlspecialchars($compra['usuario']['nombre'] . ' ' . $compra['usuario']['apellidos']); ?><br>
                <?php echo htmlspecialchars($compra['usuario']['email']); ?>
            </div>
            <div>
                <strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
                <strong>Forma de Pago:</strong> Tarjeta<br>
                <strong>Estado:</strong> Pagado
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Descripción</th>
                    <th>Cant.</th>
                    <th>P. Unit.</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $item_num = 1;
                foreach ($compra['peliculas'] as $item): 
                    $pelicula = $item['pelicula'];
                    $cantidad = $item['cantidad'];
                    $subtotal = $item['subtotal'];
                ?>
                <tr>
                    <td style="text-align: center;"><?php echo str_pad($item_num, 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($pelicula['titulo']); ?></td>
                    <td style="text-align: center;"><?php echo $cantidad; ?></td>
                    <td class="precio">S/ <?php echo number_format($pelicula['precio'], 2); ?></td>
                    <td class="precio">S/ <?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php 
                $item_num++;
                endforeach; 
                ?>
            </tbody>
        </table>

        <div class="total">
            <div style="border-top: 1px solid #000; padding-top: 5px; margin-top: 5px;">
                <strong>TOTAL: S/ <?php echo number_format($compra['total'], 2); ?></strong>
            </div>
        </div>

        <div class="footer">
            <div style="margin-top: 30px;">
                <strong>Total de artículos:</strong> <?php echo $compra['items_total']; ?><br>
                Documento generado el <?php echo date('d/m/Y H:i:s'); ?>
            </div>
            <div style="margin-top: 20px;">
                ¡Gracias por su compra en Cinepoint!<br>
            </div>
        </div>
    </div>

    <script>
        // Auto-imprimir si se pasa el parámetro print=1
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                window.print();
            }
        }
    </script>
</body>
</html>