-- Tokens de dispositivo para notificaciones push nativas (Firebase Cloud
-- Messaging) desde la app Flutter. Complementa a push_subscriptions, que es
-- solo para el navegador/PWA (Web Push).
-- Ejecutar una sola vez en phpMyAdmin / consola MySQL de Hostinger.

CREATE TABLE fcm_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    id_usuario INT NULL,
    plataforma VARCHAR(20) NOT NULL DEFAULT 'android',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_token (token),
    CONSTRAINT fk_fcm_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
