<?php
session_start();

// 🔒 VERIFICAR QUE EL ADMIN ESTÉ LOGUEADO
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

require 'config.php';

$mensaje = '';
$tipo = '';
$editar = null;
$tipo_editar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'categoria') {
            if (isset($_POST['id']) && $_POST['id'] != '') {
                $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
                $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['id']]);
                $mensaje = "✅ Categoría actualizada correctamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
                $stmt->execute([$_POST['nombre'], $_POST['descripcion']]);
                $mensaje = "✅ Categoría registrada correctamente";
            }
            $tipo = "success";
        }
        elseif ($accion === 'producto') {
            if (isset($_POST['id']) && $_POST['id'] != '') {
                $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, talla = ?, imagen = ?, id_categoria = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['nombre'], $_POST['descripcion'], $_POST['precio'], 
                    $_POST['stock'], $_POST['talla'], $_POST['imagen'], $_POST['id_categoria'], $_POST['id']
                ]);
                $mensaje = "✅ Producto actualizado correctamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, talla, imagen, id_categoria) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'], $_POST['descripcion'], $_POST['precio'], 
                    $_POST['stock'], $_POST['talla'], $_POST['imagen'], $_POST['id_categoria']
                ]);
                $mensaje = "✅ Producto registrado correctamente";
            }
            $tipo = "success";
        }
        elseif ($accion === 'cliente') {
            if (isset($_POST['id']) && $_POST['id'] != '') {
                $stmt = $pdo->prepare("UPDATE clientes SET nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?, ciudad = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['nombre'], $_POST['apellido'], $_POST['email'], 
                    $_POST['telefono'], $_POST['direccion'], $_POST['ciudad'], $_POST['id']
                ]);
                $mensaje = "✅ Cliente actualizado correctamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, direccion, ciudad) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'], $_POST['apellido'], $_POST['email'], 
                    $_POST['telefono'], $_POST['direccion'], $_POST['ciudad']
                ]);
                $mensaje = "✅ Cliente registrado correctamente";
            }
            $tipo = "success";
        }
        elseif ($accion === 'venta') {
            $prod = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
            $prod->execute([$_POST['id_producto']]);
            $precio = $prod->fetchColumn();
            $total = $precio * $_POST['cantidad'];
            
            $stmt = $pdo->prepare("INSERT INTO ventas (id_cliente, id_producto, cantidad, total) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['id_cliente'], $_POST['id_producto'], $_POST['cantidad'], $total]);
            $mensaje = "✅ Venta registrada correctamente - Total: $" . number_format($total, 2);
            $tipo = "success";
        }
        // 🔑 NUEVO: Lógica para crear un nuevo administrador
        elseif ($accion === 'nuevo_admin') {
            $usuario = trim($_POST['usuario']);
            $nombre = trim($_POST['nombre']);
            $password = $_POST['password'];
            
            if (empty($usuario) || empty($nombre) || empty($password)) {
                $mensaje = "⚠️ Todos los campos son obligatorios";
                $tipo = "error";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM administradores WHERE usuario = ?");
                $stmt->execute([$usuario]);
                
                if ($stmt->fetch()) {
                    $mensaje = "❌ El nombre de usuario ya está en uso";
                    $tipo = "error";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO administradores (usuario, password, nombre) VALUES (?, ?, ?)");
                    $stmt->execute([$usuario, $password_hash, $nombre]);
                    $mensaje = "✅ Administrador creado correctamente";
                    $tipo = "success";
                }
            }
        }
        elseif ($accion === 'eliminar_categoria') {
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensaje = "🗑️ Categoría eliminada correctamente";
            $tipo = "success";
        }
        elseif ($accion === 'eliminar_producto') {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensaje = "🗑️ Producto eliminado correctamente";
            $tipo = "success";
        }
        elseif ($accion === 'eliminar_cliente') {
            $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $mensaje = "🗑️ Cliente eliminado correctamente";
            $tipo = "success";
        }
    } catch (PDOException $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
        $tipo = "error";
    }
}

