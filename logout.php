<?php
// Iniciar sesión al principio del archivo
session_start();

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Eliminar la cookie de la sesión (si existiera)
setcookie(session_name(), '', time() - 3600, '/');

// Redirigir a la página de inicio de sesión
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Location: login.php");
exit(); // Asegurarse de que no haya más código ejecutándose después del redireccionamiento
?>

