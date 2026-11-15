<?php
session_start();

$id_producto = $_POST['id'] ?? null;
$nueva_cantidad = (int)($_POST['cantidad'] ?? 1);

if ($id_producto && isset($_SESSION['carrito'])) {
    if ($nueva_cantidad <= 0) {
        // Si la cantidad es 0 o menos, eliminar el producto
        $_SESSION['carrito'] = array_filter($_SESSION['carrito'], function($item) use ($id_producto) {
            return $item['id'] != $id_producto;
        });
    } else {
        // Actualizar cantidad
        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id'] == $id_producto) {
                $item['cantidad'] = $nueva_cantidad;
                break;
            }
        }
    }
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
}

header('Location: carrito.php');
exit;
?>