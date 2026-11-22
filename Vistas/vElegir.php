<!DOCTYPE html>
<!-- Selección de una clase sin horario para crear uno nuevo. -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Vistas/elegir.css" rel="stylesheet">
    <title>Elegir clase</title>
</head>
<body>
    <nav>
        <!-- Enlaces para cambiar a las otras operaciones de horario. -->
        <ul>
            <li><a href="index.php?controlador=cHorario&metodo=obtenerClasesHorario">Visualizar horario</a></li>
            <li><a href="index.php?controlador=cHorario&metodo=obtenerClasesModificar">Modificar horario</a></li>
            <li><a href="index.php?controlador=cHorario&metodo=obtenerClasesEliminar">Borrar horario</a></li>
        </ul>
    </nav>
    <div id="padre">
        <h1 id="textoPadre">Alta de horario</h1>
        <!-- El nombre del usuario procede de la sesión iniciada. -->
        <h2>Hola <?php echo $_SESSION['usuario']; ?></h2>
    <div id="hijo">
            <!-- Envía la clase elegida al controlador para preparar el formulario de alta. -->
            <form action="index.php?controlador=cHorario&metodo=cargarAlta" method="post">
                <h2 id="textoHijo">Elige clase</h2>

                <select name="idClase">
                    <?php
                    foreach ($clases as $fila) {
                        echo '<option value="'.$fila['idClase'].'">'.$fila['idClase'].'</option>';
                    }
                    ?>
                </select>
                <input type="submit" id="submit" value="enviar">
                <a id="cerrarSesion" href="index.php?controlador=CUsuario&metodo=mostrarConfirmacionCierre">Cerrar sesión</a>
            </form>
        </div>
    </div>
</body>
</html>













