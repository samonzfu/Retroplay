<?php
// Iniciar la sesión para poder acceder a ella y destruirla.
session_start();

// Liberar todas las variables de sesión.
session_unset();

// Destruir la sesión completamente (borra la información en el servidor).
session_destroy();

// Redirigir al usuario a la página de login.
header('Location: ../login/login.html');

// Detener la ejecución del script.
exit;
?>