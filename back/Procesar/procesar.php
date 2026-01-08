<?php


// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../Conexion_BD/conexion.php';

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion == 'registro') {
        $nickname = $_POST['nickname'];
        $contrasena = $_POST['contrasena']; // Guardamos la contraseña tal cual (texto plano)
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];


        // Consulta directa INSERT
        // IMPORTANTE: id debe ser AUTO_INCREMENT en la base de datos
        $sql = "INSERT INTO usuarios (nickname, correo, telefono, contrasena) VALUES ('$nickname', '$correo', '$telefono', '$contrasena')";

        if (mysqli_query($conexion, $sql)) {
            echo "<script>
                    alert('Registro exitoso.');
                    window.location.href = '../../front/login/login.html';
                  </script>";
        } else {
            echo "Error al registrar: " . mysqli_error($conexion);
        }

    } elseif ($accion == 'login') {
        $nickname = $_POST['nickname'];
        $contrasena = $_POST['contrasena'];

        // Consulta directa SELECT buscando coincidencia exacta de usuario y contraseña
        $sql = "SELECT * FROM usuarios WHERE nickname = '$nickname' AND contrasena = '$contrasena'";
        $resultado = mysqli_query($conexion, $sql);

        if (mysqli_num_rows($resultado) > 0) {
            $row = mysqli_fetch_assoc($resultado);
            echo "<script>
                    alert('¡Bienvenido, " . $row['nickname'] . "!');
                    window.location.href = '../../front/login/login.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Usuario o contraseña incorrectos.');
                    window.history.back();
                  </script>";
        }
    }
} else {
    echo "Faltan parámetros.";
}
?>