<?php
include_once 'configdb.php';

// Contiene las consultas y operaciones de escritura del horario y sus asignaturas.
class Horario extends ConfigDB {

    // LEFT JOIN busca horarios para cada clase; IS NULL deja solo las clases sin filas en Horario.
    function consultarClases(){
        $sql = "SELECT DISTINCT Clase.idClase, Clase.nombre FROM Clase
        LEFT JOIN Horario ON Clase.idClase = Horario.idClase
        WHERE Horario.idClase IS NULL;";
        $resultado = $this->conexion->query($sql);
        $clases = [];

        while($fila = $resultado->fetch_array()){
            $clases[] = $fila;
        }
        return $clases;
    }

    // Consulta las clases que ya tienen horario: une Clase y Horario y conserva coincidencias existentes.
    function consultarClasesHorario(){
        // SELECT DISTINCT evita repetir una clase aunque tenga muchas filas de horario.
        $sql = "SELECT DISTINCT Clase.idClase, Clase.nombre FROM Clase
        LEFT JOIN Horario ON Clase.idClase = Horario.idClase
        WHERE Horario.idClase IS NOT NULL;";
        // Ejecuta la consulta en la conexión creada por ConfigDB.
        $resultado = $this->conexion->query($sql);
        // Prepara un array vacío para guardar las clases encontradas.
        $clases = [];

        // Lee cada fila del resultado y la añade al array.
        while($fila = $resultado->fetch_array()){
            $clases[] = $fila;
        }
        // Entrega la lista al controlador para que la vista la recorra.
        return $clases;
    }

    // Recibe idClase y reúne los datos relacionados de cada tramo para la vista de visualización.
    function consultarHorarioPorClase($idClase){
        // Selecciona el día y el idHora, además del nombre/color de la asignatura y el nombre del profesor.
        // Los INNER JOIN relacionan cada id guardado en Horario con su fila en las tablas correspondientes.
        // WHERE limita el resultado a la clase recibida y ORDER BY muestra primero las horas más tempranas.
        $sql = "SELECT Horario.dia, Horario.idHora,
                       Asignatura.nombre AS nombreAsignatura,
                       Asignatura.color AS colorAsignatura,
                       Profesor.nombreCompleto AS nombreProfesor
                FROM Horario
                INNER JOIN Hora ON Horario.idHora = Hora.idHora
                INNER JOIN Asignatura ON Horario.idAsignatura = Asignatura.idAsignatura
                INNER JOIN Profesor ON Horario.idProfesor = Profesor.idProfesor
                WHERE Horario.idClase = '$idClase'
                ORDER BY Hora.hora";
        // Ejecuta la consulta con la conexión a la base de datos.
        $resultado = $this->conexion->query($sql);
        // Crea el array que contendrá todas las filas de esa clase.
        $horario = [];

        // Convierte cada fila del resultado en un elemento del array.
        while($fila = $resultado->fetch_array()){
            $horario[] = $fila;
        }
        // Devuelve las filas al controlador para que las ordene en la cuadrícula semanal.
        return $horario;
    }

    // Filtra por idClase y organiza cada resultado como horario[día][idHora] para rellenar los selectores actuales.
    function consultarSemanaParaModificar($idClase){
        $sql = "SELECT dia, idHora, idAsignatura, idProfesor
                FROM Horario
                WHERE idClase = '$idClase'";
        $resultado = $this->conexion->query($sql);
        $horario = [];

        while($fila = $resultado->fetch_array()){
            $horario[$fila['dia']][$fila['idHora']] = [
                'idAsignatura' => $fila['idAsignatura'],
                'idProfesor' => $fila['idProfesor']
            ];
        }
        return $horario;
    }

    // Filtra Asignatura por idClase, ordena los nombres y devuelve las filas para el desplegable.
    function consultarAsignaturasClase($idClase){
        $sql = "SELECT idAsignatura, nombre FROM Asignatura
                WHERE idClase = '$idClase'
                ORDER BY nombre";
        $resultado = $this->conexion->query($sql);
        $asignaturas = [];

        while($fila = $resultado->fetch_array()){
            $asignaturas[] = $fila;
        }
        return $asignaturas;
    }

