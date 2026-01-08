-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS retroplay;
USE retroplay;

-- Crear tablas en el orden correcto para respetar claves foráneas

-- 1. Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT NOT NULL AUTO_INCREMENT,
  nickname VARCHAR(255),
  correo VARCHAR(255),
  telefono VARCHAR(255),
  contrasena VARCHAR(255),
  PRIMARY KEY (id)
);

-- 2. Producto
CREATE TABLE IF NOT EXISTS producto (
  id INT NOT NULL AUTO_INCREMENT,
  categoria VARCHAR(255),
  titulo VARCHAR(255),
  descripcion VARCHAR(255),
  precio VARCHAR(255),
  PRIMARY KEY (id)
);

-- 3. Reservas (depende de usuarios)
CREATE TABLE IF NOT EXISTS reservas (
  id INT NOT NULL AUTO_INCREMENT,
  fecha VARCHAR(255),
  usuario_id INT,
  PRIMARY KEY (id),
  CONSTRAINT fk_reservas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- 4. LineaReservas (depende de reservas y producto)
CREATE TABLE IF NOT EXISTS lineareservas (
  id INT NOT NULL AUTO_INCREMENT,
  reservas_id INT,
  producto_id INT,
  PRIMARY KEY (id),
  CONSTRAINT fk_lineareservas_reserva FOREIGN KEY (reservas_id) REFERENCES reservas(id),
  CONSTRAINT fk_lineareservas_producto FOREIGN KEY (producto_id) REFERENCES producto(id)
);

-- CREAR USUARIO:
CREATE USER 
'retroplay'@'localhost' 
IDENTIFIED  BY 'Retroplay123$';

GRANT USAGE ON *.* TO 'retroplay'@'localhost';


ALTER USER 'retroplay'@'localhost' 
REQUIRE NONE 
WITH MAX_QUERIES_PER_HOUR 0 
MAX_CONNECTIONS_PER_HOUR 0 
MAX_UPDATES_PER_HOUR 0 
MAX_USER_CONNECTIONS 0;

-- dale acceso a la base de datos retroplay
GRANT ALL PRIVILEGES ON retroplay.* 
TO 'retroplay'@'localhost';

-- recarga la tabla de privilegios
FLUSH PRIVILEGES;


-- INSERT DE LOS PRODUCTOS (todos con el mismo precio por el tema del alquiler)
INSERT INTO producto (categoria, titulo, descripcion, precio) VALUES
-- CONSOLAS (20)
('consola', 'Nintendo NES', 'Consola Nintendo Entertainment System clásica', '15'),
('consola', 'Super Nintendo', 'Consola Super Nintendo original', '15'),
('consola', 'Sega Mega Drive', 'Consola Sega Mega Drive de 16 bits', '15'),
('consola', 'Game Boy Classic', 'Consola portátil Game Boy original', '15'),
('consola', 'Game Boy Color', 'Consola portátil Game Boy Color', '15'),
('consola', 'Nintendo 64', 'Consola Nintendo 64 clásica', '15'),
('consola', 'PlayStation 1', 'Primera consola Sony PlayStation', '15'),
('consola', 'Sega Saturn', 'Consola Sega Saturn', '15'),
('consola', 'Sega Dreamcast', 'Última consola de Sega', '15'),
('consola', 'Atari 2600', 'Consola Atari clásica', '15'),
('consola', 'Neo Geo AES', 'Consola Neo Geo doméstica', '15'),
('consola', 'Neo Geo Pocket', 'Consola portátil Neo Geo', '15'),
('consola', 'Game Gear', 'Consola portátil de Sega', '15'),
('consola', 'PlayStation One', 'Versión compacta de PS1', '15'),
('consola', 'SNES Mini', 'Consola Super Nintendo mini', '15'),
('consola', 'NES Mini', 'Consola NES mini', '15'),
('consola', 'Commodore 64', 'Ordenador retro para videojuegos', '15'),
('consola', 'Amiga 500', 'Ordenador Amiga para juegos clásicos', '15'),
('consola', 'Master System', 'Consola Sega Master System', '15'),
('consola', 'PSP', 'Consola portátil PlayStation', '15'),
-- VIDEOJUEGOS (20)
('videojuego', 'Super Mario Bros', 'Plataformas clásico para NES', '5'),
('videojuego', 'Super Mario World', 'Plataformas para Super Nintendo', '5'),
('videojuego', 'The Legend of Zelda', 'Aventura clásica para NES', '5'),
('videojuego', 'Zelda Ocarina of Time', 'Aventura para Nintendo 64', '5'),
('videojuego', 'Sonic the Hedgehog', 'Plataformas para Mega Drive', '5'),
('videojuego', 'Sonic 2', 'Secuela clásica para Mega Drive', '5'),
('videojuego', 'Street Fighter II', 'Juego de lucha para SNES', '5'),
('videojuego', 'Mortal Kombat', 'Juego de lucha clásico', '5'),
('videojuego', 'Pac-Man', 'Arcade clásico', '5'),
('videojuego', 'Donkey Kong', 'Clásico de plataformas', '5'),
('videojuego', 'Mega Man 2', 'Acción y plataformas para NES', '5'),
('videojuego', 'Castlevania', 'Acción y terror clásico', '5'),
('videojuego', 'Final Fantasy VII', 'RPG clásico de PS1', '5'),
('videojuego', 'Chrono Trigger', 'RPG clásico de SNES', '5'),
('videojuego', 'Tetris', 'Puzzle clásico', '5'),
('videojuego', 'Metal Gear Solid', 'Acción y sigilo para PS1', '5'),
('videojuego', 'Resident Evil', 'Survival horror clásico', '5'),
('videojuego', 'GoldenEye 007', 'Shooter para Nintendo 64', '5'),
('videojuego', 'Crash Bandicoot', 'Plataformas para PS1', '5'),
('videojuego', 'Spyro the Dragon', 'Plataformas para PS1', '5');