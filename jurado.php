<?php
// Archivo: jurado.php

// Incluir la conexión a la base de datos
$conn = require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $edad = $_POST['edad'];
    $telefono = $_POST['telefono'];
    $ciudad = $_POST['ciudad'];

    // Consulta para insertar un nuevo jurado
    $sql = "INSERT INTO jurados (nombre, apellido, edad, telefono, ciudad)
            VALUES ('$nombre', '$apellido', '$edad', '$telefono', '$ciudad')";

    if ($conn->query($sql) === TRUE) {
        echo "Jurado registrado correctamente";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>

<form action="jurado.php" method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" required>

    <label for="apellido">Apellido:</label>
    <input type="text" name="apellido" id="apellido" required>

    <label for="edad">Edad:</label>
    <input type="number" name="edad" id="edad" required>

    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" id="telefono" required>

    <label for="ciudad">Ciudad:</label>
    <input type="text" name="ciudad" id="ciudad" required>

    <button type="submit">Registrar Jurado</button>
</form>
