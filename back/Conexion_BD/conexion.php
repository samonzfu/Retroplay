<?php
$conexion = mysqli_connect("localhost", "retroplay", "Retroplay123$", "retroplay");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>