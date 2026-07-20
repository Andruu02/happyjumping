-- Suscripciones de notificaciones push del navegador (Web Push / VAPID).
-- Ejecutar una sola vez en phpMyAdmin / consola MySQL de Hostinger.

CREATE TABLE push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    endpoint VARCHAR(600) NOT NULL,
    p256dh VARCHAR(255) NOT NULL,
    auth VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_endpoint (endpoint(255)),
    CONSTRAINT fk_push_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
