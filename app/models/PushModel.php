<?php
class PushModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /** Guarda o actualiza una suscripción push (el navegador puede reenviar la misma). */
    public function guardarSuscripcion($endpoint, $p256dh, $auth, $id_usuario, $user_agent) {
        $this->query("INSERT INTO push_subscriptions (id_usuario, endpoint, p256dh, auth, user_agent)
                      VALUES (:id_usuario, :endpoint, :p256dh, :auth, :user_agent)
                      ON DUPLICATE KEY UPDATE
                          p256dh = VALUES(p256dh),
                          auth = VALUES(auth),
                          id_usuario = VALUES(id_usuario),
                          user_agent = VALUES(user_agent)");
        $this->bind(':id_usuario', $id_usuario, $id_usuario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $this->bind(':endpoint',   $endpoint);
        $this->bind(':p256dh',     $p256dh);
        $this->bind(':auth',       $auth);
        $this->bind(':user_agent', $user_agent);
        return $this->execute();
    }

    /** Elimina una suscripción (el usuario desactivó las notificaciones o el endpoint expiró). */
    public function eliminarSuscripcion($endpoint) {
        $this->query("DELETE FROM push_subscriptions WHERE endpoint = :endpoint");
        $this->bind(':endpoint', $endpoint);
        return $this->execute();
    }

    /** Todas las suscripciones activas, para enviarles una notificación. */
    public function obtenerSuscripciones() {
        $this->query("SELECT id, endpoint, p256dh, auth FROM push_subscriptions ORDER BY id ASC");
        return $this->resultSet();
    }

    public function contarSuscripciones() {
        $this->query("SELECT COUNT(*) AS total FROM push_subscriptions");
        return (int) $this->single()->total;
    }

    // ── FCM (app Flutter nativa) ──────────────────────────────────────────────

    /** Guarda o actualiza un token FCM (la app lo reenvía en cada Home / rotación). */
    public function guardarTokenFcm($token, $plataforma, $id_usuario) {
        $this->query("INSERT INTO fcm_tokens (token, id_usuario, plataforma)
                      VALUES (:token, :id_usuario, :plataforma)
                      ON DUPLICATE KEY UPDATE
                          id_usuario = COALESCE(VALUES(id_usuario), id_usuario),
                          plataforma = VALUES(plataforma),
                          updated_at = NOW()");
        $this->bind(':token',      $token);
        $this->bind(':id_usuario', $id_usuario, $id_usuario === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $this->bind(':plataforma', $plataforma);
        return $this->execute();
    }

    /** Elimina un token FCM (Firebase reporta que ya no está registrado). */
    public function eliminarTokenFcm($token) {
        $this->query("DELETE FROM fcm_tokens WHERE token = :token");
        $this->bind(':token', $token);
        return $this->execute();
    }

    /** Todos los tokens FCM activos, para enviarles una notificación. */
    public function obtenerTokensFcm() {
        $this->query("SELECT token FROM fcm_tokens ORDER BY id ASC");
        return $this->resultSet();
    }

    public function contarTokensFcm() {
        $this->query("SELECT COUNT(*) AS total FROM fcm_tokens");
        return (int) $this->single()->total;
    }
}
