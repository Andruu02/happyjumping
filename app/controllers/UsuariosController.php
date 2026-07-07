<?php
class UsuariosController extends Controller {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    // ── LOGIN ─────────────────────────────────────────────────────────────────
    public function login() {
        if (isset($_SESSION['id_usuario'])) {
            header('Location: ' . URL_ROOT . (($_SESSION['usuario_rol'] ?? '') === 'admin' ? '/admin' : '/perfil'));
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'titulo'         => 'Iniciar Sesion - Happy&Jumping',
                'correo'         => trim($_POST['correo']),
                'password'       => trim($_POST['password']),
                'correo_error'   => '',
                'password_error' => ''
            ];

            if (empty($datos['correo']))
                $datos['correo_error'] = 'Por favor, ingresa tu correo.';
            elseif (strtolower(trim($datos['correo'])) !== 'admin@happyjumping.com'
                    && !str_ends_with(strtolower($datos['correo']), '@gmail.com'))
                $datos['correo_error'] = 'Solo se aceptan correos @gmail.com.';

            if (empty($datos['password']))
                $datos['password_error'] = 'Por favor, ingresa tu contraseña.';

            if (empty($datos['correo_error']) && empty($datos['password_error'])) {
                $user = $this->usuarioModel->login($datos['correo'], $datos['password']);
                if ($user) {
                    // Si no está verificado → mandar a verificar
                    if (!$user->is_verificado) {
                        $_SESSION['correo_verificacion'] = $user->correo;
                        header('Location: ' . URL_ROOT . '/usuarios/verificar');
                        exit();
                    }
                    $this->createUsuarioSession($user);
                    header('Location: ' . URL_ROOT . ($user->rol === 'admin' ? '/admin' : '/perfil'));
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
                'correo_error'   => '', 'password_error' => ''
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
            elseif (!str_ends_with(strtolower($datos['correo']), '@gmail.com'))
                $datos['correo_error'] = 'Solo se aceptan correos @gmail.com.';
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
                header('Location: ' . URL_ROOT . '/usuarios/login?verificado=1');
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
    public function recover() {
        $datos = ['titulo' => 'Recuperar Contraseña - Happy&Jumping'];
        $this->view('usuarios/recover', $datos);
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
