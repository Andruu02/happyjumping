<?php
/*
|--------------------------------------------------------------------------
| Modelo Base (¡VERSIÓN CORREGIDA CON 'PROTECTED'!)
|--------------------------------------------------------------------------
|
| Esto soluciona el error 'Call to a member function on null'.
|
*/
 
class Model {
    
    /*
     * ======================================================
     * ¡AQUÍ ESTÁ EL CAMBIO!
     * Cambiamos 'private' por 'protected' para que las clases
     * "hijas" (como ReservaModel) puedan heredar la conexión.
     * ======================================================
     */
    protected $dbh; // Database Handler
    protected $stmt; // Statement
    protected $error;
 
    public function __construct() {
        $dsn = $this->construirDsn();
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ];
 
        try {
            // Ahora $this->dbh será visible para ReservaModel
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Forzar zona horaria Perú (UTC-5) para todas las consultas
            $this->dbh->exec("SET time_zone = '-05:00'");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die('Error de conexión a la BD: ' . $this->error);
        }
    }
    
    /** Prepara la consulta SQL */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
 
    /** Vincula los valores (para prevenir inyección SQL) */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
 
    /** Ejecuta la consulta preparada (con reconexión automática si MySQL cerró la conexión) */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            // Error 2006: MySQL server has gone away (conexión inactiva/cerrada por timeout)
            // Error 2013: Lost connection to MySQL server during query
            if (strpos($e->getMessage(), '2006') !== false || strpos($e->getMessage(), '2013') !== false) {
                $this->reconectar();
                // El statement original quedó atado a la conexión vieja: hay que re-prepararlo
                $sql = $this->stmt->queryString;
                $this->stmt = $this->dbh->prepare($sql);
                return $this->stmt->execute();
            }
            throw $e;
        }
    }

    /** Reabre la conexión PDO si se perdió */
    protected function reconectar() {
        $dsn = $this->construirDsn();
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ];
        $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        $this->dbh->exec("SET time_zone = '-05:00'");
    }

    /**
     * Arma el DSN de conexión. Si DB_SOCKET está definido (Cloud SQL en
     * Cloud Run), se conecta por socket Unix; si no, por host/puerto TCP
     * (comportamiento de siempre en Hostinger).
     */
    private function construirDsn() {
        if (defined('DB_SOCKET') && DB_SOCKET !== '') {
            return 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        }
        $puerto = defined('DB_PORT') && DB_PORT !== '' ? ';port=' . DB_PORT : '';
        return 'mysql:host=' . DB_HOST . $puerto . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    }

    /** Obtiene un solo registro */
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
 
    /** Obtiene el número de filas afectadas */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
 
    /** Obtiene TODOS los registros como resultado */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>