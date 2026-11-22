<!DOCTYPE html>
<!-- Selección de una clase con horario para abrir su formulario de modificación. -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Vistas/elegir.css" rel="stylesheet">
    <title>Elegir clase</title>
</head>
<body>
    <nav>
        <!-- Enlaces a las demás operaciones de horario. -->
        <ul>
            <li><a href="index.php?controlador=cHorario&metodo=obtenerClases">Añadir horario</a></li>
            <li><a href="index.php?controlador=cHorario&metodo=obtenerClasesHorario">Visualizar horario</a></li>
            <li><a href="index.php?controlador=cHorario&metodo=obtenerClasesEliminar">Borrar horario</a></li>
        </ul>
    </nav>
    <div id="padre">
        <h1 id="textoPadre">Modificar horario</h1>
        <!-- El usuario se muestra desde los datos de sesión. -->
        <h2>Hola <?php echo $_SESSION['usuario']; ?></h2>
        <div id="hijo">
            <!-- Envía la clase elegida al método que carga los valores existentes. -->
            <form action="index.php?controlador=cHorario&metodo=cargarAlta" method="post">
                <h2 id="textoHijo">Elige clase</h2>

                <select name="idClase">
                    <?php
                    foreach ($clases as $fila) {
                        echo '<option value="'.$fila['idClase'].'">'.$fila['idClase'].'</option>';
                    }
                    ?>
                </select>
                <input type="hidden" name="modificando" value="1">
                <input type="submit" id="submit" value="Modificar horario">
                <a id="cerrarSesion" href="index.php?controlador=CUsuario&metodo=mostrarConfirmacionCierre">Cerrar sesión</a>
            </form>
        </div>
    </div>
</body>
</html>












