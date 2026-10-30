<?php
// CONEXIÓN A LA BASE DE DATOS
// Incluimos el archivo que contiene la configuración para conectarse a la base de datos (host, usuario, contraseña, nombre BD).
include '../Conexion_BD/conexion.php';

// INCLUIMOS EL VALIDADOR DE CONTRASEÑAS
include '../Validadores/validar_contrasena.php';

session_start();

// Límites del carrito (los mismos que en front/assets/js/app.js).
const MAX_CANTIDAD = 10;
const MAX_SEMANAS = 8;

// Redirige a una página del front añadiendo un parámetro (mensaje de éxito o de error).
function volver($pagina, $clave = null, $valor = '1')
{
    $url = '../../front/' . $pagina;
    if ($clave !== null) {
        $url .= '?' . $clave . '=' . urlencode($valor);
    }
    header('Location: ' . $url);
    exit;
}

// Convierte el precio guardado como texto ("15€") en número.
function precio_a_numero($precio)
{
    return (float) str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $precio));
}

// VERIFICACIÓN DE DATOS RECIBIDOS
// Comprobamos si el formulario nos ha enviado un campo llamado 'accion' (hidden input) para saber qué hacer.
if (isset($_POST['accion'])) {

    // Guardamos la acción en una variable para usarla más fácilmente.
    $accion = $_POST['accion'];

    try {

        // PROCESO DE REGISTRO
        if ($accion == 'registro') {

            // Recogemos los datos enviados desde el formulario de registro.
            $nickname = trim($_POST['nickname']);
            $contrasena_raw = $_POST['contrasena'];
            $correo = trim($_POST['correo']);
            $telefono = trim($_POST['telefono']);

            if ($nickname === '') {
                volver('registro/registro.html', 'error', 'Escribe un nombre de usuario.');
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                volver('registro/registro.html', 'error', 'El correo no es válido.');
            }

            // VALIDAR LA CONTRASEÑA
            $validacion = validar_contrasena($contrasena_raw);
            if (!$validacion['valida']) {
                volver('registro/registro.html', 'error', implode(' ', $validacion['errores']));
            }

            // Comprobar que el usuario no existe ya.
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nickname = ?");
            $stmt->bind_param("s", $nickname);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if ($existe) {
                volver('registro/registro.html', 'error', 'Ese nombre de usuario ya está en uso.');
            }

            // Hashear la contraseña solo después de validarla
            $contrasena = password_hash($contrasena_raw, PASSWORD_DEFAULT);

            // Usar prepared statement para insertar de forma segura.
            $stmt = $conexion->prepare("INSERT INTO usuarios (nickname, correo, telefono, contrasena) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nickname, $correo, $telefono, $contrasena);
            $stmt->execute();
            $stmt->close();

            // Registro exitoso — redirigir al login.
            volver('login/login.html', 'registrado');

        // PROCESO DE LOGIN
        } elseif ($accion == 'login') {

            // Recogemos los datos del formulario de login.
            $nickname = $_POST['nickname'];
            $contrasena = $_POST['contrasena'];

            // Buscar usuario por nickname y comprobar contraseña con password_verify().
            $stmt = $conexion->prepare("SELECT id, nickname, contrasena FROM usuarios WHERE nickname = ?");
            $stmt->bind_param("s", $nickname);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if ($row && password_verify($contrasena, $row['contrasena'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nickname'] = $row['nickname'];
                $_SESSION['flash'] = "¡Bienvenido, " . $row['nickname'] . "!";
                volver('inicio/inicio.php');
            }
            volver('login/login.html', 'error', 'Usuario o contraseña incorrectos.');

        // ACCIÓN: CREAR RESERVA (ENVÍO DESDE EL CARRITO)
        } elseif ($accion == 'create_reserva') {
            // Devolver JSON
            header('Content-Type: application/json');

            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Inicia sesión para reservar.']);
                exit;
            }

            $usuario_id = intval($_SESSION['user_id']);
            $reservas_json = isset($_POST['reservas']) ? $_POST['reservas'] : '[]';
            $items = json_decode($reservas_json, true);

            if (!is_array($items) || count($items) === 0) {
                echo json_encode(['success' => false, 'error' => 'No hay artículos para reservar.']);
                exit;
            }

            // Unificamos líneas repetidas y calculamos el total con los precios de la BD
            // (nunca nos fiamos del precio que envía el navegador).
            $lineas = [];
            $total = 0;
            $stmt_p = $conexion->prepare("SELECT precio FROM producto WHERE id = ?");
            foreach ($items as $it) {
                $pid = isset($it['id']) ? intval($it['id']) : 0;
                $cantidad = isset($it['qty']) ? max(1, min(MAX_CANTIDAD, intval($it['qty']))) : 1;
                $semanas = isset($it['weeks']) ? max(1, min(MAX_SEMANAS, intval($it['weeks']))) : 1;
                if ($pid <= 0) continue;

                $stmt_p->bind_param("i", $pid);
                $stmt_p->execute();
                $prod = $stmt_p->get_result()->fetch_assoc();
                if (!$prod) {
                    echo json_encode(['success' => false, 'error' => 'Uno de los productos ya no está disponible.']);
                    exit;
                }
                $lineas[] = ['pid' => $pid, 'cantidad' => $cantidad, 'semanas' => $semanas];
                $total += precio_a_numero($prod['precio']) * $cantidad * $semanas;
            }
            $stmt_p->close();

            if (count($lineas) === 0) {
                echo json_encode(['success' => false, 'error' => 'No hay artículos válidos para reservar.']);
                exit;
            }

            // Transacción para insertar reserva y sus líneas
            mysqli_begin_transaction($conexion);
            try {
                $fecha = date('Y-m-d H:i:s');

                $stmt = $conexion->prepare("INSERT INTO reservas (fecha, usuario_id, total) VALUES (?, ?, ?)");
                $stmt->bind_param("sid", $fecha, $usuario_id, $total);
                $stmt->execute();
                $reserva_id = $conexion->insert_id;
                $stmt->close();

                $stmt2 = $conexion->prepare("INSERT INTO lineareservas (reservas_id, producto_id, cantidad, semanas) VALUES (?, ?, ?, ?)");
                foreach ($lineas as $l) {
                    $stmt2->bind_param("iiii", $reserva_id, $l['pid'], $l['cantidad'], $l['semanas']);
                    $stmt2->execute();
                }
                $stmt2->close();

                mysqli_commit($conexion);
                echo json_encode(['success' => true, 'reserva_id' => $reserva_id, 'total' => $total]);
            } catch (Exception $e) {
                mysqli_rollback($conexion);
                throw $e;
            }
            exit;

        // ACCIÓN: ACTUALIZAR DATOS DE MI CUENTA
        } elseif ($accion == 'update_profile') {

            if (!isset($_SESSION['user_id'])) {
                volver('login/login.html');
            }
            $id = intval($_SESSION['user_id']);

            $nickname = trim($_POST['nickname']);
            $correo = trim($_POST['correo']);
            $telefono = trim($_POST['telefono']);
            $nueva = isset($_POST['new_password']) ? $_POST['new_password'] : '';

            if ($nickname === '') {
                volver('mi_cuenta/mi_cuenta.php', 'error', 'El nombre de usuario no puede estar vacío.');
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                volver('mi_cuenta/mi_cuenta.php', 'error', 'El correo no es válido.');
            }

            // El nickname no puede coincidir con el de otro usuario.
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nickname = ? AND id <> ?");
            $stmt->bind_param("si", $nickname, $id);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if ($existe) {
                volver('mi_cuenta/mi_cuenta.php', 'error', 'Ese nombre de usuario ya está en uso.');
            }

            if ($nueva !== '') {
                $validacion = validar_contrasena($nueva);
                if (!$validacion['valida']) {
                    volver('mi_cuenta/mi_cuenta.php', 'error', implode(' ', $validacion['errores']));
                }
                $hash = password_hash($nueva, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE usuarios SET nickname = ?, correo = ?, telefono = ?, contrasena = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $nickname, $correo, $telefono, $hash, $id);
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET nickname = ?, correo = ?, telefono = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nickname, $correo, $telefono, $id);
            }
            $stmt->execute();
            $stmt->close();

            $_SESSION['nickname'] = $nickname;
            volver('mi_cuenta/mi_cuenta.php', 'ok');
        }

    } catch (Throwable $e) {
        // Cualquier fallo inesperado de la base de datos.
        if ($accion == 'create_reserva') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar la reserva. Inténtalo de nuevo.']);
            exit;
        }
        $destino = ['registro' => 'registro/registro.html', 'login' => 'login/login.html'];
        volver(isset($destino[$accion]) ? $destino[$accion] : 'mi_cuenta/mi_cuenta.php', 'error', 'Error en el servidor. Inténtalo más tarde.');
    }

} else {
    // CASO DE ERROR: PARAMETROS FALTANTES
    // Si alguien intenta entrar a este archivo directamente sin enviar datos POST.
    echo "Faltan parámetros.";
}
?>
