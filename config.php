<?php
try {
    // 🌐 ¿Estamos en Railway? (siempre tiene MYSQL_URL)
    if (getenv('MYSQL_URL')) {
        $url = getenv('MYSQL_URL');
        $parsed = parse_url($url);
        $host = $parsed['host'];
        $port = $parsed['port'] ?? 3306;
        $dbname = ltrim($parsed['path'], '/');
        $user = $parsed['user'] ?? 'root';
        $pass = $parsed['pass'] ?? '';
    }
    // 💻 ¿Estamos en local? (usamos los datos de XAMPP con contraseña)
    else {
        $host = 'localhost';
        $port = '3306';
        $dbname = 'tienda_ropa';
        $user = 'root';
        $pass = 'luk251709';  // ⚠️ Tu contraseña local
    }

    // Conectar
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 🔒 Crear tabla de administradores si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Crear admin por defecto si la tabla está vacía
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM administradores");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO administradores (usuario, password, nombre) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $password_hash, 'Administrador Principal']);
    }

} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>