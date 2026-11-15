<?php
session_start();

$id_producto = $_GET['id'] ?? null;

if ($id_producto && isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array_filter($_SESSION['carrito'], function($item) use ($id_producto) {
        return $item['id'] != $id_producto;
    });
    $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
}

header('Location: carrito.php');
exit;
?>