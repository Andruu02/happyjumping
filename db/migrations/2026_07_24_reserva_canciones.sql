-- Canciones que el usuario sugiere para la playlist de su fiesta (buscadas
-- en el catálogo de Spotify durante el Paso 2 de la reserva).
-- Ejecutar una sola vez en phpMyAdmin / consola MySQL de Hostinger.

CREATE TABLE reserva_canciones (
    id_cancion INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    artista VARCHAR(200) NOT NULL,
    spotify_url VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancion_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
