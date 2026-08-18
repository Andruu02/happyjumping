-- Métodos de pago: Yape automático vía Mercado Pago (Checkout API) y Plin
-- manual (solo QR, sin verificación). Guardamos con qué método se pagó y,
-- si fue Yape/Mercado Pago, el id del pago devuelto por su API.
-- Ejecutar una sola vez en phpMyAdmin / consola MySQL de Hostinger.

ALTER TABLE pagos
    ADD COLUMN metodo_pago VARCHAR(20) NOT NULL DEFAULT 'yape' AFTER estado,
    ADD COLUMN mp_payment_id VARCHAR(50) NULL AFTER metodo_pago;
