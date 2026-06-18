<?php
include('conexion.php');

if (isset($_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['ciudad'])) {
    $categoria = $_POST['categoria'] ?? null;
    $cinturon = $_POST['cinturon'] ?? null;
    $edad = isset($_POST['edad']) && is_numeric($_POST['edad']) ? intval($_POST['edad']) : null;
    $id_escuela = isset($_POST['id_escuela']) && is_numeric($_POST['id_escuela']) ? intval($_POST['id_escuela']) : null;
    $query = "INSERT INTO participantes (nombre, apellido, telefono, ciudad, edad, categoria, cinturon, id_escuela) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('ssssissi', $_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['ciudad'], $edad, $categoria, $cinturon, $id_escuela);
    
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