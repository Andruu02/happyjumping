-- Agrega el campo DNI al registro de usuarios y una tabla de caché
-- para las consultas a la API de APIsPERU (RENIEC).
-- Ejecutar una sola vez en phpMyAdmin / consola MySQL de Hostinger.

ALTER TABLE usuarios
    ADD COLUMN dni VARCHAR(8) NULL UNIQUE AFTER correo;

CREATE TABLE consultas_dni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(8) NOT NULL,
    nombres VARCHAR(150) NULL,
    apellido_paterno VARCHAR(100) NULL,
    apellido_materno VARCHAR(100) NULL,
    encontrado TINYINT(1) NOT NULL DEFAULT 0,
    fecha_consulta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_dni (dni)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
