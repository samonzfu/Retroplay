-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS retroplay;
USE retroplay;

-- Crear usuario de base de datos (según Creacion de usuario.sql)
CREATE USER IF NOT EXISTS 'retroplay'@'localhost' IDENTIFIED BY 'retroplay123';
GRANT ALL PRIVILEGES ON retroplay.* TO 'retroplay'@'localhost';
FLUSH PRIVILEGES;

-- Crear tablas en el orden correcto para respetar claves foráneas

-- 1. Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT NOT NULL,
  nickname VARCHAR(255),
  correo VARCHAR(255),
  telefono VARCHAR(255),
  contrasena VARCHAR(255),
  PRIMARY KEY (id)
);

-- 2. Producto
CREATE TABLE IF NOT EXISTS producto (
  id INT NOT NULL,
  categoria VARCHAR(255),
  titulo VARCHAR(255),
  descripcion VARCHAR(255),
  precio VARCHAR(255),
  PRIMARY KEY (id)
);

-- 3. Reservas (depende de usuarios)
CREATE TABLE IF NOT EXISTS reservas (
  id INT NOT NULL,
  fecha VARCHAR(255),
  usuario_id INT,
  PRIMARY KEY (id),
  CONSTRAINT fk_reservas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- 4. LineaReservas (depende de reservas y producto)
CREATE TABLE IF NOT EXISTS lineareservas (
  id INT NOT NULL,
  reservas_id INT,
  producto_id INT,
  PRIMARY KEY (id),
  CONSTRAINT fk_lineareservas_reserva FOREIGN KEY (reservas_id) REFERENCES reservas(id),
  CONSTRAINT fk_lineareservas_producto FOREIGN KEY (producto_id) REFERENCES producto(id)
);
