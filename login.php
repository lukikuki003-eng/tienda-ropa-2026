<?php
session_start();
require 'config.php';

$error = '';

// Si ya está logueado, redirigir al panel
if (isset($_SESSION['admin_logueado']) && $_SESSION['admin_logueado'] === true) {
    header('Location: sistema.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = '⚠️ Por favor completa todos los campos';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // ✅ Login exitoso
                $_SESSION['admin_logueado'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                
                header('Location: sistema.php');
                exit;
            } else {
                $error = '❌ Usuario o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $error = '❌ Error de conexión: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Elegance Store Admin</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header .logo {
            width: 80px;
            height: 80px;
            background: #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
        }
        .login-header h1 {
            color: #1a1a1a;
            font-size: 1.8rem;
            margin: 0 0 0.5rem;
        }
        .login-header p {
            color: #666;
            margin: 0;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #1a1a1a;
            color: #d4af37;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-login:hover {
            background: #d4af37;
            color: #1a1a1a;
            transform: translateY(-2px);
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 0.875rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
            border-left: 4px solid #c33;
        }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        .login-footer a {
            color: #666;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .login-footer a:hover {
            color: #d4af37;
        }
        .credenciales-demo {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
        .credenciales-demo strong {
            color: #1a1a1a;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <div class="logo">👗</div>
        <h1>Elegance Store</h1>
        <p>Panel de Administración</p>
    </div>

    <?php if ($error): ?>
        <div class="error-message"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="usuario">👤 Usuario</label>
            <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">🔒 Contraseña</label>
            <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
        </div>

        <button type="submit" class="btn-login">Iniciar Sesión</button>
    </form>

    <div class="credenciales-demo">
        <strong>🔑 Credenciales por defecto:</strong><br>
        Usuario: <code>admin</code><br>
        Contraseña: <code>admin123</code>
    </div>

    <div class="login-footer">
        <a href="index.php">← Volver a la tienda</a>
    </div>
</div>

</body>
</html>