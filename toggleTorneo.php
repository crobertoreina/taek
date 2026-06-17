<?php
header('Content-Type: application/json');

include('conexion.php');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "UPDATE torneos SET activo = IF(activo = 1, 0, 1) WHERE idTorneo = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estado cambiado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
}
$conexion->close();
?>
