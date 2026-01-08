CREATE TABLE reservas (
  id INT,
  fecha VARCHAR(255),
  usuario_id INT,
  PRIMARY KEY (id),
  CONSTRAINT fk_reservas_1 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE usuarios (
  id INT,
  nickname VARCHAR(255),
  correo VARCHAR(255),
  telefono VARCHAR(255),
  contrasena VARCHAR(255),

  PRIMARY KEY (id)
);

CREATE TABLE producto (
  id INT,
  categoria VARCHAR(255),
  titulo VARCHAR(255),
  descripcion VARCHAR(255),
  precio VARCHAR(255),
  PRIMARY KEY (id)
);

CREATE TABLE lineareservas (
  id INT,
  reservas_id INT,
  producto_id INT,
  PRIMARY KEY (id),
  CONSTRAINT fk_lineareservas_1 FOREIGN KEY (reservas_id) REFERENCES reservas(id),
  CONSTRAINT fk_lineareservas_2 FOREIGN KEY (producto_id) REFERENCES producto(id)
);