// Verificar si se solicita editar
if (isset($_GET['editar_categoria'])) {
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$_GET['editar_categoria']]);
    $editar = $stmt->fetch();
    $tipo_editar = 'categoria';
} elseif (isset($_GET['editar_producto'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$_GET['editar_producto']]);
    $editar = $stmt->fetch();
    $tipo_editar = 'producto';
} elseif (isset($_GET['editar_cliente'])) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$_GET['editar_cliente']]);
    $editar = $stmt->fetch();
    $tipo_editar = 'cliente';
}

// Determinar qué pestaña debe estar activa automáticamente
$tab_activo = 'tab-categorias';
if ($tipo_editar === 'producto') $tab_activo = 'tab-productos';
elseif ($tipo_editar === 'cliente') $tab_activo = 'tab-clientes';
elseif ($tipo_editar === 'categoria') $tab_activo = 'tab-categorias';
elseif ($tipo_editar === 'admin') $tab_activo = 'tab-usuarios';

// Obtener datos
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id DESC")->fetchAll();
$productos = $pdo->query("SELECT p.*, c.nombre AS cat_nombre 
                          FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id 
                          ORDER BY p.id DESC")->fetchAll();
$clientes = $pdo->query("SELECT * FROM clientes ORDER BY id DESC")->fetchAll();
$ventas = $pdo->query("SELECT v.*, c.nombre AS cli_nombre, c.apellido AS cli_apellido,
                       p.nombre AS prod_nombre 
                       FROM ventas v 
                       JOIN clientes c ON v.id_cliente = c.id 
                       JOIN productos p ON v.id_producto = p.id 
                       ORDER BY v.id DESC")->fetchAll();
// 🔑 NUEVO: Obtener lista de administradores
$admins = $pdo->query("SELECT id, usuario, nombre, fecha_creacion FROM administradores ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegance Store - Administración</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .consulta-header { background: linear-gradient(135deg, #d4af37, #f4d03f); color: #1a1a1a; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center; }
        .consulta-header h2 { border: none; padding: 0; color: #1a1a1a; margin: 0; }
        .search-box { position: relative; margin-bottom: 1.5rem; }
        .search-box input { width: 100%; padding: 1rem 1rem 1rem 3rem; border: 2px solid #e5e5e5; border-radius: 50px; font-size: 1rem; transition: all 0.3s; box-sizing: border-box; }
        .search-box input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2); outline: none; }
        .search-box::before { content: '🔍'; position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.2rem; }
        .filter-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; }
        .filter-tag { padding: 0.5rem 1rem; background: #f5f5f5; border: 2px solid #e5e5e5; border-radius: 50px; cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: all 0.3s; }
        .filter-tag:hover, .filter-tag.active { background: #1a1a1a; color: #d4af37; border-color: #1a1a1a; }
        .results-count { background: #f5f5f5; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; color: #666; }
        .results-count strong { color: #d4af37; }
        .no-results { text-align: center; padding: 3rem; color: #999; font-style: italic; }
        .no-results::before { content: '😕'; display: block; font-size: 3rem; margin-bottom: 1rem; }
        .highlight { background: #fff3cd; padding: 2px 4px; border-radius: 3px; font-weight: bold; }
        .stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; border-left: 4px solid #d4af37; }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #1a1a1a; }
        .stat-card .label { color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .btn-clear { background: #e74c3c; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-left: 0.5rem; }
        .btn-clear:hover { background: #c0392b; }
        .btn-edit { background: #3498db; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem; text-decoration: none; display: inline-block; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85rem; }
        .btn-delete:hover { background: #c0392b; }
        .accion-buttons { display: flex; gap: 0.3rem; }
        .cancelar-edicion { background: #95a5a6; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; margin-left: 1rem; text-decoration: none; display: inline-block; }
        .cancelar-edicion:hover { background: #7f8c8d; }
        .modo-edicion { background: #fff3cd; border: 2px solid #ffc107; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .modo-edicion h3 { color: #856404; margin-bottom: 0.5rem; }
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
            <li><a href="index.php#categorias">Categorías</a></li>
            <li><a href="index.php#galeria">Galería</a></li>
            <li style="display: flex; align-items: center; gap: 1rem;">
                <span style="color: #d4af37; font-size: 0.9rem; font-weight: bold;">👤 <?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin') ?></span>
                <a href="cerrar_sesion.php" style="background: #e74c3c; color: white; padding: 0.4rem 0.8rem; border-radius: 50px; text-decoration: none; font-size: 0.85rem; font-weight: bold;">Salir</a>
            </li>
        </ul>
    </div>
</nav>

<div class="sistema-container">
    <div class="sistema-header">
        <h1>👗 Panel de Administración</h1>
        <p>Gestiona tu tienda de ropa</p>
    </div>

    <nav class="tabs">
        <button class="tab-btn <?= $tab_activo === 'tab-categorias' ? 'active' : '' ?>" data-tab="tab-categorias">🏷️ Categorías</button>
        <button class="tab-btn <?= $tab_activo === 'tab-productos' ? 'active' : '' ?>" data-tab="tab-productos">👕 Productos</button>
        <button class="tab-btn <?= $tab_activo === 'tab-clientes' ? 'active' : '' ?>" data-tab="tab-clientes">👤 Clientes</button>
        <button class="tab-btn <?= $tab_activo === 'tab-ventas' ? 'active' : '' ?>" data-tab="tab-ventas">💰 Ventas</button>
        <!-- 🔑 NUEVO: Botón de Usuarios -->
        <button class="tab-btn <?= $tab_activo === 'tab-usuarios' ? 'active' : '' ?>" data-tab="tab-usuarios">🔑 Usuarios</button>
        <button class="tab-btn <?= $tab_activo === 'tab-consultas' ? 'active' : '' ?>" data-tab="tab-consultas">🔍 Consultas</button>
    </nav>

    <?php if ($mensaje): ?>
        <div style="padding: 1.5rem 2rem 0;">
            <div class="alert <?= $tipo ?>"><?= $mensaje ?></div>
        </div>
    <?php endif; ?>

    <!-- TAB CATEGORÍAS -->
    <div id="tab-categorias" class="tab-content <?= $tab_activo === 'tab-categorias' ? 'active' : '' ?>">
        <h2><?= isset($editar) && $tipo_editar === 'categoria' ? '✏️ Editar Categoría' : 'Nueva Categoría' ?></h2>
        <?php if (isset($editar) && $tipo_editar === 'categoria'): ?>
            <div class="modo-edicion"><h3>📝 Modo Edición</h3><p>Estás modificando la categoría ID: <?= $editar['id'] ?></p></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="accion" value="categoria">
            <?php if (isset($editar) && $tipo_editar === 'categoria'): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="<?= isset($editar) && $tipo_editar === 'categoria' ? htmlspecialchars($editar['nombre']) : '' ?>" required></div>
                <div class="form-group" style="grid-column: 1 / -1;"><label>Descripción</label><textarea name="descripcion" rows="3"><?= isset($editar) && $tipo_editar === 'categoria' ? htmlspecialchars($editar['descripcion']) : '' ?></textarea></div>
            </div>
            <button type="submit" class="btn"><?= isset($editar) && $tipo_editar === 'categoria' ? '💾 Actualizar Categoría' : '💾 Guardar Categoría' ?></button>
            <?php if (isset($editar) && $tipo_editar === 'categoria'): ?><a href="sistema.php" class="btn cancelar-edicion">❌ Cancelar</a><?php endif; ?>
        </form>
        <h2 style="margin-top:2rem;">Listado de Categorías</h2>
        <?php if (empty($categorias)): ?><p class="empty">No hay categorías registradas.</p><?php else: ?>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td><td><?= htmlspecialchars($c['nombre']) ?></td><td><?= htmlspecialchars($c['descripcion']) ?></td>
                        <td class="accion-buttons">
                            <a href="sistema.php?editar_categoria=<?= $c['id'] ?>" class="btn-edit">✏️ Editar</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                                <input type="hidden" name="accion" value="eliminar_categoria"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-delete">🗑️ Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- TAB PRODUCTOS -->
    <div id="tab-productos" class="tab-content <?= $tab_activo === 'tab-productos' ? 'active' : '' ?>">
        <h2><?= isset($editar) && $tipo_editar === 'producto' ? '✏️ Editar Producto' : 'Nuevo Producto' ?></h2>
        <?php if (isset($editar) && $tipo_editar === 'producto'): ?>
            <div class="modo-edicion"><h3>📝 Modo Edición</h3><p>Estás modificando el producto ID: <?= $editar['id'] ?></p></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="accion" value="producto">
            <?php if (isset($editar) && $tipo_editar === 'producto'): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="<?= isset($editar) && $tipo_editar === 'producto' ? htmlspecialchars($editar['nombre']) : '' ?>" required></div>
                <div class="form-group"><label>Precio ($)</label><input type="number" name="precio" step="0.01" min="0" value="<?= isset($editar) && $tipo_editar === 'producto' ? $editar['precio'] : '' ?>" required></div>
                <div class="form-group"><label>Stock</label><input type="number" name="stock" min="0" value="<?= isset($editar) && $tipo_editar === 'producto' ? $editar['stock'] : '' ?>" required></div>
                <div class="form-group"><label>Talla</label>
                    <select name="talla">
                        <option value="">-- Selecciona --</option>
                        <option <?= (isset($editar) && $tipo_editar === 'producto' && $editar['talla'] == 'XS') ? 'selected' : '' ?>>XS</option>
                        <option <?= (isset($editar) && $tipo_editar === 'producto' && $editar['talla'] == 'S') ? 'selected' : '' ?>>S</option>
                        <option <?= (isset($editar) && $tipo_editar === 'producto' && $editar['talla'] == 'M') ? 'selected' : '' ?>>M</option>
                        <option <?= (isset($editar) && $tipo_editar === 'producto' && $editar['talla'] == 'L') ? 'selected' : '' ?>>L</option>
                        <option <?= (isset($editar) && $tipo_editar === 'producto' && $editar['talla'] == 'XL') ? 'selected' : '' ?>>XL</option>
                        <option <?= (isset($editar) && $tipo_editar === 'producto' && $editar['talla'] == 'XXL') ? 'selected' : '' ?>>XXL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="id_categoria" required>
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($editar) && $tipo_editar === 'producto' && $editar['id_categoria'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Imagen (nombre del archivo)</label>
                    <input type="text" name="imagen" value="<?= isset($editar) && $tipo_editar === 'producto' ? htmlspecialchars($editar['imagen']) : '' ?>" placeholder="Ej: conjunto.jpg">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;"><label>Descripción</label><textarea name="descripcion" rows="3"><?= isset($editar) && $tipo_editar === 'producto' ? htmlspecialchars($editar['descripcion']) : '' ?></textarea></div>
            </div>
            <button type="submit" class="btn"><?= isset($editar) && $tipo_editar === 'producto' ? '💾 Actualizar Producto' : '💾 Guardar Producto' ?></button>
            <?php if (isset($editar) && $tipo_editar === 'producto'): ?><a href="sistema.php" class="btn cancelar-edicion">❌ Cancelar</a><?php endif; ?>
        </form>
        <h2 style="margin-top:2rem;">Listado de Productos</h2>
        <?php if (empty($productos)): ?><p class="empty">No hay productos registrados.</p><?php else: ?>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Talla</th><th>Categoría</th><th>Acciones</th></tr>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td><td><?= htmlspecialchars($p['nombre']) ?></td><td>$<?= number_format($p['precio'], 2) ?></td><td><?= $p['stock'] ?></td><td><?= htmlspecialchars($p['talla']) ?></td><td><?= htmlspecialchars($p['cat_nombre'] ?? 'Sin categoría') ?></td>
                        <td class="accion-buttons">
                            <a href="sistema.php?editar_producto=<?= $p['id'] ?>" class="btn-edit">✏️ Editar</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                <input type="hidden" name="accion" value="eliminar_producto"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-delete">🗑️ Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- TAB CLIENTES -->
    <div id="tab-clientes" class="tab-content <?= $tab_activo === 'tab-clientes' ? 'active' : '' ?>">
        <h2><?= isset($editar) && $tipo_editar === 'cliente' ? '✏️ Editar Cliente' : 'Nuevo Cliente' ?></h2>
        <?php if (isset($editar) && $tipo_editar === 'cliente'): ?>
            <div class="modo-edicion"><h3>📝 Modo Edición</h3><p>Estás modificando el cliente ID: <?= $editar['id'] ?></p></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="accion" value="cliente">
            <?php if (isset($editar) && $tipo_editar === 'cliente'): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="<?= isset($editar) && $tipo_editar === 'cliente' ? htmlspecialchars($editar['nombre']) : '' ?>" required></div>
                <div class="form-group"><label>Apellido</label><input type="text" name="apellido" value="<?= isset($editar) && $tipo_editar === 'cliente' ? htmlspecialchars($editar['apellido']) : '' ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= isset($editar) && $tipo_editar === 'cliente' ? htmlspecialchars($editar['email']) : '' ?>" required></div>
                <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="<?= isset($editar) && $tipo_editar === 'cliente' ? htmlspecialchars($editar['telefono']) : '' ?>"></div>
                <div class="form-group"><label>Dirección</label><input type="text" name="direccion" value="<?= isset($editar) && $tipo_editar === 'cliente' ? htmlspecialchars($editar['direccion']) : '' ?>"></div>
                <div class="form-group"><label>Ciudad</label><input type="text" name="ciudad" value="<?= isset($editar) && $tipo_editar === 'cliente' ? htmlspecialchars($editar['ciudad']) : '' ?>"></div>
            </div>
            <button type="submit" class="btn"><?= isset($editar) && $tipo_editar === 'cliente' ? '💾 Actualizar Cliente' : '💾 Guardar Cliente' ?></button>
            <?php if (isset($editar) && $tipo_editar === 'cliente'): ?><a href="sistema.php" class="btn cancelar-edicion">❌ Cancelar</a><?php endif; ?>
        </form>
        <h2 style="margin-top:2rem;">Listado de Clientes</h2>
        <?php if (empty($clientes)): ?><p class="empty">No hay clientes registrados.</p><?php else: ?>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Ciudad</th><th>Acciones</th></tr>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td><td><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></td><td><?= htmlspecialchars($c['email']) ?></td><td><?= htmlspecialchars($c['telefono']) ?></td><td><?= htmlspecialchars($c['ciudad']) ?></td>
                        <td class="accion-buttons">
                            <a href="sistema.php?editar_cliente=<?= $c['id'] ?>" class="btn-edit">✏️ Editar</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este cliente?');">
                                <input type="hidden" name="accion" value="eliminar_cliente"><input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-delete">🗑️ Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- TAB VENTAS -->
    <div id="tab-ventas" class="tab-content <?= $tab_activo === 'tab-ventas' ? 'active' : '' ?>">
        <h2>Nueva Venta</h2>
        <form method="POST">
            <input type="hidden" name="accion" value="venta">
            <div class="form-grid">
                <div class="form-group">
                    <label>Cliente</label>
                    <select name="id_cliente" required>
                        <option value="">-- Selecciona un cliente --</option>
                        <?php foreach ($clientes as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Producto</label>
                    <select name="id_producto" required>
                        <option value="">-- Selecciona un producto --</option>
                        <?php foreach ($productos as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?> - $<?= number_format($p['precio'], 2) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" min="1" value="1" required></div>
            </div>
            <button type="submit" class="btn">💾 Registrar Venta</button>
        </form>
        <h2 style="margin-top:2rem;">Historial de Ventas</h2>
        <?php if (empty($ventas)): ?><p class="empty">No hay ventas registradas.</p><?php else: ?>
            <table>
                <tr><th>ID</th><th>Cliente</th><th>Producto</th><th>Cantidad</th><th>Total</th><th>Fecha</th></tr>
                <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td><td><?= htmlspecialchars($v['cli_nombre'].' '.$v['cli_apellido']) ?></td><td><?= htmlspecialchars($v['prod_nombre']) ?></td><td><?= $v['cantidad'] ?></td><td>$<?= number_format($v['total'], 2) ?></td><td><?= $v['fecha_venta'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- 🔑 TAB USUARIOS (ADMINISTRADORES) -->
    <div id="tab-usuarios" class="tab-content <?= $tab_activo === 'tab-usuarios' ? 'active' : '' ?>">
        <h2>🔑 Gestión de Administradores</h2>
        <p style="color: #666; margin-bottom: 2rem;">Crea nuevos usuarios para que puedan acceder al panel de administración.</p>
        
        <form method="POST" style="background: #f9f9f9; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
            <input type="hidden" name="accion" value="nuevo_admin">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Ej: Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" name="usuario" placeholder="Ej: juan.perez" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
            </div>
            <button type="submit" class="btn">➕ Crear Nuevo Administrador</button>
        </form>

        <h3 style="margin-bottom: 1rem;">Lista de Administradores</h3>
        <?php if (empty($admins)): ?>
            <p class="empty">No hay administradores registrados.</p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Fecha de Creación</th></tr>
                <?php foreach ($admins as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td><code style="background: #f5f5f5; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($a['usuario']) ?></code></td>
                        <td><?= date('d/m/Y H:i', strtotime($a['fecha_creacion'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- TAB CONSULTAS -->
    <div id="tab-consultas" class="tab-content <?= $tab_activo === 'tab-consultas' ? 'active' : '' ?>">
        <div class="consulta-header">
            <h2>🔍 Centro de Consultas</h2>
            <p style="margin-top: 0.5rem;">Busca en cualquier tabla de forma instantánea</p>
        </div>
        <div class="stats-cards">
            <div class="stat-card"><div class="number"><?= count($categorias) ?></div><div class="label">Categorías</div></div>
            <div class="stat-card"><div class="number"><?= count($productos) ?></div><div class="label">Productos</div></div>
            <div class="stat-card"><div class="number"><?= count($clientes) ?></div><div class="label">Clientes</div></div>
            <div class="stat-card"><div class="number"><?= count($ventas) ?></div><div class="label">Ventas</div></div>
        </div>
        <h2>Selecciona qué consultar</h2>
        <div class="filter-tags" id="tableFilters">
            <span class="filter-tag active" data-table="all">📋 Todas</span>
            <span class="filter-tag" data-table="categorias">🏷️ Categorías</span>
            <span class="filter-tag" data-table="productos">👕 Productos</span>
            <span class="filter-tag" data-table="clientes">👤 Clientes</span>
            <span class="filter-tag" data-table="ventas">💰 Ventas</span>
        </div>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Escribe lo que buscas... (nombre, email, producto, etc.)">
        </div>
        <div class="results-count" id="resultsCount">
            Mostrando <strong>todos</strong> los registros
            <button class="btn-clear" id="clearSearch">✕ Limpiar</button>
        </div>
        <div id="searchResults"></div>
    </div>
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
            <a href="sistema.php">Administración</a>
        </div>
        <div class="footer-section">
            <h3>Contacto</h3>
            <p>📧 contacto@elegance.com</p>
            <p>📞 +52 555 123 4567</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Elegance Store. Todos los derechos reservados.</p>
    </div>
</footer>

<script src="assets/script.js"></script>
<script>
const datos = {
    categorias: <?= json_encode($categorias, JSON_UNESCAPED_UNICODE) ?>,
    productos: <?= json_encode($productos, JSON_UNESCAPED_UNICODE) ?>,
    clientes: <?= json_encode($clientes, JSON_UNESCAPED_UNICODE) ?>,
    ventas: <?= json_encode($ventas, JSON_UNESCAPED_UNICODE) ?>
};

let tablaActual = 'all';
let textoBusqueda = '';

document.querySelectorAll('#tableFilters .filter-tag').forEach(tag => {
    tag.addEventListener('click', function() {
        document.querySelectorAll('#tableFilters .filter-tag').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        tablaActual = this.dataset.table;
        ejecutarConsulta();
    });
});

const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', function() {
    textoBusqueda = this.value.toLowerCase().trim();
    ejecutarConsulta();
});

document.getElementById('clearSearch').addEventListener('click', function() {
    searchInput.value = '';
    textoBusqueda = '';
    ejecutarConsulta();
});

function ejecutarConsulta() {
    const contenedor = document.getElementById('searchResults');
    const contador = document.getElementById('resultsCount');
    let html = '';
    let totalResultados = 0;
    
    const tablasAConsultar = tablaActual === 'all' ? ['categorias', 'productos', 'clientes', 'ventas'] : [tablaActual];
    
    tablasAConsultar.forEach(tabla => {
        const resultados = datos[tabla].filter(item => {
            if (!textoBusqueda) return true;
            return Object.values(item).some(valor => {
                if (valor === null || valor === undefined) return false;
                return String(valor).toLowerCase().includes(textoBusqueda);
            });
        });
        totalResultados += resultados.length;
        if (resultados.length > 0) {
            html += generarTablaResultados(tabla, resultados);
        }
    });
    
    if (textoBusqueda) {
        contador.innerHTML = `Se encontraron <strong>${totalResultados}</strong> resultado(s) para "<strong>${textoBusqueda}</strong>" <button class="btn-clear" id="clearSearch">✕ Limpiar</button>`;
    } else {
        contador.innerHTML = `Mostrando <strong>${totalResultados}</strong> registros en total <button class="btn-clear" id="clearSearch">✕ Limpiar</button>`;
    }
    
    document.getElementById('clearSearch').addEventListener('click', function() {
        searchInput.value = '';
        textoBusqueda = '';
        ejecutarConsulta();
    });
    
    contenedor.innerHTML = html === '' ? '<div class="no-results">No se encontraron resultados para tu búsqueda</div>' : html;
}

function generarTablaResultados(tabla, resultados) {
    const titulos = { categorias: '🏷️ Categorías', productos: '👕 Productos', clientes: '👤 Clientes', ventas: '💰 Ventas' };
    const columnas = {
        categorias: ['ID', 'Nombre', 'Descripción'],
        productos: ['ID', 'Nombre', 'Precio', 'Stock', 'Talla', 'Categoría'],
        clientes: ['ID', 'Nombre completo', 'Email', 'Teléfono', 'Ciudad'],
        ventas: ['ID', 'Cliente', 'Producto', 'Cantidad', 'Total', 'Fecha']
    };
    
    let html = `<h2 style="margin-top: 1.5rem;">${titulos[tabla]} <span style="font-size: 0.9rem; color: #666;">(${resultados.length})</span></h2>`;
    html += '<table><tr>';
    columnas[tabla].forEach(col => html += `<th>${col}</th>`);
    html += '</tr>';
    
    resultados.forEach(item => {
        html += '<tr>';
        if (tabla === 'categorias') {
            html += `<td>${item.id}</td><td>${resaltar(item.nombre)}</td><td>${resaltar(item.descripcion || '')}</td>`;
        } else if (tabla === 'productos') {
            html += `<td>${item.id}</td><td>${resaltar(item.nombre)}</td><td>$${parseFloat(item.precio).toFixed(2)}</td><td>${item.stock}</td><td>${item.talla || '-'}</td><td>${resaltar(item.cat_nombre || 'Sin categoría')}</td>`;
        } else if (tabla === 'clientes') {
            html += `<td>${item.id}</td><td>${resaltar(item.nombre + ' ' + item.apellido)}</td><td>${resaltar(item.email)}</td><td>${item.telefono || '-'}</td><td>${resaltar(item.ciudad || '')}</td>`;
        } else if (tabla === 'ventas') {
            html += `<td>${item.id}</td><td>${resaltar(item.cli_nombre + ' ' + item.cli_apellido)}</td><td>${resaltar(item.prod_nombre)}</td><td>${item.cantidad}</td><td>$${parseFloat(item.total).toFixed(2)}</td><td>${item.fecha_venta}</td>`;
        }
        html += '</tr>';
    });
    html += '</table>';
    return html;
}

function resaltar(texto) {
    if (!textoBusqueda) return texto;
    const regex = new RegExp(`(${textoBusqueda})`, 'gi');
    return texto.replace(regex, '<span class="highlight">$1</span>');
}

ejecutarConsulta();
</script>
</body>
</html>