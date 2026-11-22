<!DOCTYPE html>
<!-- Confirmación previa de borrado para la clase indicada. -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Vistas/elegir.css" rel="stylesheet">
    <title>Borrar horario</title>
</head>
<body>
    <div id="padre">
        <h1 id="textoPadre">Borrar horario</h1>
        <div id="hijo">
            <h2 id="textoHijo">¿Quieres borrar el horario de <?php echo $objeto->idClase; ?>?</h2>

            <form action="index.php?controlador=cHorario&metodo=eliminarHorario" method="post">
                <input type="hidden" name="idClase" value="<?php echo $objeto->idClase; ?>">
                <input type="submit" id="submit" value="Sí, borrar horario">
            </form>

            <a id="cerrarSesion" href="index.php?controlador=cHorario&metodo=obtenerClasesEliminar">No, volver a la lista</a>
        </div>
    </div>
</body>
</html>
