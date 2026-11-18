<?php
session_start();
require 'config.php';

// 🛒 Contar items en el carrito para mostrar en el navbar
$total_items_carrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total_items_carrito += $item['cantidad'];
    }
}

// 🏷️ Obtener categorías para mostrar en el inicio (máximo 6)
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id ASC LIMIT 6")->fetchAll();

// 👗 Obtener productos destacados (los 8 más recientes que tengan stock)
$productos_destacados = $pdo->query("SELECT p.*, c.nombre AS cat_nombre 
                                     FROM productos p 
                                     LEFT JOIN categorias c ON p.id_categoria = c.id 
                                     WHERE p.stock > 0 
                                     ORDER BY p.id DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Elegance Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Estilos específicos del Home */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-hero {
            display: inline-block;
            background: #d4af37;
            color: #1a1a1a;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-hero:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .seccion {
            padding: 4rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .seccion-titulo {
            text-align: center;
            font-size: 2rem;
            color: #1a1a1a;
            margin-bottom: 3rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
        }
        .seccion-titulo::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: #d4af37;
            margin: 1rem auto 0;
        }
        
        /* ✅ ESTILOS MEJORADOS PARA LAS TARJETAS DE CATEGORÍAS */
        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .categoria-card {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            padding: 2rem 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #1a1a1a;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .categoria-card:hover {
            border-color: #d4af37;
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2);
            background: linear-gradient(135deg, #fff 0%, #fff9e6 100%);
        }
        .categoria-icono {
            font-size: 3rem;
            transition: transform 0.3s ease;
        }
        .categoria-card:hover .categoria-icono {
            transform: scale(1.2);
        }
        .categoria-nombre {
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: capitalize;
            letter-spacing: 0.5px;
        }
        .categoria-flecha {
            color: #d4af37;
            font-size: 1.5rem;
            font-weight: bold;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }
        .categoria-card:hover .categoria-flecha {
            opacity: 1;
            transform: translateX(0);
        }

        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        .producto-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .producto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .producto-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: #f5f5f5;
        }
        .producto-info {
            padding: 1.5rem;
            text-align: center;
        }
        .producto-nombre {
            font-size: 1.1rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .producto-precio {
            font-size: 1.3rem;
            color: #d4af37;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <img src="assets/img/logo.jpg" alt="Logo" onerror="this.style.display='none'">
            <span>ELEGANCE</span>
        </a>
        <button class="menu-toggle">☰</button>
        <ul class="nav-menu">
            <li><a href="index.php" class="active">Inicio</a></li>
            <li><a href="catalogo.php">Catálogo</a></li>
            <li>
                <a href="carrito.php" style="background: #d4af37; color: #1a1a1a; padding: 0.5rem 1rem; border-radius: 50px; font-weight: bold;">
                    🛒 Carrito (<?= $total_items_carrito ?>)
                </a>
            </li>
            <li><a href="login.php">🔒 Admin</a></li>
        </ul>
    </div>
</nav>

<!-- HERO SECTION -->
<header class="hero">
    <div>
        <h1>Define tu Estilo</h1>
        <p>Descubre las últimas tendencias en moda con nuestra colección exclusiva de prendas elegantes y accesorios de alta calidad.</p>
        <a href="catalogo.php" class="btn-hero">Ver Catálogo Completo</a>
    </div>
</header>

<!-- CATEGORÍAS DESTACADAS -->
<section class="seccion" id="categorias">
    <h2 class="seccion-titulo">Nuestras Categorías</h2>
    <div class="categorias-grid">
        <?php 
        $iconos = [
            'vestidos' => '👗', 'playeras' => '👕', 'pantalones' => '👖',
            'accesorios' => '💍', 'calzado' => '👠', 'bolsas' => '👜',
            'conjuntos' => '👔', 'camisas' => '👔'
        ];
        foreach ($categorias as $cat): 
            $nombre_lower = strtolower($cat['nombre']);
            $icono = $iconos[$nombre_lower] ?? '🛍️';
        ?>
            <a href="catalogo.php?categoria=<?= urlencode($nombre_lower) ?>" class="categoria-card">
                <div class="categoria-icono"><?= $icono ?></div>
                <div class="categoria-nombre"><?= htmlspecialchars($cat['nombre']) ?></div>
                <div class="categoria-flecha">→</div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section class="seccion" style="background: #f9f9f9;">
    <h2 class="seccion-titulo">Productos Destacados</h2>
    <div class="productos-grid">
        <?php if (empty($productos_destacados)): ?>
            <p style="text-align: center; grid-column: 1 / -1; color: #666;">Pronto agregaremos nuevos productos.</p>
        <?php else: ?>
            <?php foreach ($productos_destacados as $prod): ?>
                <div class="producto-card">
                    <a href="catalogo.php">
                        <img src="<?= !empty($prod['imagen']) ? 'assets/img/' . htmlspecialchars($prod['imagen']) : 'https://via.placeholder.com/300x400/f5f5f5/999?text=Sin+Imagen' ?>" 
                             alt="<?= htmlspecialchars($prod['nombre']) ?>" 
                             class="producto-img"
                             onerror="this.src='https://via.placeholder.com/300x400/f5f5f5/999?text=Imagen+no+encontrada'">
                    </a>
                    <div class="producto-info">
                        <div class="producto-nombre"><?= htmlspecialchars($prod['nombre']) ?></div>
                        <div class="producto-precio">$<?= number_format($prod['precio'], 2) ?></div>
                        <a href="agregar_carrito.php?id=<?= $prod['id'] ?>" style="display: block; margin-top: 1rem; background: #1a1a1a; color: #d4af37; padding: 0.75rem; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem;">
                            🛒 Agregar al Carrito
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div style="text-align: center; margin-top: 3rem;">
        <a href="catalogo.php" class="btn-hero" style="background: #1a1a1a; color: #d4af37;">Ver Todos los Productos</a>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Elegance Store</h3>
            <p>Tu destino de moda favorito. Calidad, estilo y elegancia en cada prenda.</p>
        </div>
        <div class="footer-section">
            <h3>Enlaces Rápidos</h3>
            <a href="index.php">Inicio</a>
            <a href="catalogo.php">Catálogo</a>
            <a href="carrito.php">Carrito</a>
            <a href="login.php">Acceso Admin</a>
        </div>
        <div class="footer-section">
            <h3>Contacto</h3>
            <p>📧 contacto@elegance.com</p>
            <p>📞 +52 555 123 4567</p>
            <p>📍 Jilotepec, Estado de México</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Elegance Store. Todos los derechos reservados.</p>
        <p style="margin-top: 0.5rem; font-size: 0.85rem; opacity: 0.7;">
            Desarrollado con HTML5, CSS3, JavaScript, PHP y MySQL
        </p>
    </div>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
