-- Recuperación de contraseña por correo (código de 6 dígitos, igual que la
-- verificación de cuenta nueva). Ejecutar una sola vez en phpMyAdmin /
-- consola MySQL de Hostinger.

ALTER TABLE usuarios
    ADD COLUMN codigo_reset VARCHAR(6) NULL AFTER codigo_verificacion,
    ADD COLUMN codigo_reset_expira DATETIME NULL AFTER codigo_reset;
