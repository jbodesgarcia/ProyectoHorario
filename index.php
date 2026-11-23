<?php
    // Punto de entrada de la aplicación: el navegador siempre llega a este archivo.
    // Si no se indica una ruta, se muestra el formulario de inicio de sesión.
    $controlador = 'cUsuario';
    $metodo = 'mostrarFormulario';

    // El controlador y el método se reciben en la URL como parámetros GET.
    if (isset($_GET["controlador"])) {
        $controlador = $_GET["controlador"];
    }

    if (isset($_GET["metodo"])) {
        $metodo = $_GET["metodo"];
    }

    // Cargamos el controlador solicitado y creamos su objeto.
    include 'controladores/' . $controlador . '.php';

    $objeto = new $controlador;

    // Ejecutamos el método indicado y guardamos los datos que devuelve.
    $datos = $objeto->$metodo();

    // Dejamos los datos disponibles con los nombres que usan las vistas.
    $clases = $datos;
    $horario = $datos;

    // El controlador selecciona qué vista se debe mostrar.
    include $objeto->vista;
?>







