<?php
class UsuarioModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function login($correo, $password) {
        $this->query("SELECT * FROM usuarios WHERE correo = :correo");
        $this->bind(':correo', $correo);
        $row = $this->single();
        if ($row && password_verify($password, $row->password)) return $row;
        return false;
    }

    public function register($datos) {
        $this->query("INSERT INTO usuarios (nombre, correo, dni, password, is_verificado, codigo_verificacion)
                      VALUES (:nombre, :correo, :dni, :password, 0, :codigo)");
        $this->bind(':nombre',  $datos['nombre']);
        $this->bind(':correo',  $datos['correo']);
        $this->bind(':dni',     $datos['dni']);
        $this->bind(':password',$datos['password']);
        $this->bind(':codigo',  $datos['codigo']);
        return $this->execute();
    }

    public function findUserByDni($dni) {
        $this->query("SELECT id_usuario FROM usuarios WHERE dni = :dni");
        $this->bind(':dni', $dni);
        $this->single();
        return $this->rowCount() > 0;
    }

    public function verificarCodigo($correo, $codigo) {
        $this->query("SELECT id_usuario FROM usuarios
                      WHERE correo = :correo AND codigo_verificacion = :codigo
                      LIMIT 1");
        $this->bind(':correo', $correo);
        $this->bind(':codigo', $codigo);
        $row = $this->single();
        if ($row) {
            $this->query("UPDATE usuarios
                          SET is_verificado = 1, codigo_verificacion = NULL
                          WHERE correo = :correo");
            $this->bind(':correo', $correo);
            $this->execute();
            return true;
        }
        return false;
    }

    public function actualizarCodigo($correo, $codigo) {
        $this->query("UPDATE usuarios SET codigo_verificacion = :codigo WHERE correo = :correo");
        $this->bind(':codigo', $codigo);
        $this->bind(':correo', $correo);
        return $this->execute();
    }

    public function findUserByEmail($correo) {
        $this->query("SELECT id_usuario FROM usuarios WHERE correo = :correo");
        $this->bind(':correo', $correo);
        $this->single();
        return $this->rowCount() > 0;
    }

    // ── Recuperación de contraseña ──────────────────────────────────────────

    /** Guarda el código de recuperación y su expiración (15 min desde ahora). */
    public function guardarCodigoReset($correo, $codigo) {
        $this->query("UPDATE usuarios
                      SET codigo_reset = :codigo, codigo_reset_expira = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                      WHERE correo = :correo");
        $this->bind(':codigo', $codigo);
        $this->bind(':correo', $correo);
        return $this->execute();
    }

    /** true si el código es correcto y todavía no expiró. */
    public function codigoResetValido($correo, $codigo) {
        $this->query("SELECT id_usuario FROM usuarios
                      WHERE correo = :correo AND codigo_reset = :codigo
                        AND codigo_reset_expira IS NOT NULL AND codigo_reset_expira >= NOW()
                      LIMIT 1");
        $this->bind(':correo', $correo);
        $this->bind(':codigo', $codigo);
        $this->single();
        return $this->rowCount() > 0;
    }

    /** Cambia la contraseña y limpia el código (ya usado, que no sirva dos veces). */
    public function actualizarPassword($correo, $passwordHash) {
        $this->query("UPDATE usuarios
                      SET password = :password, codigo_reset = NULL, codigo_reset_expira = NULL
                      WHERE correo = :correo");
        $this->bind(':password', $passwordHash);
        $this->bind(':correo', $correo);
        return $this->execute();
    }
}
