<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado (por ejemplo, si hay una variable de sesión llamada 'user_id')
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión activa, redirigir al usuario a la página de inicio de sesión
    header("Location: login.php");
    exit();
}
else{
	header("Location: index.html");
    exit();
}
?>