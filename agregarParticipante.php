<?php
include('conexion.php');

if (isset($_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['ciudad'])) {
    $query = "INSERT INTO participantes (nombre, apellido, telefono, ciudad) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('ssss', $_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['ciudad']);
    
    if ($stmt->execute()) {
        echo "Nuevo participante agregado con éxito.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Faltan datos.";
}
$conexion->close();
?>