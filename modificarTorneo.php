<?php
header('Content-Type: application/json');

include('conexion.php');

if (isset($_POST['id'], $_POST['nombre'], $_POST['fecha'], $_POST['ciudad'])) {
    $query = "UPDATE torneos SET nombre = ?, fecha = ?, ciudad = ? WHERE idTorneo = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('sssi', $_POST['nombre'], $_POST['fecha'], $_POST['ciudad'], $_POST['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Torneo modificado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
}
$conexion->close();
?>