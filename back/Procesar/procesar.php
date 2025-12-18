<?php
// Recogemos el dato que viene del 'name' del input
$nombre_usuario = $_POST['nombre'];
$contrasena = $_POST['password'];

echo "¡Gracias por registrarte, " . $nombre_usuario . "!";
?>