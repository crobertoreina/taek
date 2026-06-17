<?php
include('conexion.php');

if (isset($_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['ciudad'], $_POST['user'], $_POST['pass'], $_POST['level'])) {
    $query = "INSERT INTO jueces (nombre, apellido, telefono, ciudad, user, pass, level) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('ssssssi', $_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['ciudad'], $_POST['user'], $_POST['pass'], $_POST['level']);
    
    if ($stmt->execute()) {
        echo "Nuevo juez agregado con éxito.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Faltan datos.";
}
$conexion->close();
?>