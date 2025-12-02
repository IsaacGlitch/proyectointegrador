<?php
session_start();
header('Content-Type: application/json');

// Verificar que se recibió el ID de la película
if (!isset($_POST['id_pelicula']) || !is_numeric($_POST['id_pelicula'])) {
    echo json_encode(['success' => false, 'message' => 'ID de película inválido']);
    exit;
}

$id_pelicula = (int)$_POST['id_pelicula'];

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar que la película existe y tiene stock
require_once 'config/conexion.php';

try {
    $stmt = $pdo->prepare("SELECT id, titulo, precio, stock FROM peliculas WHERE id = ? AND stock > 0");
    $stmt->execute([$id_pelicula]);
    $pelicula = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pelicula) {
        echo json_encode(['success' => false, 'message' => 'Película no encontrada o sin stock']);
        exit;
    }
    
    // Verificar si ya está en el carrito
    if (isset($_SESSION['carrito'][$id_pelicula])) {
        // Si ya está, incrementar cantidad (máximo según stock disponible)
        if ($_SESSION['carrito'][$id_pelicula] < $pelicula['stock']) {
            $_SESSION['carrito'][$id_pelicula]++;
        } else {
            echo json_encode(['success' => false, 'message' => 'No hay más stock disponible']);
            exit;
        }
    } else {
        // Si no está, agregarlo con cantidad 1
        $_SESSION['carrito'][$id_pelicula] = 1;
    }
    
    // Calcular total de items en carrito
    $total_items = array_sum($_SESSION['carrito']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Película agregada al carrito',
        'total_items' => $total_items
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>