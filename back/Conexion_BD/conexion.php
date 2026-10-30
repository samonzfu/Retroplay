<?php
$conexion = mysqli_connect("localhost", "retroplay", "Retroplay123$", "retroplay");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
// Evita que los acentos y el símbolo € salgan mal (Ã¡, â‚¬...).
mysqli_set_charset($conexion, 'utf8mb4');
?>