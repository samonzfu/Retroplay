<?php


include '../Conexion_BD/conexion.php';

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion == 'registro') {
        $nickname = $_POST['nickname'];
        $contrasena = $_POST['contrasena']; // Guardamos la contraseña tal cual (texto plano)
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];


        // Consulta directa INSERT
        $sql = "INSERT INTO usuarios (id, nickname, correo, telefono, contrasea) VALUES ($id, '$nickname', '$correo', '$telefono', '$contrasena')";

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
        $sql = "SELECT * FROM usuarios WHERE nickname = '$nickname' AND contrasea = '$contrasena'";
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