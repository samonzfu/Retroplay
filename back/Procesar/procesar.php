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
        $nickname = mysqli_real_escape_string($conexion, $_POST['nickname']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Hashear contraseña

        // Usar prepared statement para evitar inyección SQL
        $stmt = $conexion->prepare("INSERT INTO usuarios (nickname, correo, telefono, contrasena) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $nickname, $correo, $telefono, $contrasena);
            if ($stmt->execute()) {
                echo "<script>
                        alert('Registro exitoso.');
                        window.location.href = '../../front/login/login.html';
                      </script>";
            } else {
                if (isset($_GET['debug'])) { echo "<p style='color:red'>Error al ejecutar statement: " . htmlspecialchars($stmt->error) . "</p>"; }
                echo "Error al registrar: " . mysqli_error($conexion);
            }
            $stmt->close();
        } else {
            if (isset($_GET['debug'])) { echo "<p style='color:red'>Error prepare: " . htmlspecialchars($conexion->error) . "</p>"; }
            echo "Error al registrar: " . mysqli_error($conexion);
        }

        // PROCESO DE LOGIN
        // Si la acción es 'login', entramos en este otro bloque.
    } elseif ($accion == 'login') {

        // Recogemos los datos del formulario de login.
        $nickname = mysqli_real_escape_string($conexion, $_POST['nickname']);
        $contrasena = $_POST['contrasena'];

        // Comprobar usuario
        $stmt = $conexion->prepare("SELECT id, nickname, contrasena FROM usuarios WHERE nickname = ?");
        if ($stmt) {
            $stmt->bind_param("s", $nickname);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                if (password_verify($contrasena, $row['contrasena'])) {
                    session_start();
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['nickname'] = $row['nickname'];
                    header('Location: ../../front/inicio/inicio.php');
                    exit;
                } else {
                    echo "<script>alert('Usuario o contraseña incorrectos.'); window.history.back();</script>";
                }
            } else {
                echo "<script>alert('Usuario o contraseña incorrectos.'); window.history.back();</script>";
            }
            $stmt->close();
        } else {
            if (isset($_GET['debug'])) { echo "<p style='color:red'>Error en la consulta: " . htmlspecialchars($conexion->error) . "</p>"; }
        }

    // Acción para actualizar perfil
    } elseif ($accion == 'update_profile') {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo "<script>alert('No autorizado.'); window.location.href='../../front/login/login.html';</script>";
            exit;
        }
        $id = $_SESSION['user_id'];
        $nickname = mysqli_real_escape_string($conexion, $_POST['nickname']);
        $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $newpass = (isset($_POST['new_password']) && trim($_POST['new_password']) !== '') ? password_hash($_POST['new_password'], PASSWORD_DEFAULT) : null;

        if ($newpass) {
            $stmt = $conexion->prepare("UPDATE usuarios SET nickname=?, correo=?, telefono=?, contrasena=? WHERE id=?");
            $stmt->bind_param("ssssi", $nickname, $correo, $telefono, $newpass, $id);
        } else {
            $stmt = $conexion->prepare("UPDATE usuarios SET nickname=?, correo=?, telefono=? WHERE id=?");
            $stmt->bind_param("sssi", $nickname, $correo, $telefono, $id);
        }
        if ($stmt->execute()) {
            $_SESSION['nickname'] = $nickname;
            echo "<script>alert('Perfil actualizado'); window.location.href='../../front/mi_cuenta/mi_cuenta.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar perfil.'); window.history.back();</script>";
        }
        $stmt->close();
}
}
?>