<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$conn = require 'conexion.php';

$id_torneo = isset($_GET['id_torneo']) ? intval($_GET['id_torneo']) : 0;

if ($id_torneo <= 0) {
    echo json_encode([]);
    exit;
}

$query = "SELECT p.id, p.nombre, p.apellido, p.telefono, p.ciudad
           FROM participantes p
           INNER JOIN torneoparticipante tp ON p.id = tp.idParticipante
           WHERE tp.idTorneo = ?
           ORDER BY p.id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$result = $stmt->get_result();

$participantes = [];
while ($row = $result->fetch_assoc()) {
    $participantes[] = $row;
}

echo json_encode($participantes);

$stmt->close();
$conn->close();
?>