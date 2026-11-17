<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

try {
    // 🔄 INICIAR TRANSACCIÓN
    $pdo->beginTransaction();

    $tipo_cliente = $_POST['tipo_cliente'];
    $id_cliente = null;

    if ($tipo_cliente === 'existente') {
        $id_cliente = $_POST['id_cliente_existente'];
    } else {
        $nombre_completo = $_POST['nombre_completo'];
        $partes = explode(' ', $nombre_completo, 2);
        $nombre = $partes[0];
        $apellido = $partes[1] ?? '';
        $email = $_POST['email'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $ciudad = $_POST['ciudad'];

        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $cliente_existente = $stmt->fetch();

        if ($cliente_existente) {
            $id_cliente = $cliente_existente['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, direccion, ciudad) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $telefono, $direccion, $ciudad]);
            $id_cliente = $pdo->lastInsertId();
        }
    }

    // 🔍 VERIFICAR STOCK DISPONIBLE ANTES DE VENDER
    $productos_sin_stock = [];
    foreach ($_SESSION['carrito'] as $item) {
        $stmt = $pdo->prepare("SELECT nombre, stock FROM productos WHERE id = ?");
        $stmt->execute([$item['id']]);
        $producto_actual = $stmt->fetch();

        if (!$producto_actual || $producto_actual['stock'] < $item['cantidad']) {
            $productos_sin_stock[] = $producto_actual['nombre'] . " (solo quedan " . ($producto_actual['stock'] ?? 0) . ")";
        }
    }

    // ❌ Si algún producto no tiene stock suficiente, cancelar todo
    if (!empty($productos_sin_stock)) {
        $pdo->rollBack();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Error de Stock - Elegance Store</title>
            <link rel="stylesheet" href="assets/style.css">
            <style>
                .error-container {
                    max-width: 600px;
                    margin: 5rem auto;
                    padding: 3rem;
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .error-icono { font-size: 5rem; margin-bottom: 1rem; }
                .error-container h1 { color: #e74c3c; margin-bottom: 1rem; }
                .error-container ul { text-align: left; color: #666; margin: 1rem 0; }
                .btn-volver {
                    display: inline-block;
                    background: #1a1a1a;
                    color: #d4af37;
                    padding: 1rem 2rem;
                    border-radius: 50px;
                    text-decoration: none;
                    font-weight: bold;
                    margin-top: 1rem;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icono">❌</div>
                <h1>No se pudo completar la compra</h1>
                <p>Los siguientes productos no tienen stock suficiente:</p>
                <ul>
                    <?php foreach ($productos_sin_stock as $p): ?>
                        <li>• <?= htmlspecialchars($p) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Ajusta tu carrito y vuelve a intentarlo.</p>
                <a href="carrito.php" class="btn-volver">Volver al Carrito</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    // 💰 REGISTRAR VENTAS Y DESCONTAR STOCK
    $total_general = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total_producto = $item['precio'] * $item['cantidad'];
        $total_general += $total_producto;

        // 1️⃣ Registrar la venta
        $stmt = $pdo->prepare("INSERT INTO ventas (id_cliente, id_producto, cantidad, total) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_cliente, $item['id'], $item['cantidad'], $total_producto]);

        // 2️⃣ 🔥 DESCONTAR EL STOCK (esto es lo que faltaba)
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['cantidad'], $item['id']]);
    }

    // ✅ CONFIRMAR TRANSACCIÓN
    $pdo->commit();

    // Vaciar carrito
    $_SESSION['carrito'] = [];
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>¡Compra Exitosa! - Elegance Store</title>
        <link rel="stylesheet" href="assets/style.css">
        <style>
            .exito-container {
                max-width: 600px;
                margin: 5rem auto;
                padding: 3rem;
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                text-align: center;
            }
            .exito-icono { font-size: 5rem; margin-bottom: 1rem; }
            .exito-container h1 { color: #27ae60; margin-bottom: 1rem; }
            .exito-container p { color: #666; margin-bottom: 2rem; }
            .btn-volver {
                display: inline-block;
                background: #1a1a1a;
                color: #d4af37;
                padding: 1rem 2rem;
                border-radius: 50px;
                text-decoration: none;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="exito-container">
            <div class="exito-icono">✅</div>
            <h1>¡Compra Exitosa!</h1>
            <p>Tu pedido por <strong>$<?= number_format($total_general, 2) ?></strong> ha sido registrado correctamente.</p>
            <p>El inventario ha sido actualizado.</p>
            <a href="index.php" class="btn-volver">Volver al Inicio</a>
        </div>
    </body>
    </html>
    <?php
    exit;

} catch (PDOException $e) {
    // ❌ Si algo falla, revertir todos los cambios
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error: " . $e->getMessage());
}
?>
