<?php
/*
|--------------------------------------------------------------------------
| Modelo de Comentarios (sección tipo blog del inicio)
|--------------------------------------------------------------------------
*/

class ComentarioModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Últimos comentarios, con el nombre de quien lo escribió, del más
     * reciente al más antiguo.
     */
    public function obtenerComentarios($limite = 20) {
        $this->query("SELECT c.id_comentario, c.comentario, c.fecha_creacion, u.nombre
                      FROM comentarios c
                      INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                      ORDER BY c.fecha_creacion DESC
                      LIMIT :limite");
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }

    public function crearComentario($id_usuario, $texto) {
        $this->query("INSERT INTO comentarios (id_usuario, comentario) VALUES (:id_usuario, :texto)");
        $this->bind(':id_usuario', $id_usuario);
        $this->bind(':texto', $texto);
        return $this->execute();
    }
}
?>
