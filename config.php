<?php
// 🔐 DATOS DE CONEXIÓN (se adaptan automáticamente a donde esté la página)
// En Railway: usa las variables de entorno automáticas
// En XAMPP local: usa los valores por defecto (localhost, root, sin contraseña)
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'tienda_ropa';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';  // ✅ SIN CONTRASEÑA POR DEFECTO

try {
    // 1. Conectar a la base de datos
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 2. 🔒 Crear tabla de administradores si no existe (necesario para el login)
    $pdo->exec("CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Crear admin por defecto si la tabla está vacía
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM administradores");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO administradores (usuario, password, nombre) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $password_hash, 'Administrador Principal']);
    }

} catch (PDOException $e) {
    // En producción, no mostramos el error real al usuario por seguridad
    die("❌ Error de conexión a la base de datos. Contacta al administrador.");
}
?>
