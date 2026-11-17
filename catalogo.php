<?php
session_start();
require 'config.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Obtener categoría del URL
$categoria_filtro = $_GET['categoria'] ?? '';

// Obtener todas las categorías
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

// Consultar productos (filtrados o todos)
if ($categoria_filtro) {
    $stmt = $pdo->prepare("SELECT p.*, c.nombre AS cat_nombre 
                           FROM productos p 
                           LEFT JOIN categorias c ON p.id_categoria = c.id 
                           WHERE LOWER(c.nombre) LIKE ?
                           ORDER BY p.id DESC");
    $stmt->execute(["%$categoria_filtro%"]);
    $productos = $stmt->fetchAll();
} else {
    $productos = $pdo->query("SELECT p.*, c.nombre AS cat_nombre 
                              FROM productos p 
                              LEFT JOIN categorias c ON p.id_categoria = c.id 
                              ORDER BY p.id DESC")->fetchAll();
}

// Nombre de la categoría para el título
$nombres_categoria = [
    'vestidos' => 'Vestidos', 'vestido' => 'Vestidos',
    'conjuntos' => 'Conjuntos', 'conjunto' => 'Conjuntos',
    'pantalones' => 'Pantalones', 'pantalon' => 'Pantalones',
    'accesorios' => 'Accesorios', 'accesorio' => 'Accesorios',
    'camisas' => 'Camisas', 'camisa' => 'Camisas',
    'playeras' => 'Playeras', 'playera' => 'Playeras',
    'bolsas' => 'Bolsas', 'bolsa' => 'Bolsas'
];

$titulo_categoria = $nombres_categoria[strtolower($categoria_filtro)] ?? 'Todos los Productos';

// Contar items en el carrito
$total_items_carrito = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total_items_carrito += $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_categoria ?> - Elegance Store</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .catalogo-header {
            background: linear-gradient(135deg, #1a1a1a, #333);
            color: white;
            padding: 3rem 5%;
            text-align: center;
        }
        .catalogo-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .catalogo-header a { color: #d4af37; text-decoration: none; }
        
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 3rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .producto-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
        }
        
        .producto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .producto-card.agotado {
            opacity: 0.7;
        }
        
        .producto-img-container {
            position: relative;
            overflow: hidden;
        }
        
        .producto-img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            background: #f5f5f5;
            transition: all 0.3s;
        }
        
        .producto-card.agotado .producto-img {
            filter: grayscale(100%);
        }
        
        .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .badge-agotado {
            background: #e74c3c;
            color: white;
        }
        
        .badge-ultimas {
            background: #f39c12;
            color: white;
        }
        
        .badge-nuevo {
            background: #27ae60;
            color: white;
            top: 15px;
            left: 15px;
            right: auto;
        }
        
        .producto-info { padding: 1.5rem; }
        
        .producto-categoria {
            color: #d4af37;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .producto-nombre {
            font-size: 1.2rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .producto-precio {
            font-size: 1.5rem;
            color: #1a1a1a;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .producto-stock {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .producto-stock.bajo {
            color: #e67e22;
            font-weight: 600;
        }
        
        .producto-stock.agotado {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .btn-carrito {
            width: 100%;
            padding: 0.875rem;
            background: #1a1a1a;
            color: #d4af37;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            text-align: center;
            text-decoration: none;
            box-sizing: border-box;
        }
        
        .btn-carrito:hover:not(:disabled) {
            background: #d4af37;
            color: #1a1a1a;
        }
        
        .btn-carrito:disabled {
            background: #95a5a6;
            color: white;
            cursor: not-allowed;
        }
        
        .filtros-rapidos {
            display: flex;
            justify-content: center;
            gap: 1rem;
            padding: 2rem 5%;
            flex-wrap: wrap;
        }
        
        .filtro-btn {
            padding: 0.75rem 1.5rem;
            background: white;
            border: 2px solid #e5e5e5;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filtro-btn:hover, .filtro-btn.active {
            background: #1a1a1a;
            color: #d4af37;
            border-color: #1a1a1a;
        }
        
        .sin-productos {
            text-align: center;
            padding: 5rem 2rem;
            color: #999;
        }
        
        .sin-productos::before {
            content: '🛍️';
            display: block;
            font-size: 4rem;
            margin-bottom: 1rem;
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
            <li><a href="catalogo.php">Catalogo</a></li>
            <li><a href="carrito.php" style="background: #d4af37; color: #1a1a1a; padding: 0.5rem 1rem; border-radius: 50px; font-weight: bold;">🛒 Carrito (<?= $total_items_carrito ?>)</a></li>
            <li><a href="sistema.php">Admin</a></li>
        </ul>
    </div>
</nav>

<div class="catalogo-header">
    <p><a href="index.php">← Volver al Inicio</a></p>
    <h1><?= htmlspecialchars($titulo_categoria) ?></h1>
    <p><?= count($productos) ?> producto(s) disponible(s)</p>
</div>

<!-- Filtros rápidos -->
<div class="filtros-rapidos">
    <a href="catalogo.php" class="filtro-btn <?= !$categoria_filtro ? 'active' : '' ?>">Todos</a>
    <a href="catalogo.php?categoria=vestidos" class="filtro-btn <?= in_array(strtolower($categoria_filtro), ['vestidos','vestido']) ? 'active' : '' ?>">Vestidos</a>
    <a href="catalogo.php?categoria=conjuntos" class="filtro-btn <?= in_array(strtolower($categoria_filtro), ['conjuntos','conjunto']) ? 'active' : '' ?>">Conjuntos</a>
    <a href="catalogo.php?categoria=pantalones" class="filtro-btn <?= in_array(strtolower($categoria_filtro), ['pantalones','pantalon']) ? 'active' : '' ?>">Pantalones</a>
    <a href="catalogo.php?categoria=playeras" class="filtro-btn <?= in_array(strtolower($categoria_filtro), ['playeras','playera']) ? 'active' : '' ?>">Playeras</a>
    <a href="catalogo.php?categoria=accesorios" class="filtro-btn <?= in_array(strtolower($categoria_filtro), ['accesorios','accesorio']) ? 'active' : '' ?>">Accesorios</a>
    <a href="catalogo.php?categoria=bolsas" class="filtro-btn <?= in_array(strtolower($categoria_filtro), ['bolsas','bolsa']) ? 'active' : '' ?>">Bolsas</a>
</div>

<!-- Productos -->
<?php if (empty($productos)): ?>
    <div class="sin-productos">
        <h2>No hay productos en esta categoría</h2>
        <p>Pronto agregaremos nuevos productos</p>
    </div>
<?php else: ?>
    <div class="productos-grid">
        <?php foreach ($productos as $producto): 
            $stock = (int)$producto['stock'];
            $agotado = ($stock === 0);
            $bajo_stock = ($stock > 0 && $stock < 5);
        ?>
            <div class="producto-card <?= $agotado ? 'agotado' : '' ?>">
                <div class="producto-img-container">
                    <img src="<?= !empty($producto['imagen']) ? 'assets/img/' . htmlspecialchars($producto['imagen']) : 'https://via.placeholder.com/400x500/f5f5f5/999?text=Sin+Imagen' ?>" 
                         alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                         class="producto-img"
                         onerror="this.src='https://via.placeholder.com/400x500/f5f5f5/999?text=Imagen+no+encontrada'">
                    
                    <!-- BADGES -->
                    <?php if ($agotado): ?>
                        <span class="badge badge-agotado">Agotado</span>
                    <?php elseif ($bajo_stock): ?>
                        <span class="badge badge-ultimas">¡Últimas <?= $stock ?> piezas!</span>
                    <?php else: ?>
                        <span class="badge badge-nuevo">Nuevo</span>
                    <?php endif; ?>
                </div>
                
                <div class="producto-info">
                    <div class="producto-categoria"><?= htmlspecialchars($producto['cat_nombre'] ?? 'Sin categoría') ?></div>
                    <h3 class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></h3>
                    <div class="producto-precio">$<?= number_format($producto['precio'], 2) ?></div>
                    
                    <div class="producto-stock <?= $agotado ? 'agotado' : ($bajo_stock ? 'bajo' : '') ?>">
                        <?php if ($agotado): ?>
                            ❌ Sin disponibilidad
                        <?php elseif ($bajo_stock): ?>
                            ⚠️ ¡Solo quedan <?= $stock ?> en stock!
                        <?php else: ?>
                            ✅ <?= $stock ?> disponibles
                        <?php endif; ?>
                    </div>
                    
                    <!-- BOTÓN DEL CARRITO -->
                    <?php if ($agotado): ?>
                        <button class="btn-carrito" disabled>❌ Agotado</button>
                    <?php else: ?>
                        <a href="agregar_carrito.php?id=<?= $producto['id'] ?>" class="btn-carrito">🛒 Agregar al Carrito</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Elegance Store</h3>
            <p>Tu destino de moda favorito.</p>
        </div>
        <div class="footer-section">
            <h3>Enlaces</h3>
            <a href="index.php">Inicio</a>
            <a href="catalogo.php">Catalogo</a>
            <a href="carrito.php">Carrito</a>
            <a href="sistema.php">Administración</a>
        </div>
        <div class="footer-section">
            <h3>Contacto</h3>
            <p>📧 lukikuki@gamil.com</p>
            <p>📞 +52 5650860329</p>
        </div>
   <div class="footer-bottom">
    <p>&copy; 2026 litzi Aislinn Perez.</p>
    <p style="margin-top: 0.5rem; font-size: 0.85rem; opacity: 0.7;">
        Desarrollado con HTML5, CSS3, JavaScript, PHP y MySQL
    </p>
</div>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
