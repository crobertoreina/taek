<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$conn = require 'conexion.php';

$id_torneo = isset($_POST['id_torneo']) ? intval($_POST['id_torneo']) : 0;
$id_juez = isset($_POST['id_juez']) ? intval($_POST['id_juez']) : 0;

if ($id_torneo <= 0 || $id_juez <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$query = "INSERT INTO torneojueces (idTorneo, idJuez) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_torneo, $id_juez);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Juez asignado al torneo']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>