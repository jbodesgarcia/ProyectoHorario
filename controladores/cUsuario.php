<?php
include 'modelos/mUsuario.php';
include 'modelos/mHorario.php';

// Controla el inicio de sesión y las acciones de la cuenta del usuario.
class CUsuario {

    public $vista;
    public $usuario;
    public $contrasena;

    // Recoge los datos enviados por el formulario de inicio de sesión.
    function __construct() {

        if (isset($_POST['usuario'])) {
            $this->usuario = $_POST['usuario'];
        }

        if (isset($_POST['contrasena'])) {
            $this->contrasena = $_POST['contrasena'];
        }
    }

    // Selecciona la vista inicial de acceso.
    function mostrarFormulario() {
        $this->vista = 'Vistas/vLogin.html';
    }

    // Comprueba las credenciales y, si son válidas, guarda el usuario en la sesión.
    function iniciarSesion() {
        session_start();
        if (isset($this->usuario) && isset($this->contrasena)) {
            $datos = new Usuario();
            $usuario = $datos->consultaUsuarios($this->usuario, $this->contrasena);

            if ($usuario) {
                $_SESSION['idUsuario'] = $usuario['idUsuario'];
                $_SESSION['usuario'] = $usuario['usuario'];

                // Tras iniciar sesión, muestra las clases disponibles para añadir horario.
                $horario = new Horario();
                $clases = $horario->consultarClases();

                $this->vista = 'Vistas/vElegir.php';
                return $clases;
            }
        }

        $this->vista = 'Vistas/vLogin.html';
    }

    // Muestra la pantalla que pregunta si se quiere cerrar la sesión.
    function mostrarConfirmacionCierre() {
        $this->vista = 'Vistas/vConfirmarCierre.html';
    }

    // Finaliza la sesión y vuelve a mostrar el formulario de acceso.
    function cerrarSesion() {
        session_start();
        session_destroy();
        $this->vista = 'Vistas/vLogin.html';
    }
}
?>


