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
}
