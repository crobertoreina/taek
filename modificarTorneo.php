<?php
header('Content-Type: application/json');

include('conexion.php');

if (isset($_POST['id'], $_POST['nombre'], $_POST['fecha'], $_POST['ciudad'])) {
    $query = "UPDATE torneos SET nombre = ?, fecha = ?, ciudad = ?, activo = ? WHERE idTorneo = ?";
    $stmt = $conexion->prepare($query);
    $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 1;
    $stmt->bind_param('sssii', $_POST['nombre'], $_POST['fecha'], $_POST['ciudad'], $activo, $_POST['id']);
    
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