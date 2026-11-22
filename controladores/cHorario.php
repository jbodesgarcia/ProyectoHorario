<?php
    // Cargamos el modelo.
    include 'modelos/mHorario.php';

    // Recibe las acciones de horarios y prepara los datos para cada vista.
    class CHorario {

        // Ruta de la vista que cargará index.php al terminar el método.
        public $vista;
        // Clase seleccionada en el formulario o en la URL.
        public $idClase;
        // Datos necesarios para crear o modificar el horario.
        public $asignaturas;
        public $profesores;
        public $horas;
        public $horarioActual;
        // Estructuras que las vistas recorren para imprimir las tablas.
        public $dias;
        public $filasHorario;
        public $filasHorarioVisualizacion;
        // Indica si se está editando un horario existente.
        public $modificando = false;

        // Prioriza los datos POST del formulario y usa GET para enlaces; guarda los valores en propiedades para los métodos.
        function __construct() {
            if (isset($_POST['idClase'])) {
                $this->idClase = $_POST['idClase'];
            }
            else if (isset($_GET['idClase'])) {
                $this->idClase = $_GET['idClase'];
            }

            if (isset($_POST['asignatura'])) {
                $this->asignaturas = $_POST['asignatura'];
            }

            if (isset($_POST['profesor'])) {
                $this->profesores = $_POST['profesor'];
            }

            if (isset($_POST['modificando'])) {
                $this->modificando = true;
            }

        }

        // Pide al modelo las clases sin horario, selecciona vElegir y devuelve la lista a index.php.
        function obtenerClases(){
            session_start();
            $datos = new Horario();
            $clases = $datos->consultarClases();
            $this->vista = 'Vistas/vElegir.php';
            return $clases;
        }

        // Pide al modelo las clases con horario, selecciona vElegirVisualizar y devuelve la lista.
        function obtenerClasesHorario(){
            return $this->cargarClasesConHorario('Vistas/vElegirVisualizar.php');
        }

        // Pide al modelo las clases con horario, selecciona vElegirModificar y devuelve la lista.
        function obtenerClasesModificar(){
            return $this->cargarClasesConHorario('Vistas/vElegirModificar.php');
        }

        // Pide al modelo las clases con horario, selecciona vElegirEliminar y devuelve la lista.
        function obtenerClasesEliminar(){
            return $this->cargarClasesConHorario('Vistas/vElegirEliminar.php');
        }

        // Reúne la carga común de las tres pantallas que eligen una clase con horario.
        private function cargarClasesConHorario($vista){
            session_start();
            $datos = new Horario();
            $clases = $datos->consultarClasesHorario();
            $this->vista = $vista;
            return $clases;
        }

        // Con idClase consulta sus filas y prepara la tabla; sin idClase carga la lista para volver a elegir.
        function visualizarHorario() {
            session_start();
            $datos = new Horario();

            if (isset($this->idClase)) {
                $horario = $datos->consultarHorarioPorClase($this->idClase);
                // Carga las horas formateadas para relacionar los idHora de la consulta con las columnas visibles.
                $this->horas = $datos->consultarHoras();
                $this->prepararFilasVisualizacion($horario);
                $this->vista = 'Vistas/vHorario.php';
                return $horario;
            }

            $this->vista = 'Vistas/vElegirVisualizar.php';
            return $datos->consultarClasesHorario();
        }

        // Con idClase prepara la confirmación y devuelve ese id; sin él vuelve a la lista de clases con horario.
        function confirmarEliminarHorario() {
            if (isset($this->idClase)) {
                $this->vista = 'Vistas/vConfirmarEliminarHorario.php';
                return $this->idClase;
            }

            $datos = new Horario();
            $this->vista = 'Vistas/vElegirEliminar.php';
            return $datos->consultarClasesHorario();
        }

        // Envía idClase al modelo para borrar sus filas y después devuelve la lista actualizada de horarios.
        function eliminarHorario() {
            session_start();
            $datos = new Horario();

            if (isset($this->idClase)) {
                $datos->eliminarHorario($this->idClase);
            }

            $this->vista = 'Vistas/vElegirEliminar.php';
            return $datos->consultarClasesHorario();
        }

        // Si hay clase y arrays del formulario, pide guardarlos; si funciona muestra el horario y si falla recarga el formulario.
        function introducirHorario() {
            // Inicia o recupera la sesión para que la vista resultante conserve los datos del usuario.
            session_start();

            // idClase se carga desde POST o GET en el constructor; sin ella no se puede guardar un horario.
            if (!isset($this->idClase)) {
                // Indica a index.php que vuelva a mostrar la pantalla de selección de clase para alta.
                $this->vista = 'Vistas/vElegir.php';
                // Crea el modelo para consultar las clases que todavía no tienen horario.
                $datos = new Horario();
                // Devuelve esas clases; index.php las deja disponibles para el desplegable de la vista.
                return $datos->consultarClases();
            }

            // Los dos arrays llegan del formulario: asignatura[día][idHora] y profesor[día][idHora].
            if (isset($this->asignaturas) && isset($this->profesores)) {
                // Crea el modelo que guarda y consulta los datos en la base de datos.
                $datos = new Horario();

                // guardarSemana reemplaza el horario dentro de una transacción y devuelve true si lo confirma.
                if ($datos->guardarSemana($this->idClase, $this->asignaturas, $this->profesores)) {
                    // Si se guardó, vuelve a leer las filas ya almacenadas para mostrarlas.
                    $horario = $datos->consultarHorarioPorClase($this->idClase);
                    // Carga las horas visibles para traducir cada idHora al texto de la tabla.
                    $this->horas = $datos->consultarHoras();
                    // Convierte las filas consultadas en una tabla completa con días, horas y colores.
                    $this->prepararFilasVisualizacion($horario);
                    // Selecciona la vista que imprimirá el horario guardado.
                    $this->vista = 'Vistas/vHorario.php';
                    // Entrega las filas a index.php; la vista también usa las propiedades preparadas del controlador.
                    return $horario;
                }
            }

            // Si faltaban datos o falló el guardado, vuelve a preparar el formulario de alta o modificación.
            $this->cargarAlta();
        }

        // Consulta asignaturas y horas de la clase; al editar consulta también sus valores actuales, prepara la tabla y elige vista.
        function cargarAlta() {
            // El formulario anterior debería enviar idClase; si no llegó, regresa a la selección de clases.
            if (!isset($this->idClase)) {
                // Indica a index.php qué vista debe cargar.
                $this->vista = 'Vistas/vElegir.php';
                // Crea el modelo para recuperar las clases sin horario.
                $datos = new Horario();
                // Devuelve las clases que la vista mostrará en su select.
                return $datos->consultarClases();
            }

            // Crea el modelo para consultar la información necesaria de la clase seleccionada.
            $datos = new Horario();
            // Trae solo las asignaturas cuyo idClase coincide con la clase elegida.
            $this->asignaturas = $datos->consultarAsignaturasClase($this->idClase);
            // Recupera los ids y textos de las horas que forman las filas del horario.
            $this->horas = $datos->consultarHoras();
            // Empieza con el horario actual vacío; en un alta no hay valores que seleccionar.
            $this->horarioActual = [];

            // En modificación, consulta lo guardado y lo usa luego para marcar los selectores actuales.
            if ($this->modificando) {
                $this->horarioActual = $datos->consultarSemanaParaModificar($this->idClase);
            }

            // Construye en filasHorario los días, horas, asignaturas y profesores que se mostrarán en la tabla.
            $this->prepararFilasHorario($datos);

            // El indicador modificando viene como campo oculto del formulario de selección.
            if ($this->modificando) {
                // En modo modificación muestra los valores ya guardados.
                $this->vista = 'Vistas/vModificarHorario.php';
            } else {
                // En modo alta muestra los selectores vacíos para crear un horario nuevo.
                $this->vista = 'Vistas/vAlta.php';
            }
        }

        // Convierte horas y días en filas/celdas; para cada tramo crea opciones de asignatura y consulta profesores libres.
        private function prepararFilasHorario($datos) {
            // Prepara el mapa de códigos (LU, MA...) a nombres que se usarán como columnas.
            $this->prepararDias();

            // Empieza un mapa vacío que relacionará el texto de hora con su id en la base de datos.
            $horasPorTexto = [];
            // Recorre las horas que cargó cargarAlta para construir esa relación.
            foreach ($this->horas as $filaHora) {
                // Ejemplo: guarda qué idHora corresponde a la hora 08:15.
                $horasPorTexto[$filaHora['hora']] = $filaHora['idHora'];
            }

            // Define las horas visibles y lectivas en el orden que tendrá la tabla.
            $horasDelHorario = ['08:15', '09:10', '10:05', '11:00', '11:30', '12:25', '13:20', '14:15'];
            // Reinicia las filas para crear desde cero la cuadrícula que recibirá la vista.
            $this->filasHorario = [];

            // Recorre una vez cada hora para crear una fila de la tabla.
            foreach ($horasDelHorario as $horaTexto) {
                // Cada fila contiene el texto de la hora y una colección de celdas por día.
                $filaHorario = [
                    'hora' => $horaTexto,
                    'celdas' => []
                ];

                // Dentro de cada fila recorre Lunes, Martes, Miércoles, Jueves y Viernes.
                foreach ($this->dias as $dia => $nombreDia) {
                    // Deja la celda vacía y sus selectores ocultos; más abajo se activan solo para horas lectivas.
                    $celda = [
                        'texto' => '',
                        'nombreAsignatura' => '',
                        'nombreProfesor' => '',
                        'atributosSelectores' => 'disabled hidden',
                        'opcionesAsignaturas' => [],
                        'opcionesProfesores' => [],
                        'placeholderAsignatura' => '',
                        'placeholderProfesor' => ''
                    ];

                    // A las 11:00 no hay clase: escribe Recreo y conserva los selectores ocultos.
                    if ($horaTexto == '11:00') {
                        $celda['texto'] = 'Recreo';
                    // Si hay idHora, prepara controles; las 14:15 solo son lectivas el lunes.
                    } else if (isset($horasPorTexto[$horaTexto]) && ($horaTexto != '14:15' || $dia == 'LU')) {
                        // Obtiene el id de base de datos que corresponde a esta hora visible.
                        $idHora = $horasPorTexto[$horaTexto];
                        // Estos valores vacíos sirven para el alta y para tramos aún vacíos al modificar.
                        $idAsignaturaActual = '';
                        $idProfesorActual = '';

                        // Si existe un registro guardado para este día/hora, copia sus ids para marcarlo seleccionado.
                        if (isset($this->horarioActual[$dia][$idHora])) {
                            $idAsignaturaActual = $this->horarioActual[$dia][$idHora]['idAsignatura'];
                            $idProfesorActual = $this->horarioActual[$dia][$idHora]['idProfesor'];
                        }

                        // Prepara las asignaturas de esta clase como opciones del selector.
                        $opcionesAsignaturas = [];
                        foreach ($this->asignaturas as $asignatura) {
                            // Solo esta opción lleva selected cuando coincide con la asignatura ya guardada.
                            $seleccionada = '';
                            if ($asignatura['idAsignatura'] == $idAsignaturaActual) {
                                $seleccionada = 'selected';
                            }

                            $opcionesAsignaturas[] = [
                                'id' => $asignatura['idAsignatura'],
                                'nombre' => $asignatura['nombre'],
                                'seleccionada' => $seleccionada
                            ];
                        }

                        // Consulta quién está libre en esta hora y prepara esas personas como opciones.
                        $opcionesProfesores = [];
                        $profesoresDisponibles = $datos->consultarProfesoresDisponibles($dia, $idHora, $this->idClase);
                        foreach ($profesoresDisponibles as $profesor) {
                            // Marca el profesor actual solo si sigue disponible en este tramo.
                            $seleccionado = '';
                            if ($profesor['idProfesor'] == $idProfesorActual) {
                                $seleccionado = 'selected';
                            }

                            $opcionesProfesores[] = [
                                'id' => $profesor['idProfesor'],
                                'nombre' => $profesor['nombreCompleto'],
                                'seleccionado' => $seleccionado
                            ];
                        }

                        // En alta el placeholder queda bloqueado; al editar queda seleccionado si el tramo no tenía valor.
                        $placeholderAsignatura = '';
                        $placeholderProfesor = '';
                        if (!$this->modificando) {
                            $placeholderAsignatura = 'disabled selected hidden';
                            $placeholderProfesor = 'disabled selected hidden';
                        } else {
                            if ($idAsignaturaActual == '') {
                                $placeholderAsignatura = 'selected';
                            }
                            if ($idProfesorActual == '') {
                                $placeholderProfesor = 'selected';
                            }
                        }

                        // Asigna los nombres que harán que PHP reciba arrays indexados por día e idHora.
                        $celda['nombreAsignatura'] = 'asignatura[' . $dia . '][' . $idHora . ']';
                        $celda['nombreProfesor'] = 'profesor[' . $dia . '][' . $idHora . ']';
                        // Activa la validación HTML para obligar a elegir ambos valores en el navegador.
                        $celda['atributosSelectores'] = 'required';
                        // Copia en la celda las opciones y estados para que la vista solo los imprima.
                        $celda['opcionesAsignaturas'] = $opcionesAsignaturas;
                        $celda['opcionesProfesores'] = $opcionesProfesores;
                        $celda['placeholderAsignatura'] = $placeholderAsignatura;
                        $celda['placeholderProfesor'] = $placeholderProfesor;
                    }

                    // Añade la celda preparada a la fila del día/hora actual.
                    $filaHorario['celdas'][] = $celda;
                }

                // Al terminar los cinco días, añade la fila completa a la tabla.
                $this->filasHorario[] = $filaHorario;
            }
        }

        // Indexa las filas recibidas por día/hora y construye la cuadrícula completa con texto, colores y celdas vacías.
        private function prepararFilasVisualizacion($horario) {
            $this->prepararDias();

            // Crea un mapa de idHora a hora visible (por ejemplo, 1 => 08:15).
            $horasPorId = [];
            foreach ($this->horas as $filaHora) {
                $horasPorId[$filaHora['idHora']] = $filaHora['hora'];
            }

            // Convierte cada fila del modelo al formato que la vista imprime en una celda.
            $datosHorario = [];
            foreach ($horario as $fila) {
                // Usa idHora para localizar el texto de hora; la consulta del modelo no necesita DATE_FORMAT.
                $horaTexto = $horasPorId[$fila['idHora']];
                $datosHorario[$fila['dia']][$horaTexto] = [
                    'texto' => $fila['nombreAsignatura'] . ' - ' . $fila['nombreProfesor'],
                    'atributos' => 'style="background-color: ' . $fila['colorAsignatura'] . ';"'
                ];
            }

            $horasDelHorario = ['08:15', '09:10', '10:05', '11:00', '11:30', '12:25', '13:20', '14:15'];
            $this->filasHorarioVisualizacion = [];

            foreach ($horasDelHorario as $horaTexto) {
                $filaHorario = [
                    'hora' => $horaTexto,
                    'celdas' => []
                ];

                foreach ($this->dias as $dia => $nombreDia) {
                    // Por defecto la celda queda vacía, excepto recreo o un tramo guardado.
                    $celda = [
                        'texto' => '',
                        'atributos' => ''
                    ];

                    if ($horaTexto == '11:00') {
                        $celda['texto'] = 'Recreo';
                    } else if (($horaTexto != '14:15' || $dia == 'LU') && isset($datosHorario[$dia][$horaTexto])) {
                        $celda = $datosHorario[$dia][$horaTexto];
                    }

                    $filaHorario['celdas'][] = $celda;
                }

                $this->filasHorarioVisualizacion[] = $filaHorario;
            }
        }

        // Crea el mapa código=>nombre usado para recorrer columnas y reconocer cada día.
        private function prepararDias() {
            $this->dias = [
                'LU' => 'Lunes',
                'MA' => 'Martes',
                'MI' => 'Miércoles',
                'JU' => 'Jueves',
                'VI' => 'Viernes'
            ];
        }

}
