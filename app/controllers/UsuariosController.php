<?php
class UsuariosController extends Controller {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    /**
     * Valida que un destino de "volver aquí después de iniciar sesión" sea
     * una ruta relativa del propio sitio (nunca una URL externa), para
     * evitar un open redirect.
     */
    private function redirectSeguro($redirect) {
        $redirect = trim((string) $redirect);
        if ($redirect === '' || $redirect[0] !== '/' || (isset($redirect[1]) && $redirect[1] === '/')) {
            return '';
        }
        return $redirect;
    }

    // ── LOGIN ─────────────────────────────────────────────────────────────────
    public function login() {
        $redirect = $this->redirectSeguro($_GET['redirect'] ?? ($_POST['redirect'] ?? ''));

        if (isset($_SESSION['id_usuario'])) {
            $destino = $redirect ?: (($_SESSION['usuario_rol'] ?? '') === 'admin' ? '/admin' : '/perfil');
            header('Location: ' . URL_ROOT . $destino);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'titulo'         => 'Iniciar Sesion - Happy&Jumping',
                'correo'         => trim($_POST['correo']),
                'password'       => trim($_POST['password']),
                'correo_error'   => '',
                'password_error' => '',
                'redirect'       => $redirect,
            ];

            if (empty($datos['correo']))
                $datos['correo_error'] = 'Por favor, ingresa tu correo.';

            if (empty($datos['password']))
                $datos['password_error'] = 'Por favor, ingresa tu contraseña.';

            if (empty($datos['correo_error']) && empty($datos['password_error'])) {
                $user = $this->usuarioModel->login($datos['correo'], $datos['password']);
                if ($user) {
                    // Si no está verificado → mandar a verificar
                    if (!$user->is_verificado) {
                        $_SESSION['correo_verificacion'] = $user->correo;
                        if ($redirect) $_SESSION['redirect_post_verificacion'] = $redirect;
                        header('Location: ' . URL_ROOT . '/usuarios/verificar');
                        exit();
                    }
                    $this->createUsuarioSession($user);
                    $destino = $redirect ?: ($user->rol === 'admin' ? '/admin' : '/perfil');
                    header('Location: ' . URL_ROOT . $destino);
                    exit();
                } else {
                    $datos['password_error'] = 'Correo o contraseña incorrectos. Intenta de nuevo.';
                    $this->view('usuarios/login', $datos);
                }
            } else {
                $this->view('usuarios/login', $datos);
            }
        } else {
            $this->view('usuarios/login', [
                'titulo'         => 'Iniciar Sesion - Happy&Jumping',
                'correo'         => '', 'password'       => '',
                'correo_error'   => '', 'password_error' => '',
                'redirect'       => $redirect,
            ]);
        }
    }

    // ── REGISTER ──────────────────────────────────────────────────────────────
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'titulo'                 => 'Crear Cuenta - Happy&Jumping',
                'nombre'                 => trim($_POST['nombre']),
                'correo'                 => trim($_POST['correo']),
                'dni'                    => trim($_POST['dni'] ?? ''),
                'password'               => trim($_POST['password']),
                'confirm_password'       => trim($_POST['confirm_password']),
                'nombre_error'           => '', 'correo_error'           => '', 'dni_error' => '',
                'password_error'         => '', 'confirm_password_error' => ''
            ];

            if (empty($datos['dni']))
                $datos['dni_error'] = 'Por favor, ingresa tu DNI.';
            elseif (!preg_match('/^\d{8}$/', $datos['dni']))
                $datos['dni_error'] = 'El DNI debe tener 8 dígitos.';
            elseif ($this->usuarioModel->findUserByDni($datos['dni']))
                $datos['dni_error'] = 'Este DNI ya está registrado.';

            if (empty($datos['nombre']))
                $datos['nombre_error'] = 'Por favor, ingresa tu nombre.';

            if (empty($datos['correo']))
                $datos['correo_error'] = 'Por favor, ingresa tu correo.';
            elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL))
                $datos['correo_error'] = 'El correo no es válido.';
            elseif ($this->usuarioModel->findUserByEmail($datos['correo']))
                $datos['correo_error'] = 'Este correo ya está registrado.';

            if (empty($datos['password']))
                $datos['password_error'] = 'Por favor, ingresa una contraseña.';
            elseif (strlen($datos['password']) < 8)
                $datos['password_error'] = 'La contraseña debe tener al menos 8 caracteres.';

            if (empty($datos['confirm_password']))
                $datos['confirm_password_error'] = 'Por favor, confirma la contraseña.';
            elseif ($datos['password'] != $datos['confirm_password'])
                $datos['confirm_password_error'] = 'Las contraseñas no coinciden.';

            $sinErrores = empty($datos['nombre_error'])
                       && empty($datos['correo_error'])
                       && empty($datos['dni_error'])
                       && empty($datos['password_error'])
                       && empty($datos['confirm_password_error']);

            if ($sinErrores) {
                $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
                $datos['codigo']   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                if ($this->usuarioModel->register($datos)) {
                    // Enviar correo con código
                    require_once APP_ROOT . '/../app/core/Mailer.php';
                    Mailer::enviarCodigoVerificacion(
                        $datos['correo'],
                        $datos['nombre'],
                        $datos['codigo']
                    );

                    $_SESSION['correo_verificacion'] = $datos['correo'];
                    header('Location: ' . URL_ROOT . '/usuarios/verificar');
                    exit();
                } else {
                    $datos['correo_error'] = 'Error al crear la cuenta. Intenta de nuevo.';
                    $this->view('usuarios/register', $datos);
                }
            } else {
                $this->view('usuarios/register', $datos);
            }

        } else {
            $this->view('usuarios/register', [
                'titulo'                 => 'Crear Cuenta - Happy&Jumping',
                'nombre'                 => '', 'correo'                 => '', 'dni' => '',
                'password'               => '', 'confirm_password'       => '',
                'nombre_error'           => '', 'correo_error'           => '', 'dni_error' => '',
                'password_error'         => '', 'confirm_password_error' => ''
            ]);
        }
    }

    // ── VERIFICAR ─────────────────────────────────────────────────────────────
    public function verificar() {
        if (!isset($_SESSION['correo_verificacion'])) {
            header('Location: ' . URL_ROOT . '/usuarios/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Reenviar código
            if (isset($_POST['reenviar'])) {
                $nuevo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $this->usuarioModel->actualizarCodigo($_SESSION['correo_verificacion'], $nuevo);
                require_once APP_ROOT . '/../app/core/Mailer.php';
                Mailer::enviarCodigoVerificacion(
                    $_SESSION['correo_verificacion'],
                    '',
                    $nuevo
                );
                $datos = ['titulo' => 'Verificar Cuenta', 'error' => '', 'exito' => 'Código reenviado. Revisa tu bandeja.'];
                $this->view('usuarios/verificar', $datos);
                return;
            }

            // Verificar código
            $codigo = trim($_POST['codigo'] ?? '');
            $correo = $_SESSION['correo_verificacion'];

            if ($this->usuarioModel->verificarCodigo($correo, $codigo)) {
                unset($_SESSION['correo_verificacion']);
                $redirect = $this->redirectSeguro($_SESSION['redirect_post_verificacion'] ?? '');
                unset($_SESSION['redirect_post_verificacion']);
                $extra = $redirect ? '&redirect=' . urlencode($redirect) : '';
                header('Location: ' . URL_ROOT . '/usuarios/login?verificado=1' . $extra);
                exit();
            } else {
                $datos = ['titulo' => 'Verificar Cuenta', 'error' => 'Código incorrecto. Inténtalo de nuevo.', 'exito' => ''];
                $this->view('usuarios/verificar', $datos);
            }
        } else {
            $datos = ['titulo' => 'Verificar Cuenta', 'error' => '', 'exito' => ''];
            $this->view('usuarios/verificar', $datos);
        }
    }

    // ── RECOVER ───────────────────────────────────────────────────────────────

    /**
     * Paso 1: pide el correo. Por seguridad, la respuesta es la misma exista
     * o no ese correo en la BD (si no se hiciera así, cualquiera podría usar
     * este formulario para averiguar qué correos están registrados). El
     * código solo se genera y se manda de verdad cuando el correo sí existe.
     */
    public function recover() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo'] ?? '');

            if (!empty($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL) && $this->usuarioModel->findUserByEmail($correo)) {
                $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $this->usuarioModel->guardarCodigoReset($correo, $codigo);

                require_once APP_ROOT . '/core/Mailer.php';
                Mailer::enviarCodigoRecuperacion($correo, '', $codigo);
            }

            $_SESSION['correo_reset'] = $correo;
            header('Location: ' . URL_ROOT . '/usuarios/recover-codigo');
            exit();
        }

        $datos = ['titulo' => 'Recuperar Contraseña - Happy&Jumping'];
        $this->view('usuarios/recover', $datos);
    }

    /** Paso 2: ingresar el código de 6 dígitos + la nueva contraseña. */
    public function recoverCodigo() {
        if (!isset($_SESSION['correo_reset'])) {
            header('Location: ' . URL_ROOT . '/usuarios/recover');
            exit();
        }

        $correo = $_SESSION['correo_reset'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Reenviar código
            if (isset($_POST['reenviar'])) {
                if ($this->usuarioModel->findUserByEmail($correo)) {
                    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $this->usuarioModel->guardarCodigoReset($correo, $codigo);
                    require_once APP_ROOT . '/core/Mailer.php';
                    Mailer::enviarCodigoRecuperacion($correo, '', $codigo);
                }
                $datos = ['titulo' => 'Recuperar Contraseña', 'correo' => $correo, 'error' => '', 'exito' => 'Código reenviado. Revisa tu bandeja.'];
                $this->view('usuarios/recover_codigo', $datos);
                return;
            }

            $codigo           = trim($_POST['codigo'] ?? '');
            $password         = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');
            $error            = '';

            if (empty($codigo) || strlen($codigo) !== 6) {
                $error = 'Ingresa el código de 6 dígitos que te enviamos.';
            } elseif (empty($password) || strlen($password) < 8) {
                $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
            } elseif ($password !== $confirm_password) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (!$this->usuarioModel->codigoResetValido($correo, $codigo)) {
                $error = 'Código incorrecto o vencido. Solicita uno nuevo.';
            }

            if ($error === '') {
                $this->usuarioModel->actualizarPassword($correo, password_hash($password, PASSWORD_DEFAULT));
                unset($_SESSION['correo_reset']);
                header('Location: ' . URL_ROOT . '/usuarios/login?reset=1');
                exit();
            }

            $datos = ['titulo' => 'Recuperar Contraseña', 'correo' => $correo, 'error' => $error, 'exito' => ''];
            $this->view('usuarios/recover_codigo', $datos);
        } else {
            $datos = ['titulo' => 'Recuperar Contraseña', 'correo' => $correo, 'error' => '', 'exito' => ''];
            $this->view('usuarios/recover_codigo', $datos);
        }
    }

    // ── SESSION ───────────────────────────────────────────────────────────────
    public function createUsuarioSession($user) {
        $_SESSION['id_usuario']     = $user->id_usuario;
        $_SESSION['usuario_correo'] = $user->correo;
        $_SESSION['usuario_nombre'] = $user->nombre;
        $_SESSION['usuario_rol']    = $user->rol;
    }

    public function logout() {
        unset($_SESSION['id_usuario'], $_SESSION['usuario_correo'],
              $_SESSION['usuario_nombre'], $_SESSION['usuario_rol']);
        session_destroy();
        header('Location: ' . URL_ROOT);
        exit();
    }
}
