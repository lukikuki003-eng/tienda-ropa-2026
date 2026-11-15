<?php
session_start();
require 'config.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Obtener datos del producto
$id_producto = $_GET['id'] ?? null;

if ($id_producto) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id_producto]);
    $producto = $stmt->fetch();
    
    if ($producto) {
        // Verificar si ya está en el carrito
        $encontrado = false;
        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id'] == $id_producto) {
                $item['cantidad']++;
                $encontrado = true;
                break;
            }
        }
        
        // Si no estaba, agregarlo
        if (!$encontrado) {
            $_SESSION['carrito'][] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'imagen' => $producto['imagen'],
                'cantidad' => 1
            ];
        }
    }
}

// Redirigir de vuelta
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'catalogo.php'));
exit;
?>