    // Recibe día, idHora y clase actual; devuelve los profesores que no están ocupados en ese tramo en otra clase.
    function consultarProfesoresDisponibles($dia, $idHora, $idClase){
        // El LEFT JOIN intenta encontrar para cada profesor un horario en el mismo día y hora.
        // La condición idClase != $idClase ignora las filas de la clase que se está modificando.
        // IS NULL conserva profesores para los que no se encontró una ocupación en otra clase.
        $sql = "SELECT Profesor.idProfesor, Profesor.nombreCompleto
                FROM Profesor
                LEFT JOIN Horario
                    ON Profesor.idProfesor = Horario.idProfesor
                    AND Horario.dia = '$dia'
                    AND Horario.idHora = '$idHora'
                    AND Horario.idClase != '$idClase'
                WHERE Horario.idHorario IS NULL
                ORDER BY Profesor.nombreCompleto";
        // Ejecuta la búsqueda para este único tramo.
        $resultado = $this->conexion->query($sql);
        // Prepara el array de profesores libres que devolverá la función.
        $profesores = [];

        // Añade al array cada profesor que superó el filtro de disponibilidad.
        while($fila = $resultado->fetch_array()){
            $profesores[] = $fila;
        }
        // El controlador usa esta lista como opciones del select de esa celda.
        return $profesores;
    }

    // Consulta Hora, convierte cada valor a HH:MM y devuelve filas con idHora y hora para construir la tabla.
    function consultarHoras(){
        $sql = "SELECT idHora, DATE_FORMAT(hora, '%H:%i') AS hora FROM Hora ORDER BY hora";
        $resultado = $this->conexion->query($sql);
        $horas = [];

        while($fila = $resultado->fetch_array()){
            $horas[] = $fila;
        }
        return $horas;
    }

    // Borra e inserta el horario dentro de una transacción; verifica profesores y 31 filas, y confirma o revierte todo.
    function guardarSemana($idClase, $asignaturas, $profesores){
        // Cuenta los tramos insertados para confirmar solo si están los 31.
        $filasInsertadas = 0;
        $filasEsperadas = 31;
        $transaccionIniciada = false;

        try {
        // Inicia la transacción dentro del try para capturar también errores al comenzarla.
        $this->conexion->begin_transaction();
        $transaccionIniciada = true;

        // El borrado también queda dentro de la transacción para poder recuperarlo si un INSERT falla.
        $sql = "DELETE FROM Horario WHERE idClase = '$idClase'";
        if (!$this->conexion->query($sql)) {
            // Revierte la operación si falla el borrado.
            $this->conexion->rollback();
            $transaccionIniciada = false;
            return false;
        }

        foreach ($asignaturas as $dia => $horas){
            foreach ($horas as $idHora => $idAsignatura){
                if ($idAsignatura != '') {
                    // No guarda una asignatura sin profesor en el mismo día y hora.
                    if (!isset($profesores[$dia][$idHora]) || $profesores[$dia][$idHora] == '') {
                        // Revierte la operación si falta elegir el profesor.
                        $this->conexion->rollback();
                        $transaccionIniciada = false;
                        return false;
                    }

                    $idProfesor = $profesores[$dia][$idHora];
                    $sql = "INSERT INTO Horario
                            (dia, idHora, idClase, idAsignatura, idProfesor)
                            VALUES ('$dia', $idHora, '$idClase', $idAsignatura, $idProfesor)";

                    if (!$this->conexion->query($sql)) {
                        // Revierte la operación si falla el INSERT.
                        $this->conexion->rollback();
                        $transaccionIniciada = false;
                        return false;
                    }

                    $filasInsertadas++;
                }
            }
        }

        if ($filasInsertadas != $filasEsperadas) {
            // Evita confirmar si no llegaron todas las 31 filas de clase.
            $this->conexion->rollback();
            $transaccionIniciada = false;
            return false;
        }

        // Solo confirma cuando se han insertado todos los tramos esperados.
        $resultado = $this->conexion->commit();
        $transaccionIniciada = false;
        return $resultado;
        } catch (mysqli_sql_exception $error) {
            // Revierte el horario si una consulta lanza una excepción.
            if ($transaccionIniciada) {
                try {
                    $this->conexion->rollback();
                } catch (mysqli_sql_exception $errorRollback) {
                    // Ignora un segundo error durante el rollback.
                }
            }
            return false;
        }
    }

    // Ejecuta DELETE para la clase recibida y devuelve true si la consulta se completó.
    function eliminarHorario($idClase){
        $sql = "DELETE FROM Horario WHERE idClase = '$idClase'";
        return $this->conexion->query($sql);
    }


}
?>


