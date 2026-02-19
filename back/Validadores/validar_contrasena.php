<?php

/**
 * Valida una contraseña según criterios de seguridad
 * 
 * Requisitos:
 * - Mínimo 8 caracteres
 * - Al menos una mayúscula
 * - Al menos una minúscula
 * - Al menos un número
 * - Al menos un carácter especial (!@#$%^&*)
 * 
 * @param string $contrasena La contraseña a validar
 * @return array Array con 'valida' (bool) y 'errores' (array de mensajes)
 */
function validar_contrasena($contrasena) {
    $errores = [];
    $valida = true;

    // Validar longitud mínima
    if (strlen($contrasena) < 8) {
        $errores[] = "La contraseña debe tener mínimo 8 caracteres.";
        $valida = false;
    }

    // Validar que contenga al menos una mayúscula
    if (!preg_match('/[A-Z]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos una mayúscula.";
        $valida = false;
    }

    // Validar que contenga al menos una minúscula
    if (!preg_match('/[a-z]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos una minúscula.";
        $valida = false;
    }

    // Validar que contenga al menos un número
    if (!preg_match('/[0-9]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos un número.";
        $valida = false;
    }

    // Validar que contenga al menos un carácter especial
    if (!preg_match('/[!@#$%^&*\-_=+\[\]{};:\'",.<>?\/\\|`~]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos un carácter especial (!@#$%^&*-_=+).";
        $valida = false;
    }

    return [
        'valida' => $valida,
        'errores' => $errores
    ];
}

?>
