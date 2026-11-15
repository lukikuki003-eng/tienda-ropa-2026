<?php
session_start();
require 'config.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Calcular total
$total = 0;
$total_items = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
    $total_items += $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Elegance Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .carrito-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 5%;
        }
        .carrito-header {
            background: linear-gradient(135deg, #1a1a1a, #333);
            color: white;
            padding: 2rem 5%;
            text-align: center;
        }
        .carrito-header h1 {
            font-size: 2.5rem;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .carrito-item {
            display: grid;
            grid-template-columns: 100px 2fr 1fr 1fr 1fr 50px;
            gap: 1rem;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .carrito-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .carrito-item .nombre { font-weight: 600; font-size: 1.1rem; }
        .carrito-item .precio { color: #d4af37; font-weight: bold; }
        .cantidad-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .cantidad-control button {
            width: 30px;
            height: 30px;
            border: 2px solid #e5e5e5;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .cantidad-control button:hover { background: #f5f5f5; }
        .cantidad-control span { font-weight: bold; min-width: 30px; text-align: center; }
        .btn-eliminar {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-eliminar:hover { background: #c0392b; }
        .resumen-carrito {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }
        .resumen-linea {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .resumen-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a1a1a;
            border-top: 2px solid #d4af37;
            margin-top: 1rem;
            padding-top: 1rem;
        }
        .btn-finalizar {
            width: 100%;
            padding: 1rem;
            background: #1a1a1a;
            color: #d4af37;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-finalizar:hover { background: #d4af37; color: #1a1a1a; }
        .carrito-vacio {
            text-align: center;
            padding: 5rem 2rem;
            color: #999;
        }
        .carrito-vacio::before {
            content: '🛒';
            display: block;
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        .form-cliente {
            background: #f9f9f9;
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        .form-cliente h3 {
            margin-bottom: 1rem;
            color: #1a1a1a;
        }
        .form-cliente input, .form-cliente select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .form-cliente input:focus, .form-cliente select:focus {
            outline: none;
            border-color: #d4af37;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <img src="assets/img/logo.jpg" alt="Logo">
            <span>ELEGANCE</span>
        </a>
        <button class="menu-toggle">☰</button>
        <ul class="nav-menu">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="catalogo.php">Catálogo</a></li>
            <li><a href="carrito.php" style="background: #d4af37; color: #1a1a1a; padding: 0.5rem 1rem; border-radius: 50px;">🛒 Carrito (<?= $total_items ?>)</a></li>
        </ul>
    </div>
</nav>

<div class="carrito-header">
    <p><a href="catalogo.php" style="color: #d4af37; text-decoration: none;">← Seguir comprando</a></p>
    <h1>Mi Carrito</h1>
    <p><?= $total_items ?> producto(s) en tu carrito</p>
</div>

<div class="carrito-container">
    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="carrito-vacio">
            <h2>Tu carrito está vacío</h2>
            <p>Agrega productos desde nuestro catálogo</p>
            <a href="catalogo.php" style="display: inline-block; margin-top: 1rem; background: #1a1a1a; color: #d4af37; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: bold;">Ver Catálogo</a>
        </div>
    <?php else: ?>
        <?php foreach ($_SESSION['carrito'] as $item): ?>
            <div class="carrito-item">
                <img src="<?= !empty($item['imagen']) ? 'assets/img/' . htmlspecialchars($item['imagen']) : 'https://via.placeholder.com/100' ?>" 
                     alt="<?= htmlspecialchars($item['nombre']) ?>">
                <div class="nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                <div class="precio">$<?= number_format($item['precio'], 2) ?></div>
                <div class="cantidad-control">
                    <form method="POST" action="actualizar_cantidad.php" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="cantidad" value="<?= max(1, $item['cantidad'] - 1) ?>">
                        <button type="submit">-</button>
                    </form>
                    <span><?= $item['cantidad'] ?></span>
                    <form method="POST" action="actualizar_cantidad.php" style="display: flex; gap: 0.5rem; align-items: center;">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="cantidad" value="<?= $item['cantidad'] + 1 ?>">
                        <button type="submit">+</button>
                    </form>
                </div>
                <div style="font-weight: bold;">$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></div>
                <a href="eliminar_carrito.php?id=<?= $item['id'] ?>" class="btn-eliminar">🗑️</a>
            </div>
        <?php endforeach; ?>

        <div class="resumen-carrito">
            <div class="resumen-linea">
                <span>Subtotal (<?= $total_items ?> productos)</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <div class="resumen-linea">
                <span>Envío</span>
                <span style="color: #27ae60;">GRATIS</span>
            </div>
            <div class="resumen-linea resumen-total">
                <span>TOTAL</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>

            <!-- Formulario para finalizar compra -->
            <form method="POST" action="finalizar_compra.php" class="form-cliente">
                <h3>📋 Datos para finalizar la compra</h3>
                
                <label>¿Eres cliente registrado?</label>
                <select name="tipo_cliente" id="tipoCliente" onchange="toggleCliente()" required>
                    <option value="nuevo">Soy cliente nuevo</option>
                    <option value="existente">Ya estoy registrado</option>
                </select>

                <div id="clienteExistente" style="display: none;">
                    <label>Selecciona tu cuenta:</label>
                    <select name="id_cliente_existente">
                        <option value="">-- Selecciona --</option>
                        <?php 
                        $clientes = $pdo->query("SELECT * FROM clientes ORDER BY nombre")->fetchAll();
                        foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="clienteNuevo">
                    <label>Nombre completo:</label>
                    <input type="text" name="nombre_completo" placeholder="Ej: Juan Pérez">
                    
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="tu@email.com">
                    
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" placeholder="555 123 4567">
                    
                    <label>Dirección de envío:</label>
                    <input type="text" name="direccion" placeholder="Calle, número, colonia">
                    
                    <label>Ciudad:</label>
                    <input type="text" name="ciudad" placeholder="Tu ciudad">
                </div>

                <button type="submit" class="btn-finalizar">💳 Finalizar Compra - $<?= number_format($total, 2) ?></button>
            </form>
        </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Elegance Store</h3>
            <p>Tu destino de moda favorito.</p>
        </div>
        <div class="footer-section">
            <h3>Enlaces</h3>
            <a href="index.php">Inicio</a>
            <a href="catalogo.php">Catálogo</a>
            <a href="carrito.php">Carrito</a>
        </div>
        <div class="footer-section">
            <h3>Contacto</h3>
            <p>📧 likikuki@gmail.com</p>
            <p>📞 +52 5650860329</p>
        </div>
    </div>
    <div class="footer-bottom">
    <p>&copy; 2026 litzi Aislinn Perez.</p>
    <p style="margin-top: 0.5rem; font-size: 0.85rem; opacity: 0.7;">
        Desarrollado con HTML5, CSS3, JavaScript, PHP y MySQL
    </p>
</div>
</footer>

<script>
function toggleCliente() {
    const tipo = document.getElementById('tipoCliente').value;
    document.getElementById('clienteExistente').style.display = tipo === 'existente' ? 'block' : 'none';
    document.getElementById('clienteNuevo').style.display = tipo === 'nuevo' ? 'block' : 'none';
}
</script>
</body>
</html>