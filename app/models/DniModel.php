<?php
class DniModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Busca un DNI: primero en caché local, si no existe consulta la API
     * externa de APIsPERU y guarda el resultado.
     * Devuelve un array con nombres/apellidos, o null si no se encontró.
     */
    public function consultar($dni) {
        $cache = $this->buscarEnCache($dni);
        if ($cache !== null) {
            return $cache;
        }

        $resultado = $this->consultarApiExterna($dni);

        if ($resultado === null) {
            $this->guardarEnCache($dni, null, null, null, false);
            return null;
        }

        $this->guardarEnCache(
            $dni,
            $resultado['nombres'],
            $resultado['apellidoPaterno'],
            $resultado['apellidoMaterno'],
            true
        );

        return $resultado;
    }

    private function buscarEnCache($dni) {
        $this->query("SELECT * FROM consultas_dni WHERE dni = :dni LIMIT 1");
        $this->bind(':dni', $dni);
        $row = $this->single();

        if (!$row) {
            return null;
        }

        if (!$row->encontrado) {
            return null;
        }

        return [
            'nombres'          => $row->nombres,
            'apellidoPaterno'  => $row->apellido_paterno,
            'apellidoMaterno'  => $row->apellido_materno,
        ];
    }

    private function guardarEnCache($dni, $nombres, $apellidoPaterno, $apellidoMaterno, $encontrado) {
        $this->query("INSERT INTO consultas_dni (dni, nombres, apellido_paterno, apellido_materno, encontrado)
                      VALUES (:dni, :nombres, :apellido_paterno, :apellido_materno, :encontrado)
                      ON DUPLICATE KEY UPDATE
                        nombres = :nombres2, apellido_paterno = :apellido_paterno2,
                        apellido_materno = :apellido_materno2, encontrado = :encontrado2");
        $this->bind(':dni', $dni);
        $this->bind(':nombres', $nombres);
        $this->bind(':apellido_paterno', $apellidoPaterno);
        $this->bind(':apellido_materno', $apellidoMaterno);
        $this->bind(':encontrado', $encontrado);
        $this->bind(':nombres2', $nombres);
        $this->bind(':apellido_paterno2', $apellidoPaterno);
        $this->bind(':apellido_materno2', $apellidoMaterno);
        $this->bind(':encontrado2', $encontrado);
        $this->execute();
    }

    private function consultarApiExterna($dni) {
        $url = APISPERU_DNI_URL . urlencode($dni) . '?token=' . urlencode(APISPERU_TOKEN);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);

        if (!$data || empty($data['success']) || empty($data['nombres'])) {
            return null;
        }

        return [
            'nombres'         => $data['nombres'],
            'apellidoPaterno' => $data['apellidoPaterno'] ?? '',
            'apellidoMaterno' => $data['apellidoMaterno'] ?? '',
        ];
    }
}
