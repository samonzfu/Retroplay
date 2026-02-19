<?php
// CONEXIÓN A LA BASE DE DATOS
// Incluimos el archivo que contiene la configuración para conectarse a la base de datos (host, usuario, contraseña, nombre BD).
include '../Conexion_BD/conexion.php';

// INCLUIMOS EL VALIDADOR DE CONTRASEÑAS
include '../Validadores/validar_contrasena.php';

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
        $contrasena_raw = $_POST['contrasena'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];

        // VALIDAR LA CONTRASEÑA
        $validacion = validar_contrasena($contrasena_raw);
        if (!$validacion['valida']) {
            // Si la contraseña no es válida, devolvemos error con detalles
            $errores = implode('\n', $validacion['errores']);
            echo "<script>alert('Contraseña no segura:\\n\\n" . $errores . "'); window.history.back();</script>";
            exit;
        }

        // Hashear la contraseña solo después de validarla
        $contrasena = password_hash($contrasena_raw, PASSWORD_DEFAULT);

        // Usar prepared statement para insertar de forma segura.
        $stmt = $conexion->prepare("INSERT INTO usuarios (nickname, correo, telefono, contrasena) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $nickname, $correo, $telefono, $contrasena);
            if ($stmt->execute()) {
                // Registro exitoso — redirigir al login.
                header('Location: ../../front/login/login.html');
                exit;
            } else {
                echo "Error al registrar: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . htmlspecialchars($conexion->error);
        }

        // PROCESO DE LOGIN
        // Si la acción es 'login', entramos en este otro bloque.
    } elseif ($accion == 'login') {

        // Recogemos los datos del formulario de login.
        $nickname = $_POST['nickname'];
        $contrasena = $_POST['contrasena'];

        // Buscar usuario por nickname y comprobar contraseña con password_verify().
        $stmt = $conexion->prepare("SELECT id, nickname, contrasena FROM usuarios WHERE nickname = ?");
        if ($stmt) {
            $stmt->bind_param("s", $nickname);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                if (password_verify($contrasena, $row['contrasena'])) {
                    session_start();
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['nickname'] = $row['nickname'];
                    $_SESSION['flash'] = "¡Bienvenido, " . $row['nickname'] . "!";
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
            echo "Error en el servidor. Inténtalo más tarde.";
        }
    }

    // NUEVA ACCIÓN: CREAR RESERVA (ENVÍO DESDE EL FRONTEND)
    elseif ($accion == 'create_reserva') {
        // Devolver JSON
        header('Content-Type: application/json');

        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }

        $usuario_id = intval($_SESSION['user_id']);
        $reservas_json = isset($_POST['reservas']) ? $_POST['reservas'] : '[]';
        $items = json_decode($reservas_json, true);

        if (!is_array($items) || count($items) === 0) {
            echo json_encode(['success' => false, 'error' => 'Sin artículos para reservar']);
            exit;
        }

        // Transacción para insertar reserva y sus líneas
        mysqli_begin_transaction($conexion);
        try {
            $fecha = date('Y-m-d H:i:s');

            $stmt = $conexion->prepare("INSERT INTO reservas (fecha, usuario_id) VALUES (?, ?)");
            if (!$stmt) throw new Exception($conexion->error);
            $stmt->bind_param("si", $fecha, $usuario_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $reserva_id = $conexion->insert_id;
            $stmt->close();

            $stmt2 = $conexion->prepare("INSERT INTO lineareservas (reservas_id, producto_id) VALUES (?, ?)");
            if (!$stmt2) throw new Exception($conexion->error);

            foreach ($items as $it) {
                $pid = isset($it['id']) ? intval($it['id']) : 0;
                if ($pid <= 0) continue;
                $stmt2->bind_param("ii", $reserva_id, $pid);
                if (!$stmt2->execute()) throw new Exception($stmt2->error);
            }
            $stmt2->close();

            mysqli_commit($conexion);

            // Limpiar carrito si existe en la sesión
            session_start();
            if (isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            echo json_encode(['success' => true, 'reserva_id' => $reserva_id]);
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // ACCIÓN: ACTUALIZAR CARRITO EN LA SESIÓN
    elseif ($accion == 'update_cart') {
        header('Content-Type: application/json');
        session_start();
        $cart_json = isset($_POST['cart']) ? $_POST['cart'] : '[]';
        $cart = json_decode($cart_json, true);
        if (!is_array($cart)) {
            echo json_encode(['success' => false, 'error' => 'Formato de carrito no válido']);
            exit;
        }
        $_SESSION['cart'] = $cart;
        echo json_encode(['success' => true]);
        exit;
    }

} else {
    // CASO DE ERROR: PARAMETROS FALTANTES
    // Si alguien intenta entrar a este archivo directamente sin enviar datos POST.
    echo "Faltan parámetros.";
}
?>