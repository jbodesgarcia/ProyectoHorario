<!DOCTYPE html>
<!-- Formulario para crear el horario de la clase seleccionada. -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Vistas/alta.css" rel="stylesheet">
    <title>Añadir horario</title>
</head>
<body>
    <div class="contenedor">
        <h1>
            Añadir horario para
            <?php echo $objeto->idClase; ?>
        </h1>
        <form action="index.php?controlador=cHorario&metodo=introducirHorario" method="post">
            <!-- Identifica la clase a la que pertenecen las filas enviadas. -->
            <input type="hidden" name="idClase" value="<?php echo $objeto->idClase; ?>">
            <!-- El controlador prepara los días, las horas y las opciones de cada celda. -->
            <table>
                <?php
                echo '<tr><th>Hora</th>';
                foreach ($objeto->dias as $nombreDia) {
                    echo '<th>' . $nombreDia . '</th>';
                }
                echo '</tr>';

                foreach ($objeto->filasHorario as $filaHorario) {
                    echo '<tr><th>' . $filaHorario['hora'] . '</th>';

                    foreach ($filaHorario['celdas'] as $celda) {
                        echo '<td>' . $celda['texto'];
                        echo '<select name="' . $celda['nombreAsignatura'] . '" ' . $celda['atributosSelectores'] . '>';
                        echo '<option value="" ' . $celda['placeholderAsignatura'] . '>Asignatura</option>';

                        foreach ($celda['opcionesAsignaturas'] as $asignatura) {
                            echo '<option value="' . $asignatura['id'] . '" ' . $asignatura['seleccionada'] . '>' . $asignatura['nombre'] . '</option>';
                        }

                        echo '</select>';
                        echo '<select name="' . $celda['nombreProfesor'] . '" ' . $celda['atributosSelectores'] . '>';
                        echo '<option value="" ' . $celda['placeholderProfesor'] . '>Profesor</option>';

                        foreach ($celda['opcionesProfesores'] as $profesor) {
                            echo '<option value="' . $profesor['id'] . '" ' . $profesor['seleccionado'] . '>' . $profesor['nombre'] . '</option>';
                        }

                        echo '</select></td>';
                    }

                    echo '</tr>';
                }
                ?>
            </table>

            <br>
            <input type="submit" id="submit" value="Guardar horario">
        </form>

        <!-- Regresa a la selección de clases que todavía no tienen horario. -->
        <form action="index.php" method="get">
            <input type="hidden" name="controlador" value="cHorario">
            <input type="hidden" name="metodo" value="obtenerClases">
            <input type="submit" value="Volver a elegir clase para introducir">
        </form>
    </div>
</body>
</html>
