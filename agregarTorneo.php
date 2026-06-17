<?php
include('conexion.php');

if (isset($_POST['nombre'], $_POST['fecha'], $_POST['ciudad'])) {
    $query = "INSERT INTO torneos (nombre, fecha, ciudad) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('sss', $_POST['nombre'], $_POST['fecha'], $_POST['ciudad']);
    
    if ($stmt->execute()) {
        echo "Nuevo torneo agregado con éxito.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Faltan datos.";
}
$conexion->close();
?>