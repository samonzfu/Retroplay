-- Migración para bases de datos creadas con la versión anterior de setup_final.sql.
-- Añade cantidad y semanas a cada línea de reserva y el total a la reserva.
-- (Las instalaciones nuevas ya lo incluyen en setup_final.sql: no hace falta ejecutarlo.)
USE retroplay;

ALTER TABLE reservas
  ADD COLUMN total DECIMAL(8,2) NOT NULL DEFAULT 0;

ALTER TABLE lineareservas
  ADD COLUMN cantidad INT NOT NULL DEFAULT 1,
  ADD COLUMN semanas INT NOT NULL DEFAULT 1;
