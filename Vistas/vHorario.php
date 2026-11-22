<!DOCTYPE html>
<!-- Muestra el horario consultado; las celdas ocupadas usan el color de la asignatura. -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Vistas/alta.css" rel="stylesheet">
    <title>Visualizar horario</title>
</head>
<body>
    <div class="contenedor">
        <h1>Horario de <?php echo $objeto->idClase; ?></h1>

        <table>
            <!-- El controlador entrega las filas y sus celdas listas para mostrarse. -->
            <?php
            echo '<tr><th>Hora</th>';
            foreach ($objeto->dias as $nombreDia) {
                echo '<th>' . $nombreDia . '</th>';
            }
            echo '</tr>';

            foreach ($objeto->filasHorarioVisualizacion as $filaHorario) {
                echo '<tr><th>' . $filaHorario['hora'] . '</th>';
                foreach ($filaHorario['celdas'] as $celda) {
                    echo '<td ' . $celda['atributos'] . '>' . $celda['texto'] . '</td>';
                }
                echo '</tr>';
            }
            ?>
        </table>

        <br>
        <br>
        <!-- Regresa a la selección de clases para visualizar otro horario. -->
        <form action="index.php" method="get">
            <input type="hidden" name="controlador" value="cHorario">
            <input type="hidden" name="metodo" value="obtenerClasesHorario">
            <input type="submit" value="Volver a elegir horario para visualizar">
        </form>
    </div>
</body>
</html>
