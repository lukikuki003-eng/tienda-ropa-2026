CREATE DATABASE IF NOT EXISTS tienda_ropa CHARACTER SET utf8mb4;
USE tienda_ropa;

-- Tabla 1: Categorías de ropa
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Tabla 2: Productos (relacionada con categorías)
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    talla VARCHAR(10),
    imagen VARCHAR(255),
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabla 3: Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(200),
    ciudad VARCHAR(50)
);

-- Tabla 4: Ventas (relaciona clientes y productos)
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_venta DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- Datos de ejemplo
INSERT INTO categorias (nombre, descripcion) VALUES
('Vestidos', 'Vestidos para toda ocasión'),
('Camisas', 'Camisas casuales y formales'),
('Pantalones', 'Pantalones de diferentes estilos'),
('Accesorios', 'Bolsos, cinturones y más');