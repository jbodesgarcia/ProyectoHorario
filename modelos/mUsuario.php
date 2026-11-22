<?php
include_once 'configdb.php';

// Contiene las consultas relacionadas con los usuarios.
class Usuario extends ConfigDB {

    // Busca un usuario que coincida con el nombre y la contraseña recibidos.
    function consultaUsuarios($usuario, $contrasena) {
        $sql = "SELECT * FROM Usuario
                WHERE usuario = '$usuario'
                AND contrasena = '$contrasena'";

        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_array();
    }
}
?>

