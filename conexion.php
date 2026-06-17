<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDeDatos = "taekdb";

$conexion = new mysqli($servidor, $usuario, $clave, $baseDeDatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");

return $conexion;
