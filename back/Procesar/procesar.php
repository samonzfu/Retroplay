<?php
// CONEXIÓN A LA BASE DE DATOS
include '../Conexion_BD/conexion.php';

// VERIFICACIÓN DE DATOS RECIBIDOS
// Comprobamos si el formulario nos ha enviado un campo llamado 'accion' (hidden input) para saber qué hacer.
if (isset($_POST['accion'])) {
    // Guardamos la acción en una variable para usarla más fácilmente.
    $accion = $_POST['accion'];

    // PROCESO DE REGISTRO
    // Si la acción es 'registro', entramos en este bloque.
    if ($accion == 'registro') {
        // Recogemos los datos enviados desde el formulario de registro.
        $nickname = $_POST['nickname'];
        $contrasena = $_POST['contrasena']; // Nota: Idealmente las contraseñas deberían cifrarse (ej. password_hash).
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];

        // Preparamos la consulta SQL para guardar al nuevo usuario.
        // INSERT INTO tabla (columnas) VALUES (valores)
        // No necesitamos pasar el 'id' porque en la base de datos es AUTO_INCREMENT (se crea solo).
        $sql = "INSERT INTO usuarios (nickname, correo, telefono, contrasena) 
                VALUES ('$nickname', '$correo', '$telefono', '$contrasena')";

        // Ejecutamos la consulta contra la base de datos.
        if (mysqli_query($conexion, $sql)) {
            // SI TIENE ÉXITO:
            // Mostramos una alerta JS y redirigimos al usuario a la página de login.
            echo "<script>
                    alert('Registro exitoso.');
                    window.location.href = '../../front/login/login.html';
                  </script>";
        } else {
            // SI FALLA:
            // Mostramos el error que nos devuelve MySQL.
            echo "Error al registrar: " . mysqli_error($conexion);
        }
        // PROCESO DE LOGIN
        // Si la acción es 'login', entramos en este otro bloque.
    } elseif ($accion == 'login') {

        // Recogemos los datos del formulario de login.
        $nickname = $_POST['nickname'];
        $contrasena = $_POST['contrasena'];

        // Preparamos la consulta SQL para buscar un usuario que coincida con el nombre Y la contraseña.
        $sql = "SELECT * FROM usuarios WHERE nickname = '$nickname' AND contrasena = '$contrasena'";

        // Ejecutamos la consulta.
        $resultado = mysqli_query($conexion, $sql);

        // Verificamos si la base de datos devolvió al menos una fila (significa que encontró al usuario).
        if (mysqli_num_rows($resultado) > 0) {
            // SI EXISTE EL USUARIO:
            // Obtenemos los datos del usuario en un array asociativo ($row).
            $row = mysqli_fetch_assoc($resultado);

            // MODIFICADO: el login ya envía al inicio
            echo "<script>
                    alert('¡Bienvenido, " . $row['nickname'] . "!');
                    window.location.href = '../../front/inicio/inicio.php'; 
                  </script>";
        } else {
            // SI NO EXISTE O CONTRASEÑA INCORRECTA:
            // Mandamos una alerta y usamos history.back() para que vuelva al formulario.
            echo "<script>
                    alert('Usuario o contraseña incorrectos.');
                    window.history.back();
                  </script>";
        }
    }

} else {
    // CASO DE ERROR: PARAMETROS FALTANTES
    echo "Faltan parámetros.";
}
?>